<div x-data="{ openForm: false }" class="relative">
    <!-- Trigger Button -->
    <button @click="openForm = true" class="btn">
        {{ $buttonText }} 
        @if ($buttonIcon )
            <i data-lucide="{{ $buttonIcon }}" class="w-4 h-4"></i>
        @endif 
           
        
      
    </button>

    <!-- Slide-in Form -->
    <div x-show="openForm" x-cloak 
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full" 
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300" 
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed top-0 right-0 w-full sm:max-w-[70%] h-full bg-white shadow-xl z-[9998] flex flex-col">

        <!-- Header Row (fixed at top of drawer) -->
        <div class="flex items-center space-x-3 p-6 border-b">
            <button title="Close Form" @click="openForm = false" class="btn-danger-sm">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
            <h2 class="text-xl font-semibold">{{ $title }}</h2>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </div>
    </div>

    <!-- Overlay -->
    <div x-show="openForm" x-cloak 
        @click="openForm = false" 
        class="fixed inset-0 bg-black bg-opacity-50 z-[9997]"
        x-transition.opacity>
    </div>
</div>
