<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Hourly report generation schedule.
 * Automatically consolidates demo/external platform sales metrics every hour.
 */
Schedule::command('reports:generate')->hourly()->withoutOverlapping();
