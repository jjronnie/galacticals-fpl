<x-app-layout>
    @php
        $sortedGameweeks = collect($availableGameweeks)->sortDesc()->values();
        $standingsRows = collect($gameweekStandings)->values();
        $gameweekAveragePoints = $standingsRows->isNotEmpty()
            ? round((float) $standingsRows->avg('points'), 2)
            : null;
        $bestPoints = $standingsRows->isNotEmpty() ? (int) $standingsRows->max('points') : null;
        $worstPoints = $standingsRows->isNotEmpty() ? (int) $standingsRows->min('points') : null;
    @endphp

    <main class="mx-auto max-w-7xl space-y-6 px-2 py-8 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-white">{{ $league->name }}</h1>
                    <p class="mt-1 text-sm text-gray-300">Gameweek {{ $targetGW }} Overview</p>
                </div>

                <div class="flex flex-wrap items-end gap-3">
                    <a href="{{ route('public.leagues.show', ['slug' => $league->slug]) }}" class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-gray-200 hover:bg-secondary">
                        League Page
                    </a>

                    <div class="w-full sm:w-auto">
                        <label for="gameweek-select" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-400">Switch GW</label>
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
            </div>
        </section>

        @include('leagues.partials.share')

        <x-adsense />

        @if ($gameweekInsights)
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <x-gw-stat-card title="Best Manager" color="green" tooltip="Manager(s) with highest points in this gameweek.">
                    @foreach ($gameweekInsights['best_managers'] as $manager)
                        <div>
                            <a href="{{ route('managers.show', $manager['entry_id']) }}" class="text-sm text-green-300 hover:text-green-200">
                                {{ $manager['name'] }}
                            </a>
                            <p class="text-xs text-gray-400">{{ $manager['team_name'] }}</p>
                        </div>
                    @endforeach
                    <p class="mt-2 text-xs text-gray-300">{{ $gameweekInsights['best_points'] }} pts</p>
                </x-gw-stat-card>

                <x-gw-stat-card title="Worst Manager" color="red" tooltip="Manager(s) with lowest points in this gameweek.">
                    @foreach ($gameweekInsights['worst_managers'] as $manager)
                        <div>
                            <a href="{{ route('managers.show', $manager['entry_id']) }}" class="text-sm text-red-300 hover:text-red-200">
                                {{ $manager['name'] }}
                            </a>
                            <p class="text-xs text-gray-400">{{ $manager['team_name'] }}</p>
                        </div>
                    @endforeach
                    <p class="mt-2 text-xs text-gray-300">{{ $gameweekInsights['worst_points'] }} pts</p>
                </x-gw-stat-card>

                <x-gw-stat-card title="Average" color="blue" tooltip="Average points scored in this gameweek.">
                    <p class="text-sm">
                        {{ $gameweekAveragePoints !== null ? rtrim(rtrim(number_format($gameweekAveragePoints, 2), '0'), '.') : 'N/A' }} pts
                    </p>
                </x-gw-stat-card>
            </section>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-gw-stat-card title="Most Captained" color="purple" tooltip="Most captained players.">
                @forelse (($ownershipTrends['most_captained'] ?? []) as $captain)
                    <p class="text-sm">{{ $captain['player'] }} - &copy; by {{ $captain['count'] }} manager(s)</p>
                @empty
                    <p class="text-sm">No data.</p>
                @endforelse
            </x-gw-stat-card>

            <x-gw-stat-card title="Most Transferred In" color="green" tooltip="Most transferred in players.">
                @forelse (($ownershipTrends['most_transferred_in'] ?? []) as $player)
                    <p class="text-sm">{{ $player['player'] }} - {{ $player['count'] }} time(s)</p>
                @empty
                    <p class="text-sm">No data.</p>
                @endforelse
            </x-gw-stat-card>

            <x-gw-stat-card title="Most Transferred Out" color="red" tooltip="Most transferred out players.">
                @forelse (($ownershipTrends['most_transferred_out'] ?? []) as $player)
                    <p class="text-sm">{{ $player['player'] }} - {{ $player['count'] }} time(s)</p>
                @empty
                    <p class="text-sm">No data.</p>
                @endforelse
            </x-gw-stat-card>

            <x-gw-stat-card title="Chips" color="yellow" tooltip="Chip usage in this gameweek.">
                @forelse (($ownershipTrends['chips_played'] ?? []) as $chip => $count)
                    <p class="text-sm">{{ $chip }} - {{ $count }}</p>
                @empty
                    <p class="text-sm">No data.</p>
                @endforelse
            </x-gw-stat-card>
        </section>

        @include('leagues.partials.team-of-week-list', [
            'teamOfWeek' => $teamOfWeek,
            'emptyText' => 'No team-of-the-week data for this gameweek.',
        ])

        <div x-data="{ visibleRows: 50, totalRows: {{ $standingsRows->count() }} }" class="space-y-4">
            <section class="-mx-2 overflow-x-auto rounded-lg border border-gray-700 bg-card p-2 sm:mx-0">
        <h2 class="text-2xl p-2 text-center font-bold text-white">Gameweek Leaderboard</h2>

                <table class="min-w-full whitespace-nowrap text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left whitespace-nowrap">#</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Manager</th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">GW</th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">
                                @if ($previousGameweek !== null)
                                    vs GW{{ $previousGameweek }}
                                @else
                                    Trend
                                @endif
                            </th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">Total</th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">vs Avg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($standingsRows as $index => $standing)
                            @php
                                $isBestManager = $bestPoints !== null && (int) $standing->points === $bestPoints;
                                $isWorstManager = $worstPoints !== null && (int) $standing->points === $worstPoints;
                                $managerId = (int) ($standing->manager_id ?? 0);
                                $previousStanding = $managerId > 0 ? ($previousStandingsByManager[$managerId] ?? null) : null;
                                $pointsDelta = $previousStanding !== null
                                    ? (int) $standing->points - (int) $previousStanding['points']
                                    : null;
                            @endphp
                            <tr class="border-b border-gray-800/80" x-show="{{ $index }} < visibleRows" @if ($index >= 50) x-cloak @endif>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @if ((int) $standing->rank === 1)
                                        <span aria-label="First place" title="First place">👑</span>
                                    @elseif ((int) $standing->rank === 2)
                                        <span aria-label="Second place" title="Second place">🥈</span>
                                    @elseif ((int) $standing->rank === 3)
                                        <span aria-label="Third place" title="Third place">🥉</span>
                                    @elseif ($isBestManager)
                                        <span aria-label="Best manager in this gameweek" title="Best manager in this gameweek">⭐</span>
                                    @else
                                        {{ $standing->rank }}
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if ($standing->manager)
                                        <a href="{{ route('managers.show', $standing->manager->entry_id) }}" class="block whitespace-nowrap text-white hover:text-white">
                                            {{ $standing->manager->player_name }}
                                        </a>
                                        <p class="whitespace-nowrap text-xs text-gray-400">{{ $standing->manager->team_name }}</p>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">{{ $standing->points }}</td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    @if ($pointsDelta === null)
                                        <span class="text-gray-400">-</span>
                                    @elseif ($pointsDelta > 0)
                                        <span class="font-semibold text-green-300">↑ {{ $pointsDelta }}</span>
                                    @elseif ($pointsDelta < 0)
                                        <span class="font-semibold text-red-300">↓ {{ abs($pointsDelta) }}</span>
                                    @else
                                        <span class="font-semibold text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">{{ $standing->total_points }}</td>
                                <td class="px-3 py-2 text-right whitespace-nowrap {{ $standing->difference_to_average >= 0 ? 'text-green-300' : 'text-red-300' }}">
                                    {{ $standing->difference_to_average >= 0 ? '+' : '' }}{{ $standing->difference_to_average }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-gray-400">No standings for this gameweek.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            @if ($standingsRows->count() > 50)
                <div class="flex justify-center" x-show="visibleRows < totalRows">
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

        <x-back-to-top />
    </main>
</x-app-layout>
