<x-app-layout>
    @php
        $standingsRows = collect($gameweekStandings)->values();
        $gameweekAveragePoints = $standingsRows->isNotEmpty()
            ? round((float) $standingsRows->avg('points'), 2)
            : null;
        $bestPoints = $standingsRows->isNotEmpty() ? (int) $standingsRows->max('points') : null;
        $worstPoints = $standingsRows->isNotEmpty() ? (int) $standingsRows->min('points') : null;
    @endphp

    <main class="mx-auto max-w-7xl space-y-6">
        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="text-center">
                <h1 class="text-2xl font-extrabold text-white">{{ $league->name }}</h1>
                <p class="mt-1 text-sm text-gray-300">Gameweek {{ $targetGW }} Overview</p>
            </div>
        </section>

        @include('leagues.partials.tabs', [
            'league' => $league,
            'activeTab' => 'gameweeks',
            'availableGameweeks' => $availableGameweeks,
            'selectedGameweek' => $targetGW,
        ])


        @if ($gameweekInsights)
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">BEST MANAGER</h3>
                        <i data-lucide="award" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 space-y-2 text-sm text-gray-300">
                        @foreach ($gameweekInsights['best_managers'] as $manager)
                            <div class="rounded-lg bg-card px-3 py-2">
                                <a href="{{ route('managers.show', $manager['entry_id']) }}" class="font-semibold text-green-300 hover:text-green-200">
                                    {{ $manager['name'] }}
                                </a>
                                <p class="text-xs text-gray-400">{{ $manager['team_name'] }}</p>
                            </div>
                        @endforeach
                        <p class="inline-flex rounded-md bg-green-500/20 px-3 py-1 text-sm font-semibold text-green-300">{{ $gameweekInsights['best_points'] }} pts</p>
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">WORST MANAGER</h3>
                        <i data-lucide="badge-x" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 space-y-2 text-sm text-gray-300">
                        @foreach ($gameweekInsights['worst_managers'] as $manager)
                            <div class="rounded-lg bg-card px-3 py-2">
                                <a href="{{ route('managers.show', $manager['entry_id']) }}" class="font-semibold text-red-300 hover:text-red-200">
                                    {{ $manager['name'] }}
                                </a>
                                <p class="text-xs text-gray-400">{{ $manager['team_name'] }}</p>
                            </div>
                        @endforeach
                        <p class="inline-flex rounded-md bg-red-500/20 px-3 py-1 text-sm font-semibold text-red-300">{{ $gameweekInsights['worst_points'] }} pts</p>
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">AVERAGE</h3>
                        <i data-lucide="bar-chart-3" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 text-sm text-gray-300">
                        <p class="rounded-lg bg-card px-3 py-2 font-semibold text-accent">
                            {{ $gameweekAveragePoints !== null ? rtrim(rtrim(number_format($gameweekAveragePoints, 2), '0'), '.') : 'N/A' }} pts
                        </p>
                    </div>
                </article>
            </section>
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-sm font-semibold text-white">MOST CAPTAINED</h3>
                    <i data-lucide="captain" class="h-4 w-4 text-accent"></i>
                </div>
                <div class="mt-3 space-y-1 text-sm text-gray-300">
                    @forelse (($ownershipTrends['most_captained'] ?? []) as $captain)
                        <div class="flex items-center justify-between rounded-lg bg-card px-3 py-2">
                            <span class="truncate">{{ $captain['player'] }}</span>
                            <span class="font-semibold text-accent">{{ $captain['count'] }}</span>
                        </div>
                    @empty
                        <p class="text-gray-400">No data.</p>
                    @endforelse
                </div>
            </article>

            <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-sm font-semibold text-white">MOST TRANSFERRED IN</h3>
                    <i data-lucide="arrow-down-left" class="h-4 w-4 text-accent"></i>
                </div>
                <div class="mt-3 space-y-1 text-sm text-gray-300">
                    @forelse (($ownershipTrends['most_transferred_in'] ?? []) as $player)
                        <div class="flex items-center justify-between rounded-lg bg-card px-3 py-2">
                            <span class="truncate">{{ $player['player'] }}</span>
                            <span class="font-semibold text-accent">{{ $player['count'] }}</span>
                        </div>
                    @empty
                        <p class="text-gray-400">No data.</p>
                    @endforelse
                </div>
            </article>

            <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-sm font-semibold text-white">MOST TRANSFERRED OUT</h3>
                    <i data-lucide="arrow-up-right" class="h-4 w-4 text-accent"></i>
                </div>
                <div class="mt-3 space-y-1 text-sm text-gray-300">
                    @forelse (($ownershipTrends['most_transferred_out'] ?? []) as $player)
                        <div class="flex items-center justify-between rounded-lg bg-card px-3 py-2">
                            <span class="truncate">{{ $player['player'] }}</span>
                            <span class="font-semibold text-accent">{{ $player['count'] }}</span>
                        </div>
                    @empty
                        <p class="text-gray-400">No data.</p>
                    @endforelse
                </div>
            </article>

            <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-sm font-semibold text-white">CHIPS</h3>
                    <i data-lucide="package" class="h-4 w-4 text-accent"></i>
                </div>
                <div class="mt-3 space-y-1 text-sm text-gray-300">
                    @forelse (($ownershipTrends['chips_played'] ?? []) as $chip => $count)
                        <div class="flex items-center justify-between rounded-lg bg-card px-3 py-2">
                            <span class="truncate">{{ $chip }}</span>
                            <span class="font-semibold text-accent">{{ $count }}</span>
                        </div>
                    @empty
                        <p class="text-gray-400">No data.</p>
                    @endforelse
                </div>
            </article>
        </section>

        @include('leagues.partials.team-of-week-list', [
            'teamOfWeek' => $teamOfWeek,
            'historyUrl' => route('dashboard.team-of-week-history', ['gameweek' => $targetGW]),
            'emptyText' => 'No team-of-the-week data for this gameweek.',
        ])

        <div x-data="{ visibleRows: 50, totalRows: {{ $standingsRows->count() }} }" class="space-y-4">
            <section class="-mx-2 overflow-x-auto rounded-lg border border-gray-700 bg-card p-2 sm:mx-0">
                <h2 class="p-2 text-center text-2xl font-bold text-white">Gameweek Leaderboard</h2>

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
