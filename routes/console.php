<?php

use App\Support\Demo;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Shared hosting (cPanel) has no queue daemon: drain the queue every minute from the single cron entry.
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();

// Roll up the day's attendance every night, and draft the month's payrolls once a month for review and approval.
Schedule::command('attendance:summary')->dailyAt('23:55')->withoutOverlapping();
Schedule::command('app:dispatch-generate-payrolls-batch')->monthlyOn(config('payroll.generate_on_day', 20), '06:00')->withoutOverlapping();

Schedule::command('hr:send-reminders')->weekdays()->at('08:00')->withoutOverlapping();

// Public demo only: restore a clean dataset on DEMO_RESET_CRON (hourly by default).
if (Demo::enabled()) {
    Schedule::command('demo:reset')->cron(config('demo.reset_cron'))->withoutOverlapping();
}
