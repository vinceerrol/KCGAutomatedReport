<?php

namespace Database\Seeders;

use App\Models\HourlyMetric;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HourlyMetricSeeder extends Seeder
{
    /**
     * Run the database seeds for Shopee shops.
     */
    public function run(): void
    {
        $shopeeShops = Shop::whereHas('platform', fn ($q) => $q->where('code', 'shopee'))->get();
        if ($shopeeShops->isEmpty()) {
            return;
        }

        $datesToSeed = [
            Carbon::yesterday()->format('Y-m-d') => 23,
            Carbon::today()->format('Y-m-d') => max((int) Carbon::now()->hour, 11),
        ];

        // Also ensure 2026-09-18 and 2026-09-19 reference days exist
        $datesToSeed['2026-09-18'] = 23;
        $datesToSeed['2026-09-19'] = 23;

        foreach ($datesToSeed as $date => $maxHour) {
            for ($h = 0; $h <= $maxHour; $h++) {
                $multiplier = 1.0;
                if ($h >= 11 && $h <= 13) $multiplier = 1.45; // Lunch peak
                if ($h >= 19 && $h <= 22) $multiplier = 1.65; // Evening peak
                if ($h < 6) $multiplier = 0.35;               // Late night

                foreach ($shopeeShops as $shop) {
                    $baseOrders = match ($shop->code) {
                        'shopee_a' => 8,
                        'shopee_b' => 5,
                        'shopee_c' => 4,
                        default    => 4,
                    };

                    $orders = max(1, (int) round($baseOrders * $multiplier));
                    $units = (int) round($orders * 1.3);
                    $avgPrice = match ($shop->code) {
                        'shopee_a' => 380.00,
                        'shopee_b' => 310.00,
                        'shopee_c' => 280.00,
                        default    => 250.00,
                    };

                    $hash = abs(crc32($shop->code . '_' . $h . '_' . $date)) % 15;
                    $gross = round($orders * $avgPrice * (0.92 + ($hash / 100)), 2);
                    $disc = round($gross * 0.035, 2);
                    $net = round($gross - $disc, 2);
                    $adSpend = round($gross * 0.18, 2);

                    HourlyMetric::updateOrCreate(
                        ['shop_id' => $shop->id, 'report_date' => $date, 'hour' => $h],
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
}
