<x-app-layout>
    <div class="space-y-6" x-data="{ previewUrl: null }">
        <x-page-title title="Profile Verifications" />

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

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-700 bg-card p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400">Pending</p>
                <p class="mt-1 text-2xl font-bold text-amber-200">{{ $pendingCount }}</p>
            </div>
            <div class="rounded-2xl border border-gray-700 bg-card p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400">Rejected</p>
                <p class="mt-1 text-2xl font-bold text-red-200">{{ $rejectedCount }}</p>
            </div>
            <div class="rounded-2xl border border-gray-700 bg-card p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400">Approved</p>
                <p class="mt-1 text-2xl font-bold text-green-200">{{ $approvedCount }}</p>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <form method="GET" action="{{ route('admin.verifications.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label for="status" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-400">Status</label>
                    <select id="status" name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white">
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                        <option value="approved" @selected($status === 'approved')>Approved</option>
                        <option value="all" @selected($status === 'all')>All</option>
                    </select>
                </div>
            </form>
        </section>

        <section
            class="rounded-2xl border border-gray-700 bg-card p-5"
            x-data="manualVerificationSearch(@js(route('admin.verifications.managers.search')), @js($managerSearch))"
            x-init="init()"
        >
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-300">Manual Verification</h2>
            <p class="mt-1 text-xs text-gray-400">Search claimed profiles and verify or revoke directly.</p>

            <div class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                <div>
                    <label for="manager_search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-400">Search Claimed Profile</label>
                    <input
                        id="manager_search"
                        type="text"
                        x-model="query"
                        @input.debounce.350ms="search()"
                        placeholder="Team, manager, entry ID, claimant name or email"
                        class="w-full rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white placeholder:text-gray-400"
                    >
                </div>
                <div class="flex items-center justify-end">
                    <p x-show="loading" x-cloak class="text-xs text-gray-400">Searching...</p>
                </div>
            </div>

            <div x-ref="resultsContainer">
                @include('admin.verifications.partials.manual-managers-results', [
                    'managerSearch' => $managerSearch,
                    'manualManagers' => $manualManagers,
                ])
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">User</th>
                            <th class="px-3 py-2 text-left">Team</th>
                            <th class="px-3 py-2 text-left">Submission</th>
                            <th class="px-3 py-2 text-left">Screenshot</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $submission)
                            <tr class="border-b border-gray-800/80 align-top">
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <p class="font-semibold text-white">{{ $submission->user?->name }}</p>
                                    <p>{{ $submission->user?->email }}</p>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <p class="font-semibold text-white">{{ $submission->team_name }}</p>
                                    <p>{{ $submission->player_name }}</p>
                                    <p>Entry {{ $submission->entry_id }}</p>
                                    <a
                                        href="{{ route('managers.show', ['entryId' => $submission->entry_id]) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mt-2 inline-flex rounded bg-primary px-2 py-1 text-[11px] font-semibold text-cyan-200 hover:bg-secondary"
                                    >
                                        Open Public Profile
                                    </a>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <p>Submitted {{ $submission->created_at?->diffForHumans() }}</p>
                                    @if ($submission->notes)
                                        <p class="mt-2 max-w-xs rounded-lg bg-primary p-2 text-gray-200">{{ $submission->notes }}</p>
                                    @else
                                        <p class="mt-2 text-gray-500">No notes provided.</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if ($submission->screenshot_path)
                                        @php $screenshotUrl = route('admin.verifications.screenshot', $submission); @endphp
                                        <button
                                            type="button"
                                            class="group block overflow-hidden rounded-lg border border-gray-700"
                                            @click="previewUrl = '{{ $screenshotUrl }}'"
                                        >
                                            <img src="{{ $screenshotUrl }}" alt="Verification screenshot" class="h-24 w-40 object-cover transition group-hover:scale-105">
                                        </button>
                                        <button
                                            type="button"
                                            class="mt-2 rounded bg-primary px-2 py-1 text-[11px] font-semibold text-cyan-200 hover:bg-secondary"
                                            @click="previewUrl = '{{ $screenshotUrl }}'"
                                        >
                                            View Full Screen
                                        </button>
                                    @elseif ($submission->status === 'approved')
                                        <p class="text-xs text-gray-400">Discarded after approval.</p>
                                    @else
                                        <p class="text-xs text-gray-400">Screenshot unavailable.</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold
                                        {{ $submission->status === 'pending' ? 'bg-yellow-900/40 text-yellow-300' : '' }}
                                        {{ $submission->status === 'rejected' ? 'bg-red-900/40 text-red-300' : '' }}
                                        {{ $submission->status === 'approved' ? 'bg-green-900/40 text-green-300' : '' }}">
                                        {{ ucfirst($submission->status) }}
                                    </span>

                                    @if ($submission->status === 'rejected' && $submission->rejection_reason)
                                        <p class="mt-2 max-w-xs text-xs text-red-200">{{ $submission->rejection_reason }}</p>
                                    @endif

                                    @if ($submission->reviewed_at)
                                        <p class="mt-2 text-xs text-gray-500">
                                            Reviewed {{ $submission->reviewed_at->diffForHumans() }} by {{ $submission->reviewer?->name ?? 'Unknown' }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if ($submission->status === 'pending')
                                        <div class="space-y-2">
                                            <form method="POST" action="{{ route('admin.verifications.resolve', $submission) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="w-full rounded-lg bg-green-700 px-3 py-2 text-xs font-semibold text-white hover:bg-green-600">
                                                    Approve & Verify
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.verifications.resolve', $submission) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="reject">
                                                <textarea
                                                    name="rejection_reason"
                                                    rows="3"
                                                    maxlength="2000"
                                                    placeholder="Reason shown to user when rejected."
                                                    class="w-full rounded-lg border border-gray-600 bg-primary px-2 py-1 text-xs text-white placeholder:text-gray-400"
                                                    required
                                                ></textarea>
                                                <button type="submit" class="w-full rounded-lg bg-red-700 px-3 py-2 text-xs font-semibold text-white hover:bg-red-600">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-500">No actions available.</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-400">No verification submissions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $submissions->links() }}
            </div>
        </section>

        <div
            x-show="previewUrl"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
            @click.self="previewUrl = null"
            @keydown.escape.window="previewUrl = null"
        >
            <button
                type="button"
                class="absolute right-4 top-4 rounded-full bg-card p-2 text-white hover:bg-primary"
                @click="previewUrl = null"
                aria-label="Close preview"
            >
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>

            <img :src="previewUrl" alt="Full screen verification screenshot" class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain">
        </div>

        <script>
            function manualVerificationSearch(searchUrl, initialQuery = '') {
                return {
                    searchUrl,
                    query: initialQuery,
                    loading: false,
                    requestAbortController: null,
                    init() {
                        if (this.query.trim() !== '') {
                            this.search();
                        }
                    },
                    search() {
                        const normalizedQuery = this.query.trim();

                        if (this.requestAbortController !== null) {
                            this.requestAbortController.abort();
                        }

                        this.requestAbortController = new AbortController();
                        this.loading = true;

                        const url = `${this.searchUrl}?q=${encodeURIComponent(normalizedQuery)}`;

                        fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                signal: this.requestAbortController.signal,
                            })
                            .then((response) => {
                                if (!response.ok) {
                                    throw new Error('Unable to search managers.');
                                }

                                return response.text();
                            })
                            .then((html) => {
                                this.$refs.resultsContainer.innerHTML = html;

                                this.$nextTick(() => {
                                    if (window.Alpine) {
                                        window.Alpine.initTree(this.$refs.resultsContainer);
                                    }
                                });
                            })
                            .catch((error) => {
                                if (error.name !== 'AbortError') {
                                    this.$refs.resultsContainer.innerHTML =
                                        '<p class="mt-4 text-xs text-red-300">Search failed. Please try again.</p>';
                                }
                            })
                            .finally(() => {
                                this.loading = false;
                            });
                    },
                };
            }
        </script>
    </div>
</x-app-layout>
