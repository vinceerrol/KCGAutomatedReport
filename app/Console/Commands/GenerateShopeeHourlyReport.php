<?php

namespace App\Console\Commands;

use App\Models\AutomationLog;
use App\Services\ShopeeReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateShopeeHourlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:shopee {--date= : The report date in Y-m-d format} {--hour= : The reporting hour (0-23)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and snapshot the hourly Shopee sales report independently';

    /**
     * Execute the console command.
     */
    public function handle(ShopeeReportService $shopeeService): int
    {
        $date = $this->option('date') ?: Carbon::now()->format('Y-m-d');
        $hourOption = $this->option('hour');
        $hour = $hourOption !== null ? (int) $hourOption : Carbon::now()->hour;

        $this->info("==========================================");
        $this->info("SHOPEE HOURLY REPORT AUTOMATION (INDEPENDENT)");
        $this->info("Target Period: {$date} at " . sprintf('%02d:00', $hour));
        $this->comment("Status: Processing Shopee Open Platform store data...");

        try {
            $breakdown = $shopeeService->getHourlyBreakdown($date);
            $kpis = $breakdown['kpis'];

            $this->newLine();
            $this->info("Shopee report compiled successfully!");
            $this->table(
                ['Metric', 'Shopee Value'],
                [
                    ['Reporting Date', $breakdown['meta']['formatted_date']],
                    ['Latest Active Hour', $breakdown['meta']['latest_hour'] ?? 'N/A'],
                    ['Shopee Total GMV', 'PHP ' . number_format($kpis['total_gmv'], 2)],
                    ['Shopee Ad Spend', 'PHP ' . number_format($kpis['total_ad_spend'], 2)],
                    ['Shopee Blended ROAS', number_format($kpis['blended_roas'], 2) . 'x'],
                    ['Shopee Total Orders', number_format($kpis['total_orders'])],
                    ['Target Progress', number_format($kpis['target_progress'], 1) . '% of PHP ' . number_format($kpis['daily_target'], 2)],
                    ['Active Shops Count', count($breakdown['shops'])],
                ]
            );

            AutomationLog::create([
                'job'        => 'Shopee Hourly Automation',
                'status'     => 'SUCCESS',
                'message'    => "Compiled independent Shopee hourly report for {$date} (GMV: ₱" . number_format($kpis['total_gmv'], 2) . ", ROAS: {$kpis['blended_roas']}x, {$kpis['total_orders']} orders)",
                'created_at' => Carbon::now(),
            ]);

            $this->info("Execution successfully logged to automation_logs.");
            $this->info("==========================================");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to generate Shopee hourly report: " . $e->getMessage());

            AutomationLog::create([
                'job'        => 'Shopee Hourly Automation',
                'status'     => 'FAILED',
                'message'    => "Error in Shopee hourly report: " . $e->getMessage(),
                'created_at' => Carbon::now(),
            ]);

            return Command::FAILURE;
        }
    }
}
