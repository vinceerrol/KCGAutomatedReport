<?php

namespace App\Http\Controllers;

use App\Models\AutomationLog;
use App\Models\GeneratedReport;
use App\Services\ReportGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AutomationController extends Controller
{
    public function __construct(
        protected ReportGeneratorService $reportGenerator
    ) {}

    /**
     * Display the automation status and logs.
     */
    public function index(): Response
    {
        $lastReport = GeneratedReport::orderByDesc('generated_at')->first();
        $nextScheduled = Carbon::now()->addHour()->startOfHour();
        $logs = AutomationLog::orderByDesc('id')->take(25)->get();

        return Inertia::render('Automation/Index', [
            'status' => 'Active',
            'schedule' => 'Every hour on the hour',
            'cronExpression' => '0 * * * *',
            'lastRun' => $lastReport ? $lastReport->generated_at->format('M d, Y h:i A') : 'None yet',
            'nextRun' => $nextScheduled->format('M d, Y h:i A'),
            'totalReportsCount' => GeneratedReport::count(),
            'logs' => $logs,
        ]);
    }

    /**
     * Trigger manual generation from automation panel.
     */
    public function run(Request $request): RedirectResponse
    {
        try {
            $report = $this->reportGenerator->generate();

            return redirect()->route('automation.index')
                ->with('success', "Hourly report (#{$report->id}) generated successfully at " . Carbon::now()->format('h:i:s A'));
        } catch (\Throwable $e) {
            return redirect()->route('automation.index')
                ->with('error', "Automation error: " . $e->getMessage());
        }
    }
}
