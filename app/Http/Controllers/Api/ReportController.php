<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneratedReport;
use App\Services\ExcelExportService;
use App\Services\ReportGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportGeneratorService $reportGenerator,
        protected ExcelExportService $excelExporter
    ) {}

    /**
     * Get paginated list of generated snapshots.
     */
    public function index(Request $request): JsonResponse
    {
        $query = GeneratedReport::query()
            ->orderByDesc('report_date')
            ->orderByDesc('report_time')
            ->orderByDesc('report_hour')
            ->orderByDesc('id');

        if ($request->filled('date')) {
            $query->where('report_date', $request->input('date'));
        }

        $reports = $query->paginate($request->input('per_page', 15));

        return response()->json($reports);
    }

    /**
     * Get specific report snapshot details.
     */
    public function show(int $id): JsonResponse
    {
        $report = GeneratedReport::findOrFail($id);

        return response()->json($report);
    }

    /**
     * Generate a manual real-time or hourly snapshot.
     */
    public function generate(Request $request): JsonResponse
    {
        $now = Carbon::now();
        $date = $request->input('date') ?: $now->format('Y-m-d');
        $hour = $request->has('hour') && $request->input('hour') !== null ? (int) $request->input('hour') : (int) $now->hour;
        $time = $request->input('time') ?: $now->format('H:i');

        try {
            $report = $this->reportGenerator->generate(
                date: $date,
                hour: $hour,
                generatedAt: $now,
                exactTime: $time,
                isManual: true
            );

            $formattedTime = Carbon::createFromFormat('H:i', $report->report_time)->format('h:i A');

            return response()->json([
                'success' => true,
                'message' => "Real-time report snapshot (#{$report->id}) for " . Carbon::parse($report->report_date)->format('M d, Y') . " at {$formattedTime} PHT generated successfully!",
                'report'  => $report,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Report generation failed: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export single report as Excel.
     */
    public function exportSingleExcel(int $id): HttpResponse
    {
        $report = GeneratedReport::findOrFail($id);
        $dateStr = $report->report_date->format('Y-m-d');
        $timeStr = $report->report_time ? str_replace(':', '', $report->report_time) : sprintf('%02d00', $report->report_hour);
        $filename = "hourly_report_{$report->id}_{$dateStr}_{$timeStr}.xlsx";

        $excelBinary = $this->excelExporter->generateSingleReportExcel($report);

        return response($excelBinary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Export single report as CSV.
     */
    public function exportSingleCsv(int $id): StreamedResponse
    {
        $report = GeneratedReport::findOrFail($id);
        $dateStr = $report->report_date->format('Y-m-d');
        $timeStr = $report->report_time ? str_replace(':', '', $report->report_time) : sprintf('%02d00', $report->report_hour);
        $filename = "hourly_report_{$report->id}_{$dateStr}_{$timeStr}.csv";

        return response()->streamDownload(function () use ($report, $dateStr, $hourStr) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Report ID', 'Report Date', 'Reporting Hour', 'Storefront', 'Platform', 'Orders', 'Units Sold', 'Gross Sales (PHP)', 'Discounts (PHP)', 'Refunds (PHP)', 'Net Sales (PHP)']);

            if (!empty($report->report_data['shops'])) {
                foreach ($report->report_data['shops'] as $shop) {
                    fputcsv($handle, [
                        $report->id,
                        $dateStr,
                        $hourStr,
                        $shop['shop_name'] ?? 'N/A',
                        $shop['platform_name'] ?? 'N/A',
                        $shop['orders'] ?? 0,
                        $shop['units_sold'] ?? 0,
                        $shop['gross_sales'] ?? 0,
                        $shop['discounts'] ?? 0,
                        $shop['refunds'] ?? 0,
                        $shop['net_sales'] ?? 0,
                    ]);
                }
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Batch export summary Excel.
     */
    public function exportBatchSummaryExcel(Request $request): HttpResponse
    {
        $query = GeneratedReport::orderByDesc('report_date')->orderByDesc('report_hour');
        if ($request->filled('date')) {
            $query->where('report_date', $request->input('date'));
        }
        $reports = $query->take(200)->get();

        $excelBinary = $this->excelExporter->generateBatchSummaryExcel($reports);
        $filename = 'hourly_reports_summary_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return response($excelBinary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Batch export detailed stores Excel.
     */
    public function exportBatchDetailedExcel(Request $request): HttpResponse
    {
        $query = GeneratedReport::orderByDesc('report_date')->orderByDesc('report_hour');
        if ($request->filled('date')) {
            $query->where('report_date', $request->input('date'));
        }
        $reports = $query->take(100)->get();

        $excelBinary = $this->excelExporter->generateBatchDetailedExcel($reports);
        $filename = 'hourly_reports_detailed_stores_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return response($excelBinary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
