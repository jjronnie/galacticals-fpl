<x-app-layout>
    <main class="mx-auto max-w-6xl">
        <x-adsense />

        <section class="py-8 text-center sm:py-12">
            <p class="text-xs font-semibold uppercase tracking-widest text-accent">Premier League Fantasy Tracker</p>
            <h1 class="mt-2 text-3xl font-extrabold leading-tight text-white sm:text-4xl">
                Your League Story, Week by Week
            </h1>
            <p class="mx-auto mt-3 max-w-lg text-sm text-gray-400 sm:text-base">
                Track every gameweek, see who's rising and falling, and discover which managers are making the smartest moves.
            </p>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-500">
                        Open Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-500">
                        Create Free Account
                    </a>
                @endauth

                <a href="{{ route('public.leagues.list') }}" class="rounded-lg border border-gray-600 px-5 py-2.5 text-sm font-semibold text-gray-300 transition hover:border-accent hover:text-accent">
                    Explore Leagues
                </a>
            </div>
        </section>

        @if($currentEvent)
            <section class="w-full rounded-3xl border border-gray-700 bg-card">
                <div class="flex items-center justify-center gap-4 border-b border-gray-700 px-4 py-4 sm:px-8 sm:py-5">
                    @if($prevEvent)
                        <a href="{{ route('home', ['event' => $prevEvent]) }}"
                           class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gray-600 text-gray-300 transition hover:border-accent hover:text-accent">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                    @else
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gray-700 text-gray-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </span>
                    @endif

                    <h2 class="text-lg font-bold text-white sm:text-xl">
                        Gameweek {{ $currentEvent }}
                    </h2>

                    @if($nextEvent)
                        <a href="{{ route('home', ['event' => $nextEvent]) }}"
                           class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gray-600 text-gray-300 transition hover:border-accent hover:text-accent">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gray-700 text-gray-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    @endif
                </div>

                <div class="divide-y divide-gray-800/60">
                    @forelse($groupedByDate as $date => $dateFixtures)
                        <div class="px-3 py-3 sm:px-6 sm:py-4">
                            <p class="mb-2 text-center text-[10px] font-semibold uppercase tracking-widest text-gray-500 sm:mb-3 sm:text-xs">{{ $date }}</p>

                            <div class="space-y-1.5 sm:space-y-2">
                                @foreach($dateFixtures as $fixture)
                                    <div class="flex items-center justify-between gap-1 py-1.5 sm:gap-3 sm:py-2">
                                        <div class="flex flex-1 min-w-0 items-center justify-end gap-1.5 sm:gap-3">
                                            <div class="text-right min-w-0">
                                                <span class="block truncate text-[11px] font-semibold text-white sm:text-sm">
                                                    {{ $fixture->homeTeam?->name ?? 'TBD' }}
                                                </span>
                                            </div>
                                            @if($fixture->homeTeam)
                                                <img src="{{ route('img.team', $fixture->homeTeam->id) }}" alt="{{ $fixture->homeTeam->short_name }}" class="h-5 w-5 shrink-0 rounded object-contain sm:h-7 sm:w-7" loading="lazy" onerror="this.style.display='none'" />
                                            @endif
                                        </div>

                                        <div class="flex min-w-[48px] flex-col items-center justify-center sm:min-w-[80px]">
                                            @if($fixture->isFinished())
                                                <span class="text-xs font-extrabold tracking-tight text-white sm:text-lg">
                                                    {{ $fixture->team_h_score ?? '-' }} - {{ $fixture->team_a_score ?? '-' }}
                                                </span>
                                                <span class="text-[8px] font-bold uppercase tracking-wider text-gray-500 sm:text-[10px]">FT</span>
@elseif($fixture->isLive())
    <span class="text-xs font-extrabold tracking-tight text-red-400 animate-pulse sm:text-lg">
        {{ $fixture->team_h_score ?? 0 }} - {{ $fixture->team_a_score ?? 0 }}
    </span>
    <span class="text-[8px] font-bold uppercase tracking-wider text-red-400 sm:text-[10px] live-fixture-minutes" data-fixture-id="{{ $fixture->fpl_fixture_id }}" data-start-minutes="{{ $fixture->minutes }}">{{ $fixture->minutes }}'</span>
