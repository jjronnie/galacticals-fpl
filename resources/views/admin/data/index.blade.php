<x-app-layout>
    <div class="space-y-6" x-data="{ autoRefresh: true }" x-init="setInterval(() => { if (autoRefresh) { window.location.reload(); } }, 10000)">
        <x-page-title title="Admin Data Sync" />

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
            <x-stat-card title="Total Leagues" :value="$leagues->count()" icon="trophy" />
            <x-stat-card title="Claimed Managers" :value="$claimedManagers" icon="users" />
            <x-stat-card title="Processing Jobs" :value="$leagues->where('sync_status', 'processing')->count()" icon="loader-circle" />
            <x-stat-card title="Failed Jobs" :value="$leagues->where('sync_status', 'failed')->count()" icon="triangle-alert" />
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">Queue Actions</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <form method="POST" action="{{ route('admin.data.fetchFpl') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-accent px-4 py-3 text-sm font-semibold text-primary hover:bg-cyan-300">
                        Sync FPL Teams/Players
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.data.fetchManagers') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-500">
                        Sync Claimed Profiles
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.data.computeGameweeks') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-secondary px-4 py-3 text-sm font-semibold text-white hover:opacity-90">
                        Compute All GW Tables
                    </button>
                </form>

                <label class="flex items-center justify-center gap-2 rounded-lg border border-gray-600 px-4 py-3 text-sm text-gray-300">
                    <input type="checkbox" x-model="autoRefresh" class="rounded border-gray-600 bg-primary text-accent focus:ring-accent">
                    Auto-refresh
                </label>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">League Sync Progress</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">League</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Progress</th>
                            <th class="px-3 py-2 text-left">Message</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leagues as $league)
                            @php
                                $progress = $league->total_managers > 0
                                    ? (int) round(($league->synced_managers / $league->total_managers) * 100)
                                    : 0;
                            @endphp
                            <tr class="border-b border-gray-800/80">
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-white">{{ $league->name }}</p>
                                    <p class="text-xs text-gray-400">ID {{ $league->league_id }} / {{ $league->managers_count }} managers</p>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold
                                        {{ $league->sync_status === 'completed' ? 'bg-green-900/40 text-green-300' : '' }}
                                        {{ $league->sync_status === 'processing' ? 'bg-blue-900/40 text-blue-300' : '' }}
                                        {{ $league->sync_status === 'failed' ? 'bg-red-900/40 text-red-300' : '' }}">
                                        {{ ucfirst($league->sync_status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="w-44">
                                        <div class="h-2 w-full rounded bg-primary">
                                            <div class="h-2 rounded bg-accent" style="width: {{ $progress }}%;"></div>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-400">{{ $league->synced_managers }}/{{ $league->total_managers }} ({{ $progress }}%)</p>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">{{ $league->sync_message ?: '-' }}</td>
                                <td class="px-3 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.data.refreshLeague', $league) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white hover:bg-secondary">
                                                Refresh League
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.data.computeGameweeks') }}">
                                            @csrf
                                            <input type="hidden" name="league_id" value="{{ $league->id }}">
                                            <button type="submit" class="rounded-lg bg-secondary px-3 py-2 text-xs font-semibold text-white hover:opacity-90">
                                                Compute GW
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
