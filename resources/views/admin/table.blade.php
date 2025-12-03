<x-app-layout>

    <x-adsense />



    <x-page-title title="Season Standings" />
    @if($standings->count() > 0)
    <x-table :headers="['#', 'Team', 'Total ']">
        @foreach($standings as $index => $standing)
        <x-table.row class="{{ $index === 0 ? 'bg-green-100 ' : '' }}">
            <x-table.cell class="font-bold">{{ $index + 1 }}</x-table.cell>



            <x-table.cell>
                <div class="flex items-center">
                    <div class="ml-4">
                        <div class="text-sm font-medium text-white">{{ $standing['team'] }}</div>
                        <div class="text-sm text-gray-500">{{ $standing['name'] }}</div>
                    </div>
                </div>
            </x-table.cell>


            <x-table.cell class="text-lg font-extrabold">{{ $standing['total_points'] }}</x-table.cell>
        </x-table.row>
        @endforeach
    </x-table>
    @else
    <x-empty-state message="No scores recorded yet. Add managers and their GW scores!" />
    @endif

    <x-adsense />
     <x-back-to-top/>

</x-app-layout>