<x-app-layout>
    @php
        $cardItemsStep = 20;
        $managerDirectory = $stats['manager_directory'] ?? [];
        $managerProfileUrl = static fn (?string $name): ?string => ($name !== null && isset($managerDirectory[$name]))
            ? route('managers.show', $managerDirectory[$name])
            : null;
        $hundredKingsEntries = collect($stats['hundred_plus_league'] ?? [])->values();
        $hallOfShameEntries = collect($stats['hall_of_shame'] ?? [])
            ->map(fn ($count, $name): array => ['name' => (string) $name, 'count' => (int) $count])
            ->values();
        $menStandingEntries = collect($stats['men_standing'] ?? [])->map(fn ($name): string => (string) $name)->values();
        $neverBestEntries = collect($stats['never_best_in_gw'] ?? [])->map(fn ($name): string => (string) $name)->values();
        $countryDistributionEntries = collect($stats['country_distribution'] ?? [])
            ->map(fn ($count, $country): array => ['country' => (string) $country, 'count' => (int) $count])
            ->values();
        $favouriteTeamEntries = collect($stats['favourite_team_totals'] ?? [])
            ->map(fn ($count, $team): array => ['team' => (string) $team, 'count' => (int) $count])
            ->values();
    @endphp

    <main class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <section class="space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-extrabold text-white">{{ $league->name }}</h1>
                <p class="mt-2 text-sm text-gray-300">{{ $league->season }}/{{ $league->season + 1 }} Season Analytics</p>
            </div>

            @include('leagues.partials.tabs', [
                'league' => $league,
                'activeTab' => 'overview',
                'availableGameweeks' => $availableGameweeks,
                'selectedGameweek' => $currentGW,
            ])

            @include('leagues.partials.share')

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">THE 100+ KINGS</h3>
                        <i data-lucide="crown" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div x-data="{ visibleItems: {{ $cardItemsStep }}, totalItems: {{ $hundredKingsEntries->count() }} }" class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse ($hundredKingsEntries as $index => $entry)
                            @php
                                $entryName = null;
                                $entryPoints = null;
                                $entryGameweek = null;
                                $entryId = 0;

                                if (is_array($entry)) {
                                    $entryName = (string) ($entry['name'] ?? 'Unknown');
                                    $entryPoints = isset($entry['points']) ? (int) $entry['points'] : null;
                                    $entryGameweek = isset($entry['gw']) ? (int) $entry['gw'] : null;
                                    $entryId = (int) ($entry['entry_id'] ?? 0);
                                } elseif (is_string($entry)) {
                                    $entryText = trim($entry);
                                    if (preg_match('/^(.*?)\s*\((\d+)\s*pts in GW\s*(\d+)\)$/i', $entryText, $matches) === 1) {
                                        $entryName = trim($matches[1]);
                                        $entryPoints = (int) $matches[2];
                                        $entryGameweek = (int) $matches[3];
                                        $entryId = (int) ($managerDirectory[$entryName] ?? 0);
                                    } else {
                                        $entryName = $entryText;
                                        $entryId = (int) ($managerDirectory[$entryName] ?? 0);
                                    }
                                } else {
                                    $entryName = 'Unknown';
                                }

                                $profileUrl = $entryId > 0 ? route('managers.show', $entryId) : null;
                            @endphp
                            <div x-show="{{ $index }} < visibleItems" @if ($index >= $cardItemsStep) x-cloak @endif>
                                <div class="min-w-0 rounded-lg bg-primary px-3 py-2">
                                    @if ($profileUrl)
                                        <a href="{{ $profileUrl }}" class="block truncate font-semibold text-white hover:text-accent hover:underline">
                                            {{ $entryName }}
                                        </a>
                                    @else
                                        <p class="truncate font-semibold text-white">{{ $entryName }}</p>
                                    @endif
                                    @if ($entryPoints !== null && $entryGameweek !== null)
                                        <p class="text-xs text-gray-400">{{ $entryPoints }} pts in GW {{ $entryGameweek }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400">No 100+ scores yet.</p>
                        @endforelse

                        @if ($hundredKingsEntries->count() > $cardItemsStep)
                            <div class="mt-3 flex justify-center gap-2">
                                <button
                                    x-show="visibleItems < totalItems"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = Math.min(totalItems, visibleItems + {{ $cardItemsStep }})"
                                >
                                    Load More
                                </button>
                                <button
                                    x-show="visibleItems > {{ $cardItemsStep }}"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = {{ $cardItemsStep }}"
                                >
                                    Show Less
                                </button>
                            </div>
                        @endif
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">MOST GW LEADS</h3>
                        <i data-lucide="medal" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse($stats['most_gw_leads'] as $name => $count)
                            @php
                                $profileUrl = $managerProfileUrl($name);
                            @endphp
                            <div class="flex min-w-0 items-center justify-between rounded-lg bg-primary px-3 py-2">
                                @if ($profileUrl)
                                    <a href="{{ $profileUrl }}" class="truncate font-semibold text-white hover:text-accent hover:underline">{{ $name }}</a>
                                @else
                                    <span class="truncate">{{ $name }}</span>
                                @endif
                                <span class="shrink-0 font-semibold text-accent">{{ $count }}x</span>
                            </div>
                        @empty
                            <p class="text-gray-400">No lead records yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">MOST GW LAST</h3>
                        <i data-lucide="shield-x" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse($stats['most_gw_last'] as $name => $count)
                            @php
                                $profileUrl = $managerProfileUrl($name);
                            @endphp
                            <div class="flex min-w-0 items-center justify-between rounded-lg bg-primary px-3 py-2">
                                @if ($profileUrl)
                                    <a href="{{ $profileUrl }}" class="truncate font-semibold text-white hover:text-accent hover:underline">{{ $name }}</a>
                                @else
                                    <span class="truncate">{{ $name }}</span>
                                @endif
                                <span class="shrink-0 font-semibold text-accent">{{ $count }}x</span>
                            </div>
                        @empty
                            <p class="text-gray-400">No last-place records yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">HIGHEST GW POINTS</h3>
                        <i data-lucide="trending-up" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse(($stats['highest_gw_scores'] ?? []) as $row)
                            @php
                                $profileUrl = ((int) ($row['entry_id'] ?? 0) > 0)
                                    ? route('managers.show', $row['entry_id'])
                                    : null;
                            @endphp
                            <div class="flex min-w-0 items-center justify-between rounded-lg bg-primary px-3 py-2">
                                <div class="min-w-0">
                                    @if ($profileUrl)
                                        <a href="{{ $profileUrl }}" class="block truncate font-semibold text-white hover:text-accent hover:underline">
                                            {{ $row['manager'] }}
                                        </a>
                                    @else
                                        <p class="truncate">{{ $row['manager'] }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400">GW {{ $row['gw'] }}</p>
                                </div>
                                <span class="shrink-0 font-semibold text-accent">{{ $row['points'] }} pts</span>
                            </div>
                        @empty
                            <p class="text-gray-400">No gameweek scores available yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">LOWEST GW POINTS</h3>
                        <i data-lucide="trending-down" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse(($stats['lowest_gw_scores'] ?? []) as $row)
                            @php
                                $profileUrl = ((int) ($row['entry_id'] ?? 0) > 0)
                                    ? route('managers.show', $row['entry_id'])
                                    : null;
                            @endphp
                            <div class="flex min-w-0 items-center justify-between rounded-lg bg-primary px-3 py-2">
                                <div class="min-w-0">
                                    @if ($profileUrl)
                                        <a href="{{ $profileUrl }}" class="block truncate font-semibold text-white hover:text-accent hover:underline">
                                            {{ $row['manager'] }}
                                        </a>
                                    @else
                                        <p class="truncate">{{ $row['manager'] }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400">GW {{ $row['gw'] }}</p>
                                </div>
                                <span class="shrink-0 font-semibold text-accent">{{ $row['points'] }} pts</span>
                            </div>
                        @empty
                            <p class="text-gray-400">No gameweek scores available yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">LONGEST TOP STREAK</h3>
                        <i data-lucide="flame" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 text-sm text-gray-300">
                        @if (($stats['longest_top_streak']['length'] ?? 0) > 0)
                            @php
                                $streakManager = $stats['longest_top_streak']['manager'] ?? null;
                                $profileUrl = $managerProfileUrl($streakManager);
                            @endphp
                            <div class="rounded-lg bg-primary px-3 py-2">
                                @if ($profileUrl)
                                    <a href="{{ $profileUrl }}" class="font-semibold text-white hover:text-accent hover:underline">
                                        {{ $streakManager }}
                                    </a>
                                @else
                                    <span class="font-semibold text-white">{{ $streakManager }}</span>
                                @endif
                                <span class="text-gray-300"> led for {{ $stats['longest_top_streak']['length'] }} gameweeks</span>
                            </div>
                            <p class="mt-2 text-xs text-gray-400">
                                GW {{ $stats['longest_top_streak']['start_gw'] }} to GW {{ $stats['longest_top_streak']['end_gw'] }}
                            </p>
                        @else
                            <p class="text-gray-400">No clear single-manager top streak yet.</p>
                        @endif
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">THE BLOWOUT</h3>
                        <i data-lucide="gauge" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 space-y-2 text-sm text-gray-300">
                        @if (($stats['the_blowout']['difference'] ?? 0) > 0)
                            @php
                                $highestName = $stats['the_blowout']['highest_scorer'] ?? null;
                                $lowestName = $stats['the_blowout']['lowest_scorer'] ?? null;
                                $highestProfileUrl = $managerProfileUrl($highestName);
                                $lowestProfileUrl = $managerProfileUrl($lowestName);
                            @endphp
                            <p class="rounded-lg bg-primary px-3 py-2">
                                {{ $stats['the_blowout']['difference'] }} point gap (GW {{ $stats['the_blowout']['gw'] }})
                            </p>
                            <p class="rounded-lg bg-primary px-3 py-2 text-xs text-gray-300">
                                High:
                                @if ($highestProfileUrl)
                                    <a href="{{ $highestProfileUrl }}" class="font-semibold text-white hover:text-accent hover:underline">{{ $highestName }}</a>
                                @else
                                    <span class="font-semibold text-white">{{ $highestName }}</span>
                                @endif
                                ({{ $stats['the_blowout']['highest_points'] }})
                            </p>
                            <p class="rounded-lg bg-primary px-3 py-2 text-xs text-gray-300">
                                Low:
                                @if ($lowestProfileUrl)
                                    <a href="{{ $lowestProfileUrl }}" class="font-semibold text-white hover:text-accent hover:underline">{{ $lowestName }}</a>
                                @else
                                    <span class="font-semibold text-white">{{ $lowestName }}</span>
                                @endif
                                ({{ $stats['the_blowout']['lowest_points'] }})
                            </p>
                        @else
                            <p class="text-gray-400">No gameweek data available yet.</p>
                        @endif
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">MOST VALUABLE TEAMS</h3>
                        <i data-lucide="coins" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200">
                            <thead>
                                <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                                    <th class="px-3 py-2 text-left">Pos</th>
                                    <th class="px-3 py-2 text-left">Team</th>
                                    <th class="px-3 py-2 text-right">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stats['most_valuable_teams'] as $teamRow)
                                    <tr class="border-b border-gray-800/70">
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
                                        <td colspan="3" class="px-3 py-6 text-center text-gray-400">No team value records available yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">LEAGUE ZONES</h3>
                        <i data-lucide="layers-3" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 space-y-3 text-sm text-gray-300">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-300">Champions League</p>
                            @forelse ($stats['league_zones']['champions_league'] as $manager)
                                @php
                                    $profileUrl = $managerProfileUrl($manager);
                                @endphp
                                <div class="rounded-lg bg-primary px-3 py-2">
                                    @if ($profileUrl)
                                        <a href="{{ $profileUrl }}" class="font-semibold text-white hover:text-accent hover:underline">{{ $manager }}</a>
                                    @else
                                        <span>{{ $manager }}</span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-gray-400">No managers yet.</p>
                            @endforelse
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-yellow-300">Europa League</p>
                            @forelse ($stats['league_zones']['europa_league'] as $manager)
                                @php
                                    $profileUrl = $managerProfileUrl($manager);
                                @endphp
                                <div class="rounded-lg bg-primary px-3 py-2">
                                    @if ($profileUrl)
                                        <a href="{{ $profileUrl }}" class="font-semibold text-white hover:text-accent hover:underline">{{ $manager }}</a>
                                    @else
                                        <span>{{ $manager }}</span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-gray-400">No managers yet.</p>
                            @endforelse
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-red-300">Relegation Zone</p>
                            @forelse ($stats['league_zones']['relegation_zone'] as $manager)
                                @php
                                    $profileUrl = $managerProfileUrl($manager);
                                @endphp
                                <div class="rounded-lg bg-primary px-3 py-2">
                                    @if ($profileUrl)
                                        <a href="{{ $profileUrl }}" class="font-semibold text-white hover:text-accent hover:underline">{{ $manager }}</a>
                                    @else
                                        <span>{{ $manager }}</span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-gray-400">No managers yet.</p>
                            @endforelse
                        </div>
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">WOODEN SPOON CONTENDERS</h3>
                        <i data-lucide="badge-x" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse ($stats['wooden_spoon_contenders'] as $contender)
                            @php
                                $profileUrl = $managerProfileUrl($contender);
                            @endphp
                            <div class="rounded-lg bg-primary px-3 py-2">
                                @if ($profileUrl)
                                    <a href="{{ $profileUrl }}" class="font-semibold text-white hover:text-accent hover:underline">{{ $contender }}</a>
                                @else
                                    <span>{{ $contender }}</span>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-400">No managers yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">HALL OF SHAME</h3>
                        <i data-lucide="triangle-alert" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div x-data="{ visibleItems: {{ $cardItemsStep }}, totalItems: {{ $hallOfShameEntries->count() }} }" class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse($hallOfShameEntries as $index => $row)
                            @php
                                $profileUrl = $managerProfileUrl($row['name']);
                            @endphp
                            <div x-show="{{ $index }} < visibleItems" @if ($index >= $cardItemsStep) x-cloak @endif>
                                <div class="flex min-w-0 items-center justify-between rounded-lg bg-primary px-3 py-2">
                                    @if ($profileUrl)
                                        <a href="{{ $profileUrl }}" class="truncate font-semibold text-white hover:text-accent hover:underline">{{ $row['name'] }}</a>
                                    @else
                                        <span class="truncate">{{ $row['name'] }}</span>
                                    @endif
                                    <span class="shrink-0 font-semibold text-accent">{{ $row['count'] }}x</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400">No one has been last 3+ times.</p>
                        @endforelse

                        @if ($hallOfShameEntries->count() > $cardItemsStep)
                            <div class="mt-3 flex justify-center gap-2">
                                <button
                                    x-show="visibleItems < totalItems"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = Math.min(totalItems, visibleItems + {{ $cardItemsStep }})"
                                >
                                    Load More
                                </button>
                                <button
                                    x-show="visibleItems > {{ $cardItemsStep }}"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = {{ $cardItemsStep }}"
                                >
                                    Show Less
                                </button>
                            </div>
                        @endif
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">MEN STANDING</h3>
                        <i data-lucide="shield-check" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div x-data="{ visibleItems: {{ $cardItemsStep }}, totalItems: {{ $menStandingEntries->count() }} }" class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse($menStandingEntries as $index => $name)
                            @php
                                $profileUrl = $managerProfileUrl($name);
                            @endphp
                            <div x-show="{{ $index }} < visibleItems" @if ($index >= $cardItemsStep) x-cloak @endif>
                                <div class="rounded-lg bg-primary px-3 py-2">
                                    @if ($profileUrl)
                                        <a href="{{ $profileUrl }}" class="font-semibold text-white hover:text-accent hover:underline">{{ $name }}</a>
                                    @else
                                        <span>{{ $name }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400">No men standing records yet.</p>
                        @endforelse

                        @if ($menStandingEntries->count() > $cardItemsStep)
                            <div class="mt-3 flex justify-center gap-2">
                                <button
                                    x-show="visibleItems < totalItems"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = Math.min(totalItems, visibleItems + {{ $cardItemsStep }})"
                                >
                                    Load More
                                </button>
                                <button
                                    x-show="visibleItems > {{ $cardItemsStep }}"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = {{ $cardItemsStep }}"
                                >
                                    Show Less
                                </button>
                            </div>
                        @endif
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">NEVER BEST IN A GW</h3>
                        <i data-lucide="circle-off" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div x-data="{ visibleItems: {{ $cardItemsStep }}, totalItems: {{ $neverBestEntries->count() }} }" class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse($neverBestEntries as $index => $name)
                            @php
                                $profileUrl = $managerProfileUrl($name);
                            @endphp
                            <div x-show="{{ $index }} < visibleItems" @if ($index >= $cardItemsStep) x-cloak @endif>
                                <div class="rounded-lg bg-primary px-3 py-2">
                                    @if ($profileUrl)
                                        <a href="{{ $profileUrl }}" class="font-semibold text-white hover:text-accent hover:underline">{{ $name }}</a>
                                    @else
                                        <span>{{ $name }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400">Everyone has had a best GW.</p>
                        @endforelse

                        @if ($neverBestEntries->count() > $cardItemsStep)
                            <div class="mt-3 flex justify-center gap-2">
                                <button
                                    x-show="visibleItems < totalItems"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = Math.min(totalItems, visibleItems + {{ $cardItemsStep }})"
                                >
                                    Load More
                                </button>
                                <button
                                    x-show="visibleItems > {{ $cardItemsStep }}"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = {{ $cardItemsStep }}"
                                >
                                    Show Less
                                </button>
                            </div>
                        @endif
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">COUNTRY DISTRIBUTION</h3>
                        <i data-lucide="globe" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div x-data="{ visibleItems: {{ $cardItemsStep }}, totalItems: {{ $countryDistributionEntries->count() }} }" class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse($countryDistributionEntries as $index => $row)
                            <div x-show="{{ $index }} < visibleItems" @if ($index >= $cardItemsStep) x-cloak @endif>
                                <div class="flex items-center justify-between rounded-lg bg-primary px-3 py-2">
                                    <span class="truncate">{{ $row['country'] }}</span>
                                    <span class="font-semibold text-accent">{{ $row['count'] }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400">No region data yet.</p>
                        @endforelse

                        @if ($countryDistributionEntries->count() > $cardItemsStep)
                            <div class="mt-3 flex justify-center gap-2">
                                <button
                                    x-show="visibleItems < totalItems"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = Math.min(totalItems, visibleItems + {{ $cardItemsStep }})"
                                >
                                    Load More
                                </button>
                                <button
                                    x-show="visibleItems > {{ $cardItemsStep }}"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = {{ $cardItemsStep }}"
                                >
                                    Show Less
                                </button>
                            </div>
                        @endif
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">FAVOURITE TEAMS</h3>
                        <i data-lucide="shield" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div x-data="{ visibleItems: {{ $cardItemsStep }}, totalItems: {{ $favouriteTeamEntries->count() }} }" class="mt-3 space-y-2 text-sm text-gray-300">
                        @forelse($favouriteTeamEntries as $index => $row)
                            <div x-show="{{ $index }} < visibleItems" @if ($index >= $cardItemsStep) x-cloak @endif>
                                <div class="flex items-center justify-between rounded-lg bg-primary px-3 py-2">
                                    <span class="truncate">{{ $row['team'] }}</span>
                                    <span class="font-semibold text-accent">{{ $row['count'] }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400">No favourite teams captured yet.</p>
                        @endforelse

                        @if ($favouriteTeamEntries->count() > $cardItemsStep)
                            <div class="mt-3 flex justify-center gap-2">
                                <button
                                    x-show="visibleItems < totalItems"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = Math.min(totalItems, visibleItems + {{ $cardItemsStep }})"
                                >
                                    Load More
                                </button>
                                <button
                                    x-show="visibleItems > {{ $cardItemsStep }}"
                                    type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary"
                                    @click="visibleItems = {{ $cardItemsStep }}"
                                >
                                    Show Less
                                </button>
                            </div>
                        @endif
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-gray-700 bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-white">CHIP INSIGHTS</h3>
                        <i data-lucide="package" class="h-4 w-4 text-accent"></i>
                    </div>
                    <div class="mt-3 space-y-2 text-sm text-gray-300">
                        <div class="rounded-lg bg-primary px-3 py-2">Most used: {{ $stats['most_used_chip'] ?? 'N/A' }}</div>
                        <div class="rounded-lg bg-primary px-3 py-2">Least used: {{ $stats['least_used_chip'] ?? 'N/A' }}</div>
                        <div class="rounded-lg bg-primary px-3 py-2">Most effective: {{ $stats['most_effective_chip'] ?? 'N/A' }}</div>
                    </div>
                </article>
            </div>
        </section>

        @include('leagues.partials.share')

        @guest
            <div class="flex justify-center">
                <a href="{{ route('register') }}" class="rounded-lg bg-green-600 px-6 py-2 font-semibold text-white shadow-md transition hover:bg-green-500">
                    Create account for your league
                </a>
            </div>
        @endguest

        <x-back-to-top />
    </main>
</x-app-layout>
