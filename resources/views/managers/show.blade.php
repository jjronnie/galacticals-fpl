<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-gray-700 bg-card p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold text-white">{{ $manager->team_name }}</h1>
                        @if ($isVerified)
                            <x-verified-badge />
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-gray-300">{{ $manager->player_name }}</p>
                </div>

                <div>
                    @if ($isClaimed)
                        <span class=""> </span>
                    @else
                        <span class="rounded-full bg-yellow-500/20 px-3 py-1 text-xs font-semibold text-yellow-200">Unclaimed Profile</span>
                    @endif
                </div>
            </div>
        </section>

        @if (session('status'))
            <div class="rounded-xl border border-green-700 bg-green-900/30 px-4 py-3 text-sm text-green-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->has('subject') || $errors->has('message') || $errors->has('complaint'))
            <div class="rounded-xl border border-red-700 bg-red-900/30 px-4 py-3 text-sm text-red-200">
                {{ $errors->first('complaint') ?: $errors->first('subject') ?: $errors->first('message') }}
            </div>
        @endif

        @include('profile.partials.share', ['profileShareManager' => $manager])

        @if (! $isClaimed)
            <section class="rounded-2xl border border-yellow-700 bg-yellow-900/20 p-6">
                <h2 class="text-lg font-semibold text-yellow-100">This profile has not been claimed yet</h2>
                <p class="mt-2 text-sm text-yellow-100/80">
                    If this is your real FPL team, claim it from your account to unlock personal analytics.
                    Please do not claim profiles that do not belong to you.
                </p>

                <div class="mt-4">
                    @auth
                        <a href="{{ route('profile.search') }}" class="inline-flex rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                            Claim Now
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                            Login to Claim
                        </a>
                    @endauth
                </div>
            </section>
        @else
            <x-adsense />

            @if (! $isVerified)
                <section class="rounded-2xl border border-gray-700 bg-card p-6" x-data="{ complaintOpen: {{ $errors->has('subject') || $errors->has('message') || $errors->has('complaint') ? 'true' : 'false' }} }">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Claim Complaint</h2>
                            <p class="mt-1 text-sm text-gray-300">If this claimed profile appears incorrect, report it for admin review.</p>
                        </div>

                        @auth
                            <button
                                type="button"
                                class="inline-flex rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600"
                                @click="complaintOpen = !complaintOpen"
                            >
                                Report Claimed Profile
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                                Login to Report
                            </a>
                        @endauth
                    </div>

                    @auth
                        <form
                            x-show="complaintOpen"
                            x-transition
                            method="POST"
                            action="{{ route('profile.complaint', $manager) }}"
                            class="mt-4 space-y-3 rounded-xl border border-gray-700 bg-primary p-4"
                        >
                            @csrf

                            <div class="space-y-1">
                                <label for="complaint-subject" class="text-xs font-semibold uppercase tracking-wide text-gray-300">Subject</label>
                                <input
                                    id="complaint-subject"
                                    name="subject"
                                    type="text"
                                    maxlength="150"
                                    value="{{ old('subject') }}"
                                    placeholder="Example: Wrong person claimed this team"
                                    class="w-full rounded-lg border border-gray-600 bg-card px-3 py-2 text-sm text-white placeholder:text-gray-400 focus:border-accent focus:ring-accent"
                                    required
                                >
                            </div>

                            <div class="space-y-1">
                                <label for="complaint-message" class="text-xs font-semibold uppercase tracking-wide text-gray-300">Details</label>
                                <textarea
                                    id="complaint-message"
                                    name="message"
                                    rows="4"
                                    minlength="10"
                                    maxlength="2000"
                                    placeholder="Share the details that can help admin verify ownership."
                                    class="w-full rounded-lg border border-gray-600 bg-card px-3 py-2 text-sm text-white placeholder:text-gray-400 focus:border-accent focus:ring-accent"
                                    required
                                >{{ old('message') }}</textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">
                                    Submit Complaint
                                </button>
                            </div>
                        </form>
                    @endauth
                </section>
            @endif

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-stat-card title="Total Points" :value="$stats['summary']['total_points'] ?? 0" icon="trophy" tooltip="Total points scored by this team this season." />
                <x-stat-card title="Overall Rank" :value="$stats['summary']['overall_rank'] ? number_format((int) $stats['summary']['overall_rank']) : 'N/A'" icon="chart-no-axes-column" tooltip="Global overall rank from FPL for the latest gameweek." />
                <x-stat-card title="Favourite Club" :value="$stats['summary']['favourite_club'] ?? 'Not set'" icon="shield" tooltip="Manager's declared favourite Premier League club." />
                <x-stat-card title="Bench Points" :value="$stats['summary']['bench_points'] ?? 0" icon="armchair" tooltip="Total points left on the bench across all gameweeks." />
                <x-stat-card title="Transfers" :value="$stats['summary']['transfers_made'] ?? 0" icon="repeat" tooltip="Number of transfers made during the season." />
                <x-stat-card title="Transfer Hits" :value="$stats['summary']['transfer_hits'] ?? 0" icon="minus-circle" tooltip="Points spent on extra transfers." />
                <x-stat-card title="Auto-sub Impact" :value="$stats['summary']['auto_sub_points'] ?? 0" icon="replace" tooltip="Points gained or lost from automatic substitutions." />
                <x-stat-card title="Country" :value="$stats['summary']['country'] ?? 'Unknown'" icon="flag" tooltip="Country/region declared by the manager in FPL." />
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-700 bg-card p-5">
                    <h2 class="text-lg font-semibold text-white">Top Player Contributions</h2>
                    <p class="mb-4 text-xs text-gray-400">Players with the biggest point contribution to this squad.</p>

                    <div class="space-y-2">
                        @forelse (($stats['player_contribution'] ?? []) as $player)
                            <div class="flex items-center justify-between rounded-lg bg-primary px-3 py-2 text-sm">
                                <span class="text-white">{{ $player['player'] }} <span class="text-gray-400">({{ $player['team'] }})</span></span>
                                <span class="font-semibold text-accent">{{ $player['points'] }} pts</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">No player contribution data available yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-700 bg-card p-5">
                    <h2 class="text-lg font-semibold text-white">Chip Usage</h2>
                    <div class="mt-4 space-y-2">
                        @forelse (($stats['chip_usage']['rows'] ?? []) as $chip)
                            <div class="rounded-lg bg-primary px-3 py-2 text-sm text-gray-200">
                                GW{{ $chip['gameweek'] }} -
                                {{ $chip['chip'] }}
                                <span class="ml-2 text-accent">{{ $chip['points_gained'] >= 0 ? '+' : '' }}{{ $chip['points_gained'] }} pts</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">No chips recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-700 bg-card p-5">
                    <h2 class="text-lg font-semibold text-white">Captaincy Performance</h2>
                    <p class="mb-4 text-xs text-gray-400">Actual captain points versus best possible captain pick.</p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200">
                            <thead>
                                <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                                    <th class="px-3 py-2 text-left">GW</th>
                                    <th class="px-3 py-2 text-left">Captain</th>
                                    <th class="px-3 py-2 text-right">Actual</th>
                                    <th class="px-3 py-2 text-right">What-if</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (($stats['captaincy']['rows'] ?? []) as $row)
                                    <tr class="border-b border-gray-800/80">
                                        <td class="px-3 py-2">{{ $row['gameweek'] }}</td>
                                        <td class="px-3 py-2">{{ $row['captain'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ $row['captain_points'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ $row['what_if_points'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-gray-400">No captaincy data yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-700 bg-card p-5">
                    <h2 class="text-lg font-semibold text-white">Transfer Efficiency</h2>
                    <p class="mb-4 text-xs text-gray-400">Transfers, hit costs and net weekly points.</p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200">
                            <thead>
                                <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                                    <th class="px-3 py-2 text-left">GW</th>
                                    <th class="px-3 py-2 text-right">Transfers</th>
                                    <th class="px-3 py-2 text-right">Hit</th>
                                    <th class="px-3 py-2 text-right">Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (($stats['transfers']['rows'] ?? []) as $row)
                                    <tr class="border-b border-gray-800/80">
                                        <td class="px-3 py-2">{{ $row['gameweek'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ $row['transfers'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ $row['hit_cost'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ $row['net_points'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-gray-400">No transfer data yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-700 bg-card p-5">
                    <h2 class="text-lg font-semibold text-white">Points Trajectory</h2>
                    <p class="mb-4 text-xs text-gray-400">Your points vs league average and best manager by gameweek.</p>
                    <canvas id="publicPointsTrajectoryChart" height="120"></canvas>
                </div>

                <div class="rounded-2xl border border-gray-700 bg-card p-5">
                    <h2 class="text-lg font-semibold text-white">Squad Value Evolution</h2>
                    <p class="mb-4 text-xs text-gray-400">Team value progression over the season.</p>
                    <canvas id="publicSquadValueChart" height="120"></canvas>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-700 bg-card p-5">
                    <h2 class="text-lg font-semibold text-white">Favourite Club Bias</h2>
                    <p class="mt-2 text-sm text-gray-300">{{ $stats['favourite_club_bias']['team'] ?? 'No favourite club selected' }}</p>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ $stats['favourite_club_bias']['points'] ?? 0 }} points
                        ({{ $stats['favourite_club_bias']['percent'] ?? 0 }}% of contributed points)
                    </p>
                </div>

                @if (!empty($stats['awards']))
                    <div class="rounded-2xl border border-gray-700 bg-card p-5">
                        <h2 class="text-lg font-semibold text-white">Awards</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($stats['awards'] as $award)
                                <span class="rounded-full bg-accent/20 px-3 py-1 text-xs font-semibold text-accent">{{ $award }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            @php
                $profileHistoryRows = collect($stats['history_rows'] ?? [])->values();
            @endphp

            <section class="rounded-2xl border border-gray-700 bg-card p-5">
                <h2 class="text-lg font-semibold text-white">Gameweek History</h2>
                <p class="mb-4 text-xs text-gray-400">Weekly points, running total, and global rank.</p>

                <div x-data="{ visibleRows: 10, totalRows: {{ $profileHistoryRows->count() }} }">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200">
                            <thead>
                                <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                                    <th class="px-3 py-2 text-left">GW</th>
                                    <th class="px-3 py-2 text-right">Points</th>
                                    <th class="px-3 py-2 text-right">Total</th>
                                    <th class="px-3 py-2 text-right">Overall Rank</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($profileHistoryRows as $index => $history)
                                    <tr class="border-b border-gray-800/80" x-show="{{ $index }} < visibleRows" @if ($index >= 10) x-cloak @endif>
                                        <td class="px-3 py-2">{{ $history['gameweek'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ $history['points'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ $history['total_points'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ $history['overall_rank'] > 0 ? number_format($history['overall_rank']) : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-gray-400">No gameweek history available yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($profileHistoryRows->count() > 10)
                        <div class="mt-4 flex justify-center" x-show="visibleRows < totalRows">
                            <button
                                type="button"
                                class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary"
                                @click="visibleRows += 10"
                            >
                                Load More
                            </button>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($leagueMemberships->isNotEmpty())
            <section class="rounded-2xl border border-gray-700 bg-card p-5">
                <h2 class="text-lg font-semibold text-white">Leagues</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($leagueMemberships as $league)
                        <a
                            href="{{ route('public.leagues.show', ['slug' => $league->slug]) }}"
                            class="rounded-full bg-primary px-3 py-1 text-xs font-semibold text-gray-200 hover:bg-secondary"
                        >
                            {{ $league->name }}
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @include('profile.partials.share', ['profileShareManager' => $manager])

        <x-adsense />

        @if ($isClaimed)
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const pointsCtx = document.getElementById('publicPointsTrajectoryChart');
                    const squadCtx = document.getElementById('publicSquadValueChart');

                    if (!pointsCtx || !squadCtx) {
                        return;
                    }

                    const trajectory = @json($stats['trajectory'] ?? []);
                    const valueEvolution = @json($stats['value_evolution'] ?? []);

                    new Chart(pointsCtx, {
                        type: 'line',
                        data: {
                            labels: trajectory.labels || [],
                            datasets: [
                                {
                                    label: 'Your Points',
                                    data: trajectory.manager_points || [],
                                    borderColor: '#00C8FF',
                                    backgroundColor: 'rgba(0, 200, 255, 0.2)',
                                    tension: 0.35,
                                },
                                {
                                    label: 'League Average',
                                    data: trajectory.league_average || [],
                                    borderColor: '#A3A3A3',
                                    tension: 0.35,
                                },
                                {
                                    label: 'League Best',
                                    data: trajectory.league_best || [],
                                    borderColor: '#22C55E',
                                    tension: 0.35,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { labels: { color: '#e5e7eb' } },
                            },
                            scales: {
                                x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(156, 163, 175, 0.2)' } },
                                y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(156, 163, 175, 0.2)' } },
                            },
                        },
                    });

                    new Chart(squadCtx, {
                        type: 'bar',
                        data: {
                            labels: valueEvolution.labels || [],
                            datasets: [
                                {
                                    label: 'Team Value',
                                    data: valueEvolution.values || [],
                                    backgroundColor: '#6F00BC',
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { labels: { color: '#e5e7eb' } },
                            },
                            scales: {
                                x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(156, 163, 175, 0.2)' } },
                                y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(156, 163, 175, 0.2)' } },
                            },
                        },
                    });
                });
            </script>
        @endif
    </div>
</x-app-layout>
