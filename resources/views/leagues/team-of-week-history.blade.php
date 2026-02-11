<x-app-layout>
    <main class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary text-gray-200 transition hover:bg-secondary hover:text-white"
                        aria-label="Back to dashboard"
                    >
                        <i data-lucide="chevron-left" class="h-5 w-5"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-white">Team of the Week History</h1>
                        <p class="text-xs text-gray-400">{{ $league->name }}</p>
                    </div>
                </div>

                <div class="w-full sm:w-auto">
                    <label for="team-of-week-gameweek" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-400">Gameweek</label>
                    <select
                        id="team-of-week-gameweek"
                        class="w-full rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white focus:border-accent focus:ring-accent"
                        onchange="if (this.value) { window.location.href = this.value; }"
                    >
                        @foreach ($availableGameweeks as $gameweek)
                            <option
                                value="{{ route('dashboard.team-of-week-history', ['gameweek' => $gameweek]) }}"
                                @selected($selectedGameweek === $gameweek)
                            >
                                GW {{ $gameweek }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($selectedTeamOfWeek)
                @php
                    $teamOfWeekPlayers = collect($selectedTeamOfWeek['goalkeeper'] ?? [])
                        ->map(fn (array $player): array => $player + ['position_label' => 'GKP'])
                        ->merge(collect($selectedTeamOfWeek['defenders'] ?? [])->map(fn (array $player): array => $player + ['position_label' => 'DEF']))
                        ->merge(collect($selectedTeamOfWeek['midfielders'] ?? [])->map(fn (array $player): array => $player + ['position_label' => 'MID']))
                        ->merge(collect($selectedTeamOfWeek['forwards'] ?? [])->map(fn (array $player): array => $player + ['position_label' => 'FWD']));
                @endphp

                <div class="mt-5 rounded-xl border border-gray-700 bg-primary p-3">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                            GW{{ $selectedTeamOfWeek['gameweek'] }} · {{ $selectedTeamOfWeek['formation'] }}
                        </p>
                        <span class="rounded-full bg-accent/20 px-3 py-1 text-xs font-semibold text-accent">
                            {{ $selectedTeamOfWeek['total_points'] }} pts
                        </span>
                    </div>

                    <div class="grid grid-cols-[1fr_auto] border-b border-gray-700 px-2 pb-2 text-xs uppercase tracking-wide text-gray-400">
                        <span>Player</span>
                        <span>Pts</span>
                    </div>

                    <div class="mt-2 space-y-1">
                        @foreach ($teamOfWeekPlayers as $player)
                            <div class="grid grid-cols-[1fr_auto] items-center gap-4 rounded-lg px-2 py-2 transition hover:bg-card">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-md bg-card/70">
                                        <svg viewBox="0 0 64 64" class="h-6 w-6" style="width: 1.5rem; height: 1.5rem;">
                                            <path
                                                d="M23 8h18l5 7 8 3-5 13-8-3v26H23V28l-8 3-5-13 8-3 5-7z"
                                                fill="{{ $player['team_color'] ?? '#6b7280' }}"
                                            />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-white">{{ $player['web_name'] }}</p>
                                        <p class="text-xs text-gray-400">{{ $player['team_short_name'] ?? 'UNK' }} · {{ $player['position_label'] }}</p>
                                    </div>
                                </div>
                                <p class="font-semibold text-accent">{{ $player['points'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="mt-5 rounded-xl border border-gray-700 bg-primary px-4 py-6 text-center text-sm text-gray-400">
                    No team-of-the-week data available yet.
                </p>
            @endif
        </section>
    </main>
</x-app-layout>
