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
     * Generate an hourly or exact-time sales report for the given date, hour, and time.
     *
     * @param string|null $date Format: Y-m-d (defaults to today)
     * @param int|null $hour Hour 0-23 (defaults to current hour)
     * @param Carbon|null $generatedAt Specific generation timestamp
     * @param string|null $exactTime Exact time in H:i format (e.g. "14:10")
     * @param bool $isManual Whether this snapshot was manually triggered
     * @return GeneratedReport
     */
    public function generate(
        ?string $date = null,
        ?int $hour = null,
        ?Carbon $generatedAt = null,
        ?string $exactTime = null,
        bool $isManual = false
    ): GeneratedReport {
        $timestamp = $generatedAt ?? Carbon::now();
        $targetDate = $date ?? $timestamp->format('Y-m-d');
        $targetHour = $hour ?? (int) $timestamp->hour;

        // If exactTime is provided, use it; if manual, default to current minute (e.g. "14:10"); otherwise top of hour ("14:00")
        if (!empty($exactTime)) {
            $targetTime = $exactTime;
        } elseif ($isManual) {
            $targetTime = $timestamp->format('H:i');
        } else {
            $targetTime = sprintf('%02d:00', $targetHour);
        }

        try {
            return DB::transaction(function () use ($targetDate, $targetHour, $targetTime, $timestamp, $isManual) {
                // 1. Fetch hourly metrics for this date & hour
                $metrics = $this->platformDataService->fetchHourlyMetrics($targetDate, $targetHour);

                // For manual real-time generation or if no records exist, synchronize live metrics up to this exact time
                if ($isManual || $metrics->isEmpty()) {
                    try {
                        app(\App\Services\Sync\ShopeeDataSyncService::class)->syncHour($targetDate, $targetHour, null, $timestamp);
                        app(\App\Services\Sync\TikTokDataSyncService::class)->syncHour($targetDate, $targetHour, null, $timestamp);
                        $metrics = $this->platformDataService->fetchHourlyMetrics($targetDate, $targetHour);
                    } catch (\Throwable $syncErr) {
                        Log::warning("Live sync during report generation failed: " . $syncErr->getMessage());
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
                        'report_time' => $targetTime,
                        'mode'        => $isManual ? 'manual_realtime' : 'scheduled_hourly',
                        'source'      => 'Automated Live Ingestion & Snapshot Pipeline',
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

                // 5. Store or Update Generated Report keyed by date and exact time
                $report = GeneratedReport::updateOrCreate(
                    [
                        'report_date' => $targetDate,
                        'report_time' => $targetTime,
                    ],
                    [
                        'report_hour'  => $targetHour,
                        'total_orders' => $totalOrders,
                        'total_units'  => $totalUnits,
                        'gross_sales'  => $grossSales,
                        'discounts'    => $discounts,
                        'refunds'      => $refunds,
                        'net_sales'    => $netSales,
                        'report_data'  => $reportSnapshot,
                        'generated_at' => $timestamp,
                        'status'       => 'completed',
                    ]
                );

                // 6. Log Automation Success
                AutomationLog::create([
                    'job' => $isManual ? 'Manual Snapshot Generation' : 'Report Generation',
                    'status' => 'SUCCESS',
                    'message' => "Generated " . ($isManual ? "manual real-time" : "hourly") . " report for {$targetDate} {$targetTime} ({$totalOrders} orders, ₱" . number_format($netSales, 2) . " net sales)",
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
