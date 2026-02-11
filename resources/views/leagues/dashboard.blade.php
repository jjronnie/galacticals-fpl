<x-app-layout>
    <x-adsense />

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @include('leagues.partials.dash-header')

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

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-700 bg-card p-5">
                    <h2 class="text-2xl font-bold text-white">Best Leagues</h2>
                    <p class="mt-1 text-xs text-gray-400">Average score across all teams in each league.</p>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200">
                            <thead>
                                <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                                    <th class="px-3 py-2 text-left">Pos</th>
                                    <th class="px-3 py-2 text-left">League</th>
                                    <th class="px-3 py-2 text-right">Average</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bestLeagues as $leagueRow)
                                    <tr class="border-b border-gray-800/80">
                                        <td class="px-3 py-2 font-semibold text-white">{{ $leagueRow['position'] }}</td>
                                        <td class="px-3 py-2">
                                            <a href="{{ route('public.leagues.show', ['slug' => $leagueRow['league_slug']]) }}" class="font-semibold text-white hover:text-accent hover:underline">
                                                {{ $leagueRow['league_name'] }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold text-accent">{{ number_format((float) $leagueRow['average'], 1) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-8 text-center text-gray-400">No league averages available yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-700 bg-card p-5">
                    <h2 class="text-2xl font-bold text-white">Most Valuable Teams</h2>
                    <p class="mt-1 text-xs text-gray-400">Top squads by current value.</p>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200">
                            <thead>
                                <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                                    <th class="px-3 py-2 text-left">Pos</th>
                                    <th class="px-3 py-2 text-left">Team</th>
                                    <th class="px-3 py-2 text-right">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mostValuableTeams as $teamRow)
                                    <tr class="border-b border-gray-800/80">
                                        <td class="px-3 py-2 font-semibold text-white">{{ $teamRow['position'] }}</td>
                                        <td class="px-3 py-2">
                                            <a href="{{ route('managers.show', ['entryId' => $teamRow['entry_id']]) }}" class="font-semibold text-white hover:text-accent hover:underline">
                                                {{ $teamRow['team_name'] }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold text-accent">€{{ number_format((float) $teamRow['value'], 1) }}m</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-8 text-center text-gray-400">No team value records available yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-700 bg-card p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-2xl font-bold text-white">Player of the Week</h2>
                    <a
                        href="{{ route('dashboard.player-of-week-history') }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary text-gray-200 transition hover:bg-secondary hover:text-white"
                        aria-label="Open player of the week history"
                        title="Open history"
                    >
                        <i data-lucide="chevron-right" class="h-5 w-5"></i>
                    </a>
                </div>

                <div class="no-scrollbar mt-4 flex gap-3 overflow-x-auto pb-2">
                    @forelse ($playerOfWeekCards as $playerCard)
                        <a
                            href="{{ route('dashboard.player-of-week-history') }}"
                            class="min-w-[240px] rounded-xl border border-gray-700 bg-primary p-3 transition hover:border-gray-500"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <svg viewBox="0 0 64 64" class="h-10 w-10 shrink-0">
                                        <path
                                            d="M23 8h18l5 7 8 3-5 13-8-3v26H23V28l-8 3-5-13 8-3 5-7z"
                                            fill="{{ $playerCard['team_color'] }}"
                                        />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-white">{{ $playerCard['web_name'] }}</p>
                                        <p class="text-xs text-gray-400">{{ $playerCard['team_name'] ?? ($playerCard['team_short_name'] ?? 'Unknown') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between rounded-lg bg-card px-3 py-2">
                                <p class="text-xs font-semibold text-gray-300">GW{{ $playerCard['gameweek'] }}</p>
                                <p class="rounded-md bg-accent/20 px-2 py-1 text-xs font-semibold text-accent">
                                    {{ $playerCard['points'] }} pts
                                </p>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-400">No player-of-the-week records available yet.</p>
                    @endforelse
                </div>
            </section>

            @php
                $latestTeamOfWeek = collect($teamOfWeekRows)->first();
            @endphp

            @include('leagues.partials.team-of-week-list', [
                'teamOfWeek' => $latestTeamOfWeek,
                'historyUrl' => route('dashboard.team-of-week-history'),
            ])

            <x-adsense />
        </div>
    </div>
</x-app-layout>
