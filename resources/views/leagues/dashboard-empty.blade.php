<x-app-layout>
    <div class="mx-auto max-w-4xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
        <x-adsense />

        <section class="rounded-2xl border border-dashed border-gray-600 bg-card p-8 text-center">
            <h1 class="text-2xl font-bold text-white">Nothing Added Yet</h1>
            <p class="mt-3 text-sm text-gray-300">
                You do not have a league or a claimed profile yet. Add your league or claim your personal profile to unlock insights.
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('league.create') }}" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">
                    Add Your League
                </a>
                <a href="{{ route('profile.search') }}" class="rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                    Claim Profile
                </a>
            </div>
        </section>

        <x-adsense />
    </div>
</x-app-layout>
