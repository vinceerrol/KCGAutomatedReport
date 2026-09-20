<?php

use App\Http\Controllers\Api\AutomationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\ShopeeReportController;
use App\Http\Controllers\Api\TikTokReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Hourly Reporting Automation System
|--------------------------------------------------------------------------
| These routes are consumed by the decoupled React 19 SPA (autoreportFront).
*/

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index']);

// Shopee Independent Reporting Pipeline
Route::prefix('shopee')->group(function () {
    Route::get('/', [ShopeeReportController::class, 'index']);
    Route::post('/sync', [ShopeeReportController::class, 'sync']);
    Route::get('/export/excel', [ShopeeReportController::class, 'exportExcel']);
    Route::get('/export/csv', [ShopeeReportController::class, 'exportCsv']);
    Route::post('/simulate-midnight', [ShopeeReportController::class, 'simulateMidnight']);
});

// TikTok Independent Reporting Pipeline
Route::prefix('tiktok')->group(function () {
    Route::get('/', [TikTokReportController::class, 'index']);
    Route::post('/sync', [TikTokReportController::class, 'sync']);
    Route::get('/export/excel', [TikTokReportController::class, 'exportExcel']);
    Route::get('/export/csv', [TikTokReportController::class, 'exportCsv']);
    Route::post('/simulate-midnight', [TikTokReportController::class, 'simulateMidnight']);
});

// Reports Management & Exports
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index']);
    Route::post('/generate', [ReportController::class, 'generate']);
    Route::get('/export/summary.xlsx', [ReportController::class, 'exportBatchSummaryExcel']);
    Route::get('/export/detailed_stores.xlsx', [ReportController::class, 'exportBatchDetailedExcel']);
    Route::get('/{id}', [ReportController::class, 'show']);
    Route::get('/{id}/export.xlsx', [ReportController::class, 'exportSingleExcel']);
    Route::get('/{id}/export.csv', [ReportController::class, 'exportSingleCsv']);
});

// Storefronts Directory
Route::get('/shops', [ShopController::class, 'index']);

// Platforms Connector Hub & Open API Credentials Status
Route::get('/platforms', [PlatformController::class, 'index']);
Route::get('/platforms/connectors-status', [PlatformController::class, 'connectorsStatus']);

// Automation Engine & Logs
Route::prefix('automation')->group(function () {
    Route::get('/', [AutomationController::class, 'index']);
    Route::post('/run', [AutomationController::class, 'run']);
    Route::post('/run-shopee', [AutomationController::class, 'runShopee']);
    Route::post('/run-tiktok', [AutomationController::class, 'runTikTok']);
});
