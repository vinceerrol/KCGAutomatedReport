<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomationLog;
use App\Models\HourlyMetric;
use App\Services\ExcelExportService;
use App\Services\ShopeeReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShopeeReportController extends Controller
{
    public function __construct(
        protected ShopeeReportService $shopeeService,
        protected ExcelExportService $excelExportService
    ) {}

    /**
     * Get Shopee hourly breakdown data.
     */
    public function index(Request $request): JsonResponse
    {
        $today = Carbon::today()->format('Y-m-d');
        $defaultDate = $this->getDefaultDate();
        $selectedDate = $request->input('date', $defaultDate);
        $target = (float) $request->input('target', ShopeeReportService::DEFAULT_DAILY_TARGET);

        $breakdown = $this->shopeeService->getHourlyBreakdown($selectedDate, $target);

        $recentLogs = AutomationLog::where('job', 'like', '%Shopee%')
            ->orWhere('message', 'like', '%Shopee%')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return response()->json([
            'report'     => $breakdown,
            'logs'       => $recentLogs,
            'todayDate'  => $today,
            'serverTime' => Carbon::now()->format('h:i:s A') . ' PHT',
        ]);
    }

    /**
     * Download styled Microsoft Excel (.xlsx) file.
     */
    public function exportExcel(Request $request)
    {
        $selectedDate = $request->input('date', $this->getDefaultDate());
        $target = (float) $request->input('target', ShopeeReportService::DEFAULT_DAILY_TARGET);

        $breakdown = $this->shopeeService->getHourlyBreakdown($selectedDate, $target);
        $excelBinary = $this->excelExportService->generateShopeeHourlyExcel($breakdown);

        $filename = "SHOPEE_Hourly_Breakdown_{$selectedDate}.xlsx";

        return response($excelBinary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Download CSV export.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $selectedDate = $request->input('date', $this->getDefaultDate());
        $target = (float) $request->input('target', ShopeeReportService::DEFAULT_DAILY_TARGET);

        $breakdown = $this->shopeeService->getHourlyBreakdown($selectedDate, $target);
        $filename = "SHOPEE_Hourly_Breakdown_{$selectedDate}.csv";

        return response()->streamDownload(function () use ($breakdown) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ["DATE: {$breakdown['meta']['formatted_date']}", 'SHOPEE — HOURLY BREAKDOWN (GMV per shop by hour)']);
            fputcsv($handle, []);

            $header = array_merge(['SHOP', 'METRIC'], array_values($breakdown['hours']));
            fputcsv($handle, $header);

            $hourKeys = array_keys($breakdown['hours']);

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

            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL SHOPEE HOURLY']);

            $totalSalesRow = ['TOTAL SHOPEE', 'TOTAL SALES (P)'];
            foreach ($hourKeys as $hk) {
                $totalSalesRow[] = $breakdown['summary']['total_sales'][$hk] ?? '';
            }
            fputcsv($handle, $totalSalesRow);

            $incrementRow = ['TOTAL SHOPEE', 'SALES INCREMENT'];
            foreach ($hourKeys as $hk) {
                $incrementRow[] = $breakdown['summary']['sales_increment'][$hk] ?? '-';
            }
            fputcsv($handle, $incrementRow);

            $targetRow = ['TOTAL SHOPEE', 'VS DAILY TARGET'];
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
     * Simulate midnight projection.
     */
    public function simulateMidnight(Request $request): JsonResponse
    {
        $selectedDate = $request->input('date', $this->getDefaultDate());
        $this->shopeeService->simulateMidnight($selectedDate);

        return response()->json([
            'success' => true,
            'message' => 'Simulated Shopee 12:00 AM Midnight push recorded successfully!',
        ]);
    }

    /**
     * Trigger live synchronization from Shopee Open Platform API for date and hour.
     */
    public function sync(Request $request, \App\Services\Sync\ShopeeDataSyncService $syncService): JsonResponse
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $hour = $request->has('hour') ? (int) $request->input('hour') : Carbon::now()->hour;
        $shopId = $request->has('shop_id') ? (int) $request->input('shop_id') : null;

        $result = $syncService->syncHour($date, $hour, $shopId);
        $breakdown = $this->shopeeService->getHourlyBreakdown($date);

        return response()->json([
            'success'   => true,
            'message'   => "Successfully synchronized {$result['total_orders']} orders from Shopee Open Platform.",
            'sync'      => $result,
            'report'    => $breakdown,
        ]);
    }

    /**
     * Get the default reporting date for Shopee (today if metrics exist, or latest available date).
     */
    protected function getDefaultDate(): string
    {
        $today = Carbon::today()->format('Y-m-d');
        $hasTodayData = HourlyMetric::where('report_date', $today)
            ->whereHas('shop.platform', fn ($q) => $q->where('code', 'shopee'))
            ->exists();

        if ($hasTodayData) {
            return $today;
        }

        $latestDate = HourlyMetric::whereHas('shop.platform', fn ($q) => $q->where('code', 'shopee'))
            ->max('report_date');

        return $latestDate ? Carbon::parse($latestDate)->format('Y-m-d') : $today;
    }
}
