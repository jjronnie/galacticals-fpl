@php
    /** @var \App\Models\Manager $profileShareManager */
    $shortCode = strtoupper(base_convert((string) $profileShareManager->entry_id, 10, 36));
@endphp

<div class="w-full gap-4"
     x-data="{
        copied: false,
        shareUrl: '{{ route('managers.short', $shortCode) }}',
        appName: '{{ config('app.name') }}',
        managerName: '{{ addslashes($profileShareManager->player_name) }}',
        teamName: '{{ addslashes($profileShareManager->team_name) }}',

        get shareMessage() {
            return `Check out ${this.managerName} (${this.teamName}) profile on ${this.appName}: ${this.shareUrl}`;
        },

        copyLink() {
            navigator.clipboard.writeText(this.shareUrl).then(() => {
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

    <div class="rounded-xl border border-gray-700/30 bg-card p-5 shadow-md">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div class="w-full text-left sm:w-auto">
                <h3 class="mb-1 text-sm font-semibold uppercase tracking-wider text-gray-200">
                    Share Profile
                </h3>
                <p class="text-xs text-gray-500">Invite friends to view this profile</p>
            </div>

            <div class="flex w-full items-center justify-start gap-3 sm:w-auto sm:justify-end">
                <button @click="shareToWhatsapp()"
                    class="group flex h-10 w-10 items-center justify-center rounded-full bg-[#25D366] text-white transition-all duration-300"
                    title="Share on WhatsApp">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                </button>

                <button @click="shareToFacebook()"
                    class="group flex h-10 w-10 items-center justify-center rounded-full bg-[#1877F2] text-white transition-all duration-300"
                    title="Share on Facebook">
                    <i class="fa-brands fa-facebook-f text-lg"></i>
                </button>

                <button @click="shareToX()"
                    class="group flex h-10 w-10 items-center justify-center rounded-full border border-transparent bg-black text-white transition-all duration-300 hover:border-gray-600"
                    title="Share on X">
                    <i class="fa-brands fa-x-twitter text-lg"></i>
                </button>

                <div class="mx-1 h-8 w-px bg-gray-700"></div>

                <div class="relative">
                    <button @click="copyLink()"
                        class="flex items-center gap-2 rounded-full border border-gray-700 bg-gray-800 px-4 py-2 text-gray-300 transition-all hover:border-gray-500 hover:bg-gray-700">
                        <i class="fa-regular fa-copy"></i>
                        <span class="text-sm font-medium">Copy Link</span>
                    </button>

                    <div x-show="copied" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="translate-y-2 opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" style="display: none;"
                        class="absolute bottom-full left-1/2 mb-2 -translate-x-1/2 transform whitespace-nowrap rounded bg-green-500 px-3 py-1 text-xs text-white shadow-lg">
                        Link Copied!
                        <div class="absolute left-1/2 top-full -translate-x-1/2 transform border-4 border-transparent border-t-green-500"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
