@props(['business'])

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Auth;

    // Ensure the user is authenticated and the business object is valid
    if (!Auth::check() || !$business) {
        return;
    }

    // Rely on the Business Model helper methods defined previously
    $daysLeft = $business->trialDaysRemaining();
    $hasActiveSubscription = $business->subscriptions()->where('is_active', true)->exists();
    $isProfileComplete = $business->isProfileComplete();
    
    // Determine the primary status message and colors
    $isTrialActive = ($daysLeft !== null && $daysLeft > 0);
    $isSubscriptionNeeded = (!$isTrialActive && !$hasActiveSubscription);
    $isProfileIncomplete = !$isProfileComplete;

    $route = route('billing.index');
@endphp

{{-- 1. PROFILE INCOMPLETE: This takes priority and forces the user to the settings page. --}}
@if ($isProfileIncomplete)
    <section class="bg-red-50 border border-red-300 dark:bg-red-100 text-gray-900 rounded-lg p-4 mb-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-sm" role="alert">
        <div class="flex items-start md:items-center gap-3">
            <i data-lucide="settings" class="w-6 h-6 text-red-600"></i>

            <div>
                <h3 class="text-sm font-semibold text-gray-800">Action Required: Complete Setup</h3>
                <p class="text-sm text-gray-600">
                    You must complete your mandatory business settings (Name, Currency, Timezone, etc.) to proceed.
                </p>
            </div>
        </div>

        <div>
            <a href="{{ $route }}" class="btn bg-red-600 text-white hover:bg-red-700">
                Complete Settings
                <i data-lucide="chevrons-right" class="w-5 h-5 ml-2"></i>
            </a>
        </div>
    </section>

{{-- 2. TRIAL ACTIVE: Show a reminder of remaining days. --}}
@elseif($isTrialActive)
    <section class="bg-yellow-50 border border-yellow-200 dark:bg-amber-100 text-gray-900 rounded-lg p-4 mb-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-sm" role="alert">
        <div class="flex items-start md:items-center gap-3">
            <i data-lucide="crown" class="w-6 h-6 text-yellow-500"></i>

            <div>
                <h3 class="text-sm font-semibold text-gray-800">Trial Plan Active</h3>
                <p class="text-sm text-gray-600">
                    Your trial ends in
                    <span class="font-medium text-gray-900">{{ $daysLeft }} {{ Str::plural('day', $daysLeft) }}</span>. Upgrade now to prevent service interruption.
                </p>
            </div>
        </div>

        <div>
            <a href="{{ $route }}" class="btn ">
                Change Plan
                <i data-lucide="chevrons-right" class="w-5 h-5 ml-2"></i>
            </a>
        </div>
    </section>

{{-- 3. SUBSCRIPTION NEEDED: Trial expired and no paid plan. (Access is blocked by middleware) --}}
@elseif($isSubscriptionNeeded)
    <section class="bg-red-50 border border-red-300 rounded-lg p-4 mb-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-sm" role="alert">
        <div class="flex items-start md:items-center gap-3">
            <i data-lucide="alert-triangle" class="w-6 h-6 text-red-500"></i>

            <div>
                <h3 class="text-sm font-semibold text-gray-800">Subscription Required</h3>
                <p class="text-sm text-gray-600">
                    Your trial has ended and there is no active subscription. Your access to core features is restricted.
                </p>
            </div>
        </div>

        <div>
            <a href="{{ $route }}" class="btn bg-red-600 text-white hover:bg-red-700">
                Subscribe Now
                <i data-lucide="chevrons-right" class="w-5 h-5 ml-2"></i>
            </a>
        </div>
    </section>
@endif
