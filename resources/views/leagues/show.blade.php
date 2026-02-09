<x-app-layout>
    <main class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <x-adsense />

        <section class="space-y-6">
            @php
                $sortedGameweeks = collect($availableGameweeks)->sortDesc()->values();
                $sortedGwPerformance = collect($gwPerformance)->sortByDesc('gameweek')->values();
            @endphp

            <div class="text-center">
                <h1 class="text-2xl font-extrabold text-white">{{ $league->name }}</h1>
                <p class="mt-2 text-sm text-gray-300">{{ $league->season }}/{{ $league->season + 1 }} Season Analytics</p>
            </div>

            @include('leagues.partials.share')

            <div class="flex flex-wrap items-end justify-center gap-3 rounded-xl border border-gray-700 bg-card p-4">
                @auth
                    @if (auth()->user()->isAdmin())
                        <form action="{{ route('league.update') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn">Update <i class="fa-solid fa-sync"></i></button>
                        </form>
                    @endif
                @endauth

                <div class="w-full sm:w-auto">
                    <label for="gameweek-select" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-400">Gameweek Overview</label>
                    <select
                        id="gameweek-select"
                        class="w-full rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white focus:border-accent focus:ring-accent"
                        onchange="if (this.value) { window.location.href = this.value; }"
                    >
                        <option value="" selected>Choose Gameweek</option>
                        @foreach ($sortedGameweeks as $gameweek)
                            <option value="{{ route('public.leagues.gameweek.show', ['slug' => $league->slug, 'gameweek' => $gameweek]) }}">
                                GW {{ $gameweek }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <x-gw-stat-card title="THE 100+ KINGS" color="pink" tooltip="Managers who scored at least 100 points in a single gameweek.">
                    @forelse($stats['hundred_plus_league'] as $entry)
                        <p class="text-sm">- {{ $entry }}</p>
                    @empty
                        <p class="text-sm">No 100+ scores yet.</p>
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="MOST GW LEADS" color="green" tooltip="Who finished as top scorer in the most gameweeks.">
                    @forelse($stats['most_gw_leads'] as $name => $count)
                        {{ $name }} - {{ $count }} times <br>
                    @empty
                        - - -
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="MOST GW LAST" color="red" tooltip="Who finished bottom in the most gameweeks.">
                    @forelse($stats['most_gw_last'] as $name => $count)
                        {{ $name }} - {{ $count }} times <br>
                    @empty
                        - - -
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="HIGHEST GW POINTS" color="green" tooltip="Single highest score recorded in one gameweek.">
                    {{ $stats['highest_gw_score']['points'] }} - {{ $stats['highest_gw_score']['manager'] }}
                    (GW {{ $stats['highest_gw_score']['gw'] }})
                </x-gw-stat-card>

                <x-gw-stat-card title="LEAST GW POINTS" color="red" tooltip="Single lowest score recorded in one gameweek.">
                    {{ $stats['lowest_gw_score']['points'] }} - {{ $stats['lowest_gw_score']['manager'] }}
                    (GW {{ $stats['lowest_gw_score']['gw'] }})
                </x-gw-stat-card>

                <x-gw-stat-card title="LONGEST TOP STREAK" color="yellow" tooltip="Longest run of staying first in the overall league table.">
                    @if (($stats['longest_top_streak']['length'] ?? 0) > 0)
                        <p class="text-sm">{{ $stats['longest_top_streak']['manager'] }} led for {{ $stats['longest_top_streak']['length'] }} gameweeks</p>
                        <p class="text-xs text-gray-400">GW {{ $stats['longest_top_streak']['start_gw'] }} to GW {{ $stats['longest_top_streak']['end_gw'] }}</p>
                    @else
                        <p class="text-sm">No clear single-manager top streak yet.</p>
                    @endif
                </x-gw-stat-card>

                <x-gw-stat-card title="THE BLOWOUT" color="green" tooltip="Largest gap between best and worst manager in one gameweek.">
                    @if ($stats['the_blowout']['difference'] > 0)
                        <p class="text-sm">{{ $stats['the_blowout']['difference'] }} point gap (GW {{ $stats['the_blowout']['gw'] }})</p>
                        <p class="text-xs text-gray-400">High: {{ $stats['the_blowout']['highest_scorer'] }} ({{ $stats['the_blowout']['highest_points'] }})</p>
                        <p class="text-xs text-gray-400">Low: {{ $stats['the_blowout']['lowest_scorer'] }} ({{ $stats['the_blowout']['lowest_points'] }})</p>
                    @else
                        <p class="text-sm">No gameweek data available yet.</p>
                    @endif
                </x-gw-stat-card>

                <x-gw-stat-card title="COUNTRY DISTRIBUTION" color="blue" tooltip="Where managers in this league come from.">
                    @forelse($stats['country_distribution'] as $country => $count)
                        <p class="text-sm">{{ $country }} - {{ $count }}</p>
                    @empty
                        <p class="text-sm">No region data yet.</p>
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="FAVOURITE TEAMS" color="blue" tooltip="How many managers selected each favourite club.">
                    @forelse($stats['favourite_team_totals'] as $team => $count)
                        <p class="text-sm">{{ $team }} - {{ $count }}</p>
                    @empty
                        <p class="text-sm">No favourite teams captured yet.</p>
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="CHIP INSIGHTS" color="purple" tooltip="Most used, least used, and best-performing chips so far.">
                    <p class="text-sm">Most used: {{ $stats['most_used_chip'] ? (strtoupper($stats['most_used_chip']) === '3XC' ? 'Tripple Captain' : strtoupper($stats['most_used_chip'])) : 'N/A' }}</p>
                    <p class="text-sm">Least used: {{ $stats['least_used_chip'] ? (strtoupper($stats['least_used_chip']) === '3XC' ? 'Tripple Captain' : strtoupper($stats['least_used_chip'])) : 'N/A' }}</p>
                    <p class="text-sm">Most effective: {{ $stats['most_effective_chip'] ? (strtoupper($stats['most_effective_chip']) === '3XC' ? 'Tripple Captain' : strtoupper($stats['most_effective_chip'])) : 'N/A' }}</p>
                </x-gw-stat-card>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <x-gw-stat-card title="LEAGUE ZONES" color="green" tooltip="League split into top and bottom zones by total points.">
                    <p class="text-sm font-semibold text-blue-300">Champions League</p>
                    @foreach ($stats['league_zones']['champions_league'] as $manager)
                        <p class="text-sm">- {{ $manager }}</p>
                    @endforeach

                    <p class="mt-2 text-sm font-semibold text-yellow-300">Europa League</p>
                    @foreach ($stats['league_zones']['europa_league'] as $manager)
                        <p class="text-sm">- {{ $manager }}</p>
                    @endforeach

                    <p class="mt-2 text-sm font-semibold text-red-300">Relegation Zone</p>
                    @foreach ($stats['league_zones']['relegation_zone'] as $manager)
                        <p class="text-sm">- {{ $manager }}</p>
                    @endforeach
                </x-gw-stat-card>

                <x-gw-stat-card title="WOODEN SPOON CONTENDERS" color="red" tooltip="Managers currently at risk of finishing bottom.">
                    @forelse ($stats['wooden_spoon_contenders'] as $contender)
                        <p class="text-sm">- {{ $contender }}</p>
                    @empty
                        <p class="text-sm">No managers yet.</p>
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="HALL OF SHAME" color="purple" tooltip="Managers who have hit bottom position at least three times.">
                    @forelse($stats['hall_of_shame'] as $name => $count)
                        <p class="text-sm">- {{ $name }} {{ $count }} times</p>
                    @empty
                        <p class="text-sm">No one has been last 3+ times.</p>
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="NEVER BEST IN A GW" color="yellow" tooltip="Managers who have not yet topped any gameweek.">
                    @forelse($stats['never_best_in_gw'] as $name)
                        <p class="text-sm">- {{ $name }}</p>
                    @empty
                        <p class="text-sm">Everyone has had a best GW.</p>
                    @endforelse
                </x-gw-stat-card>
            </div>
        </section>

        <x-adsense />

        <section>
            <h2 id="performance" class="mb-4 text-center text-2xl font-bold text-white">Gameweek Performance</h2>
            <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-3">
                @forelse ($sortedGwPerformance as $gw)
                    <x-gw-card :gw="$gw" :league="$league" />
                @empty
                    <div class="rounded-xl border border-gray-700 bg-card p-6 text-center text-gray-400">No gameweek data available yet.</div>
                @endforelse
            </div>
        </section>

        @include('leagues.partials.share')

        <x-adsense />

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
