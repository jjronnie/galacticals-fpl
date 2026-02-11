@php
    $activeTab = $activeTab ?? 'overview';
    $sortedGameweeks = collect($availableGameweeks ?? [])
        ->map(fn ($gameweek): int => (int) $gameweek)
        ->filter(fn (int $gameweek): bool => $gameweek > 0)
        ->sortDesc()
        ->values();
    $latestGameweek = $sortedGameweeks->first();
    $currentSelectedGameweek = (int) ($selectedGameweek ?? ($latestGameweek ?? 0));
    $tabColumns = $latestGameweek !== null ? 'grid-cols-3' : 'grid-cols-2';
@endphp

<div class="sticky top-2 z-20">
    <div class="rounded-2xl border border-gray-700/70 bg-primary/90 p-2 backdrop-blur-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="grid w-full {{ $tabColumns }} gap-1 rounded-xl border border-gray-700 bg-card p-1 sm:inline-flex sm:w-auto sm:items-center sm:gap-1">
                <a
                    href="{{ route('public.leagues.show', ['slug' => $league->slug]) }}"
                    class="rounded-lg px-3 py-2 text-center text-sm font-semibold transition {{ $activeTab === 'overview' ? 'bg-accent text-primary' : 'text-gray-300 hover:bg-primary hover:text-white' }}"
                >
                    Overview
                </a>
                <a
                    href="{{ route('public.leagues.performance', ['slug' => $league->slug]) }}"
                    class="rounded-lg px-3 py-2 text-center text-sm font-semibold transition {{ $activeTab === 'performance' ? 'bg-accent text-primary' : 'text-gray-300 hover:bg-primary hover:text-white' }}"
                >
                    B & W
                </a>
                @if ($latestGameweek !== null)
                    <a
                        href="{{ route('public.leagues.gameweek.show', ['slug' => $league->slug, 'gameweek' => $latestGameweek]) }}"
                        class="rounded-lg px-3 py-2 text-center text-sm font-semibold transition {{ $activeTab === 'gameweeks' ? 'bg-accent text-primary' : 'text-gray-300 hover:bg-primary hover:text-white' }}"
                    >
                        GW Stats
                    </a>
                @endif
            </div>

            @if ($activeTab === 'gameweeks' && $sortedGameweeks->isNotEmpty())
                <div class="w-full sm:w-60">
                    <label for="league-tabs-gameweek-select" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-400">Jump to Gameweek</label>
                    <select
                        id="league-tabs-gameweek-select"
                        class="w-full rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white focus:border-accent focus:ring-accent"
                        onchange="if (this.value) { window.location.href = this.value; }"
                    >
                        @foreach ($sortedGameweeks as $gameweek)
                            <option
                                value="{{ route('public.leagues.gameweek.show', ['slug' => $league->slug, 'gameweek' => $gameweek]) }}"
                                @selected($currentSelectedGameweek === $gameweek)
                            >
                                GW {{ $gameweek }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>
</div>
