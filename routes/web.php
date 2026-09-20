<?php

use App\Http\Controllers\Api\AutomationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ShopeeReportController;
use App\Http\Controllers\Api\TikTokReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Decoupled Frontend Bridge & Direct Export Endpoints
|--------------------------------------------------------------------------
*/

$frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

// Handle rogue/leftover browser service workers by unregistering them immediately
Route::get('/sw.js', function () {
    return response("self.addEventListener('install', () => self.skipWaiting()); self.addEventListener('activate', () => self.registration.unregister());", 200, [
        'Content-Type' => 'application/javascript',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
});

// Redirect web pages directly to standalone React 19 Frontend
Route::get('/', fn () => redirect($frontendUrl))->name('dashboard');
Route::get('/shopee', fn () => redirect("{$frontendUrl}/shopee"))->name('shopee.index');
Route::get('/tiktok', fn () => redirect("{$frontendUrl}/tiktok"))->name('tiktok.index');
Route::get('/reports', fn () => redirect("{$frontendUrl}/reports"))->name('reports.index');
Route::get('/reports/{id}', fn ($id) => redirect("{$frontendUrl}/reports"))->name('reports.show');
Route::get('/shops', fn () => redirect("{$frontendUrl}/shops"))->name('shops.index');
Route::get('/platforms', fn () => redirect("{$frontendUrl}/platforms"))->name('platforms.index');
Route::get('/platforms/shopee', fn () => redirect("{$frontendUrl}/shopee"))->name('platforms.shopee');
Route::get('/platforms/tiktok', fn () => redirect("{$frontendUrl}/tiktok"))->name('platforms.tiktok');
Route::get('/automation', fn () => redirect("{$frontendUrl}/automation"))->name('automation.index');

// Reports Direct Export Endpoints
Route::prefix('reports')->name('reports.')->group(function () {
    Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
    Route::get('/export/summary.xlsx', [ReportController::class, 'exportBatchSummaryExcel'])->name('export.summary.xlsx');
    Route::get('/export/detailed_stores.xlsx', [ReportController::class, 'exportBatchDetailedExcel'])->name('export.detailed.xlsx');
    Route::get('/export/summary.csv', [ReportController::class, 'exportBatchSummaryCsv'])->name('export.summary.csv');
    Route::get('/export/detailed_stores.csv', [ReportController::class, 'exportBatchDetailedCsv'])->name('export.detailed.csv');
    Route::get('/export', [ReportController::class, 'exportBatchSummaryExcel'])->name('export.batch');

    Route::get('/{id}/export.xlsx', [ReportController::class, 'exportSingleExcel'])->name('export.single.xlsx');
    Route::get('/{id}/hourly_report_{report_id}.xlsx', [ReportController::class, 'exportSingleExcel'])->name('export.single.xlsx_named');
    Route::get('/{id}/export', [ReportController::class, 'exportSingleExcel'])->name('export.single');
    Route::get('/{id}/export.csv', [ReportController::class, 'exportSingleCsv'])->name('export.single.csv');
    Route::get('/{id}/hourly_report_{report_id}.csv', [ReportController::class, 'exportSingleCsv'])->name('export.single.named');
});

// Shopee Direct Export & Midnight Simulation Endpoints
Route::prefix('shopee')->name('shopee.')->group(function () {
    Route::get('/export/excel', [ShopeeReportController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/csv', [ShopeeReportController::class, 'exportCsv'])->name('export.csv');
    Route::post('/simulate-midnight', [ShopeeReportController::class, 'simulateMidnight'])->name('simulate.midnight');
});

// TikTok Direct Export & Midnight Simulation Endpoints
Route::prefix('tiktok')->name('tiktok.')->group(function () {
    Route::get('/export/excel', [TikTokReportController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/csv', [TikTokReportController::class, 'exportCsv'])->name('export.csv');
    Route::post('/simulate-midnight', [TikTokReportController::class, 'simulateMidnight'])->name('simulate.midnight');
});

// Automation Direct Triggers
Route::prefix('automation')->name('automation.')->group(function () {
    Route::post('/run', [AutomationController::class, 'run'])->name('run');
});
