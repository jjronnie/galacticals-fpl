@php
    $gwLink = isset($league) ? route('public.leagues.gameweek.show', ['slug' => $league->slug, 'gameweek' => $gw['gameweek']]) : null;
    $bestManagersMeta = $gw['best_managers_meta'] ?? [];
    $worstManagersMeta = $gw['worst_managers_meta'] ?? [];
@endphp

<div class="space-y-3">
    <div class="relative flex items-center justify-center">
        <h2 class="inline-flex rounded-lg bg-primary px-3 text-lg py-1 font-bold  text-white">
            Gameweek {{ $gw['gameweek'] }}
        </h2>

        @if ($gwLink)
            <a
                href="{{ $gwLink }}"
                class="absolute right-0 top-1/2 inline-flex h-9 w-9 -translate-y-1/2 shrink-0 items-center justify-center rounded-full bg-primary text-gray-200 transition hover:bg-secondary hover:text-white"
                aria-label="Open gameweek {{ $gw['gameweek'] }} overview"
                title="Open gameweek overview"
            >
                <i data-lucide="chevron-right" class="h-5 w-5"></i>
            </a>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3">
        <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Best</p>
            @if ($bestManagersMeta !== [])
                <div class="mt-2 space-y-1.5">
                    @foreach ($bestManagersMeta as $manager)
                        <div class="min-w-0">
                            <a href="{{ route('managers.show', $manager['entry_id']) }}" class="block truncate text-sm font-semibold text-green-300 hover:text-green-200">
                                {{ $manager['name'] }}
                            </a>
                            <p class="truncate text-xs text-gray-400">{{ $manager['team_name'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-2 text-sm font-semibold text-green-300">{{ implode(', ', $gw['best_managers']) }}</p>
            @endif
            <p class="mt-3 inline-flex rounded-md bg-green-500/20 px-2 py-1 text-sm font-semibold text-green-300">
                {{ $gw['best_points'] }} pts
            </p>
        </article>

        <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Worst</p>
            @if ($worstManagersMeta !== [])
                <div class="mt-2 space-y-1.5">
                    @foreach ($worstManagersMeta as $manager)
                        <div class="min-w-0">
                            <a href="{{ route('managers.show', $manager['entry_id']) }}" class="block truncate text-sm font-semibold text-red-300 hover:text-red-200">
                                {{ $manager['name'] }}
                            </a>
                            <p class="truncate text-xs text-gray-400">{{ $manager['team_name'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-2 text-sm font-semibold text-red-300">{{ implode(', ', $gw['worst_managers']) }}</p>
            @endif
            <p class="mt-3 inline-flex rounded-md bg-red-500/20 px-2 py-1 text-sm font-semibold text-red-300">
                {{ $gw['worst_points'] }} pts
            </p>
        </article>
    </div>
</div>
