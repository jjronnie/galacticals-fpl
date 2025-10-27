<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="index, follow" />
    <title> FPL Managers Stats - GW </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- @include('frontend.scripts') --}}
    
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
        }
        html { scroll-behavior: smooth; }
        @keyframes blink { 0%, 50%, 100% { opacity: 1; } 25%, 75% { opacity: 0.4; } }
        .blink { animation: blink 1.5s infinite; }
    </style>
</head>

<body class="min-h-screen text-gray-200 bg-black">
    {{-- @include('frontend.adverts.adsense-top') --}}

 <header id="top" class="py-4 text-white bg-[#5B0E9B] shadow-lg">
    {{-- New container for responsive layout --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        
        {{-- FPL Managers Title (Left/Center) --}}
        <h1 class="flex text-xl font-extrabold items-center gap-2">
            FPL Managers 
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/4e/Flag_of_Uganda.svg" alt="Uganda Flag"
                class="inline-block w-6 h-4" />
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
            <a href="{{ route('register') }}" 
               class="hidden md:inline-block px-3 py-1.5 text-sm font-semibold text-indigo-100 border border-indigo-100 rounded-md 
                      hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">
                Register
            </a>
        </nav>
    </div>
</header>

    <main class="max-w-5xl mx-auto p-4 space-y-6">
        
        
        <section class="mt-8">
            <h2 class="mb-6 text-2xl font-bold text-center">Leagues</h2>
            <div class="overflow-x-auto glass">

                   <table class="min-w-full text-left text-sm font-light">
                    <thead class="font-medium bg-white/10">
                        <tr>
                            <th scope="col" class="px-6 py-4">#</th>
                            <th scope="col" class="px-6 py-4">League</th>
                            <th scope="col" class="px-6 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leagues as $index => $league)

                  
                                <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-6 py-4">   {{ $league->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-bold">  <a href="{{ route('public.stats.show', ['slug' => $league->slug]) }}" class="text-indigo-400 hover:text-indigo-300 font-bold px-3 py-1 rounded-md border border-indigo-400 hover:border-indigo-300 transition duration-150">
                                    View League
                                </a></td>
                            </tr>
                        @empty
                            <tr class="border-b dark:border-neutral-500">
                                <td colspan="3" class="text-center py-8 text-gray-400">No leagues have been created yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>


                
            </div>
        </section>

  
        
       
        
        
    </main>
    <x-consent-banner/>

    <footer class="py-6 mt-8 text-center text-gray-500 text-sm border-t border-gray-800">
        © <span id="year"></span>
        <a href="https://techtowerinc.com" class="text-gray-400 hover:text-white transition">TechTower Inc.</a>. All rights reserved.
    </footer>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
</body>

</html>