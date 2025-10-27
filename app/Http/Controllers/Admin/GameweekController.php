<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\Season;
use App\Models\GameweekPerformance;
use Illuminate\Http\Request;

class GameweekController extends Controller
{
    public function create(League $league, Season $season)
    {
        $this->authorize('view', $league);
        
        $managers = $league->managers()->where('is_active', true)->get();
        $gameweek = $season->current_gameweek;
        
        // Check if data already exists for this gameweek
        $existingData = GameweekPerformance::where('season_id', $season->id)
            ->where('gameweek', $gameweek)
            ->get()
            ->keyBy('manager_id');
        
        return view('admin.gameweeks.create', compact('league', 'season', 'managers', 'gameweek', 'existingData'));
    }

    public function store(Request $request, League $league, Season $season)
    {
        $this->authorize('view', $league);
        
        $validated = $request->validate([
            'gameweek' => 'required|integer|min:1|max:38',
            'points' => 'required|array',
            'points.*' => 'required|integer|min:0',
        ]);

        $gameweek = $validated['gameweek'];

        foreach ($validated['points'] as $managerId => $points) {
            GameweekPerformance::updateOrCreate(
                [
                    'season_id' => $season->id,
                    'manager_id' => $managerId,
                    'gameweek' => $gameweek,
                ],
                [
                    'points' => $points,
                ]
            );
        }

        // Update season's current gameweek if we're adding the next one
        if ($gameweek >= $season->current_gameweek && $gameweek < 38) {
            $season->update(['current_gameweek' => $gameweek + 1]);
        }

        return redirect()->route('admin.seasons.show', [$league, $season])
            ->with('success', 'Gameweek ' . $gameweek . ' data saved successfully!');
    }

    public function edit(League $league, Season $season, $gameweek)
    {
        $this->authorize('view', $league);
        
        $managers = $league->managers()->where('is_active', true)->get();
        
        $existingData = GameweekPerformance::where('season_id', $season->id)
            ->where('gameweek', $gameweek)
            ->get()
            ->keyBy('manager_id');
        
        return view('admin.gameweeks.edit', compact('league', 'season', 'managers', 'gameweek', 'existingData'));
    }
}