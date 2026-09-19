<?php

namespace Database\Seeders;

use App\Models\HourlyMetric;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HourlyMetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shopeeA = Shop::where('code', 'shopee_a')->first();
        $shopeeB = Shop::where('code', 'shopee_b')->first();
        $shopeeC = Shop::where('code', 'shopee_c')->first();
        $tiktokA = Shop::where('code', 'tiktok_a')->first();
        $tiktokB = Shop::where('code', 'tiktok_b')->first();

        // Target dates according to Philippine Standard Time
        $today = Carbon::today()->format('Y-m-d'); // 2026-09-19
        $yesterday = Carbon::yesterday()->format('Y-m-d'); // 2026-09-18

        // Seed historical hours for Yesterday (September 18, 2026 - full business day: 8 AM to 11 PM)
        for ($h = 8; $h <= 23; $h++) {
            $multiplier = 1.0;
            if ($h >= 11 && $h <= 13) $multiplier = 1.4; // Lunch peak
            if ($h >= 19 && $h <= 22) $multiplier = 1.6; // Evening campaign & livestream peak

            $shopsData = [
                ['shop_id' => $shopeeA->id, 'orders' => (int)round(6 * $multiplier), 'units' => (int)round(8 * $multiplier), 'gross' => round(2200 * $multiplier, 2), 'disc' => round(70 * $multiplier, 2), 'ref' => 0.00],
                ['shop_id' => $shopeeB->id, 'orders' => (int)round(4 * $multiplier), 'units' => (int)round(5 * $multiplier), 'gross' => round(1300 * $multiplier, 2), 'disc' => round(40 * $multiplier, 2), 'ref' => 0.00],
                ['shop_id' => $shopeeC->id, 'orders' => (int)round(3 * $multiplier), 'units' => (int)round(4 * $multiplier), 'gross' => round(900 * $multiplier, 2),  'disc' => round(30 * $multiplier, 2), 'ref' => 0.00],
                ['shop_id' => $tiktokA->id, 'orders' => (int)round(5 * $multiplier), 'units' => (int)round(7 * $multiplier), 'gross' => round(1600 * $multiplier, 2), 'disc' => round(50 * $multiplier, 2), 'ref' => 0.00],
                ['shop_id' => $tiktokB->id, 'orders' => (int)round(3 * $multiplier), 'units' => (int)round(4 * $multiplier), 'gross' => round(850 * $multiplier, 2),  'disc' => round(25 * $multiplier, 2), 'ref' => 0.00],
            ];

            foreach ($shopsData as $s) {
                $net = round($s['gross'] - $s['disc'] - $s['ref'], 2);
                HourlyMetric::updateOrCreate(
                    ['shop_id' => $s['shop_id'], 'report_date' => $yesterday, 'hour' => $h],
                    [
                        'orders' => $s['orders'],
                        'units_sold' => $s['units'],
                        'gross_sales' => $s['gross'],
                        'discounts' => $s['disc'],
                        'refunds' => $s['ref'],
                        'net_sales' => $net,
                    ]
                );
            }
        }

        // Seed hours for Today (September 19, 2026) up to current time (09:00 AM PHT)
        $todayHours = [
            // Hour 0 (12:00 AM Midnight - Flash Sale)
            [
                'hour' => 0,
                'shops' => [
                    ['shop_id' => $shopeeA->id, 'orders' => 6,  'units' => 8,  'gross' => 2400.00, 'disc' => 80.00,  'ref' => 0.00,  'net' => 2320.00],
                    ['shop_id' => $shopeeB->id, 'orders' => 4,  'units' => 5,  'gross' => 1400.00, 'disc' => 45.00,  'ref' => 0.00,  'net' => 1355.00],
                    ['shop_id' => $shopeeC->id, 'orders' => 3,  'units' => 4,  'gross' => 950.00,  'disc' => 30.00,  'ref' => 0.00,  'net' => 920.00],
                    ['shop_id' => $tiktokA->id, 'orders' => 5,  'units' => 6,  'gross' => 1750.00, 'disc' => 55.00,  'ref' => 0.00,  'net' => 1695.00],
                    ['shop_id' => $tiktokB->id, 'orders' => 2,  'units' => 3,  'gross' => 700.00,  'disc' => 20.00,  'ref' => 0.00,  'net' => 680.00],
                ]
            ],
            // Hour 1 (01:00 AM - Post-Midnight Orders)
            [
                'hour' => 1,
                'shops' => [
                    ['shop_id' => $shopeeA->id, 'orders' => 7,  'units' => 9,  'gross' => 2800.00, 'disc' => 90.00,  'ref' => 0.00,  'net' => 2710.00],
                    ['shop_id' => $shopeeB->id, 'orders' => 5,  'units' => 6,  'gross' => 1650.00, 'disc' => 50.00,  'ref' => 0.00,  'net' => 1600.00],
                    ['shop_id' => $shopeeC->id, 'orders' => 3,  'units' => 4,  'gross' => 1050.00, 'disc' => 35.00,  'ref' => 0.00,  'net' => 1015.00],
                    ['shop_id' => $tiktokA->id, 'orders' => 6,  'units' => 8,  'gross' => 2000.00, 'disc' => 65.00,  'ref' => 0.00,  'net' => 1935.00],
                    ['shop_id' => $tiktokB->id, 'orders' => 3,  'units' => 4,  'gross' => 900.00,  'disc' => 30.00,  'ref' => 0.00,  'net' => 870.00],
                ]
            ],
            // Hour 2 (02:00 AM)
            [
                'hour' => 2,
                'shops' => [
                    ['shop_id' => $shopeeA->id, 'orders' => 2,  'units' => 3,  'gross' => 850.00,  'disc' => 25.00,  'ref' => 0.00,  'net' => 825.00],
                    ['shop_id' => $shopeeB->id, 'orders' => 1,  'units' => 1,  'gross' => 400.00,  'disc' => 15.00,  'ref' => 0.00,  'net' => 385.00],
                    ['shop_id' => $shopeeC->id, 'orders' => 1,  'units' => 1,  'gross' => 350.00,  'disc' => 10.00,  'ref' => 0.00,  'net' => 340.00],
                    ['shop_id' => $tiktokA->id, 'orders' => 2,  'units' => 2,  'gross' => 600.00,  'disc' => 20.00,  'ref' => 0.00,  'net' => 580.00],
                    ['shop_id' => $tiktokB->id, 'orders' => 1,  'units' => 1,  'gross' => 300.00,  'disc' => 10.00,  'ref' => 0.00,  'net' => 290.00],
                ]
            ],
            // Hour 3 (03:00 AM)
            [
                'hour' => 3,
                'shops' => [
                    ['shop_id' => $shopeeA->id, 'orders' => 1,  'units' => 1,  'gross' => 450.00,  'disc' => 15.00,  'ref' => 0.00,  'net' => 435.00],
                    ['shop_id' => $shopeeB->id, 'orders' => 1,  'units' => 1,  'gross' => 350.00,  'disc' => 10.00,  'ref' => 0.00,  'net' => 340.00],
                    ['shop_id' => $shopeeC->id, 'orders' => 1,  'units' => 1,  'gross' => 300.00,  'disc' => 10.00,  'ref' => 0.00,  'net' => 290.00],
                    ['shop_id' => $tiktokA->id, 'orders' => 1,  'units' => 1,  'gross' => 350.00,  'disc' => 10.00,  'ref' => 0.00,  'net' => 340.00],
                    ['shop_id' => $tiktokB->id, 'orders' => 0,  'units' => 0,  'gross' => 0.00,    'disc' => 0.00,   'ref' => 0.00,  'net' => 0.00],
                ]
            ],
            // Hour 4 (04:00 AM)
            [
                'hour' => 4,
                'shops' => [
                    ['shop_id' => $shopeeA->id, 'orders' => 2,  'units' => 2,  'gross' => 600.00,  'disc' => 20.00,  'ref' => 0.00,  'net' => 580.00],
                    ['shop_id' => $shopeeB->id, 'orders' => 1,  'units' => 2,  'gross' => 420.00,  'disc' => 15.00,  'ref' => 0.00,  'net' => 405.00],
                    ['shop_id' => $shopeeC->id, 'orders' => 1,  'units' => 1,  'gross' => 320.00,  'disc' => 10.00,  'ref' => 0.00,  'net' => 310.00],
                    ['shop_id' => $tiktokA->id, 'orders' => 1,  'units' => 2,  'gross' => 450.00,  'disc' => 15.00,  'ref' => 0.00,  'net' => 435.00],
                    ['shop_id' => $tiktokB->id, 'orders' => 1,  'units' => 1,  'gross' => 280.00,  'disc' => 10.00,  'ref' => 0.00,  'net' => 270.00],
                ]
            ],
            // Hour 5 (05:00 AM)
            [
                'hour' => 5,
                'shops' => [
                    ['shop_id' => $shopeeA->id, 'orders' => 3,  'units' => 4,  'gross' => 1100.00, 'disc' => 35.00,  'ref' => 0.00,  'net' => 1065.00],
                    ['shop_id' => $shopeeB->id, 'orders' => 2,  'units' => 3,  'gross' => 750.00,  'disc' => 25.00,  'ref' => 0.00,  'net' => 725.00],
                    ['shop_id' => $shopeeC->id, 'orders' => 2,  'units' => 2,  'gross' => 550.00,  'disc' => 20.00,  'ref' => 0.00,  'net' => 530.00],
                    ['shop_id' => $tiktokA->id, 'orders' => 2,  'units' => 3,  'gross' => 800.00,  'disc' => 25.00,  'ref' => 0.00,  'net' => 775.00],
                    ['shop_id' => $tiktokB->id, 'orders' => 1,  'units' => 2,  'gross' => 420.00,  'disc' => 15.00,  'ref' => 0.00,  'net' => 405.00],
                ]
            ],
            // Hour 6 (06:00 AM - Morning Wakeup)
            [
                'hour' => 6,
                'shops' => [
                    ['shop_id' => $shopeeA->id, 'orders' => 5,  'units' => 6,  'gross' => 1850.00, 'disc' => 60.00,  'ref' => 0.00,  'net' => 1790.00],
                    ['shop_id' => $shopeeB->id, 'orders' => 3,  'units' => 4,  'gross' => 1050.00, 'disc' => 35.00,  'ref' => 0.00,  'net' => 1015.00],
                    ['shop_id' => $shopeeC->id, 'orders' => 2,  'units' => 3,  'gross' => 720.00,  'disc' => 25.00,  'ref' => 0.00,  'net' => 695.00],
                    ['shop_id' => $tiktokA->id, 'orders' => 4,  'units' => 5,  'gross' => 1350.00, 'disc' => 45.00,  'ref' => 0.00,  'net' => 1305.00],
                    ['shop_id' => $tiktokB->id, 'orders' => 2,  'units' => 3,  'gross' => 680.00,  'disc' => 20.00,  'ref' => 0.00,  'net' => 660.00],
                ]
            ],
            // Hour 7 (07:00 AM - Commute & Breakfast Browsing)
            [
                'hour' => 7,
                'shops' => [
                    ['shop_id' => $shopeeA->id, 'orders' => 7,  'units' => 9,  'gross' => 2600.00, 'disc' => 85.00,  'ref' => 0.00,  'net' => 2515.00],
                    ['shop_id' => $shopeeB->id, 'orders' => 4,  'units' => 5,  'gross' => 1450.00, 'disc' => 45.00,  'ref' => 0.00,  'net' => 1405.00],
                    ['shop_id' => $shopeeC->id, 'orders' => 3,  'units' => 4,  'gross' => 980.00,  'disc' => 30.00,  'ref' => 0.00,  'net' => 950.00],
                    ['shop_id' => $tiktokA->id, 'orders' => 5,  'units' => 7,  'gross' => 1800.00, 'disc' => 60.00,  'ref' => 0.00,  'net' => 1740.00],
                    ['shop_id' => $tiktokB->id, 'orders' => 3,  'units' => 4,  'gross' => 890.00,  'disc' => 30.00,  'ref' => 0.00,  'net' => 860.00],
                ]
            ],
            // Hour 8 (08:00 AM - Office Arrival / Morning Rush)
            [
                'hour' => 8,
                'shops' => [
                    ['shop_id' => $shopeeA->id, 'orders' => 8,  'units' => 11, 'gross' => 3100.00, 'disc' => 100.00, 'ref' => 0.00,  'net' => 3000.00],
                    ['shop_id' => $shopeeB->id, 'orders' => 5,  'units' => 7,  'gross' => 1750.00, 'disc' => 55.00,  'ref' => 0.00,  'net' => 1695.00],
                    ['shop_id' => $shopeeC->id, 'orders' => 4,  'units' => 5,  'gross' => 1250.00, 'disc' => 40.00,  'ref' => 0.00,  'net' => 1210.00],
                    ['shop_id' => $tiktokA->id, 'orders' => 6,  'units' => 8,  'gross' => 2150.00, 'disc' => 70.00,  'ref' => 0.00,  'net' => 2080.00],
                    ['shop_id' => $tiktokB->id, 'orders' => 3,  'units' => 4,  'gross' => 980.00,  'disc' => 30.00,  'ref' => 0.00,  'net' => 950.00],
                ]
            ],
            // Hour 9 (09:00 AM - Active Mid-Morning Trade)
            [
                'hour' => 9,
                'shops' => [
                    ['shop_id' => $shopeeA->id, 'orders' => 9,  'units' => 12, 'gross' => 3450.00, 'disc' => 110.00, 'ref' => 0.00,  'net' => 3340.00],
                    ['shop_id' => $shopeeB->id, 'orders' => 6,  'units' => 8,  'gross' => 1950.00, 'disc' => 60.00,  'ref' => 0.00,  'net' => 1890.00],
                    ['shop_id' => $shopeeC->id, 'orders' => 4,  'units' => 6,  'gross' => 1350.00, 'disc' => 45.00,  'ref' => 0.00,  'net' => 1305.00],
                    ['shop_id' => $tiktokA->id, 'orders' => 7,  'units' => 9,  'gross' => 2400.00, 'disc' => 75.00,  'ref' => 0.00,  'net' => 2325.00],
                    ['shop_id' => $tiktokB->id, 'orders' => 4,  'units' => 5,  'gross' => 1120.00, 'disc' => 35.00,  'ref' => 0.00,  'net' => 1085.00],
                ]
            ],
        ];

        foreach ($todayHours as $block) {
            foreach ($block['shops'] as $s) {
                HourlyMetric::updateOrCreate(
                    ['shop_id' => $s['shop_id'], 'report_date' => $today, 'hour' => $block['hour']],
                    [
                        'orders' => $s['orders'],
                        'units_sold' => $s['units'],
                        'gross_sales' => $s['gross'],
                        'discounts' => $s['disc'],
                        'refunds' => $s['ref'],
                        'net_sales' => $s['net'],
                    ]
                );
            }
        }
    }
}
