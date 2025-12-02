<x-app-layout>
    <main class="max-w-5xl mx-auto p-4 space-y-6">
        <x-adsense />
        <section class="mt-8">
            <h2 class="mb-6 text-2xl font-bold text-center">Leagues Using {{ config('app.name') }}</h2>
            @guest

            <div class="flex my-6 justify-center">
                <a href="{{ route('register') }}" target="_blank" class="btn-success transition duration-200 blink">
                    Create account for your league
                </a>
            </div>
            @endguest


            <table class="min-w-full text-left text-sm font-light bg-card rounded-2xl">
                <thead class="font-medium  border-b border-b-secondary ">
                    <tr>
                        <th scope="col" class="px-6 py-4">#</th>
                        <th scope="col" class="px-6 py-4">League</th>
                        <th scope="col" class="px-6 py-4">Action</th>
                    </tr>
                </thead>

                <tbody class=" ">
                    @forelse($leagues as $index => $league)
                    <tr class="border-t border-t-secondary">

                        <td class="whitespace-nowrap  px-6 py-4 font-medium ">{{ $index + 1 }}</td>
                        <td class="whitespace-nowrap px-6 py-4"> {{ $league->name }}</td>
                        <td class="whitespace-nowrap px-6 py-4 font-bold"> <a
                                href="{{ route('public.leagues.show', ['slug' => $league->slug]) }}" class="btn-sm">
                                View League
                            </a></td>
                    </tr>
                    @empty
                    <tr class="border-b dark:border-neutral-500">
                        <td colspan="3" class="text-center py-8 text-gray-400">No leagues have been created yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>




        </section>



        <x-adsense />


    </main>
</x-app-layout>