<x-app-layout>
    <div class="space-y-6">
        <x-page-title title="Claimed Manager Profiles" />

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

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <form method="GET" action="{{ route('admin.managers.index') }}" class="grid gap-3 md:grid-cols-4">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Search by username, team, manager, entry ID"
                    class="rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white placeholder:text-gray-400 md:col-span-2"
                >

                <select name="status" class="rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white">
                    <option value="">All statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="suspended" @selected($status === 'suspended')>Suspended</option>
                </select>

                <button type="submit" class="rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                    Filter
                </button>
            </form>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">Manager</th>
                            <th class="px-3 py-2 text-left">Claimed By</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Notes</th>
                            <th class="px-3 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($managers as $manager)
                            <tr class="border-b border-gray-800/80 align-top">
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-white">{{ $manager->team_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $manager->player_name }} / Entry {{ $manager->entry_id }}</p>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <p>{{ $manager->user?->name }}</p>
                                    <p>{{ $manager->user?->email }}</p>
                                    <p class="text-gray-500">Claimed {{ optional($manager->claimed_at)->diffForHumans() }}</p>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($manager->suspended_at)
                                        <span class="rounded-full bg-red-900/40 px-2 py-1 text-xs font-semibold text-red-300">Suspended</span>
                                    @else
                                        <span class="rounded-full bg-green-900/40 px-2 py-1 text-xs font-semibold text-green-300">Active</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">{{ $manager->notes ?: '-' }}</td>
                                <td class="px-3 py-3">
                                    <div class="space-y-2">
                                        @if ($manager->suspended_at)
                                            <form method="POST" action="{{ route('admin.managers.unsuspend', $manager) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <textarea
                                                    name="reason"
                                                    rows="2"
                                                    placeholder="Unsuspend reason"
                                                    class="w-full rounded-lg border border-gray-600 bg-primary px-3 py-2 text-xs text-white placeholder:text-gray-400"
                                                    required
                                                ></textarea>
                                                <button type="submit" class="w-full rounded-lg bg-green-700 px-3 py-2 text-xs font-semibold text-white hover:bg-green-600">
                                                    Unsuspend
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.managers.suspend', $manager) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <textarea
                                                    name="reason"
                                                    rows="2"
                                                    placeholder="Suspension reason"
                                                    class="w-full rounded-lg border border-gray-600 bg-primary px-3 py-2 text-xs text-white placeholder:text-gray-400"
                                                    required
                                                ></textarea>
                                                <button type="submit" class="w-full rounded-lg bg-red-700 px-3 py-2 text-xs font-semibold text-white hover:bg-red-600">
                                                    Suspend
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.managers.disband', $manager) }}" class="space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <textarea
                                                name="reason"
                                                rows="2"
                                                placeholder="Disband reason"
                                                class="w-full rounded-lg border border-gray-600 bg-primary px-3 py-2 text-xs text-white placeholder:text-gray-400"
                                                required
                                            ></textarea>
                                            <button type="submit" class="w-full rounded-lg bg-yellow-700 px-3 py-2 text-xs font-semibold text-white hover:bg-yellow-600">
                                                Disband Claim
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-gray-400">No claimed managers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $managers->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
