<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\AdminController;


use Illuminate\Support\Facades\Route;

//public routes


Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/how-to-find-fpl-league-id', [FrontendController::class, 'findLeagueID'])->name('find');


Route::get('/privacy-policy', function () {
    return view('privacy_policy');
})->name('privacy.policy');

Route::get('/terms-and-conditions', function () {
    return view('terms');
})->name('terms');




Route::get('/leagues', [FrontendController::class, 'listLeagues'])->name('public.leagues.list');

Route::get('/leagues/{slug}/{gameweek?}', [FrontendController::class, 'showStats'])
    ->where('gameweek', '[0-9]+') 
    ->name('public.leagues.show');


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/standings', [AdminController::class, 'table'])->name('table');

    // --- League Setup/Management ---
    Route::get('/dashboard/setup', [AdminController::class, 'createLeague'])->name('admin.league.create');
    Route::post('/dashboard/setup', [AdminController::class, 'storeLeague'])->name('admin.league.store');
    Route::post('/leaugue/update', [AdminController::class, 'updateUserLeague'])->name('admin.league.update');



   
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});







require __DIR__ . '/auth.php';