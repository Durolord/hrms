<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Shared hosting (cPanel) has no queue daemon: drain the queue every minute from the single cron entry.
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();

// Public demo only: restore a clean dataset every night.
if (config('app.demo')) {
    Schedule::command('demo:reset')->dailyAt('03:00')->withoutOverlapping();
}
