<?php

use App\Jobs\GenerateScheduledReportsJob;
use App\Jobs\ProcessSubscriptionExpirationJob;
use App\Jobs\SendSessionRemindersJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ALOS-S1-13 — Session reminder scheduler (runs every hour)
Schedule::job(new SendSessionRemindersJob)->hourly();

// ALOS-S1-15.6 — Weekly reports (e.g. every Monday 08:00)
Schedule::job(new GenerateScheduledReportsJob('weekly'))->weeklyOn(1, '08:00');

// ALOS-S1-15.6 — Monthly reports (e.g. 1st of month 08:00)
Schedule::job(new GenerateScheduledReportsJob('monthly'))->monthlyOn(1, '08:00');

// ALOS-S1-36 — Renewal monitoring: mark expired contracts, send expiring-soon emails (daily)
Schedule::job(new ProcessSubscriptionExpirationJob)->daily();
