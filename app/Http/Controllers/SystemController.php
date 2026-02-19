<?php

namespace App\Http\Controllers;

use App\Mail\LeagueUpdateStarted;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class SystemController extends Controller
{
    public function runLeagueUpdate()
    {
        // Send notification email instantly
        Mail::to((string) config('mail.admin_address'))->send(new LeagueUpdateStarted);

        // Run the command
        Artisan::call('leagues:update-all');

        return back()->with('success', 'League update started.');
    }
}
