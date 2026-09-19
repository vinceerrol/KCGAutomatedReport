<?php

namespace App\Console\Commands;

use App\Services\ReportGeneratorService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateHourlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate {--date= : The report date in Y-m-d format} {--hour= : The reporting hour (0-23)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and snapshot the hourly e-commerce sales report';

    /**
     * Execute the console command.
     */
    public function handle(ReportGeneratorService $reportGenerator): int
    {
        $date = $this->option('date') ?: Carbon::now()->format('Y-m-d');
        $hourOption = $this->option('hour');
        $hour = $hourOption !== null ? (int) $hourOption : Carbon::now()->hour;

        $this->info("==========================================");
        $this->info("HOURLY SALES REPORT AUTOMATION");
        $this->info("Target Period: {$date} at " . sprintf('%02d:00', $hour));
        $this->comment("Status: Processing demo platform data...");

        try {
            $report = $reportGenerator->generate($date, $hour);

            $this->newLine();
            $this->info("Report generated successfully! (Report #{$report->id})");
            $this->table(
                ['Metric', 'Aggregated Value'],
                [
                    ['Reporting Date', $report->report_date->format('Y-m-d')],
                    ['Reporting Hour', sprintf('%02d:00', $report->report_hour)],
                    ['Total Orders', number_format($report->total_orders)],
                    ['Total Units Sold', number_format($report->total_units)],
                    ['Gross Sales', 'PHP ' . number_format($report->gross_sales, 2)],
                    ['Discounts', 'PHP ' . number_format($report->discounts, 2)],
                    ['Refunds', 'PHP ' . number_format($report->refunds, 2)],
                    ['Net Sales', 'PHP ' . number_format($report->net_sales, 2)],
                    ['Status', strtoupper($report->status)],
                    ['Generated At', $report->generated_at->format('Y-m-d H:i:s')],
                ]
            );

            $this->info("Execution successfully logged to automation_logs.");
            $this->info("==========================================");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to generate hourly report: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
