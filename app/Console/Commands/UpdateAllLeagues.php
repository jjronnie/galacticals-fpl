<?php

namespace App\Console\Commands;

use App\Jobs\FetchLeagueStandings;
use App\Models\League;
use App\Services\SyncJobProgressService;
use Illuminate\Console\Command;

class UpdateAllLeagues extends Command
{
    protected $signature = 'leagues:update-all';

    protected $description = 'Update all leagues data from FPL API';

    public function handle()
    {

        $this->info('Starting league updates...');

        $leagues = League::where('sync_status', '!=', 'processing')->get();

        if ($leagues->isEmpty()) {
            $this->info('No leagues to update.');

            return 0;
        }

        SyncJobProgressService::queue(
            SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
            $leagues->count(),
            "Scheduled league sync queued for {$leagues->count()} leagues."
        );

        foreach ($leagues as $league) {
            $this->info("Queuing update for: {$league->name}");

            $league->update([
                'sync_status' => 'processing',
                'sync_message' => 'Scheduled automatic update...',
                'synced_managers' => 0,
            ]);

            FetchLeagueStandings::dispatch($league->id);
        }

        $this->info("Queued {$leagues->count()} leagues for update!");

        return 0;
    }
}
