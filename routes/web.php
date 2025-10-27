<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\LeagueController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\GameweekController;
use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;

// Public Frontend Route
Route::get('/league/{slug}', [FrontendController::class, 'show'])->name('frontend.league.show');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.leagues.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // Leagues
    Route::resource('leagues', LeagueController::class);
    
    // Managers
    Route::get('leagues/{league}/managers/create', [ManagerController::class, 'create'])->name('managers.create');
    Route::post('leagues/{league}/managers', [ManagerController::class, 'store'])->name('managers.store');
    Route::get('leagues/{league}/managers/{manager}/edit', [ManagerController::class, 'edit'])->name('managers.edit');
    Route::put('leagues/{league}/managers/{manager}', [ManagerController::class, 'update'])->name('managers.update');
    Route::delete('leagues/{league}/managers/{manager}', [ManagerController::class, 'destroy'])->name('managers.destroy');
    
    // Seasons
    Route::get('leagues/{league}/seasons/create', [SeasonController::class, 'create'])->name('seasons.create');
    Route::post('leagues/{league}/seasons', [SeasonController::class, 'store'])->name('seasons.store');
    Route::get('leagues/{league}/seasons/{season}', [SeasonController::class, 'show'])->name('seasons.show');
    Route::post('leagues/{league}/seasons/{season}/activate', [SeasonController::class, 'activate'])->name('seasons.activate');
    
    // Gameweeks
    Route::get('leagues/{league}/seasons/{season}/gameweeks/create', [GameweekController::class, 'create'])->name('gameweeks.create');
    Route::post('leagues/{league}/seasons/{season}/gameweeks', [GameweekController::class, 'store'])->name('gameweeks.store');
    Route::get('leagues/{league}/seasons/{season}/gameweeks/{gameweek}/edit', [GameweekController::class, 'edit'])->name('gameweeks.edit');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';