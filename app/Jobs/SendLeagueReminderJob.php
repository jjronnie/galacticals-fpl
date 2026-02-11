<?php

namespace App\Jobs;

use App\Mail\ImportLeagueReminderMail;
use App\Models\User;
use App\Services\SyncJobProgressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLeagueReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $users = User::query()
            ->whereNull('league_reminder_sent_at')
            ->whereDoesntHave('league')
            ->whereDoesntHave('claimedManagers', function ($query): void {
                $query->whereNotNull('user_id');
            })
            ->get();
        $totalUsers = $users->count();

        if ($totalUsers === 0) {
            SyncJobProgressService::complete(
                SyncJobProgressService::SEND_LEAGUE_REMINDERS,
                'No users pending league reminder.'
            );

            return;
        }

        SyncJobProgressService::start(
            SyncJobProgressService::SEND_LEAGUE_REMINDERS,
            $totalUsers,
            'Sending league reminder emails...'
        );

        $sentCount = 0;

        foreach ($users as $user) {
            Mail::to($user->email)->send(new ImportLeagueReminderMail($user));
            $user->update([
                'league_reminder_sent_at' => now(),
            ]);
            $sentCount++;

            SyncJobProgressService::progress(
                SyncJobProgressService::SEND_LEAGUE_REMINDERS,
                $sentCount,
                $totalUsers,
                "Sent {$sentCount} of {$totalUsers} reminder emails."
            );
        }

        SyncJobProgressService::complete(
            SyncJobProgressService::SEND_LEAGUE_REMINDERS,
            "Sent {$totalUsers} reminder emails."
        );
    }

    public function failed(\Throwable $exception): void
    {
        SyncJobProgressService::fail(
            SyncJobProgressService::SEND_LEAGUE_REMINDERS,
            'Failed to send league reminder emails.'
        );
    }
}
