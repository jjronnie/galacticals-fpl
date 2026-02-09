<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8"
        x-data="profileSearchClaim({
            endpoint: '{{ route('profile.search.results') }}',
            claimBase: '{{ url('/profile/claim') }}',
            csrf: '{{ csrf_token() }}',
            currentUserId: {{ (int) auth()->id() }}
        })"
    >
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

        <section class="rounded-2xl border border-gray-700 bg-card p-6">
            <h1 class="text-2xl font-bold text-white">Search and Claim FPL Team</h1>
            <p class="mt-2 text-sm text-gray-300">
                Open search and type at least 2 characters to find your FPL team instantly.
            </p>

            <button
                type="button"
                class="mt-5 inline-flex rounded-lg bg-accent px-5 py-2 text-sm font-semibold text-primary hover:bg-cyan-300"
                @click="open = true"
            >
                Search Teams
            </button>
        </section>

        <div
            x-show="open"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
            style="display: none;"
            @keydown.escape.window="closeModal()"
        >
            <div class="w-full max-w-5xl rounded-2xl border border-gray-700 bg-card p-5" @click.away="closeModal()">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-white">Find Your FPL Profile</h2>
                        <p class="text-xs text-gray-400">Search by manager name, team name, or entry ID.</p>
                    </div>
                    <button type="button" class="rounded-lg bg-primary px-3 py-1 text-xs text-gray-200 hover:bg-secondary" @click="closeModal()">
                        Close
                    </button>
                </div>

                <div class="space-y-4">
                    <input
                        type="text"
                        x-model="query"
                        @input="search()"
                        placeholder="Type at least 2 characters..."
                        class="w-full rounded-lg border border-gray-600 bg-primary px-4 py-3 text-sm text-white placeholder:text-gray-400 focus:border-accent focus:ring-accent"
                    >

                    <div x-show="query.length > 0 && query.length < 2" class="text-xs text-gray-400">
                        Keep typing. Search starts at 2 characters.
                    </div>

                    <div x-show="loading" class="text-xs text-accent">Searching...</div>
                    <div x-show="errorMessage" class="text-xs text-red-300" x-text="errorMessage"></div>

                    <div class="grid max-h-[60vh] gap-3 overflow-y-auto pr-1 sm:grid-cols-2 xl:grid-cols-3">
                        <template x-for="manager in results" :key="manager.entry_id">
                            <article class="rounded-2xl border border-gray-700 bg-primary p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-base font-bold text-white" x-text="manager.team_name"></p>
                                        <p class="text-sm text-gray-300">Manager: <span x-text="manager.player_name"></span></p>
                                        <p class="text-xs text-gray-400">Entry ID: <span x-text="manager.entry_id"></span></p>
                                    </div>
                                    <template x-if="manager.claimed_user_id">
                                        <span class="rounded-full bg-yellow-500/20 px-2 py-1 text-[10px] font-semibold text-yellow-200">Claimed</span>
                                    </template>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                    <div class="rounded-lg bg-card px-2 py-2">
                                        <p class="text-gray-400">Total Points</p>
                                        <p class="font-semibold text-white" x-text="new Intl.NumberFormat().format(manager.total_points)"></p>
                                    </div>
                                    <div class="rounded-lg bg-card px-2 py-2">
                                        <p class="text-gray-400">League Rank</p>
                                        <p class="font-semibold text-white" x-text="new Intl.NumberFormat().format(manager.rank)"></p>
                                    </div>
                                    <div class="col-span-2 rounded-lg bg-card px-2 py-2">
                                        <p class="text-gray-400">Overall Rank</p>
                                        <p class="font-semibold text-white" x-text="manager.overall_rank > 0 ? new Intl.NumberFormat().format(manager.overall_rank) : 'N/A'"></p>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center justify-between gap-2">
                                    <a :href="`{{ url('/managers') }}/${manager.entry_id}`" class="text-xs font-semibold text-accent hover:underline">
                                        View Public Profile
                                    </a>

                                    <template x-if="manager.claimed_user_id && Number(manager.claimed_user_id) !== currentUserId">
                                        <button type="button" class="rounded-lg bg-gray-600 px-3 py-1 text-xs font-semibold text-gray-200" disabled>
                                            Already Claimed
                                        </button>
                                    </template>

                                    <template x-if="!manager.claimed_user_id || Number(manager.claimed_user_id) === currentUserId">
                                        <form method="POST" :action="`${claimBase}/${manager.id}`">
                                            <input type="hidden" name="_token" :value="csrf">
                                            <button type="submit" class="rounded-lg bg-green-600 px-3 py-1 text-xs font-semibold text-white hover:bg-green-500">
                                                Claim Team
                                            </button>
                                        </form>
                                    </template>
                                </div>
                            </article>
                        </template>

                        <div x-show="!loading && query.length >= 2 && results.length === 0" class="col-span-full rounded-xl border border-gray-700 bg-card p-5 text-center text-sm text-gray-300">
                            No managers matched your search.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function profileSearchClaim(config) {
                return {
                    open: false,
                    query: '',
                    results: [],
                    loading: false,
                    errorMessage: '',
                    endpoint: config.endpoint,
                    claimBase: config.claimBase,
                    csrf: config.csrf,
                    currentUserId: Number(config.currentUserId),
                    abortController: null,
                    debounceTimer: null,

                    closeModal() {
                        this.open = false;
                        this.query = '';
                        this.results = [];
                        this.errorMessage = '';

                        if (this.abortController) {
                            this.abortController.abort();
                            this.abortController = null;
                        }
                    },

                    search() {
                        this.errorMessage = '';

                        if (this.debounceTimer) {
                            clearTimeout(this.debounceTimer);
                        }

                        if (this.query.length < 2) {
                            this.results = [];
                            this.loading = false;
                            return;
                        }

                        this.debounceTimer = setTimeout(() => {
                            this.fetchResults();
                        }, 350);
                    },

                    async fetchResults() {
                        if (this.abortController) {
                            this.abortController.abort();
                        }

                        this.abortController = new AbortController();
                        this.loading = true;

                        try {
                            const response = await fetch(`${this.endpoint}?q=${encodeURIComponent(this.query)}`, {
                                headers: {
                                    'Accept': 'application/json',
                                },
                                signal: this.abortController.signal,
                            });

                            if (!response.ok) {
                                const payload = await response.json().catch(() => ({}));
                                this.errorMessage = payload.message || 'Search failed. Please try again.';
                                this.results = [];
                                return;
                            }

                            const payload = await response.json();
                            this.results = Array.isArray(payload.data) ? payload.data : [];
                        } catch (error) {
                            if (error.name !== 'AbortError') {
                                this.errorMessage = 'Unable to complete search right now.';
                            }
                        } finally {
                            this.loading = false;
                        }
                    },
                };
            }
        </script>
    </div>
</x-app-layout>
