<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            [
                'name' => 'Shopee',
                'code' => 'shopee',
                'status' => 'active',
            ],
            [
                'name' => 'TikTok Shop',
                'code' => 'tiktok',
                'status' => 'active',
            ],
        ];

        foreach ($platforms as $p) {
            Platform::updateOrCreate(['code' => $p['code']], $p);
        }
    }
}
