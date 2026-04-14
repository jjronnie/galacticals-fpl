<x-app-layout>
    <main class="mx-auto max-w-6xl">
        <x-adsense />

        @if($currentEvent)
            <section class="w-full rounded-3xl border border-gray-700 bg-card">
                <div class="flex items-center justify-center gap-4 border-b border-gray-700 px-4 py-4 sm:px-8 sm:py-5">
                    @if($prevEvent)
                        <a href="{{ route('fixtures', ['event' => $prevEvent]) }}"
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
                        <a href="{{ route('fixtures', ['event' => $nextEvent]) }}"
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

        <x-adsense />

        <x-back-to-top />
    </main>
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hasLiveFixtures = document.querySelectorAll('.live-fixture-minutes').length > 0;
        
        if (hasLiveFixtures) {
            function refreshFixtureData() {
                const urlParams = new URLSearchParams(window.location.search);
                const currentEvent = urlParams.get('event') || 
                                   document.querySelector('[data-current-event]')?.getAttribute('data-current-event') || 1;
                                   
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
                        const fixtureContainer = document.querySelector('.divide-y.divide-gray-800/60');
                        if (fixtureContainer) {
                            fixtureContainer.innerHTML = data.html;
                            initializeLiveMinutes();
                        }
                    }
                })
                .catch(error => {
                    console.error('Failed to refresh fixture data:', error);
                });
            }
            
            function initializeLiveMinutes() {
                function updateLiveFixtureMinutes() {
                    const liveMinutesElements = document.querySelectorAll('.live-fixture-minutes');
                    
                    liveMinutesElements.forEach(element => {
                        const fixtureId = element.getAttribute('data-fixture-id');
                        const startMinutes = parseInt(element.getAttribute('data-start-minutes')) || 0;
                        
                        const elementLoadTime = element._loadTime || Date.now();
                        const elapsedSeconds = (Date.now() - elementLoadTime) / 1000;
                        const currentMinutes = startMinutes + Math.floor(elapsedSeconds / 60);
                        
                        element.textContent = currentMinutes + "'";
                    });
                }
                
                document.querySelectorAll('.live-fixture-minutes').forEach(element => {
                    if (!element._loadTime) {
                        element._loadTime = Date.now();
                    }
                });
                
                updateLiveFixtureMinutes();
                
                if (window.liveMinuteInterval) {
                    clearInterval(window.liveMinuteInterval);
                }
                window.liveMinuteInterval = setInterval(updateLiveFixtureMinutes, 30000);
            }
            
            initializeLiveMinutes();
            
            if (window.fixtureRefreshInterval) {
                clearInterval(window.fixtureRefreshInterval);
            }
            window.fixtureRefreshInterval = setInterval(refreshFixtureData, 30000);
            
            refreshFixtureData();
        }
    });
</script>