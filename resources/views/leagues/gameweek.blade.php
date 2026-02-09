<x-app-layout>
    <main class="mx-auto max-w-7xl space-y-8 px-2 py-8 sm:px-6 lg:px-8">
        <x-adsense />

        <section class="space-y-6">
            @php
                $sortedGameweeks = collect($availableGameweeks)->sortDesc()->values();
                $standingsRows = collect($gameweekStandings)->values();
            @endphp

            <div class="text-center">
                <h1 class="text-2xl font-extrabold text-white">{{ $league->name }}</h1>
                <p class="mt-2 text-sm text-gray-300">Gameweek {{ $targetGW }} Overview</p>
            </div>

            @include('leagues.partials.share')

            <div class="flex flex-wrap items-end justify-center gap-3 rounded-xl border border-gray-700 bg-card p-4">
                <a href="{{ route('public.leagues.show', ['slug' => $league->slug]) }}" class="btn">League Overview</a>

                <div class="w-full sm:w-auto">
                    <label for="gameweek-select" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-400">Switch Gameweek</label>
                    <select
                        id="gameweek-select"
                        class="w-full rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white focus:border-accent focus:ring-accent"
                        onchange="if (this.value) { window.location.href = this.value; }"
                    >
                        @foreach ($sortedGameweeks as $gameweek)
                            <option
                                value="{{ route('public.leagues.gameweek.show', ['slug' => $league->slug, 'gameweek' => $gameweek]) }}"
                                @selected($targetGW === $gameweek)
                            >
                                GW {{ $gameweek }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            @if ($gameweekInsights)
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-gw-stat-card title="BEST MANAGER" color="green" tooltip="Manager(s) with the highest points in this gameweek.">
                        @foreach ($gameweekInsights['best_managers'] as $manager)
                            <div>
                                <a href="{{ route('managers.show', $manager['entry_id']) }}" class="text-sm text-green-400 hover:text-green-400">
                                    {{ $manager['name'] }}
                                </a>
                                <p class="text-xs text-gray-400">{{ $manager['team_name'] }}</p>
                            </div>
                        @endforeach
                        <p class="mt-2 text-xs text-gray-300">{{ $gameweekInsights['best_points'] }} points</p>
                    </x-gw-stat-card>

                    <x-gw-stat-card title="WORST MANAGER" color="red" tooltip="Manager(s) with the lowest points in this gameweek.">
                        @foreach ($gameweekInsights['worst_managers'] as $manager)
                            <div>
                                <a href="{{ route('managers.show', $manager['entry_id']) }}" class="text-sm text-red-400 hover:text-red-400">
                                    {{ $manager['name'] }}
                                </a>
                                <p class="text-xs text-gray-400">{{ $manager['team_name'] }}</p>
                            </div>
                        @endforeach
                        <p class="mt-2 text-xs text-gray-300">{{ $gameweekInsights['worst_points'] }} points</p>
                    </x-gw-stat-card>

                    <x-gw-stat-card title="MOST OWNED (TOP 5)" color="blue" tooltip="Most popular players in this gameweek.">
                        @forelse (($ownershipTrends['most_owned'] ?? []) as $owned)
                            <p class="text-sm">{{ $owned['player'] }} - {{ $owned['percent'] }}%</p>
                        @empty
                            <p class="text-sm">No ownership data.</p>
                        @endforelse
                    </x-gw-stat-card>

                    <x-gw-stat-card title="TOP DIFFERENTIALS" color="yellow" tooltip="Low-owned players who returned strong points.">
                        @forelse (($ownershipTrends['differentials'] ?? []) as $diff)
                            <p class="text-sm">{{ $diff['player'] }} - {{ $diff['average_points'] }} pts ({{ $diff['ownership_percent'] }}%)</p>
                        @empty
                            <p class="text-sm">No differential data.</p>
                        @endforelse
                    </x-gw-stat-card>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-gw-stat-card title="MOST CAPTAINED" color="purple" tooltip="Players captained most often in this gameweek.">
                    @forelse (($ownershipTrends['most_captained'] ?? []) as $captain)
                        <p class="text-sm">{{ $captain['player'] }} - {{ $captain['count'] }}</p>
                    @empty
                        <p class="text-sm">No captaincy data.</p>
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="MOST TRANSFERRED IN" color="green" tooltip="Players most managers brought in compared to previous gameweek.">
                    @forelse (($ownershipTrends['most_transferred_in'] ?? []) as $player)
                        <p class="text-sm">{{ $player['player'] }} - {{ $player['count'] }}</p>
                    @empty
                        <p class="text-sm">No transfer-in data.</p>
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="MOST TRANSFERRED OUT" color="red" tooltip="Players most managers sold compared to previous gameweek.">
                    @forelse (($ownershipTrends['most_transferred_out'] ?? []) as $player)
                        <p class="text-sm">{{ $player['player'] }} - {{ $player['count'] }}</p>
                    @empty
                        <p class="text-sm">No transfer-out data.</p>
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="CHIPS PLAYED" color="yellow" tooltip="Chip counts used by managers in this gameweek.">
                    @forelse (($ownershipTrends['chips_played'] ?? []) as $chip => $count)
                        <p class="text-sm">{{ $chip }} - {{ $count }}</p>
                    @empty
                        <p class="text-sm">No chips played this GW.</p>
                    @endforelse
                </x-gw-stat-card>
            </div>

            <div x-data="{ visibleRows: 50, totalRows: {{ $standingsRows->count() }} }">
                <div class="-mx-2 overflow-x-auto sm:mx-0">
                    <table class="min-w-full text-sm text-gray-200">
                        <thead>
                            <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                                <th class="px-3 py-2 text-left">Rank</th>
                                <th class="px-3 py-2 text-left">Manager</th>
                                <th class="px-3 py-2 text-right">Points</th>
                                <th class="px-3 py-2 text-right">Total</th>
                                <th class="px-3 py-2 text-right">Diff to Avg</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($standingsRows as $index => $standing)
                                <tr class="border-b border-gray-800/80" x-show="{{ $index }} < visibleRows" @if ($index >= 50) x-cloak @endif>
                                    <td class="px-3 py-2">{{ $standing->rank }}</td>
                                    <td class="px-3 py-2">
                                        @if ($standing->manager)
                                            <a href="{{ route('managers.show', $standing->manager->entry_id) }}" class="text-white hover:text-white">
                                                {{ $standing->manager->player_name }}
                                            </a>
                                            <p class="text-xs text-gray-400">{{ $standing->manager->team_name }}</p>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right">{{ $standing->points }}</td>
                                    <td class="px-3 py-2 text-right">{{ $standing->total_points }}</td>
                                    <td class="px-3 py-2 text-right {{ $standing->difference_to_average >= 0 ? 'text-green-300' : 'text-red-300' }}">
                                        {{ $standing->difference_to_average >= 0 ? '+' : '' }}{{ $standing->difference_to_average }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-gray-400">No gameweek standings found for this gameweek.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($standingsRows->count() > 50)
                    <div class="mt-4 flex justify-center" x-show="visibleRows < totalRows">
                        <button
                            type="button"
                            class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary"
                            @click="visibleRows += 50"
                        >
                            Load More
                        </button>
                    </div>
                @endif
            </div>
        </section>

        @include('leagues.partials.share')

        <x-adsense />
        <x-back-to-top />
    </main>
</x-app-layout>
