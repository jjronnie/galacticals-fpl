<x-app-layout>


    @if($managers->isEmpty())
    <p class="text-center text-gray-500">No managers found in your league.</p>
    @else

    <x-table :headers="array_merge(['#','Manager'], array_map(fn($gw) => 'GW '.$gw, $gameweeks))">
        @foreach ($managers as $index => $manager)
        @php
        // Calculate the actual row number across pages
        $rowNumber = ($managers->currentPage() - 1) * $managers->perPage() + $index + 1;
        @endphp
        <x-table.row>

            <x-table.cell>
                {{ $rowNumber }}
            </x-table.cell>

            <x-table.cell>
                <div class="flex items-center">
                    <div class="ml-4">
                        <div class="text-sm font-medium text-white">{{ $manager->player_name }}</div>
                        <div class="text-sm text-gray-500">{{ $manager->team_name }}</div>
                    </div>
                </div>
            </x-table.cell>

            @foreach ($gameweeks as $gw)
            @php
            $score = $manager->gameweekScores->firstWhere('gameweek', $gw);
            @endphp
            <x-table.cell>{{ $score->points ?? 0 }}</x-table.cell>
            @endforeach
        </x-table.row>
        </span>
        @endforeach
    </x-table>

    @endif

    <div class="mt-4">
        {{ $managers->links() }}
    </div>
</x-app-layout>