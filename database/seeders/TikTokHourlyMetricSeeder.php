<?php

namespace Database\Seeders;

use App\Models\HourlyMetric;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TikTokHourlyMetricSeeder extends Seeder
{
    /**
     * Seed hourly metrics for TikTok shops matching the reference report.
     */
    public function run(): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $targetDates = array_unique(['2026-09-19', $today]);

        $shops = [
            'kgold_beauty'        => Shop::where('code', 'kgold_beauty')->first(),
            'pureherb'            => Shop::where('code', 'pureherb')->first(),
            'natures_beauty_old'  => Shop::where('code', 'natures_beauty_old')->first(),
            'natures_beauty_new'  => Shop::where('code', 'natures_beauty_new')->first(),
            'midaxx_gen_shop'     => Shop::where('code', 'midaxx_gen_shop')->first(),
            'mens_corner_ph'      => Shop::where('code', 'mens_corner_ph')->first(),
        ];

        // Ensure all shops exist
        foreach ($shops as $code => $shop) {
            if (!$shop) {
                $this->command->error("Shop {$code} not found! Please run ShopSeeder first.");
                return;
            }
        }

        // Data dictionary for hours 9 (9:00 AM) to 23 (11:00 PM)
        $hourlyData = [
            9 => [
                'kgold_beauty'       => ['spend' => 8543.00,  'orders' => 215, 'sales' => 40571.00],
                'pureherb'           => ['spend' => 2615.00,  'orders' => 70,  'sales' => 13956.00],
                'natures_beauty_old' => ['spend' => 1860.00,  'orders' => 54,  'sales' => 11933.00],
                'natures_beauty_new' => ['spend' => 489.00,   'orders' => 8,   'sales' => 1380.00],
                'midaxx_gen_shop'    => ['spend' => 50.00,    'orders' => 1,   'sales' => 379.00],
                'mens_corner_ph'     => ['spend' => 0.00,     'orders' => 0,   'sales' => 0.00],
            ],
            10 => [
                'kgold_beauty'       => ['spend' => 10351.00, 'orders' => 248, 'sales' => 49134.00],
                'pureherb'           => ['spend' => 3482.00,  'orders' => 85,  'sales' => 16952.00],
                'natures_beauty_old' => ['spend' => 2782.00,  'orders' => 74,  'sales' => 16489.00],
                'natures_beauty_new' => ['spend' => 574.00,   'orders' => 12,  'sales' => 2386.00],
                'midaxx_gen_shop'    => ['spend' => 63.00,    'orders' => 2,   'sales' => 474.00],
                'mens_corner_ph'     => ['spend' => 0.00,     'orders' => 0,   'sales' => 0.00],
            ],
            11 => [
                'kgold_beauty'       => ['spend' => 13233.00, 'orders' => 297, 'sales' => 59056.00],
                'pureherb'           => ['spend' => 4650.00,  'orders' => 104, 'sales' => 21655.00],
                'natures_beauty_old' => ['spend' => 4218.00,  'orders' => 91,  'sales' => 21210.00],
                'natures_beauty_new' => ['spend' => 659.00,   'orders' => 17,  'sales' => 3225.00],
                'midaxx_gen_shop'    => ['spend' => 70.00,    'orders' => 3,   'sales' => 547.00],
                'mens_corner_ph'     => ['spend' => 0.00,     'orders' => 0,   'sales' => 0.00],
            ],
            12 => [
                'kgold_beauty'       => ['spend' => 15598.00, 'orders' => 343, 'sales' => 72890.00],
                'pureherb'           => ['spend' => 5404.00,  'orders' => 112, 'sales' => 23195.00],
                'natures_beauty_old' => ['spend' => 5389.00,  'orders' => 112, 'sales' => 23195.00],
                'natures_beauty_new' => ['spend' => 761.00,   'orders' => 19,  'sales' => 3903.00],
                'midaxx_gen_shop'    => ['spend' => 75.00,    'orders' => 3,   'sales' => 547.00],
                'mens_corner_ph'     => ['spend' => 0.00,     'orders' => 0,   'sales' => 0.00],
            ],
            13 => [
                'kgold_beauty'       => ['spend' => 18355.00, 'orders' => 398, 'sales' => 84438.00],
                'pureherb'           => ['spend' => 6066.00,  'orders' => 132, 'sales' => 27849.00],
                'natures_beauty_old' => ['spend' => 6110.00,  'orders' => 128, 'sales' => 29893.00],
                'natures_beauty_new' => ['spend' => 929.00,   'orders' => 22,  'sales' => 4858.00],
                'midaxx_gen_shop'    => ['spend' => 84.00,    'orders' => 4,   'sales' => 816.00],
                'mens_corner_ph'     => ['spend' => 0.00,     'orders' => 0,   'sales' => 0.00],
            ],
            14 => [
                'kgold_beauty'       => ['spend' => 20810.00, 'orders' => 444, 'sales' => 94983.00],
                'pureherb'           => ['spend' => 6940.00,  'orders' => 149, 'sales' => 30866.00],
                'natures_beauty_old' => ['spend' => 7142.00,  'orders' => 147, 'sales' => 33849.00],
                'natures_beauty_new' => ['spend' => 1047.00,  'orders' => 23,  'sales' => 4957.00],
                'midaxx_gen_shop'    => ['spend' => 94.00,    'orders' => 4,   'sales' => 816.00],
                'mens_corner_ph'     => ['spend' => 0.00,     'orders' => 0,   'sales' => 0.00],
            ],
            15 => [
                'kgold_beauty'       => ['spend' => 24312.00, 'orders' => 480, 'sales' => 101992.00],
                'pureherb'           => ['spend' => 7324.00,  'orders' => 156, 'sales' => 32552.00],
                'natures_beauty_old' => ['spend' => 7520.00,  'orders' => 154, 'sales' => 35144.00],
                'natures_beauty_new' => ['spend' => 1101.00,  'orders' => 23,  'sales' => 4957.00],
                'midaxx_gen_shop'    => ['spend' => 103.00,   'orders' => 4,   'sales' => 816.00],
                'mens_corner_ph'     => ['spend' => 207.00,   'orders' => 0,   'sales' => 0.00],
            ],
            16 => [
                'kgold_beauty'       => ['spend' => 31527.00, 'orders' => 551, 'sales' => 116555.00],
                'pureherb'           => ['spend' => 8321.00,  'orders' => 173, 'sales' => 38071.00],
                'natures_beauty_old' => ['spend' => 8760.00,  'orders' => 176, 'sales' => 39838.00],
                'natures_beauty_new' => ['spend' => 1204.00,  'orders' => 23,  'sales' => 4957.00],
                'midaxx_gen_shop'    => ['spend' => 112.00,   'orders' => 4,   'sales' => 816.00],
                'mens_corner_ph'     => ['spend' => 246.00,   'orders' => 0,   'sales' => 0.00],
            ],
            17 => [
                'kgold_beauty'       => ['spend' => 34718.00, 'orders' => 604, 'sales' => 127339.00],
                'pureherb'           => ['spend' => 9863.00,  'orders' => 192, 'sales' => 41233.00],
                'natures_beauty_old' => ['spend' => 9439.00,  'orders' => 197, 'sales' => 45174.00],
                'natures_beauty_new' => ['spend' => 1283.00,  'orders' => 24,  'sales' => 5235.00],
                'midaxx_gen_shop'    => ['spend' => 116.00,   'orders' => 4,   'sales' => 816.00],
                'mens_corner_ph'     => ['spend' => 271.00,   'orders' => 0,   'sales' => 0.00],
            ],
            18 => [
                'kgold_beauty'       => ['spend' => 38637.00, 'orders' => 659, 'sales' => 138909.00],
                'pureherb'           => ['spend' => 10559.00, 'orders' => 211, 'sales' => 45712.00],
                'natures_beauty_old' => ['spend' => 10118.00, 'orders' => 213, 'sales' => 49190.00],
                'natures_beauty_new' => ['spend' => 1333.00,  'orders' => 25,  'sales' => 5434.00],
                'midaxx_gen_shop'    => ['spend' => 123.00,   'orders' => 5,   'sales' => 1355.00],
                'mens_corner_ph'     => ['spend' => 280.00,   'orders' => 0,   'sales' => 0.00],
            ],
            19 => [
                'kgold_beauty'       => ['spend' => 40899.00, 'orders' => 699, 'sales' => 147119.00],
                'pureherb'           => ['spend' => 11186.00, 'orders' => 230, 'sales' => 49847.00],
                'natures_beauty_old' => ['spend' => 10810.00, 'orders' => 227, 'sales' => 53248.00],
                'natures_beauty_new' => ['spend' => 1360.00,  'orders' => 25,  'sales' => 5434.00],
                'midaxx_gen_shop'    => ['spend' => 127.00,   'orders' => 6,   'sales' => 1624.00],
                'mens_corner_ph'     => ['spend' => 298.00,   'orders' => 0,   'sales' => 0.00],
            ],
            20 => [
                'kgold_beauty'       => ['spend' => 43210.00, 'orders' => 743, 'sales' => 156395.00],
                'pureherb'           => ['spend' => 12191.00, 'orders' => 254, 'sales' => 55159.00],
                'natures_beauty_old' => ['spend' => 11906.00, 'orders' => 255, 'sales' => 60197.00],
                'natures_beauty_new' => ['spend' => 1428.00,  'orders' => 27,  'sales' => 6079.00],
                'midaxx_gen_shop'    => ['spend' => 148.00,   'orders' => 6,   'sales' => 1624.00],
                'mens_corner_ph'     => ['spend' => 300.00,   'orders' => 0,   'sales' => 0.00],
            ],
            21 => [
                'kgold_beauty'       => ['spend' => 46658.00, 'orders' => 795, 'sales' => 166635.00],
                'pureherb'           => ['spend' => 13174.00, 'orders' => 271, 'sales' => 58111.00],
                'natures_beauty_old' => ['spend' => 12850.00, 'orders' => 290, 'sales' => 66397.00],
                'natures_beauty_new' => ['spend' => 1509.00,  'orders' => 30,  'sales' => 6573.00],
                'midaxx_gen_shop'    => ['spend' => 155.00,   'orders' => 6,   'sales' => 1624.00],
                'mens_corner_ph'     => ['spend' => 330.00,   'orders' => 0,   'sales' => 0.00],
            ],
            22 => [
                'kgold_beauty'       => ['spend' => 49267.00, 'orders' => 846, 'sales' => 177856.00],
                'pureherb'           => ['spend' => 13658.00, 'orders' => 286, 'sales' => 61361.00],
                'natures_beauty_old' => ['spend' => 13700.00, 'orders' => 309, 'sales' => 71717.00],
                'natures_beauty_new' => ['spend' => 1578.00,  'orders' => 32,  'sales' => 7067.00],
                'midaxx_gen_shop'    => ['spend' => 166.00,   'orders' => 7,   'sales' => 2043.00],
                'mens_corner_ph'     => ['spend' => 380.00,   'orders' => 0,   'sales' => 0.00],
            ],
            23 => [
                'kgold_beauty'       => ['spend' => 52126.00, 'orders' => 896, 'sales' => 186999.00],
                'pureherb'           => ['spend' => 13914.00, 'orders' => 296, 'sales' => 63062.00],
                'natures_beauty_old' => ['spend' => 14495.00, 'orders' => 324, 'sales' => 74508.00],
                'natures_beauty_new' => ['spend' => 1669.00,  'orders' => 32,  'sales' => 7067.00],
                'midaxx_gen_shop'    => ['spend' => 187.00,   'orders' => 9,   'sales' => 2559.00],
                'mens_corner_ph'     => ['spend' => 406.00,   'orders' => 3,   'sales' => 808.00],
            ],
        ];

        foreach ($targetDates as $targetDate) {
            $isToday = ($targetDate === $today);
            $maxHour = $isToday ? max((int) Carbon::now()->hour, 11) : 23;

            foreach ($hourlyData as $hour => $shopData) {
                if ($isToday && $hour > $maxHour) {
                    continue;
                }

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
                            'units_sold'  => (int)round($data['orders'] * 1.25),
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
}
