<?php

use App\Jobs\SendLeagueReminderJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('fpl:sync-fixtures')
    ->hourly()
    ->timezone('Africa/Kampala')
    ->withoutOverlapping();

Schedule::command('fpl:run-sync-if-matchday-complete')
    ->everyThirtyMinutes()
    ->timezone('Africa/Kampala')
    ->withoutOverlapping();

Schedule::job(new SendLeagueReminderJob)
    ->dailyAt('08:00')
    ->timezone('Africa/Kampala')
    ->withoutOverlapping();
