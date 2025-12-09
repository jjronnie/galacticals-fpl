<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\ImportLeagueReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendLeagueReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function handle()
    {
        $users = User::whereDoesntHave('league')->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new ImportLeagueReminderMail($user));
            $user->update([
                'league_reminder_sent_at' => now(),
            ]);

        }
    }
}
