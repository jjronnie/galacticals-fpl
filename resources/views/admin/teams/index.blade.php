<x-app-layout>
    <div class="space-y-6">
        <x-page-title title="FPL Teams" />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <x-stat-card title="Total Teams" :value="$teams->total()" icon="shield" />
            <x-stat-card title="Total Players" :value="$totalPlayers" icon="users-round" />
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">Teams</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">ID</th>
                            <th class="px-3 py-2 text-left">Badge</th>
                            <th class="px-3 py-2 text-left">Name</th>
                            <th class="px-3 py-2 text-left">Short</th>
                            <th class="px-3 py-2 text-right">Code</th>
                            <th class="px-3 py-2 text-right">Players</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teams as $team)
                            <tr class="border-b border-gray-800/80">
                                <td class="px-3 py-2">{{ $team->id }}</td>
                                <td class="px-3 py-2">
                                    <img src="{{ route('img.team', $team->id) }}" alt="{{ $team->short_name }}" class="h-7 w-7 rounded object-contain" loading="lazy" />
                                </td>
                                <td class="px-3 py-2 font-semibold text-white">{{ $team->name }}</td>
                                <td class="px-3 py-2">{{ $team->short_name }}</td>
                                <td class="px-3 py-2 text-right">{{ $team->code ?? '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ $team->players_count }}</td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('admin.teams.players', $team->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-600 px-3 py-1.5 text-xs font-semibold text-gray-300 transition hover:border-accent hover:text-accent">
                                        <i data-lucide="users" class="h-3.5 w-3.5"></i>
                                        View Players
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8 text-center text-gray-400">No teams found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $teams->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
