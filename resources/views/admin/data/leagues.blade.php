<x-app-layout>
    <div
        class="space-y-6"
        x-data="adminLeaguesPanel({
            initialPayload: @js($initialPayload),
            csrfToken: '{{ csrf_token() }}',
            statusUrl: '{{ route('admin.data.status') }}',
            refreshLeagueUrlTemplate: '{{ route('admin.data.refreshLeague', ['league' => '__LEAGUE_ID__']) }}',
            destroyLeagueUrlTemplate: '{{ route('admin.data.destroyLeague', ['league' => '__LEAGUE_ID__']) }}',
            computeGameweeksUrl: '{{ route('admin.data.computeGameweeks') }}',
            publicLeagueUrlTemplate: '{{ route('public.leagues.show', ['slug' => '__LEAGUE_SLUG__']) }}',
        })"
        x-init="init()"
    >
        <x-page-title title="Admin Leagues" />

        <template x-if="flash.message">
            <div :class="flash.type === 'error'
                    ? 'rounded-xl border border-red-700 bg-red-900/30 px-4 py-3 text-sm text-red-200'
                    : 'rounded-xl border border-green-700 bg-green-900/30 px-4 py-3 text-sm text-green-200'">
                <span x-text="flash.message"></span>
            </div>
        </template>

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

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Leagues</p>
                <p class="mt-2 text-2xl font-bold text-white" x-text="summary.total_leagues"></p>
            </div>
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Processing</p>
                <p class="mt-2 text-2xl font-bold text-white" x-text="summary.processing_leagues"></p>
            </div>
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Failed</p>
                <p class="mt-2 text-2xl font-bold text-white" x-text="summary.failed_leagues"></p>
            </div>
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Managers Across Leagues</p>
                <p class="mt-2 text-2xl font-bold text-white" x-text="totalManagers"></p>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-white">League Sync Progress</h2>
                <span class="rounded-full px-2 py-1 text-xs font-semibold"
                    :class="hasRunningWork() ? 'bg-blue-900/40 text-blue-300' : 'bg-green-900/40 text-green-300'"
                    x-text="hasRunningWork() ? 'Sync in progress' : 'Idle'"></span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">League</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Progress</th>
                            <th class="px-3 py-2 text-left">Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="league in leagues" :key="`progress-${league.id}`">
                            <tr class="border-b border-gray-800/80">
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-white" x-text="league.name"></p>
                                    <p class="text-xs text-gray-400">League ID <span x-text="league.league_id"></span></p>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="statusClass(league.sync_status)" x-text="formatStatus(league.sync_status)"></span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="w-44">
                                        <div class="h-2 w-full rounded bg-primary">
                                            <div class="h-2 rounded bg-accent transition-all duration-500" :style="`width: ${league.progress}%`"></div>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-400">
                                            <span x-text="league.synced_managers"></span>/<span x-text="league.total_managers"></span>
                                            (<span x-text="league.progress"></span>%)
                                        </p>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300" x-text="league.sync_message"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">Leagues</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">League</th>
                            <th class="px-3 py-2 text-left">League ID</th>
                            <th class="px-3 py-2 text-left">Owner</th>
                            <th class="px-3 py-2 text-left">Join Date</th>
                            <th class="px-3 py-2 text-right">Total Managers</th>
                            <th class="px-3 py-2 text-left">Last Updated</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="league in leagues" :key="league.id">
                            <tr class="border-b border-gray-800/80 align-top">
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-white" x-text="league.name"></p>
                                    <p class="text-xs text-gray-400" x-text="league.sync_message"></p>
                                </td>
                                <td class="px-3 py-3 font-semibold text-white" x-text="league.league_id"></td>
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <p x-text="league.owner_name"></p>
                                    <p class="text-gray-400" x-text="league.owner_email"></p>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300" x-text="league.joined_at_human || '-'"></td>
                                <td class="px-3 py-3 text-right font-semibold text-white" x-text="league.managers_count"></td>
                                <td class="px-3 py-3 text-xs text-gray-300" x-text="league.last_updated_at_human || '-'"></td>
                                <td class="px-3 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            :href="publicLeagueUrl(league.slug)"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white hover:bg-secondary"
                                            title="Open public league page"
                                        >
                                            <i data-lucide="eye" class="h-4 w-4"></i>
                                        </a>
                                        <button
                                            type="button"
                                            class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white hover:bg-secondary disabled:cursor-not-allowed disabled:opacity-60"
                                            :disabled="busyAction !== null || league.sync_status === 'processing'"
                                            @click="refreshLeague(league.id)"
                                        >
                                            Refresh
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg bg-secondary px-3 py-2 text-xs font-semibold text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                                            :disabled="busyAction !== null"
                                            @click="computeLeagueGameweek(league.id)"
                                        >
                                            Compute GW
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg bg-red-700 px-3 py-2 text-xs font-semibold text-white hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-60"
                                            :disabled="busyAction !== null"
                                            @click="deleteLeague(league)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        function adminLeaguesPanel(config) {
            return {
                busyAction: null,
                summary: config.initialPayload.summary,
                leagues: config.initialPayload.leagues,
                jobs: config.initialPayload.jobs,
                flash: {
                    type: null,
                    message: '',
                },
                pollTimer: null,
                get totalManagers() {
                    return this.leagues.reduce((total, league) => total + Number(league.managers_count || 0), 0);
                },
                init() {
                    this.pollTimer = setInterval(() => {
                        if (this.hasRunningWork()) {
                            this.fetchStatus();
                        }
                    }, 4000);
                },
                hasRunningWork() {
                    const activeJob = this.jobs.some((job) => ['queued', 'processing'].includes(String(job.status || '').toLowerCase()));
                    const activeLeague = this.leagues.some((league) => String(league.sync_status || '').toLowerCase() === 'processing');

                    return activeJob || activeLeague;
                },
                async fetchStatus() {
                    try {
                        const response = await fetch(config.statusUrl, {
                            headers: {
                                Accept: 'application/json',
                            },
                        });

                        if (!response.ok) {
                            return;
                        }

                        const payload = await response.json();
                        this.summary = payload.summary ?? this.summary;
                        this.leagues = payload.leagues ?? this.leagues;
                        this.jobs = payload.jobs ?? this.jobs;
                    } catch (error) {
                        console.error('Failed to fetch league status.', error);
                    }
                },
                publicLeagueUrl(slug) {
                    return config.publicLeagueUrlTemplate.replace('__LEAGUE_SLUG__', String(slug));
                },
                async refreshLeague(leagueId) {
                    const url = config.refreshLeagueUrlTemplate.replace('__LEAGUE_ID__', String(leagueId));
                    await this.postAction(url, {}, 'League refresh queued.');
                },
                async computeLeagueGameweek(leagueId) {
                    await this.postAction(config.computeGameweeksUrl, { league_id: leagueId }, 'League gameweek computation queued.');
                },
                async deleteLeague(league) {
                    const managersCount = Number(league.managers_count || 0);
                    const isConfirmed = window.confirm(
                        `Delete "${league.name}" and ${managersCount} manager record(s)? This action cannot be undone.`
                    );

                    if (!isConfirmed) {
                        return;
                    }

                    const url = config.destroyLeagueUrlTemplate.replace('__LEAGUE_ID__', String(league.id));

                    await this.postAction(url, {}, `League ${league.name} deleted successfully.`, 'DELETE');
                },
                async postAction(url, payload = {}, fallbackMessage = 'Action queued.', method = 'POST') {
                    this.busyAction = url;

                    try {
                        const requestOptions = {
                            method,
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                            },
                        };

                        if (method !== 'DELETE') {
                            requestOptions.body = JSON.stringify(payload);
                        }

                        const response = await fetch(url, requestOptions);

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            const validationErrors = data.errors ? Object.values(data.errors).flat() : [];
                            this.flash = {
                                type: 'error',
                                message: validationErrors[0] || data.message || 'Action failed. Please try again.',
                            };

                            return;
                        }

                        this.flash = {
                            type: 'success',
                            message: data.message || fallbackMessage,
                        };

                        if (data.payload) {
                            this.summary = data.payload.summary ?? this.summary;
                            this.leagues = data.payload.leagues ?? this.leagues;
                            this.jobs = data.payload.jobs ?? this.jobs;
                        } else {
                            await this.fetchStatus();
                        }
                    } catch (error) {
                        this.flash = {
                            type: 'error',
                            message: 'Network error while queueing action. Please retry.',
                        };
                    } finally {
                        this.busyAction = null;
                    }
                },
                formatStatus(status) {
                    return String(status || 'idle').replace('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
                },
                statusClass(status) {
                    const normalized = String(status || '').toLowerCase();

                    if (normalized === 'completed') {
                        return 'bg-green-900/40 text-green-300';
                    }

                    if (normalized === 'processing' || normalized === 'queued') {
                        return 'bg-blue-900/40 text-blue-300';
                    }

                    if (normalized === 'failed') {
                        return 'bg-red-900/40 text-red-300';
                    }

                    return 'bg-gray-700 text-gray-200';
                },
            };
        }
    </script>
</x-app-layout>
