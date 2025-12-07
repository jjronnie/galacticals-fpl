@php
$userLeague = auth()->user()->league;
@endphp

<!-- Dashboard Header & Share Section -->
<div class="w-full space-y-4" x-data="{ 
        updating: {{ $league->sync_status === 'processing' ? 'true' : 'false' }},
        copied: false,
        shareUrl: '{{ route('short.league', $league->shortcode) }}',
        appName: '{{ config('app.name') }}',
        leagueName: '{{ addslashes($league->name) }}',
        syncProgress: {{ $league->total_managers > 0 ? round(($league->synced_managers / $league->total_managers) * 100) : 0 }},
        syncedManagers: {{ $league->synced_managers }},
        totalManagers: {{ $league->total_managers }},
        syncMessage: '{{ addslashes($league->sync_message ?? '') }}',
        syncStatus: '{{ $league->sync_status }}',
        startTime: null,
        estimatedTime: 0,
        
        calculateEstimatedTime() {
            if (!this.startTime) {
                this.startTime = Date.now();
            }
            
            if (this.syncedManagers > 0 && this.syncProgress > 0) {
                const elapsed = (Date.now() - this.startTime) / 1000; // seconds
                const avgTimePerManager = elapsed / this.syncedManagers;
                const remainingManagers = this.totalManagers - this.syncedManagers;
                const estimatedSeconds = Math.ceil(avgTimePerManager * remainingManagers);
                
                // Convert to readable format
                if (estimatedSeconds < 60) {
                    this.estimatedTime = `${estimatedSeconds}s`;
                } else if (estimatedSeconds < 3600) {
                    const mins = Math.floor(estimatedSeconds / 60);
                    const secs = estimatedSeconds % 60;
                    this.estimatedTime = secs > 0 ? `${mins}m ${secs}s` : `${mins}m`;
                } else {
                    const hours = Math.floor(estimatedSeconds / 3600);
                    const mins = Math.floor((estimatedSeconds % 3600) / 60);
                    this.estimatedTime = `${hours}h ${mins}m`;
                }
            } else {
                this.estimatedTime = 'Calculating...';
            }
        },
        
        get shareMessage() {
            return `Hi, my league ${this.leagueName} has some great fun stats powered by ${this.appName}, please view it here: ${this.shareUrl}`;
        },

        get shareLink() {
            return `${this.shareUrl}`;
        },

        copyLink() {
            navigator.clipboard.writeText(this.shareLink).then(() => {
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
        },

        updateProgress() {
            if (this.syncStatus !== 'processing') return;
            
            fetch('/api/league/{{ $league->id }}/status')
                .then(response => response.json())
                .then(data => {
                    this.syncProgress = data.progress;
                    this.syncedManagers = data.synced_managers;
                    this.totalManagers = data.total_managers;
                    this.syncMessage = data.message;
                    this.syncStatus = data.status;
                    
                    // Calculate estimated time
                    this.calculateEstimatedTime();
                    
                    if (data.status === 'completed' || data.status === 'failed') {
                        this.updating = false;
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => console.error('Error fetching status:', error));
        }
     }" x-init="if (syncStatus === 'processing') { updating = true; setInterval(() => updateProgress(), 3000); }">

    <!-- Status Messages -->
    @if(session('status'))
    <div
        class="bg-gradient-to-r from-green-900/40 to-green-800/20 border border-green-700/50 rounded-xl shadow-lg p-4 backdrop-blur-sm">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-check text-green-400"></i>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-green-300 mb-1">Success</h3>
                <p class="text-sm text-green-200/90">{{ session('status') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div
        class="bg-gradient-to-r from-red-900/40 to-red-800/20 border border-red-700/50 rounded-xl shadow-lg p-4 backdrop-blur-sm">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-exclamation-triangle text-red-400"></i>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-red-300 mb-2">Error</h3>
                @foreach($errors->all() as $error)
                <p class="text-sm text-red-200/90">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Progress Bar (when updating) -->
    @if($league->sync_status === 'processing')
    <div
        class="bg-gradient-to-r from-blue-900/40 to-blue-800/20 border border-blue-700/50 rounded-xl shadow-lg p-4 backdrop-blur-sm">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-sync fa-spin text-blue-400"></i>
                </div>
            </div>
            <div class="flex-1 space-y-3">
                <div>
                    <h3 class="text-sm font-semibold text-blue-300 mb-1">Processing League Data</h3>
                    <p class="text-sm text-blue-200/90" x-text="syncMessage">{{ $league->sync_message }}</p>
                </div>

                <!-- Progress Bar -->
                <div class="space-y-2">
                    <div class="w-full bg-blue-950/50 rounded-full h-3 overflow-hidden border border-blue-800/30">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-400 h-full rounded-full transition-all duration-500 ease-out shadow-lg shadow-blue-500/30"
                            :style="`width: ${syncProgress}%`"
                            style="width: {{ $league->total_managers > 0 ? round(($league->synced_managers / $league->total_managers) * 100) : 0 }}%">
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <div class="flex items-center gap-3">
                            <span class="text-blue-400 font-medium">
                                <span x-text="syncedManagers">{{ $league->synced_managers }}</span> /
                                <span x-text="totalManagers">{{ $league->total_managers }}</span> managers
                            </span>
                            <span class="text-blue-300/70 flex items-center gap-1">
                                <i class="fa-regular fa-clock"></i>
                                <span x-text="estimatedTime">Calculating...</span> remaining
                            </span>
                        </div>
                        <span class="text-blue-500 font-bold" x-text="`${syncProgress}%`">
                            {{ $league->total_managers > 0 ? round(($league->synced_managers / $league->total_managers)
                            * 100) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

           @if(auth()->user()->isAdmin())

    @if($league->sync_status === 'failed')
    <div
        class="bg-gradient-to-r from-red-900/40 to-red-800/20 border border-red-700/50 rounded-xl shadow-lg p-4 backdrop-blur-sm">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-times-circle text-red-400"></i>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-red-300 mb-1">Sync Failed</h3>
                <p class="text-sm text-red-200/90 mb-3">{{ $league->sync_message }}</p>
                <form action="{{ route('league.update') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="text-sm font-medium text-red-400 hover:text-red-300 transition-colors inline-flex items-center gap-2">
                        <i class="fa-solid fa-rotate-right"></i>
                        Try Again
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- Main Header Card -->
    <div class="bg-card rounded-xl shadow-lg border border-gray-700/50 overflow-hidden relative">
        <!-- Background Decoration -->
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

                    <!-- Last Updated Info -->
                    @if($league && $league->sync_status === 'completed' && $league->last_synced_at)
                    <div class="flex items-center gap-2 text-sm text-gray-400">
                        <i class="fa-regular fa-clock"></i>
                        <span>Last updated: {{ $league->last_synced_at->diffForHumans() }}</span>
                    </div>
                    @endif

                    <!-- Total Managers -->
                    @if($league->total_managers > 0)
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <i class="fa-solid fa-users"></i>
                        <span>{{ $league->total_managers }} Managers</span>
                    </div>
                    @endif
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
                    @if($league->sync_status === 'processing')
                    <button type="button" disabled
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-gray-400 rounded-lg font-medium text-sm cursor-not-allowed opacity-60">
                        <i class="fa-solid fa-sync fa-spin"></i>
                        <span>Updating...</span>
                    </button>
                    @else

                    
                    @if(auth()->user()->isAdmin())

                    <form action="{{ route('league.update') }}" method="POST"
                        @submit.prevent="updating = true; $el.submit()">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition-all shadow-lg shadow-green-900/20 font-medium text-sm disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="updating">
                            <i class="fa-solid fa-sync" :class="{'fa-spin': updating}"></i>
                            <span x-text="updating ? 'Updating...' : 'Update Data'"></span>
                        </button>
                    </form>
                    @endif


                    @endif
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
                    class="group flex items-center justify-center w-10 h-10 rounded-full bg-[#25D366] text-white hover:opacity-90 transition-all duration-300"
                    title="Share on WhatsApp">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                </button>

                <!-- Facebook -->
                <button @click="shareToFacebook()"
                    class="group flex items-center justify-center w-10 h-10 rounded-full bg-[#1877F2] text-white hover:opacity-90 transition-all duration-300"
                    title="Share on Facebook">
                    <i class="fa-brands fa-facebook-f text-lg"></i>
                </button>

                <!-- X (Twitter) -->
                <button @click="shareToX()"
                    class="group flex items-center justify-center w-10 h-10 rounded-full bg-black text-white hover:opacity-90 transition-all duration-300 border border-transparent hover:border-gray-600"
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

    <!-- Loading Overlay (kept for manual update button clicks) -->
    <div x-cloak x-show="updating && syncStatus !== 'processing'"
        class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 backdrop-blur-sm">
        <div
            class="bg-gray-900 border border-gray-700 text-white p-6 rounded-xl shadow-2xl flex flex-col items-center gap-4 max-w-sm mx-4">
            <div class="w-16 h-16 rounded-full bg-green-500/20 flex items-center justify-center">
                <svg class="animate-spin h-8 w-8 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-1">Updating League</h3>
                <p class="text-sm text-gray-400">This will take a few minutes...</p>
            </div>
        </div>
    </div>
</div>