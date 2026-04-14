<x-app-layout>
    <main class="mx-auto max-w-5xl space-y-6">
        <section class="rounded-2xl border border-gray-700 bg-card p-4 sm:p-5">
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
                    $goalkeepers = collect($selectedTeamOfWeek['goalkeeper'] ?? [])
                        ->map(fn (array $player): array => $player + ['position_label' => 'GKP'])
                        ->values();
                    $defenders = collect($selectedTeamOfWeek['defenders'] ?? [])
                        ->map(fn (array $player): array => $player + ['position_label' => 'DEF'])
                        ->values();
                    $midfielders = collect($selectedTeamOfWeek['midfielders'] ?? [])
                        ->map(fn (array $player): array => $player + ['position_label' => 'MID'])
                        ->values();
                    $forwards = collect($selectedTeamOfWeek['forwards'] ?? [])
                        ->map(fn (array $player): array => $player + ['position_label' => 'FWD'])
                        ->values();

                    $teamOfWeekPlayers = $goalkeepers
                        ->concat($defenders)
                        ->concat($midfielders)
                        ->concat($forwards)
                        ->values();
                    $playerOfWeek = $teamOfWeekPlayers
                        ->sortByDesc(fn (array $player): int => (int) ($player['points'] ?? 0))
                        ->first();

                    $pitchRows = collect([
                        ['label' => 'Goalkeeper', 'players' => $goalkeepers],
                        ['label' => 'Defenders', 'players' => $defenders],
                        ['label' => 'Midfielders', 'players' => $midfielders],
                        ['label' => 'Forwards', 'players' => $forwards],
                    ]);
                @endphp

                <div class="mt-5 space-y-4">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <article class="rounded-xl border border-gray-700 bg-card p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Gameweek</p>
                            <p class="mt-2 text-xl font-bold text-white">GW {{ $selectedTeamOfWeek['gameweek'] }}</p>
                            <p class="mt-1 text-xs text-gray-400">Formation: {{ $selectedTeamOfWeek['formation'] }}</p>
                        </article>

                        <article class="rounded-xl border border-gray-700 bg-card p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Points</p>
                            <p class="mt-2 text-xl font-bold text-accent">{{ $selectedTeamOfWeek['total_points'] }} pts</p>
                            <p class="mt-1 text-xs text-gray-400">Combined team output</p>
                        </article>

                        <article class="rounded-xl border border-gray-700 bg-card p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Player of the Week</p>
                            @if ($playerOfWeek !== null)
                                <p class="mt-2 truncate text-lg font-bold text-white">{{ $playerOfWeek['web_name'] ?? 'Unknown' }}</p>
                                <p class="mt-1 text-xs text-gray-400">
                                    {{ $playerOfWeek['team_short_name'] ?? ($playerOfWeek['team_name'] ?? 'UNK') }} · {{ $playerOfWeek['points'] ?? 0 }} pts
                                </p>
                            @else
                                <p class="mt-2 text-sm text-gray-400">No player data.</p>
                            @endif
                        </article>
                    </div>

                    <section class="-mx-1 relative overflow-hidden rounded-2xl border border-emerald-400/40 bg-gradient-to-b from-emerald-700 via-emerald-600 to-emerald-700 p-2 sm:mx-0 sm:p-5">
                        <div class="pointer-events-none absolute inset-0">
                            <div class="absolute inset-x-0 top-[20%] h-px bg-white/60"></div>
                            <div class="absolute inset-x-0 top-[40%] h-px bg-white/60"></div>
                            <div class="absolute inset-x-0 top-[60%] h-px bg-white/60"></div>
                            <div class="absolute inset-x-0 top-[80%] h-px bg-white/60"></div>
                            <div class="absolute left-1/2 top-0 h-full w-px -translate-x-1/2 bg-white/60"></div>
                            <div class="absolute left-1/2 top-0 h-16 w-28 -translate-x-1/2 border-x border-b border-white/70 sm:h-20 sm:w-40"></div>
                            <div class="absolute left-1/2 top-0 h-8 w-14 -translate-x-1/2 border-x border-b border-white/70 sm:h-10 sm:w-20"></div>
                            <div class="absolute left-1/2 top-1/2 h-14 w-14 -translate-x-1/2 -translate-y-1/2 rounded-full border border-white/70 sm:h-20 sm:w-20"></div>
                            <div class="absolute left-1/2 top-1/2 h-1.5 w-1.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/80"></div>
                        </div>

                        <div class="relative z-10 flex min-h-[24rem] flex-col justify-evenly gap-3 py-1.5 sm:min-h-[36rem] sm:gap-4 sm:py-4">
                            @foreach ($pitchRows as $row)
                                @php
                                    $rowPlayers = $row['players'];
                                    $rowCount = max($rowPlayers->count(), 1);
                                    $rowGapClass = $rowCount >= 5 ? 'gap-1.5 sm:gap-4' : 'gap-2 sm:gap-4';
                                @endphp
                                @if ($rowPlayers->isNotEmpty())
                                    <div
                                        class="grid w-full items-start {{ $rowGapClass }} px-0.5 sm:px-3"
                                        style="grid-template-columns: repeat({{ $rowCount }}, minmax(0, 1fr));"
                                    >
                                        @foreach ($rowPlayers as $player)
                                            <article class="relative mx-auto w-full max-w-[7.25rem] min-w-0 rounded-lg border border-white/35 bg-white/15 p-1 text-center shadow-md backdrop-blur-md sm:p-2">
                                                @if (! empty($player['is_captain']))
                                                    <span class="absolute -right-1 -top-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-accent text-[10px] font-bold text-primary">C</span>
                                                @endif

                                                <div class="mx-auto inline-flex h-8 w-8 items-center justify-center overflow-hidden rounded-md bg-white/10 sm:h-12 sm:w-12">
                                                    @if(!empty($player['fpl_photo']))
                                                        <img src="{{ route('img.player', $player['player_id']) }}" alt="{{ $player['web_name'] }}" class="h-full w-full object-contain" loading="lazy" />
                                                    @else
                                                        <svg viewBox="0 0 64 64" class="h-6 w-6 sm:h-8 sm:w-8">
                                                            <path
                                                                d="M23 8h18l5 7 8 3-5 13-8-3v26H23V28l-8 3-5-13 8-3 5-7z"
                                                                fill="{{ $player['team_color'] ?? '#6b7280' }}"
                                                            />
                                                        </svg>
                                                    @endif
                                                </div>

                                                <p class="mt-1 truncate text-[8px] uppercase tracking-wide text-gray-100/90 sm:text-[10px]">
                                                    {{ $player['team_short_name'] ?? ($player['team_name'] ?? 'UNK') }}
                                                </p>

                                                <div class="mt-1 overflow-hidden rounded-md border border-white/20">
                                                    <p class="truncate bg-white px-1 py-0.5 text-[9px] font-semibold leading-tight text-primary sm:text-xs">
                                                        {{ $player['web_name'] ?? 'Unknown' }}
                                                    </p>
                                                    <p class="bg-card px-1 py-0.5 text-[9px] font-semibold text-accent sm:text-xs">
                                                        {{ $player['points'] ?? 0 }}
                                                    </p>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>
                </div>
            @else
                <p class="mt-5 rounded-xl border border-gray-700 bg-card px-4 py-6 text-center text-sm text-gray-400">
                    No team-of-the-week data available yet.
                </p>
            @endif
        </section>
    </main>
</x-app-layout>
