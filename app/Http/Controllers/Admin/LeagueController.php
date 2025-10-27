<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\League;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    public function index()
    {
        $leagues = auth()->user()->leagues()->withCount('managers', 'seasons')->latest()->get();
        return view('admin.leagues.index', compact('leagues'));
    }

    public function create()
    {
        return view('admin.leagues.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        
        $league = League::create($validated);

        return redirect()->route('admin.leagues.show', $league)
            ->with('success', 'League created successfully!');
    }

    public function show(League $league)
    {
        $this->authorize('view', $league);
        
        $league->load(['managers', 'seasons' => function($q) {
            $q->latest();
        }]);
        
        return view('admin.leagues.show', compact('league'));
    }

    public function edit(League $league)
    {
        $this->authorize('update', $league);
        return view('admin.leagues.edit', compact('league'));
    }

    public function update(Request $request, League $league)
    {
        $this->authorize('update', $league);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $league->update($validated);

        return redirect()->route('admin.leagues.show', $league)
            ->with('success', 'League updated successfully!');
    }

    public function destroy(League $league)
    {
        $this->authorize('delete', $league);
        
        $league->delete();

        return redirect()->route('admin.leagues.index')
            ->with('success', 'League deleted successfully!');
    }
}