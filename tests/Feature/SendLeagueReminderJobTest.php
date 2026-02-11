<?php

namespace Tests\Feature;

use App\Jobs\SendLeagueReminderJob;
use App\Mail\ImportLeagueReminderMail;
use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendLeagueReminderJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_job_targets_only_users_without_league_and_without_claimed_profile(): void
    {
        Mail::fake();

        $eligible = User::factory()->create([
            'league_reminder_sent_at' => null,
        ]);

        $leagueOwner = User::factory()->create([
            'league_reminder_sent_at' => null,
        ]);

        $claimedProfileUser = User::factory()->create([
            'league_reminder_sent_at' => null,
        ]);

        $alreadyReminded = User::factory()->create([
            'league_reminder_sent_at' => now()->subDay(),
        ]);

        $league = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 991001,
            'name' => 'Reminder League',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        Manager::create([
            'league_id' => $league->id,
            'entry_id' => 771122,
            'player_name' => 'Claimed Manager',
            'team_name' => 'Claimed Team',
            'rank' => 1,
            'total_points' => 120,
            'user_id' => $claimedProfileUser->id,
            'claimed_at' => now(),
        ]);

        (new SendLeagueReminderJob)->handle();

        Mail::assertSent(ImportLeagueReminderMail::class, 1);
        Mail::assertSent(ImportLeagueReminderMail::class, function (ImportLeagueReminderMail $mail) use ($eligible): bool {
            return $mail->hasTo($eligible->email);
        });

        Mail::assertNotSent(ImportLeagueReminderMail::class, function (ImportLeagueReminderMail $mail) use ($leagueOwner): bool {
            return $mail->hasTo($leagueOwner->email);
        });

        Mail::assertNotSent(ImportLeagueReminderMail::class, function (ImportLeagueReminderMail $mail) use ($claimedProfileUser): bool {
            return $mail->hasTo($claimedProfileUser->email);
        });

        Mail::assertNotSent(ImportLeagueReminderMail::class, function (ImportLeagueReminderMail $mail) use ($alreadyReminded): bool {
            return $mail->hasTo($alreadyReminded->email);
        });

        $this->assertDatabaseHas('users', [
            'id' => $eligible->id,
        ]);

        $this->assertNotNull($eligible->fresh()->league_reminder_sent_at);
        $this->assertNotNull($alreadyReminded->fresh()->league_reminder_sent_at);
    }
}
