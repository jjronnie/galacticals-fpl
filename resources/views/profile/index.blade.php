<x-app-layout>
    @php
        $isOverview = $activeSection === 'overview';
        $managerQuery = $selectedManager ? ['manager' => $selectedManager->id] : [];
        $showProfileVerificationAction = $isOverview
            && ! $profileSuspended
            && $profileVerificationState !== null
            && $profileVerificationState !== 'verified';
        $profileVerificationActionLabel = $profileVerificationState === 'rejected' ? 'Retry Verification' : 'Get Verified';
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-green-700 bg-green-900/30 px-4 py-3 text-sm text-green-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-700 bg-red-900/30 px-4 py-3 text-sm text-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        @if ($claimedManagers->isEmpty())
            <section class="rounded-2xl border border-dashed border-gray-600 bg-card p-8 text-center">
                <h2 class="text-xl font-semibold text-white">No Claimed Team Yet</h2>
                <p class="mt-2 text-sm text-gray-300">Search your FPL team and claim it to unlock personal analytics.</p>
                <a href="{{ route('profile.search') }}" class="mt-4 inline-flex rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                    Search and Claim
                </a>
            </section>
        @elseif ($selectedManager)
            <section class="rounded-3xl border border-indigo-500/30 bg-gradient-to-br from-indigo-700/55 via-indigo-600/40 to-blue-600/35 p-6 sm:p-7">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div class="space-y-3">
                        @if (! $isOverview)
                            <div class="flex items-center gap-3">
                                <a
                                    href="{{ route('profile.index', $managerQuery) }}"
                                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-gray-200 hover:bg-secondary"
                                >
                                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                                    Back
                                </a>
                                <h1 class="text-xl font-bold text-white">{{ data_get($profileSections, (string) $activeSection, 'Insight') }}</h1>
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-green-400/30 bg-green-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-green-200">Claimed</span>
                            @if ($selectedManager->isVerified())
                                <x-verified-badge />
                            @endif
                        </div>

                        <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ $selectedManager->team_name }}</h1>
                        <p class="text-sm text-blue-100/80 sm:text-base">{{ $selectedManager->player_name }}</p>
                    </div>

                    <div class="flex w-full flex-col gap-3 md:w-auto md:items-end">
                        @if ($showProfileVerificationAction)
                            <a href="{{ route('profile.verification.create') }}" class="inline-flex rounded-xl bg-accent px-5 py-2.5 text-sm font-semibold text-primary transition hover:bg-cyan-300">
                                {{ $profileVerificationActionLabel }}
                            </a>
                        @endif

                        @if ($claimedManagers->count() > 1)
                            <form method="GET" action="{{ $isOverview ? route('profile.index') : route('profile.section', ['section' => $activeSection]) }}" class="w-full md:w-auto">
                                <label for="manager" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-blue-100/70">Switch Team</label>
                                <select
                                    id="manager"
                                    name="manager"
                                    onchange="this.form.submit()"
                                    class="w-full rounded-lg border border-indigo-300/30 bg-primary/70 px-4 py-2 text-sm text-white focus:border-accent focus:ring-accent md:min-w-80"
                                >
                                    @foreach ($claimedManagers as $claimedManager)
                                        <option value="{{ $claimedManager->id }}" @selected($selectedManager->id === $claimedManager->id)>
                                            {{ $claimedManager->team_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                    </div>
                </div>
            </section>

            @if ($isOverview && ! $profileSuspended && $profileVerificationState !== null && $profileVerificationState !== 'verified')
                <section class="rounded-2xl border p-5
                    {{ $profileVerificationState === 'pending' ? 'border-yellow-700 bg-yellow-900/20' : '' }}
                    {{ $profileVerificationState === 'rejected' ? 'border-red-700 bg-red-900/20' : '' }}
                    {{ $profileVerificationState === 'unverified' ? 'border-cyan-700/60 bg-cyan-900/20' : '' }}">
                    @if ($profileVerificationState === 'pending')
                        <h2 class="text-lg font-semibold text-yellow-100">Verification Pending</h2>
                        <p class="mt-2 text-sm text-yellow-100/90">
                            Your verification evidence was submitted and is awaiting admin review.
                        </p>
                    @elseif ($profileVerificationState === 'rejected')
                        <h2 class="text-lg font-semibold text-red-200">Verification Rejected</h2>
                        <p class="mt-2 text-sm text-red-100/90">
                            {{ $latestProfileVerificationSubmission?->rejection_reason ?: 'Your previous submission was rejected. Please submit a clearer screenshot and retry.' }}
                        </p>
                        <a href="{{ route('profile.verification.create') }}" class="mt-4 inline-flex rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                            Retry Verification
                        </a>
                    @else
                        <h2 class="text-lg font-semibold text-cyan-100">Get Your Profile Verified</h2>
                        <p class="mt-2 text-sm text-cyan-100/90">
                            Submit one screenshot from the official FPL app while logged in, with your team name clearly visible.
                        </p>
                        <a href="{{ route('profile.verification.create') }}" class="mt-4 inline-flex rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                            Submit Verification Evidence
                        </a>
                    @endif
                </section>
            @endif

            @if ($profileSuspended)
                <section class="rounded-2xl border border-yellow-700 bg-yellow-900/20 p-8 text-center">
                    <h2 class="text-xl font-semibold text-yellow-200">Profile Suspended</h2>
                    <p class="mt-2 text-sm text-yellow-100/80">This claimed profile is currently under review by administrators.</p>
                </section>
            @elseif ($stats)
                @if ($isOverview)
                    <x-adsense />

                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <x-stat-card title="Total Points" :value="$stats['summary']['total_points']" icon="trophy" tooltip="Total points scored by this team this season." />
                        <x-stat-card title="Overall Rank" :value="$stats['summary']['overall_rank'] ?: 'N/A'" icon="chart-no-axes-column" tooltip="Global overall rank from FPL for the latest gameweek." />
                        <x-stat-card title="Favourite Club" :value="$stats['summary']['favourite_club']" icon="shield" tooltip="Manager's declared favourite Premier League club." />
                        <x-stat-card title="Squad Value" :value="'€'.number_format((float) ($stats['summary']['current_squad_value'] ?? 0), 1).'m'" icon="coins" tooltip="Current FPL squad value in millions of euros." />
                        <x-stat-card title="Transfers" :value="$stats['summary']['transfers_made']" icon="repeat" tooltip="Number of transfers made during the season." />
                        <x-stat-card title="Transfer Hits" :value="$stats['summary']['transfer_hits']" icon="minus-circle" tooltip="Points spent on extra transfers." />
                        <x-stat-card title="Bench Points" :value="$stats['summary']['bench_points']" icon="armchair" tooltip="Total points left on the bench across all gameweeks." />
                        <x-stat-card title="Auto-sub Impact" :value="$stats['summary']['auto_sub_points']" icon="replace" tooltip="Points gained or lost from automatic substitutions." />
                    </section>

                    @php
                        $insightPreviews = $stats['insight_previews'] ?? [];
                        $insightCards = $stats['insight_cards'] ?? [];
                        $contributionRows = collect($insightCards['contributions_rows'] ?? [])->take(3);
                        $captaincyRows = collect($insightCards['captaincy_rows'] ?? [])->take(5);
                        $transferRows = collect($insightCards['transfer_rows'] ?? [])->take(3);
                        $valueRows = collect($insightCards['value_rows'] ?? [])->take(5);
                        $historyRows = collect($insightCards['history_rows'] ?? [])->take(5);
                        $chipRows = collect($insightCards['chip_rows'] ?? [])->take(3);

                        $sectionBlocks = [
                            [
                                'key' => 'contributions',
                                'title' => 'Top Contributors',
                                'preview' => $insightPreviews['contributions'] ?? 'No data yet',
                                'icon' => 'users-round',
                                'cta' => 'text',
                            ],
                            [
                                'key' => 'chips',
                                'title' => 'Chip Performance',
                                'preview' => $insightPreviews['chips'] ?? 'No data yet',
                                'icon' => 'package',
                                'cta' => 'icon',
                            ],
                            [
                                'key' => 'captaincy',
                                'title' => 'Captain Performance',
                                'preview' => $insightPreviews['captaincy'] ?? 'No data yet',
                                'icon' => 'swords',
                                'cta' => 'icon',
                            ],
                            [
                                'key' => 'transfers',
                                'title' => 'Transfer Efficiency',
                                'preview' => $insightPreviews['transfers'] ?? 'No data yet',
                                'icon' => 'arrow-right-left',
                                'cta' => 'button',
                            ],
                            [
                                'key' => 'value',
                                'title' => 'Squad Value',
                                'preview' => $insightPreviews['value'] ?? 'No data yet',
                                'icon' => 'coins',
                                'cta' => 'text',
                            ],
                            [
                                'key' => 'history',
                                'title' => 'Gameweek History',
                                'preview' => $insightPreviews['history'] ?? 'No data yet',
                                'icon' => 'history',
                                'cta' => 'button',
                            ],
                        ];
                    @endphp

                    <div class="space-y-3">
                        <h2 class="text-lg font-semibold text-white">Explore Insights</h2>
                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($sectionBlocks as $block)
                                <a
                                    href="{{ route('profile.section', ['section' => $block['key']] + $managerQuery) }}"
                                    class="group rounded-xl border border-gray-700 bg-card p-4 transition hover:border-gray-500"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="text-sm font-semibold text-white">{{ $block['title'] }}</h3>
                                        @if ($block['cta'] === 'icon')
                                            <span class="rounded-full bg-card px-2 py-1 text-xs font-semibold text-accent transition group-hover:bg-accent group-hover:text-primary">→</span>
                                        @else
                                            <i data-lucide="{{ $block['icon'] }}" class="h-4 w-4 text-accent"></i>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-accent">{{ $block['preview'] }}</p>

                                    @if ($block['key'] === 'contributions')
                                        <div class="mt-3 space-y-1 text-sm text-gray-300">
                                            @forelse ($contributionRows as $row)
                                                <div class="flex items-center justify-between rounded-md bg-card px-2 py-1">
                                                    <span class="truncate">{{ $row['player'] }} <span class="text-gray-400">({{ $row['team'] }})</span></span>
                                                    <span class="font-semibold text-accent">{{ $row['points'] }}</span>
                                                </div>
                                            @empty
                                                <p class="text-gray-400">No contribution rows yet.</p>
                                            @endforelse
                                        </div>
                                    @endif

                                    @if ($block['key'] === 'chips')
                                        <div class="mt-3 space-y-1 text-sm text-gray-300">
                                            @forelse ($chipRows as $row)
                                                <div class="flex items-center justify-between rounded-md bg-card px-2 py-1">
                                                    <span>GW{{ $row['gameweek'] }} · {{ $row['chip'] }}</span>
                                                    <span class="{{ $row['points_gained'] >= 0 ? 'text-green-300' : 'text-red-300' }}">
                                                        {{ $row['points_gained'] >= 0 ? '+' : '' }}{{ $row['points_gained'] }}
                                                    </span>
                                                </div>
                                            @empty
                                                <p class="text-gray-400">No chip rows yet.</p>
                                            @endforelse
                                        </div>
                                    @endif

                                    @if ($block['key'] === 'captaincy')
                                        <div class="mt-3 overflow-x-auto">
                                            <table class="min-w-full text-sm text-gray-300">
                                                <thead>
                                                    <tr class="border-b border-gray-700/70 text-[10px] uppercase tracking-wide text-gray-400">
                                                        <th class="px-2 py-1 text-left">GW</th>
                                                        <th class="px-2 py-1 text-left">Captain</th>
                                                        <th class="px-2 py-1 text-right">Pts</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($captaincyRows as $row)
                                                        <tr class="border-b border-gray-800/60">
                                                            <td class="px-2 py-1">GW{{ $row['gameweek'] }}</td>
                                                            <td class="px-2 py-1">{{ $row['captain'] }}</td>
                                                            <td class="px-2 py-1 text-right">{{ $row['captain_points'] }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="px-2 py-2 text-center text-gray-400">No captaincy rows yet.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                    @if ($block['key'] === 'transfers')
                                        <div class="mt-3 space-y-1 text-sm text-gray-300">
                                            @forelse ($transferRows as $row)
                                                <div class="flex items-center justify-between rounded-md bg-card px-2 py-1">
                                                    <span>GW{{ $row['gameweek'] }} · {{ $row['transfers'] }} tr</span>
                                                    <span class="{{ $row['net_points'] >= 0 ? 'text-green-300' : 'text-red-300' }}">Net {{ $row['net_points'] }}</span>
                                                </div>
                                            @empty
                                                <p class="text-gray-400">No transfer rows yet.</p>
                                            @endforelse
                                        </div>
                                    @endif

                                    @if ($block['key'] === 'value')
                                        <div class="mt-3 space-y-1 text-sm text-gray-300">
                                            @forelse ($valueRows as $row)
                                                <div class="flex items-center justify-between rounded-md bg-card px-2 py-1">
                                                    <span>GW{{ $row['gameweek'] }}</span>
                                                    <span class="flex items-center gap-1">
                                                        €{{ number_format((float) $row['value'], 1) }}m
                                                        @if ($row['trend'] === 'up')
                                                            <span class="font-semibold text-green-300">↑</span>
                                                        @elseif ($row['trend'] === 'down')
                                                            <span class="font-semibold text-red-300">↓</span>
                                                        @else
                                                            <span class="font-semibold text-gray-400">-</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @empty
                                                <p class="text-gray-400">No value rows yet.</p>
                                            @endforelse
                                        </div>
                                    @endif

                                    @if ($block['key'] === 'history')
                                        <div class="mt-3 space-y-1 text-sm text-gray-300">
                                            @forelse ($historyRows as $row)
                                                <div class="flex items-center justify-between rounded-md bg-card px-2 py-1">
                                                    <span>GW{{ $row['gameweek'] }}</span>
                                                    <span class="text-accent">{{ $row['points'] }} pts</span>
                                                </div>
                                            @empty
                                                <p class="text-gray-400">No history rows yet.</p>
                                            @endforelse
                                        </div>
                                    @endif

                                    @if ($block['cta'] === 'button')
                                        <span class="mt-4 inline-flex w-full justify-center rounded-lg bg-card px-3 py-2 text-sm font-semibold text-gray-200 transition group-hover:bg-accent group-hover:text-primary">
                                            View More Details
                                        </span>
                                    @else
                                        <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-accent">
                                            View Details <span>→</span>
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @include('profile.partials.awards-list', ['awards' => $stats['awards'] ?? []])

                    <div class="flex gap-3">
                        <x-confirm-modal
                            :action="route('profile.unclaim', $selectedManager)"
                            method="POST"
                            :warning="'You are about to unclaim '.$selectedManager->player_name.' ('.$selectedManager->team_name.'). You can only claim one profile at a time.'"
                            triggerText="Unclaim Team"
                            triggerClass="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600"
                            title="Unclaim profile"
                        />
                    </div>
                @endif

                @if ($activeSection === 'contributions')
                    <section class="grid gap-6 lg:grid-cols-2">
                        <div class="rounded-2xl border border-gray-700 bg-card p-5">
                            <h2 class="text-lg font-semibold text-white">Top Player Contributions</h2>
                            <div class="mt-4 space-y-2">
                                @forelse ($stats['player_contribution'] as $player)
                                    <div class="flex items-center justify-between rounded-lg bg-card px-3 py-2 text-sm">
                                        <span class="text-white">{{ $player['player'] }} <span class="text-gray-400">({{ $player['team'] }})</span></span>
                                        <span class="font-semibold text-accent">{{ $player['points'] }} pts</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400">No pick contribution data available yet.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-700 bg-card p-5">
                            <h2 class="text-lg font-semibold text-white">Favourite Club Bias</h2>
                            <p class="mt-3 text-sm text-gray-300">
                                {{ $stats['favourite_club_bias']['team'] ?? 'No favourite club selected' }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400">
                                {{ $stats['favourite_club_bias']['points'] ?? 0 }} points
                                ({{ $stats['favourite_club_bias']['percent'] ?? 0 }}% of contributed points)
                            </p>
                        </div>
                    </section>
                @endif

                @if ($activeSection === 'chips')
                    <section class="rounded-2xl border border-gray-700 bg-card p-5">
                        <h2 class="text-lg font-semibold text-white">Chip Usage</h2>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @forelse (($stats['chip_usage']['rows'] ?? []) as $chip)
                                <div class="rounded-lg bg-card px-3 py-2 text-sm text-gray-200">
                                    GW{{ $chip['gameweek'] }} - {{ $chip['chip'] }}
                                    <span class="ml-2 text-accent">{{ $chip['points_gained'] >= 0 ? '+' : '' }}{{ $chip['points_gained'] }} pts</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400">No chips recorded yet.</p>
                            @endforelse
                        </div>
                    </section>
                @endif

                @if ($activeSection === 'captaincy')
                    <section class="rounded-2xl border border-gray-700 bg-card p-5">
                        <h2 class="text-lg font-semibold text-white">Captaincy Performance</h2>
                        <div class="mt-4 overflow-x-auto">
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
                    </section>
                @endif

                @if ($activeSection === 'transfers')
                    <section class="rounded-2xl border border-gray-700 bg-card p-5">
                        <h2 class="text-lg font-semibold text-white">Transfer Efficiency</h2>
                        <div class="mt-4 overflow-x-auto">
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
                    </section>
                @endif

                @if ($activeSection === 'value')
                    @php
                        $valueRows = collect($stats['value_evolution']['rows'] ?? [])->values();
                    @endphp
                    <section class="rounded-2xl border border-gray-700 bg-card p-5">
                        <h2 class="text-lg font-semibold text-white">Squad Value Evolution</h2>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm text-gray-200">
                                <thead>
                                    <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                                        <th class="px-3 py-2 text-left">GW</th>
                                        <th class="px-3 py-2 text-right">Squad Value (€m)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($valueRows as $index => $row)
                                        @php
                                            $previousValue = $index < ($valueRows->count() - 1)
                                                ? (float) $valueRows[$index + 1]['value']
                                                : null;
                                            $currentValue = (float) $row['value'];
                                        @endphp
                                        <tr class="border-b border-gray-800/80">
                                            <td class="px-3 py-2">
                                                <span class="inline-flex items-center gap-1">
                                                    @if ($previousValue === null || abs($currentValue - $previousValue) < 0.001)
                                                        <span class="font-semibold text-gray-400">-</span>
                                                    @elseif ($currentValue > $previousValue)
                                                        <span class="font-semibold text-green-300">↑</span>
                                                    @else
                                                        <span class="font-semibold text-red-300">↓</span>
                                                    @endif
                                                    <span>GW{{ $row['gameweek'] }}</span>
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-right">€{{ number_format((float) $row['value'], 1) }}m</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-3 py-6 text-center text-gray-400">No squad value history yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if ($activeSection === 'history')
                    @php
                        $profileHistoryRows = collect($stats['history_rows'] ?? [])->values();
                    @endphp

                    <section class="rounded-2xl border border-gray-700 bg-card p-5">
                        <h2 class="text-lg font-semibold text-white">Gameweek History</h2>
                        <div x-data="{ visibleRows: 10, totalRows: {{ $profileHistoryRows->count() }} }">
                            <div class="mt-4 overflow-x-auto">
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
                                            @php
                                                $previousPoints = $index < ($profileHistoryRows->count() - 1)
                                                    ? (int) $profileHistoryRows[$index + 1]['points']
                                                    : null;
                                                $currentPoints = (int) $history['points'];
                                            @endphp
                                            <tr class="border-b border-gray-800/80" x-show="{{ $index }} < visibleRows" @if ($index >= 10) x-cloak @endif>
                                                <td class="px-3 py-2">
                                                    <span class="inline-flex items-center gap-1">
                                                        @if ($previousPoints === null || $currentPoints === $previousPoints)
                                                            <span class="font-semibold text-gray-400">-</span>
                                                        @elseif ($currentPoints > $previousPoints)
                                                            <span class="font-semibold text-green-300">↑</span>
                                                        @else
                                                            <span class="font-semibold text-red-300">↓</span>
                                                        @endif
                                                        <span>{{ $history['gameweek'] }}</span>
                                                    </span>
                                                </td>
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

                @if (! $isOverview)
                    <div class="flex justify-end">
                        <a href="{{ route('profile.index', $managerQuery) }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-gray-200 hover:bg-secondary">
                            <i data-lucide="arrow-left" class="h-4 w-4"></i>
                            Back to Overview
                        </a>
                    </div>
                @endif

                @if ($isOverview)
                    @include('profile.partials.share', ['profileShareManager' => $selectedManager])
                @endif
            @endif
        @endif
    </div>
</x-app-layout>
