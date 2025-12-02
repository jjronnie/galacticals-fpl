<x-app-layout>
    <main class="max-w-5xl mx-auto p-4 space-y-6">
        <x-adsense />
        <section class="mt-8">
            <h2 class="mb-6 text-2xl font-bold text-center">Leagues Using {{ config('app.name') }} ({{ $total ?? 'O' }})</h2>
            @guest

            <div class="flex my-6 justify-center">
                <a href="{{ route('register') }}" target="_blank" class="btn-success transition duration-200 blink">
                    Create account for your league
                </a>
            </div>
            @endguest


            <x-table :headers="['League', 'Action']">

                @forelse($leagues as  $league)
                <x-table.row>

                    <x-table.cell>
                        {{ $league->name }}
                    </x-table.cell>

                    <x-table.cell>
                        <a href="{{ route('public.leagues.show', ['slug' => $league->slug]) }}" class="btn-sm">
                            View League
                        </a>
                    </x-table.cell>
                </x-table.row>

                @empty
                <x-table.row>
                    <x-table.cell colspan="3" class="text-center py-8 text-gray-400">
                        No leagues have been created yet
                    </x-table.cell>
                </x-table.row>
                @endforelse

            </x-table>

            





        </section>



        <x-adsense />


    </main>
</x-app-layout>