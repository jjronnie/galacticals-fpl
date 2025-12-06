<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeagueUpdateStarted;

class SystemController extends Controller
{
    public function runLeagueUpdate()
    {
        // Send notification email instantly
        Mail::to('ronaldjjuuko7@gmail.com')->send(new LeagueUpdateStarted());

        // Run the command
        Artisan::call('leagues:update-all');

        return back()->with('success', 'League update started.');
    }
}
