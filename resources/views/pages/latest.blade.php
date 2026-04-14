<x-app-layout>
    <main class="mx-auto max-w-lg">
        <x-adsense />

        @if($managerOfWeek)
            <section class="mt-6 px-2">
                <div class="flex items-center gap-4 rounded-3xl bg-card p-4 shadow-lg">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border-2 border-white/20 bg-black/40 overflow-hidden">
                        @if($managerOfWeek['favourite_team_id'])
                            <img src="{{ route('img.team', $managerOfWeek['favourite_team_id']) }}" alt="Team" class="h-full w-full rounded-full object-contain p-1" />
                        @else
                            <img src="{{ asset('assets/img/logo-light.webp') }}" alt="Team" class="h-full w-full rounded-full object-contain p-1" />
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-400 truncate">{{ $managerOfWeek['league_name'] }}</p>
                        <h3 class="text-lg font-bold text-white truncate">{{ $managerOfWeek['name'] }}</h3>
                        <p class="text-sm text-gray-400 truncate">{{ $managerOfWeek['team_name'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-extrabold text-green-400">{{ $managerOfWeek['points'] }}</p>
                        <div class="flex items-center gap-1 text-xs font-semibold text-yellow-400">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span>Manager of the Week</span>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if($playerOfWeek)
            <section class="mt-6 px-2">
                <div class="relative overflow-hidden rounded-3xl bg-card shadow-2xl">
                    <div class="relative aspect-[3/4] w-full sm:aspect-[4/3] bg-card">
                        <img 
                            src="{{ route('img.player', $playerOfWeek['player_id']) }}" 
                            alt="{{ $playerOfWeek['web_name'] }}"
                            class="h-full w-full object-contain"
                            loading="lazy"
                        />
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/60 to-transparent pt-20 pb-4 px-6">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-full border-2 border-white/30 bg-black/40 backdrop-blur-sm overflow-hidden">
                                        <img src="{{ route('img.team', $playerOfWeek['team_id'] ?? 0) }}" alt="Team" class="h-full w-full rounded-full object-contain p-0.5" />
                                    </div>
                                    <div>
                                        <h3 class="text-base sm:text-xl font-bold text-white">{{ $playerOfWeek['web_name'] }}</h3>
                                        <p class="text-[10px] sm:text-xs text-gray-300">{{ $playerOfWeek['team_name'] ?? '' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl sm:text-3xl font-extrabold text-green-400">{{ $playerOfWeek['points'] }}</p>
                                    <p class="text-[10px] sm:text-xs font-medium text-gray-400">points</p>
                                </div>
                            </div>
                            <div class="mt-3 sm:mt-4 flex items-center justify-between border-t border-white/10 pt-3 sm:pt-4">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full bg-white/10 px-2 sm:px-3 py-0.5 sm:py-1 text-[10px] sm:text-xs font-semibold text-white">GW{{ $playerOfWeek['gameweek'] }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-xs sm:text-sm font-semibold text-yellow-400">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span>Player of the Week</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @else
            <section class="mt-8 rounded-3xl border border-gray-700 bg-card p-12 text-center mx-2">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-800">
                    <svg class="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white">No Data Yet</h2>
                <p class="mt-2 text-sm text-gray-500">Manager and player of the week will appear once we have gameweek data.</p>
            </section>
        @endif

        <x-adsense />

        <x-back-to-top />
    </main>
</x-app-layout>