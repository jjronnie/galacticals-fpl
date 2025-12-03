@php
$userLeague = auth()->user()->league;
@endphp



<!-- Dashboard Header & Share Section -->
<div class="w-full space-y-4" x-data="{ 
        updating: false,
        copied: false,
        shareUrl: '{{ route('public.leagues.show', ['slug' => $league->slug]) }}',
        appName: '{{ config('app.name') }}',
        leagueName: '{{ addslashes($league->name) }}',
        
        get shareMessage() {
            return `Hi, my league ${this.leagueName} has some great fun stats powered by ${this.appName}, please view it here: ${this.shareUrl}`;
        },

        copyLink() {
            navigator.clipboard.writeText(this.shareMessage).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        },

        shareToWhatsapp() {
            window.open(`https://wa.me/?text=${encodeURIComponent(this.shareMessage)}`, '_blank');
        },

        shareToFacebook() {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(this.shareUrl)}&quote=${encodeURIComponent(this.shareMessage)}`, '_blank');
        },

        shareToX() {
            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(this.shareMessage)}`, '_blank');
        }
     }">

    <!-- Main Header Card -->
    <div class="bg-card rounded-xl shadow-lg border border-gray-700/50 overflow-hidden relative">
        <!-- Background Decoration (Optional subtle gradient) -->
        <div
            class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-primary/10 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="p-6 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">

                <!-- Left Side: Title & Info -->
                <div class="space-y-2">
                    <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                        {{ $league->name }}
                    </h1>
                    <div class="flex items-center gap-2 text-sm text-gray-400">
                        <i class="fa-regular fa-clock"></i>
                        <span>Last Updated: {{ $lastUpdated->diffForHumans() ?? 'No data yet' }}</span>
                    </div>
                </div>

                <!-- Right Side: Actions -->
                <div class="flex flex-wrap items-center gap-3">

                    <!-- View Button -->
                    @if($userLeague)
                    <a href="{{ route('public.leagues.show', ['slug' => $userLeague->slug]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors font-medium text-sm">
                        <i class="fa-solid fa-eye"></i>
                        View League
                    </a>
                    @endif

                    <!-- Update Button -->
                    <form action="{{ route('admin.league.update') }}" method="POST"
                        @submit.prevent="updating = true; $el.submit()">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition-all shadow-lg shadow-green-900/20 font-medium text-sm">
                            <i class="fa-solid fa-sync" :class="{'fa-spin': updating}"></i>
                            <span x-text="updating ? 'Updating...' : 'Update Data'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Options Card -->
    <div class="bg-card rounded-xl shadow-md border border-gray-700/30 p-5">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

            <div class="text-left w-full sm:w-auto">
                <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-1">
                    Share League
                </h3>
                <p class="text-xs text-gray-500">Invite friends to view your stats</p>
            </div>

            <!-- Social Icons Container -->
            <div class="flex items-center gap-3 w-full sm:w-auto justify-start sm:justify-end">

                <!-- WhatsApp -->
                <button @click="shareToWhatsapp()"
                    class="group flex items-center justify-center w-10 h-10 rounded-full bg-[#25D366] text-white transition-all duration-300"
                    title="Share on WhatsApp">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                </button>

                <!-- Facebook -->
                <button @click="shareToFacebook()"
                    class="group flex items-center justify-center w-10 h-10 rounded-full bg-[#1877F2] text-white transition-all duration-300"
                    title="Share on Facebook">
                    <i class="fa-brands fa-facebook-f text-lg"></i>
                </button>

                <!-- X (Twitter) -->
                <button @click="shareToX()"
                    class="group flex items-center justify-center w-10 h-10 rounded-full bg-black text-white transition-all duration-300 border border-transparent hover:border-gray-600"
                    title="Share on X">
                    <i class="fa-brands fa-x-twitter text-lg"></i>
                    
                </button>

                <div class="w-px h-8 bg-gray-700 mx-1"></div>

                <!-- Copy Link -->
                <div class="relative">
                    <button @click="copyLink()"
                        class="flex items-center gap-2 px-4 py-2 rounded-full bg-gray-800 hover:bg-gray-700 text-gray-300 transition-all border border-gray-700 hover:border-gray-500">
                        <i class="fa-regular fa-copy"></i>
                        <span class="text-sm font-medium">Copy Link</span>
                    </button>

                    <!-- Success Message Tooltip -->
                    <div x-show="copied" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" style="display: none;"
                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-green-500 text-white text-xs rounded shadow-lg whitespace-nowrap">
                        Link Copied!
                        <div
                            class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-green-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <div x-cloak x-show="updating" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white text-black p-6 rounded-lg flex items-center gap-4">
            <svg class="animate-spin h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Updating your league...</span>
        </div>
    </div>

</div>

<!-- Dependencies Check (Ensure these are in your layout) -->