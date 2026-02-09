<x-app-layout>

    <x-adsense />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

      

            {{-- @include('leagues.partials.progress') --}}


            @include('leagues.partials.dash-header') 

            <!-- Season Highlights -->
            <div>
                <x-page-title title="League Info" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    <x-stat-card title="League ID" value="{{ $league->league_id ?? '-' }}" icon="hash" />
                    <x-stat-card title="Joined {{ config('app.name') }}"
                        value="{{ $league->created_at->diffForHumans() ?? '-' }}" icon="calendar-clock" />
                    <x-stat-card title="Season" value="{{ $league->season ?? '-' }}/{{ $league->season +1 }} " icon="calendar-range" />
                    <x-stat-card title="Total Managers" value="{{ $totalManagers ?? '-' }}" icon="users" />


                </div>
            </div>

            @if (! $hasClaimedProfile)
                <section class="rounded-2xl border border-dashed border-gray-600 bg-card p-6">
                    <h2 class="text-lg font-semibold text-white">Claim Your Personal Profile</h2>
                    <p class="mt-2 text-sm text-gray-300">
                        Your league is set up. Claim your profile to unlock personal analytics and shareable profile stats.
                    </p>
                    <a href="{{ route('profile.search') }}" class="mt-4 inline-flex rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                        Search and Claim Profile
                    </a>
                </section>
            @endif

            <x-adsense />


        </div>
    </div>
    <x-adsense />
</x-app-layout>
