<x-app-layout>
    <div class="space-y-6" x-data="failedJobsPanel({
        retryUrl: '{{ route('admin.jobs.index') }}',
        retryAllUrl: '{{ route('admin.jobs.retryAll') }}',
        flushUrl: '{{ route('admin.jobs.flush') }}',
        csrfToken: '{{ csrf_token() }}',
        initialJobs: @js($failedJobs),
        totalFailed: {{ $totalFailed }},
    })">
        <x-page-title title="Failed Jobs" />

        <template x-if="flash.message">
            <div :class="flash.type === 'error'
                    ? 'rounded-xl border border-red-700 bg-red-900/30 px-4 py-3 text-sm text-red-200'
                    : 'rounded-xl border border-green-700 bg-green-900/30 px-4 py-3 text-sm text-green-200'">
                <span x-text="flash.message"></span>
            </div>
        </template>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Failed</p>
                <p class="mt-2 text-2xl font-bold text-white" x-text="jobs.length"></p>
            </div>
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Job Types</p>
                <p class="mt-2 text-2xl font-bold text-white" x-text="uniqueJobCount"></p>
            </div>
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Oldest Failure</p>
                <p class="mt-2 text-sm font-bold text-white" x-text="oldestFailure"></p>
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
            <h2 class="text-lg font-semibold text-white">Failed Jobs</h2>

            <template x-if="jobs.length === 0">
                <div class="mt-6 rounded-xl border border-gray-700 bg-card px-6 py-12 text-center">
                    <p class="text-sm text-gray-400">No failed jobs. Everything is running smoothly.</p>
                </div>
            </template>

            <template x-if="jobs.length > 0">
                <div class="mt-4 space-y-4">
                    <template x-for="job in jobs" :key="job.id">
                        <div class="rounded-xl border border-gray-700 bg-primary/30 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-red-900/40 px-2 py-0.5 text-xs font-semibold text-red-300" x-text="job.job"></span>
                                        <span class="text-xs text-gray-500" x-text="job.queue"></span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">
                                        Failed <span x-text="job.failed_at_human"></span>
                                        &middot;
                                        <span x-text="job.failed_at"></span>
                                    </p>
                                    <p class="mt-2 break-all rounded-lg bg-primary/50 p-3 text-xs leading-relaxed text-gray-300" x-text="job.exception"></p>
                                </div>
                                <div class="flex shrink-0 gap-2">
                                    <button
                                        type="button"
                                        class="rounded-lg bg-accent px-3 py-1.5 text-xs font-semibold text-primary hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="busy"
                                        @click="retryJob(job.id)"
                                    >
                                        Retry
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="busy"
                                        @click="deleteJob(job.id)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </section>
    </div>

    <script>
        function failedJobsPanel(config) {
            return {
                jobs: config.initialJobs,
                busy: false,
                flash: { type: null, message: '' },

                get uniqueJobCount() {
                    const types = new Set(this.jobs.map(j => j.job));
                    return types.size;
                },

                get oldestFailure() {
                    if (this.jobs.length === 0) return '-';
                    return this.jobs[this.jobs.length - 1].failed_at_human || '-';
                },

                async retryJob(id) {
                    this.busy = true;

                    try {
                        const response = await fetch(`${config.retryUrl}/${id}/retry`, {
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

                        this.jobs = this.jobs.filter(j => j.id !== id);
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
                        const response = await fetch(`${config.retryUrl}/${id}`, {
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

                        this.jobs = this.jobs.filter(j => j.id !== id);
                        this.flash = { type: 'success', message: data.message || 'Job deleted.' };
                    } catch {
                        this.flash = { type: 'error', message: 'Network error. Please retry.' };
                    } finally {
                        this.busy = false;
                    }
                },

                async retryAll() {
                    this.busy = true;

                    try {
                        const response = await fetch(config.retryAllUrl, {
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

                        this.jobs = [];
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
                        const response = await fetch(config.flushUrl, {
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

                        this.jobs = [];
                        this.flash = { type: 'success', message: data.message || 'All failed jobs deleted.' };
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