@endelseif
                                            @else
                                                <span class="text-[11px] font-bold text-white sm:text-base">
                                                    {{ $fixture->kickoff_time ? $fixture->kickoff_time->format('H:i') : 'TBC' }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex flex-1 min-w-0 items-center justify-start gap-1.5 sm:gap-3">
                                            @if($fixture->awayTeam)
                                                <img src="{{ route('img.team', $fixture->awayTeam->id) }}" alt="{{ $fixture->awayTeam->short_name }}" class="h-5 w-5 shrink-0 rounded object-contain sm:h-7 sm:w-7" loading="lazy" onerror="this.style.display='none'" />
                                            @endif
                                            <div class="text-left min-w-0">
                                                <span class="block truncate text-[11px] font-semibold text-white sm:text-sm">
                                                    {{ $fixture->awayTeam?->name ?? 'TBD' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <p class="text-sm font-semibold text-gray-400">No fixtures for Gameweek {{ $currentEvent }}</p>
                            <p class="mt-1 text-xs text-gray-600">Fixtures will appear once synced from the FPL API.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        @else
            <section class="mt-8 rounded-3xl border border-gray-700 bg-card p-12 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-800">
                    <svg class="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white">Premier League Fixtures</h2>
                <p class="mt-2 text-sm text-gray-500">Fixtures will appear here once the season data is synced.</p>
            </section>
        @endif

        <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="group relative overflow-hidden rounded-2xl border border-gray-700 bg-card p-6 transition hover:border-gray-600">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-accent/5 blur-xl transition group-hover:bg-accent/10"></div>
                <div class="relative">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10">
                        <svg class="h-5 w-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">League Summary</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-400">See leaders, biggest wins, lowest scores, and rivalry moments at a glance.</p>
                </div>
            </article>

            <article class="group relative overflow-hidden rounded-2xl border border-gray-700 bg-card p-6 transition hover:border-gray-600">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-green-600/5 blur-xl transition group-hover:bg-green-600/10"></div>
                <div class="relative">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-green-600/10">
                        <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Gameweek Tables</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-400">Open any gameweek and see who performed best and who struggled.</p>
                </div>
            </article>

            <article class="group relative overflow-hidden rounded-2xl border border-gray-700 bg-card p-6 transition hover:border-gray-600">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-purple-600/5 blur-xl transition group-hover:bg-purple-600/10"></div>
                <div class="relative">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-purple-600/10">
                        <svg class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Personal Profiles</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-400">Claim your team for personal analytics with points, transfers, chips, and trends.</p>
                </div>
            </article>

            <article class="group relative overflow-hidden rounded-2xl border border-gray-700 bg-card p-6 transition hover:border-gray-600">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-yellow-600/5 blur-xl transition group-hover:bg-yellow-600/10"></div>
                <div class="relative">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-yellow-600/10">
                        <svg class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Shareable Pages</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-400">Share league and profile pages with simple links and short codes.</p>
                </div>
            </article>
        </section>

        <x-adsense />

        <section class="mt-8 rounded-3xl border border-gray-700 bg-card p-8">
            <h2 class="text-xl font-bold text-white">How It Works</h2>
            <div class="mt-6 grid gap-6 md:grid-cols-3">
                <div class="relative rounded-2xl border border-gray-700/50 bg-gray-800/30 p-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/10 text-sm font-extrabold text-accent">1</span>
                    <h3 class="mt-4 text-base font-bold text-white">Create an Account</h3>
                    <p class="mt-2 text-sm text-gray-400">Sign up and confirm your email to unlock your personal dashboard.</p>
                </div>

                <div class="relative rounded-2xl border border-gray-700/50 bg-gray-800/30 p-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/10 text-sm font-extrabold text-accent">2</span>
                    <h3 class="mt-4 text-base font-bold text-white">Add Your League</h3>
                    <p class="mt-2 text-sm text-gray-400">Paste your classic league ID and let the app import all standings.</p>
                    <a href="{{ route('find') }}" class="mt-3 inline-flex text-xs font-semibold text-accent hover:underline">How to find league ID</a>
                </div>

                <div class="relative rounded-2xl border border-gray-700/50 bg-gray-800/30 p-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/10 text-sm font-extrabold text-accent">3</span>
                    <h3 class="mt-4 text-base font-bold text-white">Claim Your Profile</h3>
                    <p class="mt-2 text-sm text-gray-400">Search your FPL team and claim it for weekly personal analytics.</p>
                </div>
            </div>
        </section>

        <x-adsense />

        <section class="mt-8 rounded-3xl border border-gray-700 bg-card p-8">
            <h2 class="text-xl font-bold text-white">What You Can Track</h2>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="flex items-start gap-3 rounded-xl border border-gray-700/50 bg-gray-800/30 p-4">
                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-600/20">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-300">Longest top streak in your league</span>
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-gray-700/50 bg-gray-800/30 p-4">
                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-600/20">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-300">Most-owned and most-captained players per gameweek</span>
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-gray-700/50 bg-gray-800/30 p-4">
                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-600/20">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-300">Transfer trends and chip usage</span>
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-gray-700/50 bg-gray-800/30 p-4">
                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-600/20">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-300">Bench points, captaincy impact, and team value changes</span>
                </div>
            </div>
        </section>

        <x-adsense />

        <x-back-to-top />
    </main>
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there are any live fixtures on the page
        const hasLiveFixtures = document.querySelectorAll('.live-fixture-minutes').length > 0;
        
        if (hasLiveFixtures) {
            // Function to refresh fixture data
            function refreshFixtureData() {
                // Get current event from URL or default to first available
                const urlParams = new URLSearchParams(window.location.search);
                const currentEvent = urlParams.get('event') || 
                                   document.querySelector('[data-current-event]')?.getAttribute('data-current-event') || 1;
                                   
                // Fetch fresh fixture data via AJAX
                fetch(`/fixtures/update?event=${currentEvent}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.html) {
                        // Replace the fixture container with fresh data
                        const fixtureContainer = document.querySelector('.divide-y.divide-gray-800/60');
                        if (fixtureContainer) {
                            fixtureContainer.innerHTML = data.html;
                            
                            // Re-initialize live minute tracking for newly loaded fixtures
                            initializeLiveMinutes();
                        }
                    }
                })
                .catch(error => {
                    console.error('Failed to refresh fixture data:', error);
                });
            }
            
            // Function to initialize live minute tracking
            function initializeLiveMinutes() {
                // Function to update live fixture minutes
                function updateLiveFixtureMinutes() {
                    const liveMinutesElements = document.querySelectorAll('.live-fixture-minutes');
                    
                    liveMinutesElements.forEach(element => {
                        const fixtureId = element.getAttribute('data-fixture-id');
                        const startMinutes = parseInt(element.getAttribute('data-start-minutes')) || 0;
                        
                        // Calculate elapsed time since element creation
                        const elementLoadTime = element._loadTime || Date.now();
                        const elapsedSeconds = (Date.now() - elementLoadTime) / 1000;
                        const currentMinutes = startMinutes + Math.floor(elapsedSeconds / 60);
                        
                        element.textContent = currentMinutes + "'";
                    });
                }
                
                // Initialize load time for each element
                document.querySelectorAll('.live-fixture-minutes').forEach(element => {
                    if (!element._loadTime) {
                        element._loadTime = Date.now();
                    }
                });
                
                // Update immediately
                updateLiveFixtureMinutes();
                
                // Update every 30 seconds for live UI updates
                if (window.liveMinuteInterval) {
                    clearInterval(window.liveMinuteInterval);
                }
                window.liveMinuteInterval = setInterval(updateLiveFixtureMinutes, 30000);
            }
            
            // Initialize live minute tracking on load
            initializeLiveMinutes();
            
            // Refresh fixture data every 30 seconds during live matches
            // This catches new matches starting, goals, etc.
            if (window.fixtureRefreshInterval) {
                clearInterval(window.fixtureRefreshInterval);
            }
            window.fixtureRefreshInterval = setInterval(refreshFixtureData, 30000);
            
            // Also refresh immediately on load to ensure we have latest data
            refreshFixtureData();
        }
    });
</script>
