<?php

namespace App\Http\Controllers;

use App\Models\AutomationLog;
use App\Models\HourlyMetric;
use App\Services\ExcelExportService;
use App\Services\TikTokReportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TikTokReportController extends Controller
{
    public function __construct(
        protected TikTokReportService $tikTokService,
        protected ExcelExportService $excelExportService
    ) {}

    /**
     * Display the TikTok Hourly GMV Breakdown report dashboard.
     */
    public function index(Request $request): Response
    {
        $today = Carbon::today()->format('Y-m-d');
        // Default to 2026-09-19 as seen in the reference report, or fallback to today
        $selectedDate = $request->input('date', '2026-09-19');
        $target = (float) $request->input('target', TikTokReportService::DEFAULT_DAILY_TARGET);

        $breakdown = $this->tikTokService->getHourlyBreakdown($selectedDate, $target);

        // Fetch recent automation logs for TikTok
        $recentLogs = AutomationLog::where('job', 'like', '%TikTok%')
            ->orWhere('message', 'like', '%TikTok%')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return Inertia::render('TikTok/HourlyBreakdown', [
            'report'     => $breakdown,
            'logs'       => $recentLogs,
            'todayDate'  => $today,
            'serverTime' => Carbon::now()->format('h:i:s A') . ' PHT',
        ]);
    }

    /**
     * Download the exact replica Microsoft Excel (.xlsx) file.
     */
    public function exportExcel(Request $request)
    {
        $selectedDate = $request->input('date', '2026-09-19');
        $target = (float) $request->input('target', TikTokReportService::DEFAULT_DAILY_TARGET);

        $breakdown = $this->tikTokService->getHourlyBreakdown($selectedDate, $target);
        $excelBinary = $this->excelExportService->generateTikTokHourlyExcel($breakdown);

        $filename = "TIKTOK_Hourly_Breakdown_{$selectedDate}.xlsx";

        return response($excelBinary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Export the matrix breakdown as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $selectedDate = $request->input('date', '2026-09-19');
        $target = (float) $request->input('target', TikTokReportService::DEFAULT_DAILY_TARGET);

        $breakdown = $this->tikTokService->getHourlyBreakdown($selectedDate, $target);
        $filename = "TIKTOK_Hourly_Breakdown_{$selectedDate}.csv";

        return response()->streamDownload(function () use ($breakdown) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ["DATE: {$breakdown['meta']['formatted_date']}", 'TIKTOK — HOURLY BREAKDOWN (GMV per shop by hour)']);
            fputcsv($handle, []);

            // Header row
            $header = array_merge(['SHOP', 'METRIC'], array_values($breakdown['hours']));
            fputcsv($handle, $header);

            $hourKeys = array_keys($breakdown['hours']);

            // Shop rows
            foreach ($breakdown['shops'] as $shop) {
                $metrics = [
                    'AD SPEND' => 'ad_spend',
                    'ORDERS'   => 'orders',
                    'SALES'    => 'sales',
                    'ROAS'     => 'roas',
                ];

                foreach ($metrics as $metricLabel => $key) {
                    $row = [$shop['shop_name'], $metricLabel];
                    foreach ($hourKeys as $hk) {
                        $val = $shop['metrics'][$key][$hk] ?? '';
                        if ($key === 'roas' && $val !== '') {
                            $val = number_format((float)$val, 2) . 'x';
                        }
                        $row[] = $val;
                    }
                    fputcsv($handle, $row);
                }
            }

            // Summary rows
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL TIKTOK HOURLY']);

            $totalSalesRow = ['TOTAL TIKTOK', 'TOTAL SALES (P)'];
            foreach ($hourKeys as $hk) {
                $totalSalesRow[] = $breakdown['summary']['total_sales'][$hk] ?? '';
            }
            fputcsv($handle, $totalSalesRow);

            $incrementRow = ['TOTAL TIKTOK', 'SALES INCREMENT'];
            foreach ($hourKeys as $hk) {
                $incrementRow[] = $breakdown['summary']['sales_increment'][$hk] ?? '-';
            }
            fputcsv($handle, $incrementRow);

            $targetRow = ['TOTAL TIKTOK', 'VS DAILY TARGET'];
            foreach ($hourKeys as $hk) {
                $pct = $breakdown['summary']['vs_daily_target'][$hk] ?? null;
                $targetRow[] = $pct !== null ? "{$pct}%" : '-';
            }
            fputcsv($handle, $targetRow);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Simulate or trigger next hourly automation update.
     */
    public function simulateMidnight(Request $request): RedirectResponse
    {
        $selectedDate = $request->input('date', '2026-09-19');
        $this->tikTokService->simulateMidnight($selectedDate);

        return redirect()->back()->with('success', 'Simulated 12:00 AM Midnight final push recorded successfully!');
    }
}
