<x-app-layout>
    <div class="space-y-6" x-data="jobsPanel({
        baseUrl: '/admin/jobs',
        csrfToken: '{{ csrf_token() }}',
        initialJobs: @js($jobs),
        totalJobs: {{ $totalJobs }},
        currentPage: {{ $currentPage }},
        lastPage: {{ $lastPage }},
        perPage: {{ $perPage }}
    })">
        <x-page-title title="Job Queue" />

        <template x-if="flash.message">
            <div :class="flash.type === 'error'
                    ? 'rounded-xl border border-red-700 bg-red-900/30 px-4 py-3 text-sm text-red-200'
                    : 'rounded-xl border border-green-700 bg-green-900/30 px-4 py-3 text-sm text-green-200'">
                <span x-text="flash.message"></span>
            </div>
        </template>

<section class="grid gap-4 md:grid-cols-4">
    <div class="rounded-2xl border border-gray-600 bg-card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Jobs</p>
        <p class="mt-2 text-2xl font-bold text-white" x-text="totalJobs"></p>
    </div>
    <div class="rounded-2xl border border-gray-600 bg-card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Queued</p>
        <p class="mt-2 text-2xl font-bold text-blue-400" x-text="statusCounts.queued"></p>
    </div>
    <div class="rounded-2xl border border-gray-600 bg-card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Processing</p>
        <p class="mt-2 text-2xl font-bold text-yellow-400" x-text="statusCounts.processing"></p>
    </div>
    <div class="rounded-2xl border border-gray-600 bg-card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Failed</p>
        <p class="mt-2 text-2xl font-bold text-red-400" x-text="statusCounts.failed"></p>
    </div>
</section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-white">Actions</h2>
                <div class="flex gap-3">
                    <button
                        type="button"
                        class="rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-primary hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="busy || jobs.length === 0"
                        @click="retryAll()"
                    >
                        Retry All
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="busy || jobs.length === 0"
                        @click="flushAll()"
                    >
                        Delete All
                    </button>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">Job Queue</h2>

    <template x-if="jobs.length === 0">
        <div class="mt-6 rounded-xl border border-gray-700 bg-card px-6 py-12 text-center">
            <p class="text-sm text-gray-400">No jobs in queue.</p>
        </div>
    </template>

    <template x-if="jobs.length > 0">
        <div class="mt-4 overflow-x-auto rounded-xl border border-gray-700 bg-card">
            <table class="min-w-full text-sm text-gray-200">
                <thead class="bg-primary/50 text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Job</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Queue</th>
                        <th class="px-3 py-2 text-center">Attempts</th>
                        <th class="px-3 py-2 text-left">Created</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <template x-for="job in jobs" :key="job.id">
                        <tr class="hover:bg-primary/20">
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500" x-text="job.id"></td>
                            <td class="px-3 py-2">
                                <div class="text-xs font-semibold text-white" x-text="job.job"></div>
                                <template x-if="job.job_class && job.job_class !== 'Unknown'">
                                    <div class="text-[10px] text-gray-500 font-mono truncate max-w-[200px]" x-text="job.job_class"></div>
                                </template>
                            </td>
                            <td class="px-3 py-2">
                                <span :class="
                                    job.status === 'processing' ? 'bg-yellow-900/40 text-yellow-300' :
                                    job.status === 'queued' ? 'bg-blue-900/40 text-blue-300' :
                                    job.status === 'failed' ? 'bg-red-900/40 text-red-300' :
                                    'bg-gray-900/40 text-gray-300'
                                " class="rounded-full px-2 py-0.5 text-xs font-semibold" x-text="job.status"></span>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-400" x-text="job.queue || '-'"></td>
                            <td class="px-3 py-2 text-center text-xs text-gray-400" x-text="job.attempts ?? '-'"></td>
                            <td class="px-3 py-2 text-xs text-gray-400 whitespace-nowrap" x-text="job.created_at"></td>
                            <td class="px-3 py-2 text-right">
                                <div class="flex justify-end gap-1">
                                    <template x-if="job.status === 'failed'">
                                        <button
                                            type="button"
                                            class="rounded bg-accent px-2 py-1 text-xs font-semibold text-primary hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-60"
                                            :disabled="busy"
                                            @click="retryJob(job.id)"
                                        >
                                            Retry
                                        </button>
                                    </template>
                                    <button
                                        type="button"
                                        class="rounded bg-red-600 px-2 py-1 text-xs font-semibold text-white hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="busy"
                                        @click="deleteJob(job.id)"
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
    </template>
