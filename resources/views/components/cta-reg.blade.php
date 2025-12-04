<div x-data="{
        showModal: (() => {
            const modalData = localStorage.getItem('fpl_modal');
            if (!modalData) return true;

            const expire = JSON.parse(modalData).expire;
            const now = new Date().getTime();
            return now > expire;
        })(),
        dismiss(days) {
            const expire = new Date().getTime() + days * 24*60*60*1000;
            localStorage.setItem('fpl_modal', JSON.stringify({ expire }));
            this.showModal = false;
        }
    }" x-show="showModal" x-cloak class="fixed inset-0 flex items-center justify-center z-50 bg-black/50 p-4"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="bg-card border border-gray-500 rounded-xl shadow-lg p-6 max-w-md w-full transform"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
        <div class="text-gray-300 text-sm leading-relaxed flex flex-col gap-4">
            <p class="text-white font-semibold text-lg">Did you know?</p>
            <p>FPL Galaxy is completely free! Create an account for your league and enjoy the fun.</p>
        </div>

        <div class="flex gap-3 mt-4 flex-wrap">
            <a href="{{ url('/register') }}" @click.prevent="dismiss(30); window.location.href='{{ url('/register') }}'"
                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Get Started
            </a>
            <button @click="dismiss(7)"
                class="px-4 py-2 border border-gray-300 text-white text-sm font-medium rounded-lg hover:bg-gray-100 hover:text-gray-800 transition-colors">
                Maybe Later
            </button>
        </div>
    </div>
</div>