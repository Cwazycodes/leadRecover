<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks (run by `php artisan schedule:work` / cron)
|--------------------------------------------------------------------------
*/
Schedule::command('leads:sweep-stale')->hourly();
Schedule::command('subscriptions:trial-reminders')->dailyAt('09:00');
Schedule::command('queue:prune-batches')->daily();
