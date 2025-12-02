<x-app-layout>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto p-4 space-y-6">
        <x-adsense />

        <h1 class="text-4xl font-extrabold text-white text-center mb-6">How to Find Your League ID</h1>
        <p class="text-center text-gray-400 mb-12">Follow these steps to get your FPL League ID and add it to FPL
            Galaxy.</p>

        <!-- Step Cards -->
        <div class="grid gap-4 grid-cols-1 lg:grid-cols-1">
            <!-- Step 1 -->
            <div class="glass step-card">
                <h3>Step 1: Log in to FPL</h3>
                <p>Visit <a href="https://fantasy.premierleague.com/leagues/" target="_blank"
                        class="text-indigo-400 hover:text-indigo-300">FPL Leagues</a> and make sure you are logged in.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="glass step-card">
                <h3>Step 2: Open Your League</h3>
                <p>Click on the league you want to add. This will open the league standings page.</p>
            </div>

            <!-- Step 3 -->
            <div class="glass step-card">
                <h3>Step 3: Copy the League ID from URL</h3>
                <p>
                    Look at your browser's URL. The league ID is the number in the link, e.g.,
                    <button @click="open = true" class="text-indigo-400 hover:text-indigo-300 underline">
                        View Example
                    </button>
                </p>
            </div>

            <!-- Fullscreen Modal -->
            <div x-data="{ open: false }">
                <div x-show="open" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
                    style="display: none;">
                    <button @click="open = false" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
                    <img src="{{ asset('assets/img/leagueid.png') }}" alt="League ID Example"
                        class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-lg" />
                </div>
            </div>


            <!-- Step 4 -->
            <div class="glass step-card">
                <h3>Step 4: Enter the League ID in FPL Galaxy</h3>
                <p>Go back to FPL Galaxy, paste the league ID in the input box, and submit to track your league.</p>
            </div>

            <x-adsense />

            <!-- Step 5 -->
            <div class="glass step-card">
                <h3>OR Watch This Video for a Visual Guide</h3>
                <div class="w-full" style="aspect-ratio:16/9;">
                    <iframe class="w-full h-full rounded-lg shadow-lg" src="https://www.youtube.com/embed/your_video_id"
                        title="FPL League ID Guide" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>


        <!-- Modal Fullscreen Example -->
        <div id="example-modal" class="modal">
            <a href="#" class="close-btn">&times;</a>
            <img src="{{ asset('assets/img/leagueid.png') }}" alt="League ID Example" />
        </div>

        <x-adsense />

    </main>

</x-app-layout>