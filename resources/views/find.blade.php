<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, follow" />
    <title>How to Find Your League ID - FPL Galaxy</title>
     <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet" />
    <style>
        body { font-family: "Nunito", sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.08); 
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.18);
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            cursor: default;
        }
        h2 { @apply text-2xl font-bold mt-6 mb-3 text-indigo-300; }
        h3 { @apply text-xl font-semibold mt-4 mb-2 text-white; }
        p { @apply text-gray-400 mb-4; }
        ul { @apply list-disc list-inside ml-4 mb-4; }
        li { @apply text-gray-400; }
        .step-card:hover { transform: translateY(-2px); transition: 0.3s ease; }
        .modal { 
            display: none; position: fixed; z-index: 50; padding-top: 5%; left: 0; top: 0; width: 100%; height: 100%;  background-color: rgba(0,0,0,0.9); 
        }
        .modal img { margin: auto; display: block; max-width: 90%; max-height: 80%; }
        .modal:target { display: block; }
        .close-btn { position: absolute; top: 20px; right: 35px; color: #fff; font-size: 40px; font-weight: bold; text-decoration: none; }
        .close-btn:hover { color: #bbb; }
    </style>

      <!--Start of Tawk.to Script-->
  <script type="text/javascript">
    var Tawk_API = Tawk_API || {},
      Tawk_LoadStart = new Date();
    (function () {
      var s1 = document.createElement("script"),
        s0 = document.getElementsByTagName("script")[0];
      s1.async = true;
      s1.src = "https://embed.tawk.to/67ada27b3a842732607e284f/1ijv45d63";
      s1.charset = "UTF-8";
      s1.setAttribute("crossorigin", "*");
      s0.parentNode.insertBefore(s1, s0);
    })();
  </script>
  <!--End of Tawk.to Script-->
</head>

<body class="min-h-screen text-gray-200 bg-black">

        <header id="top" class="py-4 text-white bg-[#5B0E9B] shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">

            <h1 class="flex items-center gap-2 text-xl font-extrabold">
                <a href="/" class="flex items-center gap-2 hover:text-indigo-300 transition">
                    <x-logo class="w-12 h-12" />
                    FPL Galaxy
                </a>
            </h1>

            {{-- Login/Register Buttons (Right) --}}
            <nav class="flex space-x-4">
                {{-- Login Button --}}
                <a href="{{ route('login') }}"
                    class="px-3 py-1.5 text-sm font-semibold text-white bg-indigo-600 rounded-md 
                      hover:bg-indigo-700 transition duration-150 ease-in-out 
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-[#5B0E9B]">
                    Login
                </a>

                {{-- Register Button (Hidden on small screens, shown on medium and up) --}}
                <a href="{{ route('register') }}" class=" md:inline-block px-3 py-1.5 text-sm font-semibold text-indigo-100 border border-indigo-100 rounded-md 
                      hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">
                    Get Started
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto p-4 space-y-6">
        <x-adsense/>

        <h1 class="text-4xl font-extrabold text-white text-center mb-6">How to Find Your League ID</h1>
        <p class="text-center text-gray-400 mb-12">Follow these steps to get your FPL League ID and add it to FPL Galaxy.</p>

        <!-- Step Cards -->
<div class="grid gap-4 grid-cols-1 lg:grid-cols-1">
    <!-- Step 1 -->
    <div class="glass step-card">
        <h3>Step 1: Log in to FPL</h3>
        <p>Visit <a href="https://fantasy.premierleague.com/leagues/" target="_blank" class="text-indigo-400 hover:text-indigo-300">FPL Leagues</a> and make sure you are logged in.</p>
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
        <button 
            @click="open = true" 
            class="text-indigo-400 hover:text-indigo-300 underline">
            View Example
        </button>
    </p>
</div>

<!-- Fullscreen Modal -->
<div x-data="{ open: false }">
    <div 
        x-show="open" 
        class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
        style="display: none;"
    >
        <button @click="open = false" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
        <img src="{{ asset('assets/img/leagueid.png') }}" alt="League ID Example" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-lg"/>
    </div>
</div>


    <!-- Step 4 -->
    <div class="glass step-card">
        <h3>Step 4: Enter the League ID in FPL Galaxy</h3>
        <p>Go back to FPL Galaxy, paste the league ID in the input box, and submit to track your league.</p>
    </div>

       <x-adsense/>

    <!-- Step 5 -->
    <div class="glass step-card">
        <h3>OR Watch This Video for a Visual Guide</h3>
        <div class="w-full" style="aspect-ratio:16/9;">
            <iframe class="w-full h-full rounded-lg shadow-lg" src="https://www.youtube.com/embed/your_video_id" title="FPL League ID Guide" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
</div>


        <!-- Modal Fullscreen Example -->
        <div id="example-modal" class="modal">
            <a href="#" class="close-btn">&times;</a>
            <img src="{{ asset('assets/img/leagueid.png') }}" alt="League ID Example" />
        </div>

           <x-adsense/>

    </main>

  <x-consent-banner />

    <footer class="py-6 mt-12 text-center text-gray-500 text-sm border-t border-gray-800">
        © <span id="year"></span>
        <a href="https://techtowerinc.com" class="text-gray-400 hover:text-white transition">TechTower Inc.</a>. All
        rights reserved.
    </footer>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
</body>

</html>
