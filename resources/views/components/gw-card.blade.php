@php
    $gwLink = isset($league) ? route('public.leagues.gameweek.show', ['slug' => $league->slug, 'gameweek' => $gw['gameweek']]) : null;
    $bestManagersMeta = $gw['best_managers_meta'] ?? [];
    $worstManagersMeta = $gw['worst_managers_meta'] ?? [];
@endphp

<div class="rounded-2xl border-2 border-gray-700 bg-card p-6">
    <div class="mb-4 flex items-center justify-between gap-3">
        <h2 class="text-xl font-bold uppercase text-white">GameWeek {{ $gw['gameweek'] }}</h2>

        @if ($gwLink)
            <a
                href="{{ $gwLink }}"
                class="inline-flex items-center justify-center rounded-lg bg-primary p-2 text-white hover:bg-secondary"
                aria-label="Open gameweek {{ $gw['gameweek'] }} overview"
                title="Open gameweek overview"
            >
                <i data-lucide="square-arrow-out-up-right" class="h-4 w-4"></i>
            </a>
        @endif
    </div>

    <div class="flex justify-between">
        <div>
            <p class="text-sm text-gray-300 opacity-80">Best Manager(s)</p>
            @if ($bestManagersMeta !== [])
                <div class="space-y-1">
                    @foreach ($bestManagersMeta as $manager)
                        <div>
                            <a href="{{ route('managers.show', $manager['entry_id']) }}" class="text-lg font-bold text-green-400 hover:text-green-400">
                                {{ $manager['name'] }}
                            </a>
                            <p class="text-xs text-gray-400">{{ $manager['team_name'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-lg font-bold text-green-400">{{ implode(', ', $gw['best_managers']) }}</p>
            @endif
            <p class="text-sm font-semibold text-green-400">{{ $gw['best_points'] }}pts</p>
        </div>

        <div class="text-right">
            <p class="text-sm text-gray-300 opacity-80">Worst Manager(s)</p>
            @if ($worstManagersMeta !== [])
                <div class="space-y-1">
                    @foreach ($worstManagersMeta as $manager)
                        <div>
                            <a href="{{ route('managers.show', $manager['entry_id']) }}" class="text-lg font-bold text-red-400 hover:text-red-400">
                                {{ $manager['name'] }}
                            </a>
                            <p class="text-xs text-gray-400">{{ $manager['team_name'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-lg font-bold text-red-400">{{ implode(', ', $gw['worst_managers']) }}</p>
            @endif
            <p class="text-sm font-semibold text-red-400">{{ $gw['worst_points'] }}pts</p>
        </div>
    </div>
</div>
