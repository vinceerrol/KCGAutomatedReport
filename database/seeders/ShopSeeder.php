<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shopee = Platform::where('code', 'shopee')->first();
        $tiktok = Platform::where('code', 'tiktok')->first();

        $shops = [
            // Shopee Shops
            [
                'platform_id' => $shopee->id,
                'name' => 'Shopee Shop A (Flagship)',
                'code' => 'shopee_a',
                'status' => 'active',
            ],
            [
                'platform_id' => $shopee->id,
                'name' => 'Shopee Shop B (Beauty & Care)',
                'code' => 'shopee_b',
                'status' => 'active',
            ],
            [
                'platform_id' => $shopee->id,
                'name' => 'Shopee Shop C (Electronics & Accessories)',
                'code' => 'shopee_c',
                'status' => 'active',
            ],

            // TikTok Shops
            [
                'platform_id' => $tiktok->id,
                'name' => 'TikTok Shop A (Live Stream Official)',
                'code' => 'tiktok_a',
                'status' => 'active',
            ],
            [
                'platform_id' => $tiktok->id,
                'name' => 'TikTok Shop B (Creator Affiliate)',
                'code' => 'tiktok_b',
                'status' => 'active',
            ],
        ];

        foreach ($shops as $s) {
            Shop::updateOrCreate(['code' => $s['code']], $s);
        }
    }
}
