@php
    $title = $title ?? 'Team of the Week';
    $historyUrl = $historyUrl ?? null;
    $emptyText = $emptyText ?? 'No team-of-the-week data available yet.';
    $showMeta = $showMeta ?? false;

    $teamOfWeekPlayers = collect();

    if ($teamOfWeek !== null) {
        $teamOfWeekPlayers = collect($teamOfWeek['goalkeeper'] ?? [])
            ->map(fn (array $player): array => $player + ['position_label' => 'GKP'])
            ->merge(collect($teamOfWeek['defenders'] ?? [])->map(fn (array $player): array => $player + ['position_label' => 'DEF']))
            ->merge(collect($teamOfWeek['midfielders'] ?? [])->map(fn (array $player): array => $player + ['position_label' => 'MID']))
            ->merge(collect($teamOfWeek['forwards'] ?? [])->map(fn (array $player): array => $player + ['position_label' => 'FWD']))
            ->values();
    }
@endphp

<section class="rounded-2xl border border-gray-700 bg-card p-5">
    <div class="flex items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-white">{{ $title }}</h2>
        @if ($historyUrl !== null)
            <a
                href="{{ $historyUrl }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary text-gray-200 transition hover:bg-secondary hover:text-white"
                aria-label="Open team of the week history"
                title="Open history"
            >
                <i data-lucide="chevron-right" class="h-5 w-5"></i>
            </a>
        @else
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary text-gray-300">
                <i data-lucide="chevron-right" class="h-5 w-5"></i>
            </span>
        @endif
    </div>

    @if ($teamOfWeek !== null)
        <div class="mt-4 rounded-xl border border-gray-700 bg-primary p-3">
            @if ($showMeta)
                <div class="mb-3 flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                        GW{{ $teamOfWeek['gameweek'] }} · {{ $teamOfWeek['formation'] }}
                    </p>
                    <span class="rounded-full bg-accent/20 px-3 py-1 text-xs font-semibold text-accent">
                        {{ $teamOfWeek['total_points'] }} pts
                    </span>
                </div>
            @endif

            <div class="flex items-center justify-between border-b border-gray-700 px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                <span>Player</span>
                <span class="w-10 text-right">Pts</span>
            </div>

            <div class="mt-1">
                @foreach ($teamOfWeekPlayers as $player)
                    <div class="flex items-center gap-3 border-b border-gray-800/80 px-1 py-2.5 last:border-b-0">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center text-xs font-semibold text-gray-300">#</span>

                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-md bg-card/70">
                                <svg viewBox="0 0 64 64" class="h-6 w-6">
                                    <path
                                        d="M23 8h18l5 7 8 3-5 13-8-3v26H23V28l-8 3-5-13 8-3 5-7z"
                                        fill="{{ $player['team_color'] ?? '#6b7280' }}"
                                    />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-lg font-semibold text-white">{{ $player['web_name'] }}</p>
                                    @if (! empty($player['is_captain']))
                                        <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent/20 text-[10px] font-bold text-accent">C</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ $player['team_name'] ?? ($player['team_short_name'] ?? 'Unknown') }}
                                    <span class="ml-1 uppercase">{{ $player['position_label'] }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="h-8 w-px shrink-0 bg-gray-700"></div>

                        <p class="w-10 shrink-0 text-right font-semibold text-white">{{ $player['points'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="mt-4 rounded-xl border border-gray-700 bg-primary px-4 py-6 text-center text-sm text-gray-400">
            {{ $emptyText }}
        </p>
    @endif
</section>
