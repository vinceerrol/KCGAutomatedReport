<?php

namespace Database\Seeders;

use App\Services\ReportGeneratorService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GeneratedReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(ReportGeneratorService $reportGenerator): void
    {
        $today = Carbon::today()->format('Y-m-d'); // 2026-09-19
        $yesterday = Carbon::yesterday()->format('Y-m-d'); // 2026-09-18

        // Historical reports leading directly up to the current time in the Philippines:
        $historicalReports = [
            // Yesterday evening hours (8 PM, 9 PM, 10 PM, 11 PM)
            ['date' => $yesterday, 'hour' => 20, 'timestamp' => Carbon::parse("{$yesterday} 20:00:00")],
            ['date' => $yesterday, 'hour' => 21, 'timestamp' => Carbon::parse("{$yesterday} 21:00:00")],
            ['date' => $yesterday, 'hour' => 22, 'timestamp' => Carbon::parse("{$yesterday} 22:00:00")],
            ['date' => $yesterday, 'hour' => 23, 'timestamp' => Carbon::parse("{$yesterday} 23:00:00")],

            // Today early morning hours (12:00 AM Midnight, 01:00 AM)
            ['date' => $today, 'hour' => 0, 'timestamp' => Carbon::parse("{$today} 00:00:00")],
            ['date' => $today, 'hour' => 1, 'timestamp' => Carbon::parse("{$today} 01:00:00")],
        ];

        foreach ($historicalReports as $entry) {
            $reportGenerator->generate($entry['date'], $entry['hour'], $entry['timestamp']);
        }
    }
}
