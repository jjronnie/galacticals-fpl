<x-app-layout>

    <main class="max-w-5xl mx-auto p-4 space-y-10">
        <x-adsense />

        <h2 class="text-4xl font-extrabold text-white text-center">How to Find Your Fantasy Premier League, League ID
            Quickly & Easily</h2>


        <p class="text-center text-gray-400 mb-4">
            Watch the step by step video below to get your FPL League ID and add it to FPL Galaxy:
        </p>
        <br>
        
            <div class="text-center mt-4">
                <center>
                <iframe width="560" height="400" src="https://www.youtube.com/embed/Rlp772BoxIw?si=thEhSN20xqdiiGfa"
                    title="YouTube video player" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                     </center>
            </div>
       

        <hr class="border-t border-gray-200 my-4">

        <p class="text-center mt-2">OR</p>

        <p class="text-center text-gray-400">
            Follow the steps below:

        <div class="space-y-6">

            {{-- Step 1 --}}
            <div class="p-4 rounded-lg border-2 border-gray-700 bg-card">
                <h3 class="text-xl font-bold mb-2">Step 1. Log in to FPL</h3>
                <p>
                    Visit
                    <a href="https://fantasy.premierleague.com/leagues/" target="_blank"
                        class="text-indigo-400 hover:text-indigo-300">
                        https://fantasy.premierleague.com/leagues/
                    </a>
                    and make sure you are logged in to your league manager account.
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="p-4 rounded-lg border-2 border-gray-700 bg-card">
                <h3 class="text-xl font-bold mb-2">Step 2. Open Your League</h3>
                <p>Click the league you want to add. This opens the standings page.</p>
            </div>

            {{-- Step 3 --}}
            <div class="p-4 rounded-lg border-2 border-gray-700 bg-card">
                <h3 class="text-xl font-bold mb-2">Step 3. Copy the League ID</h3>
                <p>
                    The league ID is inside your browser URL. See example image below (the digits that are in the box).
                </p>

                <div class="mt-4 cursor-pointer">
                    <img src="{{ asset('assets/img/leagueid.png') }}" alt="League ID Example" loading="lazy"
                        class="rounded-lg shadow-md hover:opacity-90 transition"
                        onclick="openImageModal('{{ asset('assets/img/leagueid.png') }}')">
                </div>
            </div>

            {{-- Step 4 --}}
            <div class="p-4 rounded-lg border-2 border-gray-700 bg-card">
                <h3 class="text-xl font-bold mb-2">Step 4. Enter it in FPL Galaxy</h3>
                <p>Return to FPL Galaxy, paste your league ID, and submit and you are good to go</p>
            </div>

            <x-adsense />
        </div>

        {{-- Image Zoom Modal --}}
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-80 hidden items-center justify-center p-4 z-50">
            <img id="modalImage" class="max-w-full max-h-full rounded-lg shadow-lg" alt="Zoom Image">
        </div>

        <script>
            function openImageModal(src) {
                const modal = document.getElementById('imageModal')
                const modalImg = document.getElementById('modalImage')

                modalImg.src = src
                modal.classList.remove('hidden')
                modal.classList.add('flex')
            }

            document.getElementById('imageModal').addEventListener('click', () => {
                const modal = document.getElementById('imageModal')
                modal.classList.add('hidden')
                modal.classList.remove('flex')
            })
        </script>

        <x-adsense />
    </main>

</x-app-layout>