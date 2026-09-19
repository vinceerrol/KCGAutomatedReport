<?php

namespace App\Http\Controllers;

use App\Contracts\PlatformDataServiceInterface;
use App\Models\GeneratedReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected PlatformDataServiceInterface $platformDataService
    ) {}

    /**
     * Display the main management dashboard.
     */
    public function index(Request $request): Response
    {
        $today = Carbon::today()->format('Y-m-d');
        $selectedDate = $request->input('date', $today);
        $now = Carbon::now();

        // Platform Summary for selected date
        $platforms = $this->platformDataService->getPlatformSummary($selectedDate);

        // Calculate Overall KPIs from all platform metrics
        $totalOrders = (int) $platforms->sum('orders');
        $totalUnits = (int) $platforms->sum('units_sold');
        $grossSales = round($platforms->sum(fn ($p) => (float) $p['gross_sales']), 2);
        $discounts = round($platforms->sum(fn ($p) => (float) $p['discounts']), 2);
        $refunds = round($platforms->sum(fn ($p) => (float) $p['refunds']), 2);
        $netSales = round($grossSales - $discounts - $refunds, 2);

        // Shop Summary
        $shops = $this->platformDataService->getShopSummary($selectedDate);

        // Hourly Performance (for line chart and hourly table)
        $hourlyPerformance = $this->platformDataService->getHourlyPerformance($selectedDate);

        // Current hour vs Previous hour comparison
        $currentHour = $now->hour;
        $prevHour = $currentHour > 0 ? $currentHour - 1 : 23;

        // If viewing selected date or if current hour has no metrics yet, pick the latest hour with data
        if ($hourlyPerformance->isNotEmpty()) {
            $latestDataHour = $hourlyPerformance->last()['hour'];
            if (!$hourlyPerformance->firstWhere('hour', $currentHour)) {
                $currentHour = $latestDataHour;
                $prevHour = $currentHour > 0 ? $currentHour - 1 : 0;
            }
        }

        $comparison = $this->platformDataService->getComparison($selectedDate, $currentHour, $prevHour);

        // Automation Status (Last report and next scheduled report)
        $lastReport = GeneratedReport::orderByDesc('generated_at')->first();
        $nextScheduled = Carbon::now()->addHour()->startOfHour();

        return Inertia::render('Dashboard', [
            'kpis' => [
                'total_orders' => $totalOrders,
                'units_sold' => $totalUnits,
                'gross_sales' => $grossSales,
                'discounts' => $discounts,
                'refunds' => $refunds,
                'net_sales' => $netSales,
            ],
            'platforms' => $platforms,
            'shops' => $shops,
            'hourlyPerformance' => $hourlyPerformance,
            'comparison' => $comparison,
            'automation' => [
                'status' => 'Active',
                'schedule' => 'Every hour',
                'cron' => '0 * * * *',
                'last_report_at' => $lastReport ? $lastReport->generated_at->format('M d, Y h:i A') . ' PHT' : null,
                'last_report_id' => $lastReport?->id,
                'next_report_at' => $nextScheduled->format('M d, Y h:i A') . ' PHT',
            ],
            'meta' => [
                'current_date' => Carbon::now()->format('F d, Y'),
                'current_time' => Carbon::now()->format('h:i A') . ' PHT',
                'selected_date' => $selectedDate,
                'today_date' => $today,
                'yesterday_date' => Carbon::yesterday()->format('Y-m-d'),
            ],
        ]);
    }
}
