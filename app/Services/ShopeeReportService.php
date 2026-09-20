<?php

namespace App\Services;

use App\Models\AutomationLog;
use App\Models\HourlyMetric;
use App\Models\Platform;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShopeeReportService
{
    /**
     * Hours tracked in the Shopee Hourly Breakdown report.
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
     * Official Shopee shop codes and display labels.
     */
    public const SHOPEE_SHOPS = [
        'shopee_kgold'    => 'KGOLD BEAUTY (Shopee Mall)',
        'shopee_pureherb' => 'PUREHERB OFFICIAL',
        'shopee_natures'  => 'NATURES BEAUTY PH',
        'shopee_midaxx'   => 'MIDAXX GEN SHOP',
    ];

    /**
     * Default daily sales target in PHP for Shopee.
     */
    public const DEFAULT_DAILY_TARGET = 500000.00;

    /**
     * Get the complete Shopee Hourly Breakdown data structure.
     */
    public function getHourlyBreakdown(?string $date = null, ?float $target = null): array
    {
        $targetDate = $date ?? Carbon::today()->format('Y-m-d');
        $dailyTarget = $target !== null && $target > 0 ? $target : self::DEFAULT_DAILY_TARGET;

        // Fetch official Shopee platform & shops
        $platform = Platform::where('code', 'shopee')->first();
        $targetCodes = array_keys(self::SHOPEE_SHOPS);

        $shops = Shop::where('platform_id', $platform?->id)
            ->whereIn('code', $targetCodes)
            ->orderByRaw("FIELD(code, '" . implode("','", $targetCodes) . "')")
            ->get();

        // Fallback: If newer codes not seeded yet, fetch any shops belonging to Shopee platform
        if ($shops->isEmpty() && $platform) {
            $shops = Shop::where('platform_id', $platform->id)->get();
        }

        // Fetch hourly metrics for this date
        $metrics = HourlyMetric::whereIn('shop_id', $shops->pluck('id'))
            ->where('report_date', $targetDate)
            ->get()
            ->groupBy(function ($item) {
                $hour = (int) $item->hour;
                return $item->shop_id . '_' . $hour;
            });

        $hours = self::REPORT_HOURS;
        $shopsData = [];
        $hourlyTotals = [];

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
                    $hourlyTotals[$hourKey]['sales_increment'] = null;
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

        // Calculate summary metrics for latest active hour
        if ($latestActiveHour !== null) {
            foreach ($shopsData as $s) {
                $latestTotalSpend += ($s['metrics']['ad_spend'][$latestActiveHour] ?? 0);
                $latestTotalOrders += ($s['metrics']['orders'][$latestActiveHour] ?? 0);
            }
        }

        $blendedRoas = $latestTotalSpend > 0 ? round($latestTotalSales / $latestTotalSpend, 2) : 0.00;
        $targetProgress = $dailyTarget > 0 ? round(($latestTotalSales / $dailyTarget) * 100, 1) : 0.0;

        return [
            'meta' => [
                'title'          => 'SHOPEE — HOURLY BREAKDOWN (GMV per shop by hour)',
                'platform'       => 'Shopee',
                'report_date'    => $targetDate,
                'formatted_date' => Carbon::parse($targetDate)->format('F d, Y'),
                'daily_target'   => $dailyTarget,
                'latest_hour'    => $latestActiveHour ? $hours[$latestActiveHour] : null,
                'source'         => 'Shopee Open Platform Automated Reporting Pipeline',
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
     * Simulate or automate the 12:00 AM (midnight) snapshot for Shopee.
     */
    public function simulateMidnight(?string $date = null): void
    {
        $targetDate = $date ?? Carbon::today()->format('Y-m-d');
        $platform = Platform::where('code', 'shopee')->first();
        if (!$platform) return;

        $shops = Shop::where('platform_id', $platform->id)->get()->keyBy('code');

        $midnightData = [
            'shopee_kgold'    => ['spend' => 42000.00, 'orders' => 840, 'sales' => 175000.00],
            'shopee_pureherb' => ['spend' => 19500.00, 'orders' => 410, 'sales' => 89500.00],
            'shopee_natures'  => ['spend' => 16800.00, 'orders' => 380, 'sales' => 82000.00],
            'shopee_midaxx'   => ['spend' => 4500.00,  'orders' => 95,  'sales' => 18400.00],
            // Fallback aliases if legacy codes are used
            'shopee_a'        => ['spend' => 42000.00, 'orders' => 840, 'sales' => 175000.00],
            'shopee_b'        => ['spend' => 19500.00, 'orders' => 410, 'sales' => 89500.00],
            'shopee_c'        => ['spend' => 16800.00, 'orders' => 380, 'sales' => 82000.00],
        ];

        foreach ($midnightData as $code => $d) {
            if ($shop = $shops->get($code)) {
                HourlyMetric::updateOrCreate(
                    ['shop_id' => $shop->id, 'report_date' => $targetDate, 'hour' => 24],
                    [
                        'orders'      => $d['orders'],
                        'units_sold'  => (int)round($d['orders'] * 1.3),
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
            'job'        => 'Shopee Hourly Automation',
            'status'     => 'SUCCESS',
            'message'    => "Updated Shopee midnight (12:00 AM) hourly breakdown for {$date}",
            'created_at' => Carbon::now(),
        ]);
    }
}
