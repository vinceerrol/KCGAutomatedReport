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

            // TikTok Shops (Official Storefronts)
            [
                'platform_id' => $tiktok->id,
                'name' => 'KGOLD BEAUTY',
                'code' => 'kgold_beauty',
                'status' => 'active',
            ],
            [
                'platform_id' => $tiktok->id,
                'name' => 'PUREHERB',
                'code' => 'pureherb',
                'status' => 'active',
            ],
            [
                'platform_id' => $tiktok->id,
                'name' => 'NATURES BEAUTY (Old)',
                'code' => 'natures_beauty_old',
                'status' => 'active',
            ],
            [
                'platform_id' => $tiktok->id,
                'name' => 'NATURES BEAUTY (New)',
                'code' => 'natures_beauty_new',
                'status' => 'active',
            ],
            [
                'platform_id' => $tiktok->id,
                'name' => 'MIDAXX GEN SHOP',
                'code' => 'midaxx_gen_shop',
                'status' => 'active',
            ],
            [
                'platform_id' => $tiktok->id,
                'name' => 'MENS CORNER PH',
                'code' => 'mens_corner_ph',
                'status' => 'active',
            ],
        ];

        foreach ($shops as $s) {
            Shop::updateOrCreate(['code' => $s['code']], $s);
        }
    }
}
