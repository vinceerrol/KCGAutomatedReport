<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomationLog;
use App\Models\GeneratedReport;
use App\Services\ReportGeneratorService;
use App\Services\ShopeeReportService;
use App\Services\TikTokReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function __construct(
        protected ReportGeneratorService $reportGenerator,
        protected ShopeeReportService $shopeeService,
        protected TikTokReportService $tikTokService
    ) {}

    /**
     * Get automation engine status and recent logs.
     */
    public function index(): JsonResponse
    {
        $lastReport = GeneratedReport::orderByDesc('generated_at')->first();
        $nextScheduled = Carbon::now()->addHour()->startOfHour();
        $logs = AutomationLog::orderByDesc('id')->take(40)->get();

        return response()->json([
            'status'            => 'Active',
            'schedule'          => 'Independent hourly pipelines for Shopee & TikTok',
            'cronExpression'    => '0 * * * *',
            'lastRun'           => $lastReport ? $lastReport->generated_at->format('M d, Y h:i A') : 'None yet',
            'nextRun'           => $nextScheduled->format('M d, Y h:i A'),
            'totalReportsCount' => GeneratedReport::count(),
            'logs'              => $logs,
        ]);
    }

    /**
     * Trigger consolidated report generation.
     */
    public function run(): JsonResponse
    {
        $now = Carbon::now();
        try {
            $report = $this->reportGenerator->generate(
                date: $now->format('Y-m-d'),
                hour: (int) $now->hour,
                generatedAt: $now,
                exactTime: $now->format('H:i'),
                isManual: true
            );

            $formattedTime = Carbon::createFromFormat('H:i', $report->report_time)->format('h:i A');

            return response()->json([
                'success' => true,
                'message' => "Real-time consolidated report (#{$report->id}) generated successfully at {$formattedTime} PHT",
                'report'  => $report,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Automation error: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger independent Shopee hourly automation.
     */
    public function runShopee(Request $request): JsonResponse
    {
        try {
            $date = $request->input('date') ?: Carbon::now()->format('Y-m-d');
            $breakdown = $this->shopeeService->getHourlyBreakdown($date);
            $kpis = $breakdown['kpis'];

            AutomationLog::create([
                'job'        => 'Shopee Hourly Automation',
                'status'     => 'SUCCESS',
                'message'    => "Manual trigger: compiled Shopee report for {$date} (GMV: ₱" . number_format($kpis['total_gmv'], 2) . ", ROAS: {$kpis['blended_roas']}x)",
                'created_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Shopee independent automation executed successfully at " . Carbon::now()->format('h:i:s A'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Shopee automation error: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger independent TikTok hourly automation.
     */
    public function runTikTok(Request $request): JsonResponse
    {
        try {
            $date = $request->input('date') ?: Carbon::now()->format('Y-m-d');
            $breakdown = $this->tikTokService->getHourlyBreakdown($date);
            $kpis = $breakdown['kpis'];

            AutomationLog::create([
                'job'        => 'TikTok Hourly Automation',
                'status'     => 'SUCCESS',
                'message'    => "Manual trigger: compiled TikTok report for {$date} (GMV: ₱" . number_format($kpis['total_gmv'], 2) . ", ROAS: {$kpis['blended_roas']}x)",
                'created_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "TikTok independent automation executed successfully at " . Carbon::now()->format('h:i:s A'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "TikTok automation error: " . $e->getMessage(),
            ], 500);
        }
    }
}
