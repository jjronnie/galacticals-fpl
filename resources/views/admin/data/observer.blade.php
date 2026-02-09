<x-app-layout>
    <div class="space-y-6">
        <x-page-title title="FPL DB Observer" />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Teams in DB" :value="$teams->total()" icon="shield" />
            <x-stat-card title="Players in DB" :value="$players->total()" icon="users-round" />
            <x-stat-card title="Chip Records" :value="$chipRecordsCount" icon="cpu" />
            <x-stat-card title="Unique Chip Types" :value="$chipNames->count()" icon="binary" />
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">Chip Names in Database</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @forelse ($chipNames as $chipName)
                    <span class="rounded-full bg-primary px-3 py-1 text-xs font-semibold text-gray-200">{{ $chipName }}</span>
                @empty
                    <p class="text-sm text-gray-400">No chips have been stored yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">FPL Teams</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">ID</th>
                            <th class="px-3 py-2 text-left">Name</th>
                            <th class="px-3 py-2 text-left">Short</th>
                            <th class="px-3 py-2 text-right">Code</th>
                            <th class="px-3 py-2 text-right">Players</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teams as $team)
                            <tr class="border-b border-gray-800/80">
                                <td class="px-3 py-2">{{ $team->id }}</td>
                                <td class="px-3 py-2 font-semibold text-white">{{ $team->name }}</td>
                                <td class="px-3 py-2">{{ $team->short_name }}</td>
                                <td class="px-3 py-2 text-right">{{ $team->code ?? '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ $team->players_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-gray-400">No teams found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $teams->links() }}
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">FPL Players</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">ID</th>
                            <th class="px-3 py-2 text-left">Player</th>
                            <th class="px-3 py-2 text-left">Team</th>
                            <th class="px-3 py-2 text-right">Points</th>
                            <th class="px-3 py-2 text-right">Selected %</th>
                            <th class="px-3 py-2 text-right">Form</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($players as $player)
                            <tr class="border-b border-gray-800/80">
                                <td class="px-3 py-2">{{ $player->id }}</td>
                                <td class="px-3 py-2">
                                    <p class="font-semibold text-white">{{ $player->web_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $player->first_name }} {{ $player->second_name }}</p>
                                </td>
                                <td class="px-3 py-2">{{ $player->team?->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ $player->total_points }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) $player->selected_by_percent, 2) }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) $player->form, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-400">No players found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $players->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
