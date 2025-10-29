<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\AdminController;

use App\Models\League;

use Illuminate\Support\Facades\Route;

//public routes

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/privacy-policy', function () {
    return view('privacy_policy');
})->name('privacy.policy');

Route::get('/terms-and-conditions', function () {
    return view('terms');
})->name('terms');

Route::get('/how-to-find-fpl-league-id', function () {
    return view('find');
})->name('find');

Route::get('/find-league', [FrontendController::class, 'showFinder'])->name('league.find');
Route::post('/find-league', [FrontendController::class, 'search'])->name('league.search');

Route::get('/leagues', [FrontendController::class, 'listLeagues'])->name('public.leagues.list');

Route::get('/leagues/{slug}/{gameweek?}', [FrontendController::class, 'showStats'])
    ->where('gameweek', '[0-9]+') 
    ->name('public.stats.show');


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/standings', [AdminController::class, 'table'])->name('table');

    // --- League Setup/Management ---
    Route::get('/dashboard/setup', [AdminController::class, 'createLeague'])->name('admin.league.create');
    Route::post('/dashboard/setup', [AdminController::class, 'storeLeague'])->name('admin.league.store');
    Route::post('/leaugue/update', [AdminController::class, 'updateUserLeague'])->name('admin.league.update');



    // Managers CRUD (simple)
    Route::post('/dashboard/manager', [AdminController::class, 'storeManager'])->name('admin.manager.store');
    Route::delete('/dashboard/manager/{manager}', [AdminController::class, 'destroyManager'])->name('admin.manager.destroy');

    // --- Gameweek Scores & Stats ---
    Route::get('/dashboard/gameweek/add', [AdminController::class, 'createGameweekScore'])->name('admin.gameweek.create');
    Route::post('/dashboard/gameweek/add', [AdminController::class, 'storeGameweekScore'])->name('admin.gameweek.store');
    Route::post('/dashboard/gameweek/next-season', [AdminController::class, 'nextSeason'])->name('admin.season.next');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});







require __DIR__ . '/auth.php';