</section>
    </div>

    <script>
        function jobsPanel(config) {
            return {
                jobs: config.initialJobs,
                busy: false,
                flash: { type: null, message: '' },
                currentPage: config.currentPage,
                lastPage: config.lastPage,
                perPage: config.perPage,

                get statusCounts() {
                    const counts = { queued: 0, processing: 0, completed: 0, failed: 0, idle: 0 };
                    this.jobs.forEach(job => {
                        const status = job.status || 'idle';
                        if (counts[status] !== undefined) {
                            counts[status]++;
                        }
                    });
                    return counts;
                },

                async retryJob(id) {
                    this.busy = true;

                    try {
                        const response = await fetch(`/admin/jobs/${id}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                            },
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            this.flash = { type: 'error', message: data.message || 'Retry failed.' };
                            return;
                        }

                        // Reload current page to get updated job list
                        await this.changePage(this.currentPage);
                        this.flash = { type: 'success', message: data.message || 'Job requeued.' };
                    } catch {
                        this.flash = { type: 'error', message: 'Network error. Please retry.' };
                    } finally {
                        this.busy = false;
                    }
                },

                async deleteJob(id) {
                    this.busy = true;

                    try {
                        const response = await fetch(`/admin/jobs/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                            },
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            this.flash = { type: 'error', message: data.message || 'Delete failed.' };
                            return;
                        }

                        // Reload current page to get updated job list
                        await this.changePage(this.currentPage);
                        this.flash = { type: 'success', message: data.message || 'Job removed.' };
                    } catch {
                        this.flash = { type: 'error', message: 'Network error. Please retry.' };
                    } finally {
                        this.busy = false;
                    }
                },

                async retryAll() {
                    this.busy = true;

                    try {
                        const response = await fetch('/admin/jobs/retry-all', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                            },
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            this.flash = { type: 'error', message: data.message || 'Retry all failed.' };
                            return;
                        }

                        // Reload current page to get updated job list
                        await this.changePage(this.currentPage);
                        this.flash = { type: 'success', message: data.message || 'All jobs requeued.' };
                    } catch {
                        this.flash = { type: 'error', message: 'Network error. Please retry.' };
                    } finally {
                        this.busy = false;
                    }
                },

                async flushAll() {
                    if (!confirm('Delete all failed jobs? This cannot be undone.')) return;

                    this.busy = true;

                    try {
                        const response = await fetch('/admin/jobs/flush', {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                            },
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            this.flash = { type: 'error', message: data.message || 'Flush failed.' };
                            return;
                        }

                        // Reload current page to get updated job list
                        await this.changePage(this.currentPage);
                        this.flash = { type: 'success', message: data.message || 'All failed jobs deleted.' };
                    } catch {
                        this.flash = { type: 'error', message: 'Network error. Please retry.' };
                    } finally {
                        this.busy = false;
                    }
                },

                async changePage(page) {
                    if (page < 1 || page > this.lastPage) return;
                    
                    this.busy = true;
                    try {
                        const response = await fetch(`/admin/jobs?page=${page}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                            },
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            this.flash = { type: 'error', message: data.message || 'Failed to load page.' };
                            return;
                        }

                        // Update jobs and pagination info
                        this.jobs = data.jobs || [];
                        this.currentPage = data.current_page || 1;
                        this.lastPage = data.last_page || 1;
                        this.perPage = data.per_page || config.perPage;
                        
                        this.flash = { type: 'success', message: 'Page loaded.' };
                    } catch {
                        this.flash = { type: 'error', message: 'Network error. Please retry.' };
                    } finally {
                        this.busy = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
