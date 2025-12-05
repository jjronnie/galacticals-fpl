<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\League;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Services\SeoService;
use App\Services\LeagueStatsService;

class FrontendController extends Controller
{
    protected $seoService;

    protected $statsService;

    public function __construct(LeagueStatsService $statsService, SEOService $seoService) 
    {
        $this->statsService = $statsService;
        $this->seoService = $seoService;
    }


    public function home()
    {
        $this->seoService->setHome();
        return view('welcome');
    }

    public function findLeagueID()
    {
        $this->seoService->setHowToFind();
        return view('find');
    }

    public function listLeagues()
    {
        $leagues = League::all(['name', 'slug']);

        $total = $leagues->count();

        $this->seoService->setLeagues();

        return view('leagues-list', compact('leagues', 'total'));
    }

    public function showStats(string $slug, int $gameweek = null)
    {
        // 1. Fetch League (Lightweight query)
        $league = League::where('slug', $slug)->firstOrFail();

        // 2. Determine View State (Target GW)
        $currentGW = $league->gameweek_current;
        $targetGW = $gameweek ?: $currentGW;

        if ($targetGW > $currentGW || $targetGW < 1) {
            $targetGW = $currentGW;
        }

        // 3. Get Heavy Data from Service (Cached)
        $data = $this->statsService->getLeagueStats($league);

        // 4. Handle SEO (Assuming you have this service)
        if (property_exists($this, 'seoService')) {
            $this->seoService->setLeague($league);
        }

        // 5. Return View
        return view('league-stats', [
            'league' => $league,
            'targetGW' => $targetGW,
            'currentGW' => $currentGW,
            'standings' => $data['standings'],
            'gwPerformance' => $data['gwPerformance'],
            'stats' => $data['stats']
        ]);
    }



    /**
     * Return a default empty stats structure.
     * Prevents null/undefined errors when no data is available.
     */
    private function getEmptyStats()
    {
        return [
            'most_gw_leads' => [],
            'most_gw_last' => [],
            'highest_gw_score' => [
                'manager' => 'N/A',
                'points' => 0,
                'gw' => null,
            ],
            'lowest_gw_score' => [
                'manager' => 'N/A',
                'points' => 0,
                'gw' => null,
            ],
            'mediocres' => [],
            'men_standing' => [],
            'hall_of_shame' => [],
            'hundred_plus_league' => [],
        ];
    }




}
