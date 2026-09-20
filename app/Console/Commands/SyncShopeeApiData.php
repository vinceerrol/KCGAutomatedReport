<?php

namespace App\Console\Commands;

use App\Services\Sync\ShopeeDataSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncShopeeApiData extends Command
{
    protected $signature = 'sync:shopee {--date= : Report date in Y-m-d format} {--hour= : Hour 0-23}';
    protected $description = 'Fetch and synchronize hourly sales data directly from Shopee Open Platform API';

    public function handle(ShopeeDataSyncService $syncService): int
    {
        $date = $this->option('date') ?: Carbon::now()->format('Y-m-d');
        $hourOption = $this->option('hour');
        $hour = $hourOption !== null ? (int) $hourOption : Carbon::now()->hour;

        $this->info("Starting Shopee Open Platform API Sync...");
        $this->comment("Syncing for: {$date} at " . sprintf('%02d:00', $hour));

        try {
            $result = $syncService->syncHour($date, $hour);

            $this->table(
                ['Shop Name', 'Orders', 'Units', 'Gross Sales (GMV)', 'Ad Spend'],
                array_map(fn ($s) => [
                    $s['shop_name'],
                    number_format($s['orders']),
                    number_format($s['units']),
                    'PHP ' . number_format($s['gross_sales'], 2),
                    'PHP ' . number_format($s['ad_spend'], 2),
                ], $result['shops'])
            );

            $this->info("Shopee Sync Completed: {$result['total_orders']} orders, PHP " . number_format($result['total_gmv'], 2) . " GMV");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Shopee Sync Failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
