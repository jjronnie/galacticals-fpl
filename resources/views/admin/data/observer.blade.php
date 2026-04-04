<x-app-layout>
    <div class="space-y-6">
        <x-page-title title="FPL DB Observer" />

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
            <h2 class="text-lg font-semibold text-white">Quick Links</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('admin.teams') }}" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-semibold text-gray-300 transition hover:border-accent hover:text-accent">
                    View All Teams & Players
                </a>
                <a href="{{ route('admin.data.fixtures') }}" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-semibold text-gray-300 transition hover:border-accent hover:text-accent">
                    View Fixtures
                </a>
            </div>
        </section>
    </div>
</x-app-layout>
