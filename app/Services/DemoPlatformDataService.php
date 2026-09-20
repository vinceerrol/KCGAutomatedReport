<?php

namespace App\Services;

use App\Contracts\PlatformDataServiceInterface;
use App\Models\HourlyMetric;
use App\Models\Platform;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DemoPlatformDataService implements PlatformDataServiceInterface
{
    /**
     * Get hourly metrics for a given date and hour across all shops.
     */
    public function fetchHourlyMetrics(string $date, int $hour): Collection
    {
        $this->ensureMetricsExistForDate($date);

        return HourlyMetric::with(['shop.platform'])
            ->where('report_date', $date)
            ->where('hour', $hour)
            ->get();
    }

    /**
     * Get all platforms with their status and shops.
     */
    public function getPlatforms(): Collection
    {
        return Platform::withCount('shops')->orderBy('id')->get();
    }

    /**
     * Get all active shops, optionally filtered by platform.
     */
    public function getShops(?int $platformId = null): Collection
    {
        $query = Shop::with('platform');

        if ($platformId) {
            $query->where('platform_id', $platformId);
        }

        return $query->orderBy('platform_id')->orderBy('name')->get();
    }

    /**
     * Get platform summary aggregated for a given date (and optionally hour).
     */
    public function getPlatformSummary(string $date, ?int $hour = null): Collection
    {
        $this->ensureMetricsExistForDate($date);

        $platforms = Platform::with(['shops.hourlyMetrics' => function ($query) use ($date, $hour) {
            $query->where('report_date', $date);
            if ($hour !== null) {
                $query->where('hour', $hour);
            }
        }])->get();

        return $platforms->map(function ($platform) {
            $metrics = $platform->shops->flatMap->hourlyMetrics;

            $grossSales = round($metrics->sum(fn ($m) => (float) $m->gross_sales), 2);
            $discounts = round($metrics->sum(fn ($m) => (float) $m->discounts), 2);
            $refunds = round($metrics->sum(fn ($m) => (float) $m->refunds), 2);
            $netSales = round($grossSales - $discounts - $refunds, 2);

            return [
                'platform_id' => $platform->id,
                'name' => $platform->name,
                'code' => $platform->code,
                'status' => $platform->status,
                'shops_count' => $platform->shops->count(),
                'orders' => (int) $metrics->sum('orders'),
                'units_sold' => (int) $metrics->sum('units_sold'),
                'gross_sales' => $grossSales,
                'discounts' => $discounts,
                'refunds' => $refunds,
                'net_sales' => $netSales,
            ];
        });
    }

    /**
     * Get shop summary aggregated for a given date (and optionally hour).
     */
    public function getShopSummary(string $date, ?int $hour = null): Collection
    {
        $this->ensureMetricsExistForDate($date);

        $shops = Shop::with(['platform', 'hourlyMetrics' => function ($query) use ($date, $hour) {
            $query->where('report_date', $date);
            if ($hour !== null) {
                $query->where('hour', $hour);
            }
        }])->get();

        return $shops->map(function ($shop) {
            $metrics = $shop->hourlyMetrics;

            $grossSales = round($metrics->sum(fn ($m) => (float) $m->gross_sales), 2);
            $discounts = round($metrics->sum(fn ($m) => (float) $m->discounts), 2);
            $refunds = round($metrics->sum(fn ($m) => (float) $m->refunds), 2);
            $netSales = round($grossSales - $discounts - $refunds, 2);

            return [
                'shop_id' => $shop->id,
                'shop_name' => $shop->name,
                'shop_code' => $shop->code,
                'platform_id' => $shop->platform_id,
                'platform_name' => $shop->platform->name,
                'platform_code' => $shop->platform->code,
                'status' => $shop->status,
                'orders' => (int) $metrics->sum('orders'),
                'units_sold' => (int) $metrics->sum('units_sold'),
                'gross_sales' => $grossSales,
                'discounts' => $discounts,
                'refunds' => $refunds,
                'net_sales' => $netSales,
            ];
        });
    }

    /**
     * Get hourly performance timeline for a given date.
     */
    public function getHourlyPerformance(string $date): Collection
    {
        $this->ensureMetricsExistForDate($date);

        $metrics = HourlyMetric::where('report_date', $date)
            ->orderBy('hour')
            ->get()
            ->groupBy('hour');

        return $metrics->map(function ($items, $hour) {
            $grossSales = round($items->sum(fn ($m) => (float) $m->gross_sales), 2);
            $discounts = round($items->sum(fn ($m) => (float) $m->discounts), 2);
            $refunds = round($items->sum(fn ($m) => (float) $m->refunds), 2);
            $netSales = round($grossSales - $discounts - $refunds, 2);

            return [
                'hour' => (int) $hour,
                'orders' => (int) $items->sum('orders'),
                'units_sold' => (int) $items->sum('units_sold'),
                'gross_sales' => $grossSales,
                'discounts' => $discounts,
                'refunds' => $refunds,
                'net_sales' => $netSales,
            ];
        })->values();
    }

    /**
     * Get current vs previous hour comparison metrics.
     */
    public function getComparison(string $date, int $currentHour, int $previousHour): array
    {
        $this->ensureMetricsExistForDate($date);

        $currentMetrics = HourlyMetric::where('report_date', $date)
            ->where('hour', $currentHour)
            ->get();

        $previousMetrics = HourlyMetric::where('report_date', $date)
            ->where('hour', $previousHour)
            ->get();

        $currGross = round($currentMetrics->sum(fn ($m) => (float) $m->gross_sales), 2);
        $currDisc = round($currentMetrics->sum(fn ($m) => (float) $m->discounts), 2);
        $currRef = round($currentMetrics->sum(fn ($m) => (float) $m->refunds), 2);
        $currNet = round($currGross - $currDisc - $currRef, 2);
        $currOrders = (int) $currentMetrics->sum('orders');
        $currUnits = (int) $currentMetrics->sum('units_sold');

        $prevGross = round($previousMetrics->sum(fn ($m) => (float) $m->gross_sales), 2);
        $prevDisc = round($previousMetrics->sum(fn ($m) => (float) $m->discounts), 2);
        $prevRef = round($previousMetrics->sum(fn ($m) => (float) $m->refunds), 2);
        $prevNet = round($prevGross - $prevDisc - $prevRef, 2);
        $prevOrders = (int) $previousMetrics->sum('orders');
        $prevUnits = (int) $previousMetrics->sum('units_sold');

        return [
            'current' => [
                'hour' => $currentHour,
                'orders' => $currOrders,
                'units' => $currUnits,
                'gross_sales' => $currGross,
                'net_sales' => $currNet,
            ],
            'previous' => [
                'hour' => $previousHour,
                'orders' => $prevOrders,
                'units' => $prevUnits,
                'gross_sales' => $prevGross,
                'net_sales' => $prevNet,
            ],
            'diff' => [
                'orders' => $currOrders - $prevOrders,
                'units' => $currUnits - $prevUnits,
                'sales' => round($currNet - $prevNet, 2),
                'percent' => $prevNet > 0 ? round((($currNet - $prevNet) / $prevNet) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Ensure realistic hourly metrics exist for any requested date so prototype never displays zeroes.
     */
    protected function ensureMetricsExistForDate(string $date): void
    {
        if (HourlyMetric::where('report_date', $date)->exists()) {
            return;
        }

        $shops = Shop::all();
        if ($shops->isEmpty()) {
            return;
        }

        $isToday = ($date === Carbon::today()->format('Y-m-d'));
        $maxHour = $isToday ? max((int) Carbon::now()->hour, 11) : 23;

        foreach ($shops as $shop) {
            $isTikTok = ($shop->platform_id === 2);
            $startHour = $isTikTok ? 9 : 0;

            for ($h = $startHour; $h <= $maxHour; $h++) {
                $multiplier = 1.0;
                if ($h >= 11 && $h <= 13) $multiplier = 1.35; // Lunch peak
                if ($h >= 19 && $h <= 22) $multiplier = 1.60; // Evening peak
                if ($h < 6) $multiplier = 0.35;               // Late night

                $baseOrders = match ($shop->code) {
                    'shopee_a'           => 9,
                    'shopee_b'           => 6,
                    'shopee_c'           => 4,
                    'kgold_beauty'       => 16,
                    'pureherb'           => 9,
                    'natures_beauty_old' => 8,
                    'natures_beauty_new' => 4,
                    'midaxx_gen_shop'    => 2,
                    'mens_corner_ph'     => 2,
                    default              => 5,
                };

                $orders = max(1, (int) round($baseOrders * $multiplier));
                $units = (int) round($orders * 1.25);
                $avgOrderValue = match ($shop->code) {
                    'shopee_a'           => 380.00,
                    'shopee_b'           => 310.00,
                    'shopee_c'           => 280.00,
                    'kgold_beauty'       => 420.00,
                    'pureherb'           => 260.00,
                    'natures_beauty_old' => 240.00,
                    'natures_beauty_new' => 190.00,
                    'midaxx_gen_shop'    => 180.00,
                    'mens_corner_ph'     => 220.00,
                    default              => 250.00,
                };

                $hash = abs(crc32($shop->code . '_' . $h . '_' . $date)) % 20;
                $gross = round($orders * $avgOrderValue * (0.90 + ($hash / 100)), 2);
                $disc = round($gross * 0.035, 2);
                $net = round($gross - $disc, 2);
                $adSpend = round($gross * 0.22, 2);

                HourlyMetric::firstOrCreate(
                    [
                        'shop_id'     => $shop->id,
                        'report_date' => $date,
                        'hour'        => $h,
                    ],
                    [
                        'orders'      => $orders,
                        'units_sold'  => $units,
                        'ad_spend'    => $adSpend,
                        'gross_sales' => $gross,
                        'discounts'   => $disc,
                        'refunds'     => 0.00,
                        'net_sales'   => $net,
                    ]
                );
            }
        }
    }
}
