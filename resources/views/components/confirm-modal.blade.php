@props([
'action',
'buttonText' => 'Proceed',
'warning' => 'Are you sure you want to proceed?',
'triggerText' => '',
'triggerIcon' => '',
'triggerClass' => 'btn-danger',
'method' => 'DELETE',
'title' => '',
])

<div x-data="{ showModal: false, acknowledged: false }" class="inline-block">
    <!-- Trigger Button -->
    <button @click="showModal = true" type="button" title="{{ $title }}" class="{{ $triggerClass }}">
        {{ $triggerText }}
        @if ($triggerIcon)
        <i data-lucide="{{ $triggerIcon }}" class="h-4 w-4"></i>
        @endif
    </button>

    <!-- Modal -->
    <div x-show="showModal" x-transition
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" style="display: none;"
        @keydown.escape.window="showModal = false">

        <div class="bg-white rounded-lg shadow-lg max-w-sm w-full p-6" @click.away="showModal = false">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center space-x-2">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-500"></i>
                <span>Warning</span>
            </h2>

            <p class="mt-2 text-sm text-gray-600">{{ $warning }}</p>

            <!-- Toggle Acknowledgment -->
            <div class="mt-4 flex items-center space-x-2">
                <button type="button" @click="acknowledged = !acknowledged"
                    :class="acknowledged ? 'bg-green-500' : 'bg-gray-300'"
                    class="relative inline-flex h-6 w-12 items-center rounded-full transition-colors duration-300 focus:outline-none">
                    <span :class="acknowledged ? 'translate-x-6' : 'translate-x-1'"
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-300">
                    </span>
                </button>
                <span class="text-sm text-gray-700">I am aware</span>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <!-- Cancel Button -->
                <button @click="showModal = false"
                    class="px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                    Cancel
                </button>

                <!-- Action Form -->
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method($method)
                    <button type="submit" :disabled="!acknowledged"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ $buttonText }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>