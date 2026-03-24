<?php

use App\Jobs\SendLeagueReminderJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$nightlySync = Schedule::command('app:run-nightly-app-sync')
    ->dailyAt('02:30')
    ->timezone('Africa/Kampala')
    ->withoutOverlapping();

$adminEmail = (string) config('mail.admin_address');

if ($adminEmail !== '') {
    $nightlySync->emailOutputTo($adminEmail);
}

Schedule::job(new SendLeagueReminderJob)
    ->dailyAt('05:00')
    ->timezone('Africa/Kampala')
    ->withoutOverlapping();
