<?php

use App\Jobs\SendSessionRemindersJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ALOS-S1-13 — Session reminder scheduler (runs every hour)
Schedule::job(new SendSessionRemindersJob)->hourly();
