<x-app-layout>
    <main class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <section class="space-y-6">
            @php
                $sortedGwPerformance = collect($gwPerformance)->sortByDesc('gameweek')->values();
            @endphp

            <div class="text-center">
                <h1 class="text-2xl font-extrabold text-white">{{ $league->name }}</h1>
                <p class="mt-2 text-sm text-gray-300">{{ $league->season }}/{{ $league->season + 1 }} Season Analytics</p>
            </div>
        </section>

        @include('leagues.partials.tabs', [
            'league' => $league,
            'activeTab' => 'performance',
            'availableGameweeks' => $availableGameweeks,
            'selectedGameweek' => $currentGW,
        ])

        <section x-data="{ visibleCards: 10, totalCards: {{ $sortedGwPerformance->count() }} }" class="-mx-2 space-y-4 sm:mx-0">
            <h2 id="performance" class="px-2 text-center text-lg font-bold text-white sm:px-0">Gameweek Best & Worst Performers</h2>

            <div class="space-y-3  sm:px-0">
                @forelse ($sortedGwPerformance as $index => $gw)
                    <div x-show="{{ $index }} < visibleCards" @if ($index >= 10) x-cloak @endif>
                        <x-gw-card :gw="$gw" :league="$league" />
                    </div>
                @empty
                    <div class="rounded-xl border border-gray-700 bg-card p-6 text-center text-gray-400">No gameweek data available yet.</div>
                @endforelse
            </div>

            @if ($sortedGwPerformance->count() > 10)
                <div class="mt-2 flex justify-center px-2 sm:px-0">
                    <button
                        x-show="visibleCards < totalCards"
                        type="button"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary"
                        @click="visibleCards = Math.min(totalCards, visibleCards + 10)"
                    >
                        Load More
                    </button>

                    <button
                        x-show="visibleCards >= totalCards"
                        type="button"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary"
                        @click="visibleCards = 10"
                    >
                        Show Less
                    </button>
                </div>
            @endif
        </section>

        @include('leagues.partials.share')

        @guest
            <div class="flex justify-center">
                <a href="{{ route('register') }}" class="rounded-lg bg-green-600 px-6 py-2 font-semibold text-white shadow-md transition hover:bg-green-500">
                    Create account for your league
                </a>
            </div>
        @endguest

        <x-back-to-top />
    </main>
</x-app-layout>
