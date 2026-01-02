<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemController;


//public routes
Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/more', [FrontendController::class, 'more'])->name('more');
Route::get('/how-to-find-fpl-league-id', [FrontendController::class, 'findLeagueID'])->name('find');
Route::get('/privacy-policy', [FrontendController::class, 'policy'])->name('privacy.policy');
Route::get('/terms-and-conditions', [FrontendController::class, 'terms'])->name('terms');
Route::get('/sitemap.xml', function () {
    return response()->file(public_path('sitemap.xml'));
});

Route::get('/leagues', [FrontendController::class, 'index'])->name('public.leagues.list');
 Route::get('/leagues/managers/history', [LeagueController::class, 'managers'])
    ->name('table');








Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [LeagueController::class, 'index'])->name('dashboard');
    Route::get('/leagues/setup', [LeagueController::class, 'create'])->name('league.create');

    Route::get('/leagues/confirm', [LeagueController::class, 'confirm'])
    ->name('leagues.confirm');

Route::post('/leagues/confirm', [LeagueController::class, 'confirmAction'])
    ->name('leagues.confirm.action');


    Route::post('/leagues/setup', [LeagueController::class, 'store'])->name('league.store')->middleware('throttle:5,1');
    Route::post('/leagues/update', [LeagueController::class, 'update'])->name('league.update');


    Route::get('/api/league/{leagueId}/status', [LeagueController::class, 'getLeagueStatus']);


});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('admin', AdminController::class);

    Route::post('/admin/send-league-reminders', [AdminController::class, 'sendMissingLeagueReminders'])
    ->name('admin.send.league.reminders');



    Route::post('/run-league-update', [SystemController::class, 'runLeagueUpdate'])
        ->name('run.league.update');

     


});







require __DIR__ . '/auth.php';


Route::get('/s/{code}', [FrontendController::class, 'shortCode'])
    ->name('short.league');

Route::get('/leagues/{slug}/{gameweek?}', [LeagueController::class, 'show'])
    ->where('gameweek', '[0-9]+')
    ->name('public.leagues.show');



   
