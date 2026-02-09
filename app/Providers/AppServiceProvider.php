<?php

namespace App\Providers;

use App\Models\ClaimsComplaint;
use App\Models\ProfileVerificationSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());

        View::composer(['layouts.sidebar', 'layouts.nav'], function ($view): void {
            $pendingVerificationsCount = 0;
            $openComplaintsCount = 0;

            if (auth()->check() && auth()->user()->isAdmin()) {
                $pendingVerificationsCount = ProfileVerificationSubmission::query()
                    ->where('status', 'pending')
                    ->count();

                $openComplaintsCount = ClaimsComplaint::query()
                    ->whereIn('status', ['open', 'in_progress'])
                    ->count();
            }

            $view->with([
                'pendingVerificationsCount' => $pendingVerificationsCount,
                'openComplaintsCount' => $openComplaintsCount,
            ]);
        });
    }
}
