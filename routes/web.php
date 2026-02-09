<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDataController;
use App\Http\Controllers\AdminManagerController;
use App\Http\Controllers\ClaimComplaintController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\ManagerProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/more', [FrontendController::class, 'more'])->name('more');
Route::get('/how-to-find-fpl-league-id', [FrontendController::class, 'findLeagueID'])->name('find');
Route::get('/privacy-policy', [FrontendController::class, 'policy'])->name('privacy.policy');
Route::get('/terms-and-conditions', [FrontendController::class, 'terms'])->name('terms');
Route::get('/sitemap.xml', fn () => response()->file(public_path('sitemap.xml')));

Route::get('/leagues', [FrontendController::class, 'index'])->name('public.leagues.list');

Route::get('/s/{code}', [FrontendController::class, 'shortCode'])->name('short.league');
Route::get('/p/{code}', [ManagerProfileController::class, 'short'])->name('managers.short');
Route::get('/managers/{entryId}', [ManagerProfileController::class, 'show'])
    ->whereNumber('entryId')
    ->name('managers.show');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', [LeagueController::class, 'index'])->name('dashboard');
    Route::get('/leagues/setup', [LeagueController::class, 'create'])->name('league.create');
    Route::get('/leagues/confirm', [LeagueController::class, 'confirm'])->name('leagues.confirm');
    Route::post('/leagues/confirm', [LeagueController::class, 'confirmAction'])->name('leagues.confirm.action');

    Route::post('/leagues/setup', [LeagueController::class, 'store'])
        ->name('league.store')
        ->middleware('throttle:5,1');

    Route::post('/leagues/update', [LeagueController::class, 'update'])->name('league.update');

    Route::get('/api/league/{leagueId}/status', [LeagueController::class, 'getLeagueStatus']);

    Route::get('/leagues/managers/history', [LeagueController::class, 'managers'])->name('table');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/search', [ProfileController::class, 'search'])->name('profile.search');
    Route::get('/profile/search/results', [ProfileController::class, 'searchResults'])->name('profile.search.results');
    Route::get('/profile/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/profile/claim/{manager}', [ProfileController::class, 'claim'])->name('profile.claim');
    Route::post('/profile/unclaim/{manager}', [ProfileController::class, 'unclaim'])->name('profile.unclaim');
    Route::post('/profile/complaint/{manager}', [ClaimComplaintController::class, 'store'])
        ->middleware('throttle:3,1440')
        ->name('profile.complaint');
});

Route::middleware(['auth', 'can:admin'])->group(function (): void {
    Route::resource('admin', AdminController::class)->only(['index', 'update', 'destroy']);

    Route::post('/admin/send-league-reminders', [AdminController::class, 'sendMissingLeagueReminders'])
        ->name('admin.send.league.reminders');

    Route::post('/run-league-update', [SystemController::class, 'runLeagueUpdate'])
        ->name('run.league.update');

    Route::get('/admin/data', [AdminDataController::class, 'index'])->name('admin.data');
    Route::post('/admin/data/fetch-fpl', [AdminDataController::class, 'fetchFpl'])->name('admin.data.fetchFpl');
    Route::post('/admin/data/fetch-managers', [AdminDataController::class, 'fetchManagers'])->name('admin.data.fetchManagers');
    Route::post('/admin/data/compute-gameweeks', [AdminDataController::class, 'computeGameweeks'])->name('admin.data.computeGameweeks');
    Route::post('/admin/data/refresh-league/{league}', [AdminDataController::class, 'refreshLeague'])->name('admin.data.refreshLeague');

    Route::get('/admin/managers', [AdminManagerController::class, 'index'])->name('admin.managers.index');
    Route::patch('/admin/managers/{manager}/suspend', [AdminManagerController::class, 'suspend'])->name('admin.managers.suspend');
    Route::patch('/admin/managers/{manager}/unsuspend', [AdminManagerController::class, 'unsuspend'])->name('admin.managers.unsuspend');
    Route::patch('/admin/managers/{manager}/disband', [AdminManagerController::class, 'disband'])->name('admin.managers.disband');

    Route::get('/admin/complaints', [ClaimComplaintController::class, 'index'])->name('admin.complaints.index');
    Route::patch('/admin/complaints/{complaint}/resolve', [ClaimComplaintController::class, 'resolve'])->name('admin.complaints.resolve');
});

Route::get('/leagues/{slug}/gameweeks/{gameweek}', [LeagueController::class, 'showGameweek'])
    ->whereNumber('gameweek')
    ->name('public.leagues.gameweek.show');

Route::get('/leagues/{slug}/{gameweek?}', [LeagueController::class, 'show'])
    ->where('gameweek', '[0-9]+')
    ->name('public.leagues.show');

require __DIR__.'/auth.php';
