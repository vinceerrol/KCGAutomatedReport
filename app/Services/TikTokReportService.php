<?php

namespace App\Services;

use App\Models\AutomationLog;
use App\Models\HourlyMetric;
use App\Models\Platform;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TikTokReportService
{
    /**
     * Hours tracked in the TikTok Hourly Breakdown report.
     * Hours 9 (9:00 AM) to 24 (12:00 AM Midnight).
     */
    public const REPORT_HOURS = [
        9  => '9:00 AM',
        10 => '10:00 AM',
        11 => '11:00 AM',
        12 => '12:00 PM',
        13 => '1:00 PM',
        14 => '2:00 PM',
        15 => '3:00 PM',
        16 => '4:00 PM',
        17 => '5:00 PM',
        18 => '6:00 PM',
        19 => '7:00 PM',
        20 => '8:00 PM',
        21 => '9:00 PM',
        22 => '10:00 PM',
        23 => '11:00 PM',
        24 => '12:00 AM',
    ];

    /**
     * Official TikTok shop codes and display order.
     */
    public const TIKTOK_SHOPS = [
        'kgold_beauty'       => 'KGOLD BEAUTY',
        'pureherb'           => 'PUREHERB',
        'natures_beauty_old' => 'NATURES BEAUTY (Old)',
        'natures_beauty_new' => 'NATURES BEAUTY (New)',
        'midaxx_gen_shop'    => 'MIDAXX GEN SHOP',
        'mens_corner_ph'     => 'MENS CORNER PH',
    ];

    /**
     * Default daily sales target in PHP (implied ~₱670,000 based on ₱335,003 = 50%).
     */
    public const DEFAULT_DAILY_TARGET = 670000.00;

    /**
     * Get the complete TikTok Hourly Breakdown data structure.
     */
    public function getHourlyBreakdown(?string $date = null, ?float $target = null): array
    {
        $targetDate = $date ?? Carbon::today()->format('Y-m-d');
        $dailyTarget = $target !== null && $target > 0 ? $target : self::DEFAULT_DAILY_TARGET;

        // Fetch official TikTok shops
        $shops = Shop::whereIn('code', array_keys(self::TIKTOK_SHOPS))
            ->orderByRaw("FIELD(code, '" . implode("','", array_keys(self::TIKTOK_SHOPS)) . "')")
            ->get();

        // Fetch hourly metrics for this date
        $metrics = HourlyMetric::whereIn('shop_id', $shops->pluck('id'))
            ->where('report_date', $targetDate)
            ->get()
            ->groupBy(function ($item) {
                // Map hour 0 to 24 if entered as midnight
                $hour = (int)$item->hour;
                return $item->shop_id . '_' . $hour;
            });

        $hours = self::REPORT_HOURS;
        $shopsData = [];
        $hourlyTotals = [];
        $previousHourTotal = null;

        // Initialize hourly total sums
        foreach ($hours as $hourKey => $hourLabel) {
            $hourlyTotals[$hourKey] = [
                'hour'            => $hourKey,
                'label'           => $hourLabel,
                'total_sales'     => 0.00,
                'sales_increment' => null,
                'vs_daily_target' => 0.0,
                'has_data'        => false,
            ];
        }

        // Build shop rows
        foreach ($shops as $shop) {
            $adSpendRow = [];
            $ordersRow  = [];
            $salesRow   = [];
            $roasRow    = [];

            foreach ($hours as $hourKey => $hourLabel) {
                // Look up in database (check hourKey or 0 for midnight)
                $key = $shop->id . '_' . $hourKey;
                $metric = $metrics->get($key)?->first();

                if (!$metric && $hourKey === 24) {
                    $keyAlt = $shop->id . '_0';
                    $metric = $metrics->get($keyAlt)?->first();
                }

                if ($metric) {
                    $adSpend = (float) $metric->ad_spend;
                    $orders  = (int) $metric->orders;
                    $sales   = (float) $metric->gross_sales;
                    $roas    = $adSpend > 0 ? round($sales / $adSpend, 2) : 0.00;

                    $adSpendRow[$hourKey] = $adSpend;
                    $ordersRow[$hourKey]  = $orders;
                    $salesRow[$hourKey]   = $sales;
                    $roasRow[$hourKey]    = $roas;

                    // Accumulate into hourly totals
                    $hourlyTotals[$hourKey]['total_sales'] += $sales;
                    $hourlyTotals[$hourKey]['has_data'] = true;
                } else {
                    $adSpendRow[$hourKey] = null;
                    $ordersRow[$hourKey]  = null;
                    $salesRow[$hourKey]   = null;
                    $roasRow[$hourKey]    = null;
                }
            }

            $shopsData[] = [
                'shop_id'   => $shop->id,
                'shop_code' => $shop->code,
                'shop_name' => $shop->name,
                'metrics'   => [
                    'ad_spend' => $adSpendRow,
                    'orders'   => $ordersRow,
                    'sales'    => $salesRow,
                    'roas'     => $roasRow,
                ],
            ];
        }

        // Calculate sales increment and vs target for each hour
        $firstHour = true;
        $prevTotal = null;
        $latestActiveHour = null;
        $latestTotalSales = 0.00;
        $latestTotalSpend = 0.00;
        $latestTotalOrders = 0;

        foreach ($hours as $hourKey => $hourLabel) {
            $hasData = $hourlyTotals[$hourKey]['has_data'];
            $totalSales = $hourlyTotals[$hourKey]['total_sales'];

            if ($hasData) {
                $latestActiveHour = $hourKey;
                $latestTotalSales = $totalSales;

                if ($firstHour) {
                    $hourlyTotals[$hourKey]['sales_increment'] = null; // Blank for first hour
                    $firstHour = false;
                } else {
                    $hourlyTotals[$hourKey]['sales_increment'] = $prevTotal !== null ? round($totalSales - $prevTotal, 2) : null;
                }

                $percentage = $dailyTarget > 0 ? round(($totalSales / $dailyTarget) * 100) : 0;
                $hourlyTotals[$hourKey]['vs_daily_target'] = (int) $percentage;
                $prevTotal = $totalSales;
            } else {
                $hourlyTotals[$hourKey]['total_sales'] = null;
                $hourlyTotals[$hourKey]['sales_increment'] = null;
                $hourlyTotals[$hourKey]['vs_daily_target'] = null;
            }
        }

        // Calculate summary metrics for the latest active hour
        if ($latestActiveHour !== null) {
            foreach ($shopsData as $s) {
                $spend = $s['metrics']['ad_spend'][$latestActiveHour] ?? 0.00;
                $ord   = $s['metrics']['orders'][$latestActiveHour] ?? 0;
                $latestTotalSpend += (float) $spend;
                $latestTotalOrders += (int) $ord;
            }
        }

        $blendedRoas = $latestTotalSpend > 0 ? round($latestTotalSales / $latestTotalSpend, 2) : 0.00;
        $targetProgress = $dailyTarget > 0 ? round(($latestTotalSales / $dailyTarget) * 100, 1) : 0.0;

        return [
            'meta' => [
                'title'          => 'TIKTOK — HOURLY BREAKDOWN (GMV per shop by hour)',
                'report_date'    => $targetDate,
                'formatted_date' => Carbon::parse($targetDate)->format('F d, Y'),
                'daily_target'   => $dailyTarget,
                'latest_hour'    => $latestActiveHour ? ($hours[$latestActiveHour] ?? null) : null,
                'source'         => 'TikTok Shop Automated Reporting Pipeline',
            ],
            'hours' => $hours,
            'kpis' => [
                'total_gmv'       => round($latestTotalSales, 2),
                'total_ad_spend'  => round($latestTotalSpend, 2),
                'blended_roas'    => $blendedRoas,
                'total_orders'    => $latestTotalOrders,
                'daily_target'    => $dailyTarget,
                'target_progress' => $targetProgress,
            ],
            'shops' => $shopsData,
            'summary' => [
                'total_sales'     => array_column($hourlyTotals, 'total_sales', 'hour'),
                'sales_increment' => array_column($hourlyTotals, 'sales_increment', 'hour'),
                'vs_daily_target' => array_column($hourlyTotals, 'vs_daily_target', 'hour'),
            ],
        ];
    }

    /**
     * Simulate or automate the 12:00 AM (midnight) snapshot to complete the 24-hour cycle.
     */
    public function simulateMidnight(?string $date = null): void
    {
        $targetDate = $date ?? Carbon::today()->format('Y-m-d');
        $shops = Shop::whereIn('code', array_keys(self::TIKTOK_SHOPS))->get()->keyBy('code');

        // Projected midnight incremental performance (final flash rush)
        $midnightData = [
            'kgold_beauty'       => ['spend' => 54800.00, 'orders' => 935, 'sales' => 195200.00],
            'pureherb'           => ['spend' => 14450.00, 'orders' => 310, 'sales' => 65800.00],
            'natures_beauty_old' => ['spend' => 15200.00, 'orders' => 340, 'sales' => 78200.00],
            'natures_beauty_new' => ['spend' => 1750.00,  'orders' => 35,  'sales' => 7450.00],
            'midaxx_gen_shop'    => ['spend' => 205.00,   'orders' => 11,  'sales' => 2890.00],
            'mens_corner_ph'     => ['spend' => 450.00,   'orders' => 5,   'sales' => 1150.00],
        ];

        foreach ($midnightData as $code => $d) {
            if ($shop = $shops->get($code)) {
                HourlyMetric::updateOrCreate(
                    ['shop_id' => $shop->id, 'report_date' => $targetDate, 'hour' => 24],
                    [
                        'orders'      => $d['orders'],
                        'units_sold'  => (int)round($d['orders'] * 1.25),
                        'ad_spend'    => $d['spend'],
                        'gross_sales' => $d['sales'],
                        'discounts'   => 0.00,
                        'refunds'     => 0.00,
                        'net_sales'   => $d['sales'],
                    ]
                );
            }
        }

        AutomationLog::create([
            'job'        => 'TikTok Hourly Automation',
            'status'     => 'SUCCESS',
            'message'    => "Updated TikTok midnight (12:00 AM) hourly breakdown for {$date}",
            'created_at' => Carbon::now(),
        ]);
    }
}
