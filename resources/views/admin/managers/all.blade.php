<x-app-layout>
    <div
        class="space-y-6"
        x-data="adminManagerExplorer({
            initialPayload: @js($initialPayload),
            initialSearch: @js($initialSearch),
            resultsUrl: @js($resultsUrl),
        })"
        x-init="init()"
    >
        <x-page-title title="All Managers" />

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <label for="manager-search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-400">
                        Search
                    </label>
                    <input
                        id="manager-search"
                        type="text"
                        x-model="search"
                        @input="onSearchInput()"
                        placeholder="Search by manager, team, league or entry ID"
                        class="w-full rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white placeholder:text-gray-400"
                    >
                </div>

                <button
                    type="button"
                    @click="refresh()"
                    class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary"
                >
                    Refresh
                </button>
            </div>

            <p class="mt-3 text-xs text-gray-400" x-text="summaryText()"></p>
            <p x-show="errorMessage" class="mt-2 text-xs text-red-300" x-text="errorMessage"></p>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">Manager & Team</th>
                            <th class="px-3 py-2 text-left">Entry ID</th>
                            <th class="px-3 py-2 text-left">Leagues</th>
                            <th class="px-3 py-2 text-left">Claimed</th>
                            <th class="px-3 py-2 text-left">Claimed At</th>
                            <th class="px-3 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="loading">
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-400">Loading managers...</td>
                            </tr>
                        </template>

                        <template x-if="!loading && rows.length === 0">
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-400">No managers found for this search.</td>
                            </tr>
                        </template>

                        <template x-if="!loading && rows.length > 0">
                            <template x-for="row in rows" :key="row.entry_id">
                                <tr class="border-b border-gray-800/80 align-top">
                                    <td class="px-3 py-3">
                                        <p class="font-semibold text-white" x-text="row.player_name"></p>
                                        <p class="text-xs text-gray-400" x-text="row.team_name"></p>
                                    </td>
                                    <td class="px-3 py-3 font-semibold text-white" x-text="row.entry_id"></td>
                                    <td class="px-3 py-3 text-xs text-gray-300">
                                        <p>
                                            <span class="font-semibold text-white" x-text="row.leagues_count"></span>
                                            league(s)
                                        </p>
                                        <p class="mt-1 text-gray-400" x-text="leaguePreview(row.league_names)"></p>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span
                                            class="rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="row.is_claimed ? 'bg-green-900/40 text-green-300' : 'bg-gray-700 text-gray-200'"
                                            x-text="row.is_claimed ? 'Yes' : 'No'"
                                        ></span>
                                    </td>
                                    <td class="px-3 py-3 text-xs text-gray-300">
                                        <span x-text="row.claimed_at_human ?? '-'"></span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <a
                                            :href="managerUrl(row.entry_id)"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white hover:bg-secondary"
                                        >
                                            Show
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-gray-400" x-text="pageText()"></p>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="loading || pagination.current_page <= 1"
                        class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white hover:bg-secondary disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <button
                        type="button"
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="loading || pagination.current_page >= pagination.last_page"
                        class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white hover:bg-secondary disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
            </div>
        </section>
    </div>

    <script>
        function adminManagerExplorer(config) {
            return {
                search: config.initialSearch ?? '',
                rows: config.initialPayload?.rows ?? [],
                pagination: config.initialPayload?.pagination ?? {
                    current_page: 1,
                    last_page: 1,
                    per_page: 50,
                    total: 0,
                },
                loading: false,
                errorMessage: '',
                searchDebounceTimeout: null,
                resultsUrl: config.resultsUrl,
                managerUrl(entryId) {
                    return `/managers/${entryId}`;
                },
                init() {
                    this.rows = config.initialPayload?.rows ?? [];
                    this.pagination = config.initialPayload?.pagination ?? this.pagination;
                },
                onSearchInput() {
                    clearTimeout(this.searchDebounceTimeout);
                    this.searchDebounceTimeout = setTimeout(() => {
                        this.fetchRows(1);
                    }, 350);
                },
                changePage(page) {
                    if (page < 1 || page > this.pagination.last_page || this.loading) {
                        return;
                    }

                    this.fetchRows(page);
                },
                refresh() {
                    this.fetchRows(this.pagination.current_page);
                },
                fetchRows(page) {
                    this.loading = true;
                    this.errorMessage = '';

                    const params = new URLSearchParams({
                        q: this.search.trim(),
                        page: String(page),
                    });

                    fetch(`${this.resultsUrl}?${params.toString()}`, {
                        headers: {
                            Accept: 'application/json',
                        },
                    })
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error('Failed to load managers.');
                            }

                            return response.json();
                        })
                        .then((payload) => {
                            this.rows = payload.rows ?? [];
                            this.pagination = payload.pagination ?? this.pagination;
                        })
                        .catch(() => {
                            this.errorMessage = 'Could not load managers right now. Please retry.';
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },
                leaguePreview(leagueNames) {
                    if (!Array.isArray(leagueNames) || leagueNames.length === 0) {
                        return '-';
                    }

                    const preview = leagueNames.slice(0, 3).join(', ');

                    if (leagueNames.length <= 3) {
                        return preview;
                    }

                    return `${preview} +${leagueNames.length - 3} more`;
                },
                pageText() {
                    if ((this.pagination?.total ?? 0) === 0) {
                        return 'No managers available';
                    }

                    const current = this.pagination.current_page ?? 1;
                    const last = this.pagination.last_page ?? 1;
                    const total = this.pagination.total ?? 0;

                    return `Page ${current} of ${last} (${total} managers)`;
                },
                summaryText() {
                    if ((this.pagination?.total ?? 0) === 0) {
                        if (this.search.trim() === '') {
                            return 'Type in the search box to find managers.';
                        }

                        return `No results for "${this.search.trim()}".`;
                    }

                    const total = this.pagination.total ?? 0;

                    if (this.search.trim() === '') {
                        return `${total} manager profiles indexed (cached for 15 minutes).`;
                    }

                    return `${total} result(s) for "${this.search.trim()}".`;
                },
            };
        }
    </script>
</x-app-layout>
