<div class="p-6 rounded-2xl  border-2 border-gray-700 bg-card  ">

    <!-- GameWeek Title -->
    <h2 class="mb-4 text-center text-white text-xl font-bold uppercase">
        GameWeek {{ $gw['gameweek'] }}
    </h2>

    <div class="flex justify-between">

        <!-- Best Manager -->
        <div>
            <p class="text-gray-300  text-sm opacity-80">Best Manager(s)</p>
            <p class="text-green-400 font-bold text-lg">
                {{ implode(', ', $gw['best_managers']) }}
            </p>
            <p class="text-green-400 text-sm font-semibold">
                {{ $gw['best_points'] }}pts
            </p>
        </div>

        <!-- Worst Manager -->
        <div class="text-right">
            <p class="text-gray-300 text-sm opacity-80">Worst Manager(s)</p>
            <p class="text-red-400 font-bold text-lg">
                {{ implode(', ', $gw['worst_managers']) }}
            </p>
            <p class="text-red-400 text-sm font-semibold">
                {{ $gw['worst_points'] }}pts
            </p>
        </div>

    </div>

</div>
