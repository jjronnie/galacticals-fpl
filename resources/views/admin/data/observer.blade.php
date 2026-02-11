<x-app-layout>
    <div class="space-y-6">
        <x-page-title title="PL Teams and Players" />

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

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Teams in DB" :value="$teams->total()" icon="shield" />
            <x-stat-card title="Players in DB" :value="$players->total()" icon="users-round" />
            <x-stat-card title="Chip Records" :value="$chipRecordsCount" icon="cpu" />
            <x-stat-card title="Unique Chip Types" :value="$chipNames->count()" icon="binary" />
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-white">Sync Controls</h2>
                <form method="POST" action="{{ route('admin.data.fetchFpl') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                        Sync Teams/Players
                    </button>
                </form>
            </div>
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
                            <th class="px-3 py-2 text-right">Show</th>
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
                                <td class="px-3 py-2 text-right">
                                    <x-popup-modal title="Team Details">
                                        <x-slot:trigger>
                                            <button type="button" class="inline-flex rounded-lg bg-primary p-2 text-white hover:bg-secondary" title="Show team">
                                                <i data-lucide="eye" class="h-4 w-4"></i>
                                            </button>
                                        </x-slot:trigger>

                                        <dl class="grid gap-3 sm:grid-cols-2">
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Team ID</dt>
                                                <dd class="mt-1 font-semibold text-white">{{ $team->id }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Name</dt>
                                                <dd class="mt-1 text-gray-100">{{ $team->name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Short Name</dt>
                                                <dd class="mt-1 text-gray-100">{{ $team->short_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Code</dt>
                                                <dd class="mt-1 text-gray-100">{{ $team->code ?? '-' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Strength Overall</dt>
                                                <dd class="mt-1 text-gray-100">{{ $team->strength_overall ?? '-' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Players Count</dt>
                                                <dd class="mt-1 text-gray-100">{{ $team->players_count }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Created</dt>
                                                <dd class="mt-1 text-gray-100">{{ $team->created_at?->toDayDateTimeString() ?? '-' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Updated</dt>
                                                <dd class="mt-1 text-gray-100">{{ $team->updated_at?->toDayDateTimeString() ?? '-' }}</dd>
                                            </div>
                                        </dl>
                                    </x-popup-modal>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-400">No teams found.</td>
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
                            <th class="px-3 py-2 text-right">Show</th>
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
                                <td class="px-3 py-2 text-right">
                                    <x-popup-modal title="Player Details">
                                        <x-slot:trigger>
                                            <button type="button" class="inline-flex rounded-lg bg-primary p-2 text-white hover:bg-secondary" title="Show player">
                                                <i data-lucide="eye" class="h-4 w-4"></i>
                                            </button>
                                        </x-slot:trigger>

                                        <dl class="grid gap-3 sm:grid-cols-2">
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Player ID</dt>
                                                <dd class="mt-1 font-semibold text-white">{{ $player->id }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Web Name</dt>
                                                <dd class="mt-1 text-gray-100">{{ $player->web_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">First Name</dt>
                                                <dd class="mt-1 text-gray-100">{{ $player->first_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Second Name</dt>
                                                <dd class="mt-1 text-gray-100">{{ $player->second_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Team</dt>
                                                <dd class="mt-1 text-gray-100">{{ $player->team?->name ?? '-' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Element Type</dt>
                                                <dd class="mt-1 text-gray-100">{{ $player->element_type }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Current Value (x10)</dt>
                                                <dd class="mt-1 text-gray-100">{{ $player->now_cost }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Total Points</dt>
                                                <dd class="mt-1 text-gray-100">{{ $player->total_points }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Selected By %</dt>
                                                <dd class="mt-1 text-gray-100">{{ number_format((float) $player->selected_by_percent, 2) }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Form</dt>
                                                <dd class="mt-1 text-gray-100">{{ number_format((float) $player->form, 2) }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Region</dt>
                                                <dd class="mt-1 text-gray-100">{{ $player->region ?? '-' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs uppercase tracking-wide text-gray-400">Updated</dt>
                                                <dd class="mt-1 text-gray-100">{{ $player->updated_at?->toDayDateTimeString() ?? '-' }}</dd>
                                            </div>
                                        </dl>
                                    </x-popup-modal>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8 text-center text-gray-400">No players found.</td>
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
