<?php

namespace App\Jobs;

use App\Models\League;
use App\Models\Manager;
use App\Models\GameweekScore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchLeagueStandings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 3;
    public $backoff = 300; // 5 minutes between retries

    protected $leagueId;

    public function __construct($leagueId)
    {
        $this->leagueId = $leagueId;
    }

    public function handle()
    {
        $league = League::find($this->leagueId);

        if (!$league) {
            Log::error("League not found: {$this->leagueId}");
            return;
        }

        try {
            // Fetch all standings pages
            $allStandings = $this->fetchAllStandings($league);
            
            if (empty($allStandings)) {
                $league->update([
                    'sync_status' => 'failed',
                    'sync_message' => 'No managers found in this league. The league may be empty or invalid.',
                ]);
                return;
            }

            $totalManagers = count($allStandings);
            $league->update([
                'total_managers' => $totalManagers,
                'sync_message' => "Processing {$totalManagers} managers...",
            ]);

            // Process managers in chunks to avoid memory issues
            $chunks = array_chunk($allStandings, 10);
            $processedCount = 0;

            foreach ($chunks as $chunk) {
                foreach ($chunk as $entry) {
                    $this->processManager($league, $entry);
                    $processedCount++;

                    // Update progress every 5 managers
                    if ($processedCount % 25 === 0) {
                        $league->update([
                            'synced_managers' => $processedCount,
                            'sync_message' => "Processed {$processedCount} of {$totalManagers} managers...",
                        ]);
                    }

                    // Rate limiting - 0.3 seconds per manager
                    usleep(300000);
                }
            }

            // Mark as completed
            $league->update([
                'sync_status' => 'completed',
                'sync_message' => "Successfully imported {$totalManagers} managers!",
                'synced_managers' => $totalManagers,
                'last_synced_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error("FetchLeagueStandings failed for league {$this->leagueId}: " . $e->getMessage());
            
            $league->update([
                'sync_status' => 'failed',
                'sync_message' => 'Failed to fetch league data. Please try updating the league again. If the problem persists, contact support.',
            ]);

            throw $e; // Re-throw to trigger job retry
        }
    }

    private function fetchAllStandings($league)
    {
        $page = 1;
        $allStandings = [];
        $leagueInfo = null;
        $maxPages = 100; // Safety limit

        do {
            try {
                $response = Http::timeout(15)->get("https://fantasy.premierleague.com/api/leagues-classic/{$league->league_id}/standings/", [
                    'page_standings' => $page
                ]);

                if ($response->failed()) {
                    Log::warning("Failed to fetch page {$page} for league {$league->league_id}");
                    break;
                }

                $data = $response->json();

                if (!isset($data['league']) || !isset($data['standings']['results'])) {
                    break;
                }

                if (!$leagueInfo) {
                    $leagueInfo = $data['league'];
                    $league->update([
                        'name' => $leagueInfo['name'],
                        'admin_name' => $leagueInfo['admin_entry'] ?? null,
                    ]);
                }

                $standings = $data['standings']['results'] ?? [];
                $allStandings = array_merge($allStandings, $standings);

                $hasNext = $data['standings']['has_next'] ?? false;
                $page++;

                // Rate limiting between pages
                usleep(200000);

                if ($page > $maxPages) {
                    Log::warning("Reached max pages limit for league {$league->league_id}");
                    break;
                }

            } catch (\Exception $e) {
                Log::error("Error fetching page {$page} for league {$league->league_id}: " . $e->getMessage());
                break;
            }

        } while ($hasNext);

        return $allStandings;
    }

    private function processManager($league, $entry)
    {
        try {
            $manager = Manager::updateOrCreate(
                [
                    'league_id' => $league->id,
                    'entry_id' => $entry['entry'],
                ],
                [
                    'player_name' => $entry['player_name'],
                    'team_name' => $entry['entry_name'],
                    'rank' => $entry['rank'],
                    'total_points' => $entry['total'],
                ]
            );

            // Fetch gameweek history
            $historyResponse = Http::timeout(15)->get("https://fantasy.premierleague.com/api/entry/{$entry['entry']}/history/");
            
            if ($historyResponse->successful()) {
                $historyData = $historyResponse->json();

                if (isset($historyData['current'])) {
                    foreach ($historyData['current'] as $week) {
                        GameweekScore::updateOrCreate(
                            [
                                'manager_id' => $manager->id,
                                'gameweek' => $week['event'],
                                'season_year' => date('Y'),
                            ],
                            [
                                'points' => $week['points'],
                            ]
                        );
                    }
                }
            }

        } catch (\Exception $e) {
            Log::warning("Failed to process manager {$entry['entry']}: " . $e->getMessage());
            // Continue processing other managers
        }
    }

    public function failed(\Throwable $exception)
    {
        $league = League::find($this->leagueId);
        
        if ($league) {
            $league->update([
                'sync_status' => 'failed',
                'sync_message' => 'Failed to import league data after multiple attempts. Please try again later or contact support.',
            ]);
        }

        Log::error("FetchLeagueStandings job failed permanently for league {$this->leagueId}: " . $exception->getMessage());
    }
}
