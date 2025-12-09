<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\League;
use App\Jobs\FetchLeagueStandings;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeagueUpdateStarted;

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