<?php

namespace App\Console\Commands;

use App\Jobs\ComputeLeagueGameweekStandingsJob;
use App\Jobs\FetchFplDataJob;
use App\Jobs\FetchLeagueStandings;
use App\Jobs\FetchManagerProfilesJob;
use App\Mail\NightlyAppSyncCompletedMail;
use App\Models\FplFixture;
use App\Models\FplSyncRun;
use App\Models\League;
use App\Models\Manager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FplRunNightlySyncIfMatchdayComplete extends Command
{
    protected $signature = 'fpl:run-sync-if-matchday-complete';

    protected $description = 'Run the nightly app sync only when the current matchday is complete and buffer time has passed.';

    public function handle(): int
    {
        $timezone = 'Africa/Kampala';
        $bufferHours = (int) env('FPL_SYNC_BUFFER_HOURS', 5);

        $this->info("Checking if matchday is complete (buffer: {$bufferHours} hours)...");

        $latestEvent = FplFixture::query()
            ->whereNotNull('kickoff_time')
            ->max('event');

        if (! $latestEvent) {
            $this->info('No fixtures found in database. Exiting.');

            return self::SUCCESS;
        }

        $fixtures = FplFixture::query()
            ->where('event', $latestEvent)
            ->get();

        if ($fixtures->isEmpty()) {
            $this->info("No fixtures found for gameweek {$latestEvent}. Exiting.");

            return self::SUCCESS;
        }

        $unfinished = $fixtures->filter(fn ($f) => ! $f->finished);

        if ($unfinished->isNotEmpty()) {
            $this->info("Gameweek {$latestEvent} not complete: {$unfinished->count()} fixtures still unfinished.");

            return self::SUCCESS;
        }

        $lastKickoff = $fixtures->max('kickoff_time');

        if (! $lastKickoff) {
            $this->info("No kickoff time found for gameweek {$latestEvent}. Exiting.");

            return self::SUCCESS;
        }

        $bufferUntil = $lastKickoff->copy()->addHours($bufferHours);

        if (now($timezone)->lt($bufferUntil)) {
            $this->info("Waiting buffer time until {$bufferUntil->toDateTimeString()} ({$timezone}). Exiting.");

            return self::SUCCESS;
        }

        if (FplSyncRun::query()->where('event', $latestEvent)->exists()) {
            $this->info("Sync already executed for gameweek {$latestEvent}. Exiting.");

            return self::SUCCESS;
        }

        $this->info("Gameweek {$latestEvent} is complete and buffer has passed. Running full sync...");

        $startedAt = now($timezone);

        $summary = [
            'started_at' => $startedAt->toDateTimeString(),
            'completed_at' => null,
            'timezone' => $timezone,
            'fpl_synced' => false,
            'profile_entries_total' => 0,
            'profile_synced' => false,
            'leagues_total' => 0,
            'leagues_synced' => 0,
            'league_failures' => [],
            'errors' => [],
            'duration_seconds' => null,
            'triggered_by' => 'matchday-complete',
            'gameweek' => $latestEvent,
        ];

        try {
            FetchFplDataJob::dispatchSync();
            $summary['fpl_synced'] = true;
            $this->info('FPL teams and players synced.');
        } catch (\Throwable $exception) {
            $message = 'FPL sync failed: '.$exception->getMessage();
            $summary['errors'][] = $message;

            Log::error('Matchday sync failed during FPL sync.', [
                'exception_class' => $exception::class,
                'error' => $exception->getMessage(),
            ]);
        }

        $leagues = League::query()->get(['id', 'name', 'season']);
        $summary['leagues_total'] = $leagues->count();

        foreach ($leagues as $league) {
            try {
                FetchLeagueStandings::dispatchSync($league->id, false);
                $summary['leagues_synced']++;
                $this->info("League standings synced: {$league->name}");
            } catch (\Throwable $exception) {
                $summary['league_failures'][] = [
                    'name' => (string) $league->name,
                    'error' => $exception->getMessage(),
                ];

                Log::error('Matchday sync failed for league standings.', [
                    'league_id' => $league->id,
                    'league_name' => $league->name,
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $managerIds = Manager::query()->pluck('id')->all();
        $summary['profile_entries_total'] = (int) Manager::query()
            ->whereIn('id', $managerIds)
            ->distinct('entry_id')
            ->count('entry_id');

        if ($summary['profile_entries_total'] > 0) {
            try {
                FetchManagerProfilesJob::dispatchSync($managerIds);
                $summary['profile_synced'] = true;
                $this->info('Manager profiles synced.');
            } catch (\Throwable $exception) {
                $message = 'Manager profile sync failed: '.$exception->getMessage();
                $summary['errors'][] = $message;

                Log::error('Matchday sync failed during manager profile sync.', [
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        foreach ($leagues as $league) {
            try {
                ComputeLeagueGameweekStandingsJob::dispatchSync($league->id, (int) ($league->season ?? now()->year));
                $this->info("Gameweek tables computed: {$league->name}");
            } catch (\Throwable $exception) {
                $summary['errors'][] = "Compute tables failed for {$league->name}: ".$exception->getMessage();

                Log::error('Matchday sync failed during gameweek table computation.', [
                    'league_id' => $league->id,
                    'league_name' => $league->name,
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $completedAt = now($timezone);
        $summary['completed_at'] = $completedAt->toDateTimeString();
        $summary['duration_seconds'] = $completedAt->diffInSeconds($startedAt);

        $hasFailure = $summary['errors'] !== [] || $summary['league_failures'] !== [];

        FplSyncRun::create([
            'event' => $latestEvent,
            'status' => $hasFailure ? 'failed' : 'success',
            'meta' => $summary,
            'synced_at' => $completedAt,
        ]);

        $adminEmail = (string) config('mail.admin_address');

        if ($adminEmail !== '') {
            Mail::to($adminEmail)->send(new NightlyAppSyncCompletedMail($summary));
            $this->info("Completion email sent to {$adminEmail}.");
        }

        $this->info($hasFailure ? 'Matchday sync finished with issues.' : 'Matchday sync finished successfully.');

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }
}
