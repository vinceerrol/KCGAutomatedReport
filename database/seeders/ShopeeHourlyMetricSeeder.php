<?php

namespace Database\Seeders;

use App\Models\HourlyMetric;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class ShopeeHourlyMetricSeeder extends Seeder
{
    /**
     * Seed hourly metrics for Shopee shops matching realistic marketplace progression.
     */
    public function run(): void
    {
        $targetDate = '2026-09-19';

        $shops = [
            'shopee_kgold'    => Shop::where('code', 'shopee_kgold')->first(),
            'shopee_pureherb' => Shop::where('code', 'shopee_pureherb')->first(),
            'shopee_natures'  => Shop::where('code', 'shopee_natures')->first(),
            'shopee_midaxx'   => Shop::where('code', 'shopee_midaxx')->first(),
        ];

        // Ensure shops exist
        foreach ($shops as $code => $shop) {
            if (!$shop) {
                $this->command->error("Shop {$code} not found! Please run ShopSeeder first.");
                return;
            }
        }

        // Data dictionary for hours 9 (9:00 AM) to 23 (11:00 PM)
        $hourlyData = [
            9 => [
                'shopee_kgold'    => ['spend' => 6200.00,  'orders' => 185, 'sales' => 36400.00],
                'shopee_pureherb' => ['spend' => 2850.00,  'orders' => 82,  'sales' => 17200.00],
                'shopee_natures'  => ['spend' => 2100.00,  'orders' => 64,  'sales' => 13800.00],
                'shopee_midaxx'   => ['spend' => 520.00,   'orders' => 14,  'sales' => 2900.00],
            ],
            10 => [
                'shopee_kgold'    => ['spend' => 8400.00,  'orders' => 220, 'sales' => 45100.00],
                'shopee_pureherb' => ['spend' => 3700.00,  'orders' => 105, 'sales' => 21800.00],
                'shopee_natures'  => ['spend' => 2950.00,  'orders' => 88,  'sales' => 18400.00],
                'shopee_midaxx'   => ['spend' => 740.00,   'orders' => 21,  'sales' => 4100.00],
            ],
            11 => [
                'shopee_kgold'    => ['spend' => 11200.00, 'orders' => 275, 'sales' => 58200.00],
                'shopee_pureherb' => ['spend' => 4900.00,  'orders' => 130, 'sales' => 27600.00],
                'shopee_natures'  => ['spend' => 4100.00,  'orders' => 112, 'sales' => 23900.00],
                'shopee_midaxx'   => ['spend' => 980.00,   'orders' => 28,  'sales' => 5600.00],
            ],
            12 => [
                'shopee_kgold'    => ['spend' => 13800.00, 'orders' => 320, 'sales' => 69500.00],
                'shopee_pureherb' => ['spend' => 5800.00,  'orders' => 148, 'sales' => 32100.00],
                'shopee_natures'  => ['spend' => 5200.00,  'orders' => 135, 'sales' => 28400.00],
                'shopee_midaxx'   => ['spend' => 1250.00,  'orders' => 34,  'sales' => 6800.00],
            ],
            13 => [
                'shopee_kgold'    => ['spend' => 16100.00, 'orders' => 365, 'sales' => 78900.00],
                'shopee_pureherb' => ['spend' => 6900.00,  'orders' => 170, 'sales' => 36800.00],
                'shopee_natures'  => ['spend' => 6100.00,  'orders' => 152, 'sales' => 32500.00],
                'shopee_midaxx'   => ['spend' => 1500.00,  'orders' => 40,  'sales' => 7900.00],
            ],
            14 => [
                'shopee_kgold'    => ['spend' => 18400.00, 'orders' => 410, 'sales' => 88400.00],
                'shopee_pureherb' => ['spend' => 7950.00,  'orders' => 192, 'sales' => 41200.00],
                'shopee_natures'  => ['spend' => 7150.00,  'orders' => 174, 'sales' => 36900.00],
                'shopee_midaxx'   => ['spend' => 1720.00,  'orders' => 46,  'sales' => 9100.00],
            ],
            15 => [
                'shopee_kgold'    => ['spend' => 20800.00, 'orders' => 455, 'sales' => 98100.00],
                'shopee_pureherb' => ['spend' => 9100.00,  'orders' => 215, 'sales' => 46500.00],
                'shopee_natures'  => ['spend' => 8200.00,  'orders' => 195, 'sales' => 41800.00],
                'shopee_midaxx'   => ['spend' => 2010.00,  'orders' => 52,  'sales' => 10300.00],
            ],
            16 => [
                'shopee_kgold'    => ['spend' => 23400.00, 'orders' => 505, 'sales' => 108900.00],
                'shopee_pureherb' => ['spend' => 10300.00, 'orders' => 238, 'sales' => 51400.00],
                'shopee_natures'  => ['spend' => 9300.00,  'orders' => 218, 'sales' => 46700.00],
                'shopee_midaxx'   => ['spend' => 2280.00,  'orders' => 58,  'sales' => 11500.00],
            ],
            17 => [
                'shopee_kgold'    => ['spend' => 26100.00, 'orders' => 555, 'sales' => 119800.00],
                'shopee_pureherb' => ['spend' => 11500.00, 'orders' => 262, 'sales' => 56800.00],
                'shopee_natures'  => ['spend' => 10400.00, 'orders' => 240, 'sales' => 51600.00],
                'shopee_midaxx'   => ['spend' => 2550.00,  'orders' => 64,  'sales' => 12700.00],
            ],
            18 => [
                'shopee_kgold'    => ['spend' => 28900.00, 'orders' => 610, 'sales' => 131500.00],
                'shopee_pureherb' => ['spend' => 12700.00, 'orders' => 288, 'sales' => 62300.00],
                'shopee_natures'  => ['spend' => 11600.00, 'orders' => 264, 'sales' => 56800.00],
                'shopee_midaxx'   => ['spend' => 2850.00,  'orders' => 70,  'sales' => 13900.00],
            ],
            19 => [
                'shopee_kgold'    => ['spend' => 31900.00, 'orders' => 670, 'sales' => 144200.00],
                'shopee_pureherb' => ['spend' => 14000.00, 'orders' => 315, 'sales' => 68200.00],
                'shopee_natures'  => ['spend' => 12800.00, 'orders' => 290, 'sales' => 62100.00],
                'shopee_midaxx'   => ['spend' => 3150.00,  'orders' => 76,  'sales' => 15100.00],
            ],
            20 => [
                'shopee_kgold'    => ['spend' => 34800.00, 'orders' => 725, 'sales' => 156100.00],
                'shopee_pureherb' => ['spend' => 15300.00, 'orders' => 342, 'sales' => 73900.00],
                'shopee_natures'  => ['spend' => 14000.00, 'orders' => 315, 'sales' => 67500.00],
                'shopee_midaxx'   => ['spend' => 3450.00,  'orders' => 82,  'sales' => 16300.00],
            ],
            21 => [
                'shopee_kgold'    => ['spend' => 37200.00, 'orders' => 770, 'sales' => 165800.00],
                'shopee_pureherb' => ['spend' => 16400.00, 'orders' => 365, 'sales' => 78800.00],
                'shopee_natures'  => ['spend' => 15000.00, 'orders' => 338, 'sales' => 72200.00],
                'shopee_midaxx'   => ['spend' => 3750.00,  'orders' => 87,  'sales' => 17100.00],
            ],
            22 => [
                'shopee_kgold'    => ['spend' => 39500.00, 'orders' => 805, 'sales' => 173500.00],
                'shopee_pureherb' => ['spend' => 17400.00, 'orders' => 385, 'sales' => 83200.00],
                'shopee_natures'  => ['spend' => 15900.00, 'orders' => 358, 'sales' => 76400.00],
                'shopee_midaxx'   => ['spend' => 4050.00,  'orders' => 91,  'sales' => 17800.00],
            ],
            23 => [
                'shopee_kgold'    => ['spend' => 41200.00, 'orders' => 830, 'sales' => 179200.00],
                'shopee_pureherb' => ['spend' => 18200.00, 'orders' => 398, 'sales' => 86100.00],
                'shopee_natures'  => ['spend' => 16500.00, 'orders' => 372, 'sales' => 79500.00],
                'shopee_midaxx'   => ['spend' => 4250.00,  'orders' => 94,  'sales' => 18200.00],
            ],
        ];

        foreach ($hourlyData as $hour => $shopData) {
            foreach ($shopData as $shopCode => $data) {
                $shop = $shops[$shopCode];

                HourlyMetric::updateOrCreate(
                    [
                        'shop_id'     => $shop->id,
                        'report_date' => $targetDate,
                        'hour'        => $hour,
                    ],
                    [
                        'orders'      => $data['orders'],
                        'units_sold'  => (int)round($data['orders'] * 1.3),
                        'ad_spend'    => $data['spend'],
                        'gross_sales' => $data['sales'],
                        'discounts'   => 0.00,
                        'refunds'     => 0.00,
                        'net_sales'   => $data['sales'],
                    ]
                );
            }
        }
    }
}
