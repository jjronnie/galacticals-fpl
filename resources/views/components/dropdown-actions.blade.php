<div x-data="{ open: false }" class="relative inline-block text-left">
    <button @click="open = !open" type="button"
        class="inline-flex justify-center w-full rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none">
        Actions
        <svg class="w-4 h-4 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 011.06.02L10 11.293l3.71-4.06a.75.75 0 011.14.976l-4.25 4.655a.75.75 0 01-1.14 0L5.21 8.27a.75.75 0 01.02-1.06z"
                clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open" @click.away="open = false" x-cloak
        class="origin-top-right absolute right-0 mt-2 w-44 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
        <div class="py-1 text-sm text-gray-700" role="menu" aria-orientation="vertical">
            {{ $slot }}
        </div>
    </div>
</div>





