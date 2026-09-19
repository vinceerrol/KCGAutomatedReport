<?php

namespace App\Services;

use App\Contracts\PlatformDataServiceInterface;
use App\Models\AutomationLog;
use App\Models\GeneratedReport;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportGeneratorService
{
    public function __construct(
        protected PlatformDataServiceInterface $platformDataService
    ) {}

    /**
     * Generate an hourly sales report for the given date and hour.
     *
     * @param string|null $date Format: Y-m-d (defaults to today)
     * @param int|null $hour Hour 0-23 (defaults to current or latest available hour)
     * @return GeneratedReport
     */
    public function generate(?string $date = null, ?int $hour = null, ?Carbon $generatedAt = null): GeneratedReport
    {
        $targetDate = $date ?? Carbon::now()->format('Y-m-d');
        $targetHour = $hour ?? Carbon::now()->hour;
        $timestamp = $generatedAt ?? Carbon::now();

        try {
            return DB::transaction(function () use ($targetDate, $targetHour, $timestamp) {
                // 1. Fetch hourly metrics for this date & hour
                $metrics = $this->platformDataService->fetchHourlyMetrics($targetDate, $targetHour);

                // If no direct records exist for this exact hour, get the latest hour for that date
                if ($metrics->isEmpty()) {
                    $latestHour = \App\Models\HourlyMetric::where('report_date', $targetDate)->max('hour');
                    if ($latestHour !== null) {
                        $targetHour = (int) $latestHour;
                        $metrics = $this->platformDataService->fetchHourlyMetrics($targetDate, $targetHour);
                    }
                }

                // 2. Aggregate totals
                $totalOrders = (int) $metrics->sum('orders');
                $totalUnits = (int) $metrics->sum('units_sold');
                $grossSales = round($metrics->sum(fn ($m) => (float) $m->gross_sales), 2);
                $discounts = round($metrics->sum(fn ($m) => (float) $m->discounts), 2);
                $refunds = round($metrics->sum(fn ($m) => (float) $m->refunds), 2);
                $netSales = round($grossSales - $discounts - $refunds, 2);

                // 3. Generate Platform Breakdown
                $platformSummary = $this->platformDataService->getPlatformSummary($targetDate, $targetHour);

                // 4. Generate Shop Breakdown
                $shopSummary = $this->platformDataService->getShopSummary($targetDate, $targetHour);

                $reportSnapshot = [
                    'meta' => [
                        'generated_at' => $timestamp->toIso8601String(),
                        'report_date' => $targetDate,
                        'report_hour' => $targetHour,
                        'source' => 'DemoPlatformDataService (PROTOTYPE)',
                    ],
                    'summary' => [
                        'orders' => $totalOrders,
                        'units' => $totalUnits,
                        'gross_sales' => $grossSales,
                        'discounts' => $discounts,
                        'refunds' => $refunds,
                        'net_sales' => $netSales,
                    ],
                    'platforms' => $platformSummary->toArray(),
                    'shops' => $shopSummary->toArray(),
                ];

                // 5. Store Generated Report
                $report = GeneratedReport::create([
                    'report_date' => $targetDate,
                    'report_hour' => $targetHour,
                    'total_orders' => $totalOrders,
                    'total_units' => $totalUnits,
                    'gross_sales' => $grossSales,
                    'discounts' => $discounts,
                    'refunds' => $refunds,
                    'net_sales' => $netSales,
                    'report_data' => $reportSnapshot,
                    'generated_at' => $timestamp,
                    'status' => 'completed',
                ]);

                // 6. Log Automation Success
                AutomationLog::create([
                    'job' => 'Report Generation',
                    'status' => 'SUCCESS',
                    'message' => "Generated hourly report for {$targetDate} " . sprintf('%02d:00', $targetHour) . " ({$totalOrders} orders, ₱" . number_format($netSales, 2) . " net sales)",
                    'created_at' => $timestamp,
                ]);

                return $report;
            });
        } catch (Exception $e) {
            Log::error("Hourly report generation failed: " . $e->getMessage());

            AutomationLog::create([
                'job' => 'Report Generation',
                'status' => 'FAILED',
                'message' => "Error generating report: " . $e->getMessage(),
                'created_at' => $timestamp,
            ]);

            throw $e;
        }
    }
}
