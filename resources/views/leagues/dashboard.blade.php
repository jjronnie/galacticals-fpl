<x-app-layout>

    <x-adsense />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

      

            {{-- @include('leagues.partials.progress') --}}


            @include('leagues.partials.dash-header') 

            <!-- Season Highlights -->
            <div>
                <x-page-title title="League Info" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    <x-stat-card title="League ID" value="{{ $league->league_id ?? '-' }}" icon="hash" />
                    <x-stat-card title="Joined {{ config('app.name') }}"
                        value="{{ $league->created_at->diffForHumans() ?? '-' }}" icon="calendar-clock" />
                    <x-stat-card title="Season" value="{{ $league->season ?? '-' }}/{{ $league->season +1 }} " icon="calendar-range" />
                    <x-stat-card title="Total Managers" value="{{ $league->total_managers ?? '-' }}" icon="users" />


                </div>
            </div>



        </div>
    </div>
    <x-adsense />
</x-app-layout>