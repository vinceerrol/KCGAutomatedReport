<?php

use App\Http\Controllers\AutomationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TikTokReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Hourly Reporting Automation System
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
    
    // Batch Excel & CSV export endpoints
    Route::get('/export/summary.xlsx', [ReportController::class, 'exportBatchSummaryExcel'])->name('export.summary.xlsx');
    Route::get('/export/detailed_stores.xlsx', [ReportController::class, 'exportBatchDetailedExcel'])->name('export.detailed.xlsx');
    Route::get('/export/summary.csv', [ReportController::class, 'exportBatch'])->name('export.summary.csv');
    Route::get('/export/detailed_stores.csv', [ReportController::class, 'exportBatch'])->name('export.detailed.csv');
    Route::get('/export', [ReportController::class, 'exportBatch'])->name('export.batch');
    Route::get('/export.csv', [ReportController::class, 'exportBatch'])->name('export.batch.csv');

    // Single report Excel & CSV export endpoints
    Route::get('/{id}', [ReportController::class, 'show'])->name('show');
    Route::get('/{id}/export.xlsx', [ReportController::class, 'exportSingleExcel'])->name('export.single.xlsx');
    Route::get('/{id}/hourly_report_{report_id}.xlsx', [ReportController::class, 'exportSingleExcel'])->name('export.single.xlsx_named');
    Route::get('/{id}/export', [ReportController::class, 'exportSingle'])->name('export.single');
    Route::get('/{id}/export.csv', [ReportController::class, 'exportSingle'])->name('export.single.csv');
    Route::get('/{id}/hourly_report_{report_id}.csv', [ReportController::class, 'exportSingle'])->name('export.single.named');
});

// TikTok Hourly GMV Breakdown Automation Feature
Route::prefix('tiktok')->name('tiktok.')->group(function () {
    Route::get('/', [TikTokReportController::class, 'index'])->name('index');
    Route::get('/export/excel', [TikTokReportController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/csv', [TikTokReportController::class, 'exportCsv'])->name('export.csv');
    Route::post('/simulate-midnight', [TikTokReportController::class, 'simulateMidnight'])->name('simulate.midnight');
});

Route::get('/shops', [ShopController::class, 'index'])->name('shops.index');
Route::get('/platforms', [PlatformController::class, 'index'])->name('platforms.index');
Route::get('/platforms/tiktok', [TikTokReportController::class, 'index'])->name('platforms.tiktok');

Route::prefix('automation')->name('automation.')->group(function () {
    Route::get('/', [AutomationController::class, 'index'])->name('index');
    Route::post('/run', [AutomationController::class, 'run'])->name('run');
});
