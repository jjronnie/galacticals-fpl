<x-app-layout>
    <div
        class="space-y-6"
        x-data="adminDataSyncPanel({
            initialPayload: @js($initialPayload),
            csrfToken: '{{ csrf_token() }}',
            statusUrl: '{{ route('admin.data.status') }}',
            syncAllUrl: '{{ route('admin.data.syncAll') }}',
            flushCacheUrl: '{{ route('admin.data.flushCache') }}',
            fetchFplUrl: '{{ route('admin.data.fetchFpl') }}',
            fetchManagersUrl: '{{ route('admin.data.fetchManagers') }}',
            computeGameweeksUrl: '{{ route('admin.data.computeGameweeks') }}',
            syncFixturesUrl: '{{ route('admin.data.syncFixtures') }}',
            leaguesUrl: '{{ route('admin.data.leagues') }}',
            observerUrl: '{{ route('admin.data.observer') }}'
        })"
        x-init="init()"
    >
        <x-page-title title="Admin Data Sync" />

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
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Claimed Managers</p>
                <p class="mt-2 text-2xl font-bold text-white" x-text="summary.claimed_managers"></p>
            </div>
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Processing Leagues</p>
                <p class="mt-2 text-2xl font-bold text-white" x-text="summary.processing_leagues"></p>
            </div>
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Failed Leagues</p>
                <p class="mt-2 text-2xl font-bold text-white" x-text="summary.failed_leagues"></p>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">Queue Actions</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <button
                    type="button"
                    class="w-full rounded-lg bg-cyan-500 px-4 py-3 text-sm font-semibold text-primary hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="busyAction !== null"
                    @click="queueFullSync()"
                >
                    Sync Full Application
                </button>

                <button
                    type="button"
                    class="w-full rounded-lg bg-accent px-4 py-3 text-sm font-semibold text-primary hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="busyAction !== null"
                    @click="postAction(fetchFplUrl, {}, 'FPL data sync queued.')"
                >
                    Sync FPL Teams/Players
                </button>

                <button
                    type="button"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="busyAction !== null"
                    @click="postAction(syncFixturesUrl, {}, 'Fixtures sync queued.')"
                >
                    Sync Fixtures
                </button>

                <button
                    type="button"
                    class="w-full rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-500 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="busyAction !== null"
                    @click="postAction(fetchManagersUrl, {}, 'Manager profile sync queued.')"
                >
                    Sync Manager Profiles
                </button>

                <button
                    type="button"
                    class="w-full rounded-lg bg-secondary px-4 py-3 text-sm font-semibold text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="busyAction !== null"
                    @click="postAction(computeGameweeksUrl, {}, 'Gameweek table computation queued.')"
                >
                    Compute All GW Tables
                </button>

                <button
                    type="button"
                    class="w-full rounded-lg bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="busyAction !== null"
                    @click="postAction(flushCacheUrl, {}, 'Application cache flushed.')"
                >
                    Flush All Cache
                </button>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">System Job Progress</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">Job</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Progress</th>
                            <th class="px-3 py-2 text-left">Message</th>
                            <th class="px-3 py-2 text-left">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="job in jobs" :key="job.key">
                            <tr class="border-b border-gray-800/80">
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-white" x-text="job.label"></p>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="statusClass(job.status)" x-text="formatStatus(job.status)"></span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="w-52">
                                        <div class="h-2 w-full rounded bg-primary">
                                            <div class="h-2 rounded bg-accent transition-all duration-500" :style="`width: ${job.progress}%`"></div>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-400">
                                            <span x-text="job.processed"></span>/<span x-text="job.total"></span>
                                            (<span x-text="job.progress"></span>%)
                                            <template x-if="job.failed > 0">
                                                <span class="text-red-300"> | failed: <span x-text="job.failed"></span></span>
                                            </template>
                                        </p>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300" x-text="job.message"></td>
                                <td class="px-3 py-3 text-xs text-gray-400" x-text="relativeTime(job.updated_at)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">Matchday Sync History</h2>
            <p class="mt-1 text-sm text-gray-400">Heavy sync runs triggered when matchdays complete and buffer time passes.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">GW</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Trigger</th>
                            <th class="px-3 py-2 text-left">FPL</th>
                            <th class="px-3 py-2 text-left">Profiles</th>
                            <th class="px-3 py-2 text-left">Leagues</th>
                            <th class="px-3 py-2 text-left">Errors</th>
                            <th class="px-3 py-2 text-left">Duration</th>
                            <th class="px-3 py-2 text-left">When</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="run in syncRuns" :key="run.id">
                            <tr class="border-b border-gray-800/80">
                                <td class="px-3 py-3">
                                    <span class="rounded-full bg-gray-700 px-2 py-1 text-xs font-bold text-white" x-text="'GW' + run.event"></span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="run.status === 'success' ? 'bg-green-900/40 text-green-300' : 'bg-red-900/40 text-red-300'" x-text="run.status === 'success' ? 'Success' : 'Failed'"></span>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300" x-text="run.triggered_by"></td>
                                <td class="px-3 py-3">
                                    <span class="text-xs" :class="run.fpl_synced ? 'text-green-400' : 'text-red-400'" x-text="run.fpl_synced ? '✓' : '✗'"></span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-xs" :class="run.profile_synced ? 'text-green-400' : 'text-red-400'" x-text="run.profile_synced ? '✓' : '✗'"></span>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <span x-text="run.leagues_synced + '/' + run.leagues_total"></span>
                                    <template x-if="run.league_failures_count > 0">
                                        <span class="text-red-400" x-text="' (' + run.league_failures_count + ' failed)'"></span>
                                    </template>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-xs" :class="run.errors_count > 0 ? 'text-red-400' : 'text-gray-500'" x-text="run.errors_count > 0 ? run.errors_count : '-'"></span>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-400" x-text="run.duration_seconds ? run.duration_seconds + 's' : '-'"></td>
                                <td class="px-3 py-3 text-xs text-gray-400" x-text="relativeTime(run.synced_at)"></td>
                            </tr>
                        </template>
                        <template x-if="syncRuns.length === 0">
                            <tr>
                                <td colspan="9" class="px-3 py-8 text-center text-gray-400">No matchday sync runs recorded yet.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <script>
        function adminDataSyncPanel(config) {
            return {
                busyAction: null,
                fetchFplUrl: config.fetchFplUrl,
                fetchManagersUrl: config.fetchManagersUrl,
                computeGameweeksUrl: config.computeGameweeksUrl,
                syncFixturesUrl: config.syncFixturesUrl,
                flushCacheUrl: config.flushCacheUrl,
                leaguesUrl: config.leaguesUrl,
                observerUrl: config.observerUrl,
                summary: config.initialPayload.summary,
                jobs: config.initialPayload.jobs,
                leagues: config.initialPayload.leagues,
                syncRuns: config.initialPayload.sync_runs ?? [],
                flash: {
                    type: null,
                    message: ''
                },
                pollTimer: null,

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
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            return;
                        }

                        const payload = await response.json();
                        this.applyPayload(payload);
                    } catch (error) {
                        console.error('Failed to fetch sync status.', error);
                    }
                },

                applyPayload(payload) {
                    this.summary = payload.summary ?? this.summary;
                    this.jobs = payload.jobs ?? this.jobs;
                    this.leagues = payload.leagues ?? this.leagues;
                    this.syncRuns = payload.sync_runs ?? this.syncRuns;
                },

                async queueFullSync() {
                    await this.postAction(config.syncAllUrl, {}, 'Full application sync queued.');
                },

                async postAction(url, payload = {}, fallbackMessage = 'Action queued.') {
                    this.busyAction = url;

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                            },
                            body: JSON.stringify(payload),
                        });

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
                            this.applyPayload(data.payload);
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
                    return String(status || 'idle').replace('_', ' ').replace(/\b\w/g, letter => letter.toUpperCase());
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

                relativeTime(isoDate) {
                    if (!isoDate) {
                        return '-';
                    }

                    const timestamp = new Date(isoDate).getTime();

                    if (Number.isNaN(timestamp)) {
                        return '-';
                    }

                    const seconds = Math.floor((Date.now() - timestamp) / 1000);

                    if (seconds < 60) {
                        return `${seconds}s ago`;
                    }

                    const minutes = Math.floor(seconds / 60);
                    if (minutes < 60) {
                        return `${minutes}m ago`;
                    }

                    const hours = Math.floor(minutes / 60);
                    if (hours < 24) {
                        return `${hours}h ago`;
                    }

                    const days = Math.floor(hours / 24);
                    return `${days}d ago`;
                },
            };
        }
    </script>
</x-app-layout>
