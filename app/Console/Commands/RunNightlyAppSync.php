<?php

namespace App\Console\Commands;

use App\Jobs\ComputeLeagueGameweekStandingsJob;
use App\Jobs\FetchFplDataJob;
use App\Jobs\FetchLeagueStandings;
use App\Jobs\FetchManagerProfilesJob;
use App\Mail\NightlyAppSyncCompletedMail;
use App\Models\League;
use App\Models\Manager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RunNightlyAppSync extends Command
{
    protected $signature = 'app:run-nightly-app-sync';

    protected $description = 'Run nightly full app sync and send a completion email to superadmin.';

    public function handle(): int
    {
        $timezone = 'Africa/Kampala';
        $startedAt = now($timezone);

        /** @var array{
         *  started_at:string,
         *  completed_at:string|null,
         *  timezone:string,
         *  fpl_synced:bool,
         *  profile_entries_total:int,
         *  profile_synced:bool,
         *  leagues_total:int,
         *  leagues_synced:int,
         *  league_failures:array<int,array{name:string,error:string}>,
         *  errors:array<int,string>,
         *  duration_seconds:int|null
         * } $summary
         */
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
        ];

        $this->info('Nightly sync started.');

        try {
            FetchFplDataJob::dispatchSync();
            $summary['fpl_synced'] = true;
            $this->info('FPL teams and players synced.');
        } catch (\Throwable $exception) {
            $message = 'FPL sync failed: '.$exception->getMessage();
            $summary['errors'][] = $message;

            Log::error('Nightly app sync failed during FPL sync.', [
                'exception_class' => $exception::class,
                'error' => $exception->getMessage(),
            ]);
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

                Log::error('Nightly app sync failed during manager profile sync.', [
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $leagues = League::query()->get(['id', 'name', 'season']);
        $summary['leagues_total'] = $leagues->count();

        foreach ($leagues as $league) {
            try {
                FetchLeagueStandings::dispatchSync($league->id, false);
                ComputeLeagueGameweekStandingsJob::dispatchSync($league->id, (int) ($league->season ?? now()->year));
                $summary['leagues_synced']++;
                $this->info("League synced: {$league->name}");
            } catch (\Throwable $exception) {
                $summary['league_failures'][] = [
                    'name' => (string) $league->name,
                    'error' => $exception->getMessage(),
                ];

                Log::error('Nightly app sync failed for league.', [
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

        $adminEmail = (string) config('mail.admin_address');

        if ($adminEmail !== '') {
            Mail::to($adminEmail)->send(new NightlyAppSyncCompletedMail($summary));
            $this->info("Completion email sent to {$adminEmail}.");
        }

        $hasFailure = $summary['errors'] !== [] || $summary['league_failures'] !== [];

        $this->info($hasFailure ? 'Nightly sync finished with issues.' : 'Nightly sync finished successfully.');

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }
}
