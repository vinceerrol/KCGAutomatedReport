<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Hourly report generation schedules.
 * Independent background pipelines for Shopee and TikTok Shop.
 */
Schedule::command('reports:shopee')->hourly()->withoutOverlapping();
Schedule::command('reports:tiktok')->hourly()->withoutOverlapping();
Schedule::command('reports:generate')->hourly()->withoutOverlapping();
