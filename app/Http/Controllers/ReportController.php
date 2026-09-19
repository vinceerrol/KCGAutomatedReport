<?php

namespace App\Http\Controllers;

use App\Models\GeneratedReport;
use App\Services\ExcelExportService;
use App\Services\ReportGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(
        protected ReportGeneratorService $reportGenerator,
        protected ExcelExportService $excelExporter
    ) {}

    /**
     * Display a listing of generated reports.
     */
    public function index(Request $request): Response
    {
        $query = GeneratedReport::query()->orderByDesc('report_date')->orderByDesc('report_hour');

        if ($request->filled('date')) {
            $query->where('report_date', $request->input('date'));
        }

        $reports = $query->paginate(15)->withQueryString();

        return Inertia::render('Reports/Index', [
            'reports' => $reports,
            'filters' => [
                'date' => $request->input('date'),
            ],
        ]);
    }

    /**
     * Display a specific generated report.
     */
    public function show(int $id): Response
    {
        $report = GeneratedReport::findOrFail($id);

        return Inertia::render('Reports/Show', [
            'report' => $report,
        ]);
    }

    /**
     * Manually generate an hourly report.
     */
    public function generate(Request $request): RedirectResponse
    {
        $date = $request->input('date') ?: Carbon::now()->format('Y-m-d');
        $hour = $request->input('hour') !== null ? (int) $request->input('hour') : Carbon::now()->hour;

        try {
            $report = $this->reportGenerator->generate($date, $hour);

            return redirect()->route('reports.show', $report->id)
                ->with('success', "Hourly report for " . Carbon::parse($report->report_date)->format('M d, Y') . " " . sprintf('%02d:00', $report->report_hour) . " generated successfully!");
        } catch (\Throwable $e) {
            return back()->with('error', "Report generation failed: " . $e->getMessage());
        }
    }

    /**
     * Export a single report snapshot as a formatted Microsoft Excel (.xlsx) file.
     */
    public function exportSingleExcel(int $id): HttpResponse
    {
        $report = GeneratedReport::findOrFail($id);
        $dateStr = $report->report_date->format('Y-m-d');
        $hourStr = sprintf('%02d00', $report->report_hour);
        $filename = "hourly_report_{$report->id}_{$dateStr}_{$hourStr}.xlsx";

        $content = $this->excelExporter->generateSingleReportExcel($report);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Export a single report snapshot as a clean, standardized tabular CSV file.
     */
    public function exportSingle(int $id): HttpResponse
    {
        $report = GeneratedReport::findOrFail($id);
        $dateStr = $report->report_date->format('Y-m-d');
        $hourStr = sprintf('%02d00', $report->report_hour);
        $formattedHour = sprintf('%02d:00', $report->report_hour);
        $filename = "hourly_report_{$report->id}_{$dateStr}_{$hourStr}.csv";

        $handle = fopen('php://temp', 'r+');

        // UTF-8 Byte Order Mark (BOM) for Microsoft Excel compatibility
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // Standard Tabular Header - consistent across every single data row
        fputcsv($handle, [
            'Report ID',
            'Report Date',
            'Reporting Hour',
            'Row Classification',
            'Platform Name',
            'Storefront Name',
            'Store Identifier Code',
            'Orders Count',
            'Units Sold',
            'Gross Sales (PHP)',
            'Discounts (PHP)',
            'Refunds (PHP)',
            'Net Sales (PHP)',
            'Generated At (PHT)',
        ]);

        // 1. Detailed Storefront Breakdown Rows
        if (!empty($report->report_data['shops'])) {
            foreach ($report->report_data['shops'] as $shop) {
                fputcsv($handle, [
                    $report->id,
                    $dateStr,
                    $formattedHour,
                    'STOREFRONT',
                    $shop['platform_name'] ?? 'N/A',
                    $shop['shop_name'] ?? 'N/A',
                    $shop['shop_code'] ?? 'N/A',
                    $shop['orders'] ?? 0,
                    $shop['units_sold'] ?? 0,
                    number_format($shop['gross_sales'] ?? 0, 2, '.', ''),
                    number_format($shop['discounts'] ?? 0, 2, '.', ''),
                    number_format($shop['refunds'] ?? 0, 2, '.', ''),
                    number_format($shop['net_sales'] ?? 0, 2, '.', ''),
                    $report->generated_at->format('Y-m-d H:i:s'),
                ]);
            }
        }

        // 2. Platform Subtotal Rows
        if (!empty($report->report_data['platforms'])) {
            foreach ($report->report_data['platforms'] as $platform) {
                fputcsv($handle, [
                    $report->id,
                    $dateStr,
                    $formattedHour,
                    'PLATFORM_SUBTOTAL',
                    $platform['name'] ?? 'N/A',
                    "Subtotal: " . ($platform['name'] ?? 'Platform'),
                    $platform['code'] ?? 'N/A',
                    $platform['orders'] ?? 0,
                    $platform['units_sold'] ?? 0,
                    number_format($platform['gross_sales'] ?? 0, 2, '.', ''),
                    number_format($platform['discounts'] ?? 0, 2, '.', ''),
                    number_format($platform['refunds'] ?? 0, 2, '.', ''),
                    number_format($platform['net_sales'] ?? 0, 2, '.', ''),
                    $report->generated_at->format('Y-m-d H:i:s'),
                ]);
            }
        }

        // 3. Consolidated Grand Total Row
        fputcsv($handle, [
            $report->id,
            $dateStr,
            $formattedHour,
            'CONSOLIDATED_GRAND_TOTAL',
            'ALL_PLATFORMS',
            'Consolidated Grand Total (All Stores)',
            'ALL',
            $report->total_orders,
            $report->total_units,
            number_format($report->gross_sales, 2, '.', ''),
            number_format($report->discounts, 2, '.', ''),
            number_format($report->refunds, 2, '.', ''),
            number_format($report->net_sales, 2, '.', ''),
            $report->generated_at->format('Y-m-d H:i:s'),
        ]);

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Export multiple reports (filtered or all) as CSV.
     * Supports type=summary (1 row per hourly report) or type=detailed (1 row per shop per report).
     */
    public function exportBatch(Request $request): HttpResponse
    {
        $type = $request->input('type', 'summary');
        $date = $request->input('date');

        $query = GeneratedReport::query()->orderByDesc('report_date')->orderByDesc('report_hour');
        if ($date) {
            $query->where('report_date', $date);
        }

        $reports = $query->get();
        $dateSuffix = $date ?: 'all_history';
        $filename = $type === 'detailed' 
            ? "hourly_reports_detailed_stores_{$dateSuffix}.csv"
            : "hourly_reports_summary_{$dateSuffix}.csv";

        $handle = fopen('php://temp', 'r+');

        // UTF-8 BOM for Excel
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        if ($type === 'detailed') {
            // Detailed Shop-Level Export across reports
            fputcsv($handle, [
                'Report ID',
                'Report Date',
                'Reporting Hour',
                'Formatted Hour',
                'Platform Name',
                'Storefront Name',
                'Store Identifier Code',
                'Orders Count',
                'Units Sold',
                'Gross Sales (PHP)',
                'Discounts (PHP)',
                'Refunds (PHP)',
                'Net Sales (PHP)',
                'Report Generated At (PHT)',
            ]);

            foreach ($reports as $r) {
                $shops = $r->report_data['shops'] ?? [];
                foreach ($shops as $s) {
                    fputcsv($handle, [
                        $r->id,
                        $r->report_date->format('Y-m-d'),
                        $r->report_hour,
                        sprintf('%02d:00', $r->report_hour),
                        $s['platform_name'] ?? 'N/A',
                        $s['shop_name'] ?? 'N/A',
                        $s['shop_code'] ?? 'N/A',
                        $s['orders'] ?? 0,
                        $s['units_sold'] ?? 0,
                        number_format($s['gross_sales'] ?? 0, 2, '.', ''),
                        number_format($s['discounts'] ?? 0, 2, '.', ''),
                        number_format($s['refunds'] ?? 0, 2, '.', ''),
                        number_format($s['net_sales'] ?? 0, 2, '.', ''),
                        $r->generated_at->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        } else {
            // Summary Export (1 row per hourly report)
            fputcsv($handle, [
                'Report ID',
                'Report Date',
                'Reporting Hour',
                'Formatted Hour',
                'Total Orders Count',
                'Total Units Sold',
                'Gross Sales (PHP)',
                'Discounts (PHP)',
                'Refunds (PHP)',
                'Net Sales (PHP)',
                'Execution Status',
                'Report Generated At (PHT)',
            ]);

            foreach ($reports as $r) {
                fputcsv($handle, [
                    $r->id,
                    $r->report_date->format('Y-m-d'),
                    $r->report_hour,
                    sprintf('%02d:00', $r->report_hour),
                    $r->total_orders,
                    $r->total_units,
                    number_format($r->gross_sales, 2, '.', ''),
                    number_format($r->discounts, 2, '.', ''),
                    number_format($r->refunds, 2, '.', ''),
                    number_format($r->net_sales, 2, '.', ''),
                    strtoupper($r->status),
                    $r->generated_at->format('Y-m-d H:i:s'),
                ]);
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Export batch summary reports as formatted Microsoft Excel (.xlsx) file.
     */
    public function exportBatchSummaryExcel(Request $request): HttpResponse
    {
        $query = GeneratedReport::query()->orderByDesc('report_date')->orderByDesc('report_hour');
        $date = $request->input('date');
        if ($date) {
            $query->where('report_date', $date);
        }

        $reports = $query->get();
        $dateSuffix = $date ?: 'all_history';
        $filename = "hourly_reports_summary_{$dateSuffix}.xlsx";

        $content = $this->excelExporter->generateBatchSummaryExcel($reports);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Export batch detailed storefront reports as formatted Microsoft Excel (.xlsx) file.
     */
    public function exportBatchDetailedExcel(Request $request): HttpResponse
    {
        $query = GeneratedReport::query()->orderByDesc('report_date')->orderByDesc('report_hour');
        $date = $request->input('date');
        if ($date) {
            $query->where('report_date', $date);
        }

        $reports = $query->get();
        $dateSuffix = $date ?: 'all_history';
        $filename = "hourly_reports_detailed_stores_{$dateSuffix}.xlsx";

        $content = $this->excelExporter->generateBatchDetailedExcel($reports);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}

