<x-app-layout>
    <div class="space-y-6">
        <x-page-title title="Claim Complaints" />

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
            <form method="GET" action="{{ route('admin.complaints.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label for="status" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-400">Status</label>
                    <select id="status" name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white">
                        <option value="">All</option>
                        <option value="open" @selected($status === 'open')>Open</option>
                        <option value="in_progress" @selected($status === 'in_progress')>In progress</option>
                        <option value="resolved" @selected($status === 'resolved')>Resolved</option>
                    </select>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">Reporter</th>
                            <th class="px-3 py-2 text-left">Manager</th>
                            <th class="px-3 py-2 text-left">Subject</th>
                            <th class="px-3 py-2 text-left">Message</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($complaints as $complaint)
                            <tr class="border-b border-gray-800/80 align-top">
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <p>{{ $complaint->reporter?->name }}</p>
                                    <p>{{ $complaint->reporter?->email }}</p>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <p>{{ $complaint->manager?->team_name }}</p>
                                    <p>Entry {{ $complaint->manager?->entry_id }}</p>
                                </td>
                                <td class="px-3 py-3 text-sm font-semibold text-white">{{ $complaint->subject }}</td>
                                <td class="px-3 py-3 text-xs text-gray-300 max-w-sm">{{ $complaint->message }}</td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold
                                        {{ $complaint->status === 'open' ? 'bg-red-900/40 text-red-300' : '' }}
                                        {{ $complaint->status === 'in_progress' ? 'bg-yellow-900/40 text-yellow-300' : '' }}
                                        {{ $complaint->status === 'resolved' ? 'bg-green-900/40 text-green-300' : '' }}">
                                        {{ str_replace('_', ' ', ucfirst($complaint->status)) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="space-y-2">
                                        <form method="POST" action="{{ route('admin.complaints.resolve', $complaint) }}" class="flex gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="rounded-lg border border-gray-600 bg-primary px-2 py-1 text-xs text-white">
                                                <option value="in_progress">In progress</option>
                                                <option value="resolved">Resolved</option>
                                            </select>
                                            <button type="submit" class="rounded-lg bg-green-700 px-3 py-1 text-xs font-semibold text-white hover:bg-green-600">
                                                Save
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.complaints.destroy', $complaint) }}" onsubmit="return confirm('Delete this complaint permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-red-700 px-3 py-1 text-xs font-semibold text-white hover:bg-red-600">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-400">No complaints found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $complaints->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
