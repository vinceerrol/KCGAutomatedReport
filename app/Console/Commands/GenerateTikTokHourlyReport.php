<?php

namespace App\Console\Commands;

use App\Models\AutomationLog;
use App\Services\Sync\TikTokDataSyncService;
use App\Services\TikTokReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateTikTokHourlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:tiktok {--date= : The report date in Y-m-d format} {--hour= : The reporting hour (0-23)} {--no-sync : Skip live Open API data sync before report generation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and snapshot the hourly TikTok Shop sales report independently with optional Open API data sync';

    /**
     * Execute the console command.
     */
    public function handle(TikTokReportService $tikTokService, TikTokDataSyncService $syncService): int
    {
        $date = $this->option('date') ?: Carbon::now()->format('Y-m-d');
        $hourOption = $this->option('hour');
        $hour = $hourOption !== null ? (int) $hourOption : Carbon::now()->hour;
        $skipSync = (bool) $this->option('no-sync');

        $this->info("==========================================");
        $this->info("TIKTOK HOURLY REPORT AUTOMATION (INDEPENDENT)");
        $this->info("Target Period: {$date} at " . sprintf('%02d:00', $hour));

        if (!$skipSync) {
            $this->comment("Step 1/2: Synchronizing hourly metrics from TikTok Shop Open API...");
            try {
                $syncResult = $syncService->syncHour($date, $hour);
                $this->info("✓ Synced {$syncResult['total_orders']} orders across {$syncResult['total_shops']} shops.");
            } catch (\Throwable $e) {
                $this->warn("! Sync warning: " . $e->getMessage() . " (Continuing with cached data)");
            }
        } else {
            $this->comment("Step 1/2: Skipping API sync (--no-sync specified)");
        }

        $this->comment("Step 2/2: Compiling TikTok Hourly Breakdown matrix...");

        try {
            $breakdown = $tikTokService->getHourlyBreakdown($date);
            $kpis = $breakdown['kpis'];

            $this->newLine();
            $this->info("TikTok report compiled successfully!");
            $this->table(
                ['Metric', 'TikTok Value'],
                [
                    ['Reporting Date', $breakdown['meta']['formatted_date']],
                    ['Latest Active Hour', $breakdown['meta']['latest_hour'] ?? 'N/A'],
                    ['TikTok Total GMV', 'PHP ' . number_format($kpis['total_gmv'], 2)],
                    ['TikTok Ad Spend', 'PHP ' . number_format($kpis['total_ad_spend'], 2)],
                    ['TikTok Blended ROAS', number_format($kpis['blended_roas'], 2) . 'x'],
                    ['TikTok Total Orders', number_format($kpis['total_orders'])],
                    ['Target Progress', number_format($kpis['target_progress'], 1) . '% of PHP ' . number_format($kpis['daily_target'], 2)],
                    ['Active Shops Count', count($breakdown['shops'])],
                ]
            );

            AutomationLog::create([
                'job'        => 'TikTok Hourly Automation',
                'status'     => 'SUCCESS',
                'message'    => "Compiled independent TikTok hourly report for {$date} (GMV: ₱" . number_format($kpis['total_gmv'], 2) . ", ROAS: {$kpis['blended_roas']}x, {$kpis['total_orders']} orders)",
                'created_at' => Carbon::now(),
            ]);

            $this->info("Execution successfully logged to automation_logs.");
            $this->info("==========================================");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to generate TikTok hourly report: " . $e->getMessage());

            AutomationLog::create([
                'job'        => 'TikTok Hourly Automation',
                'status'     => 'ERROR',
                'message'    => "Failed to compile TikTok hourly report for {$date}: " . $e->getMessage(),
                'created_at' => Carbon::now(),
            ]);

            return Command::FAILURE;
        }
    }
}
