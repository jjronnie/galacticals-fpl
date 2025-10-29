<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="index, follow" />
    <title> FPL Managers Stats - GW </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/logo.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo.webp') }}">

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#001529">

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

    <meta name="mobile-web-app-capable" content="yes">

    <!--adsense script auto ads-->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
        crossorigin="anonymous"></script>

    {{-- @include('frontend.scripts') --}}

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Nunito", sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.18);
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
        }

        html {
            scroll-behavior: smooth;
        }

        @keyframes blink {

            0%,
            50%,
            100% {
                opacity: 1;
            }

            25%,
            75% {
                opacity: 0.4;
            }
        }

        .blink {
            animation: blink 1.5s infinite;
        }
    </style>
</head>

<body class="min-h-screen text-gray-200 bg-black">
    {{-- @include('frontend.adverts.adsense-top') --}}

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
    <main class="max-w-5xl mx-auto p-4 space-y-6">
        <x-adsense />

        <section class="mt-8">
            <h2 class="mb-6 text-2xl font-bold text-center">Leagues Using {{ config('app.name') }}</h2>
            <div class="flex my-6 justify-center">
                <a href="{{ route('register') }}" target="_blank"
                    class="py-2 px-6 text-white font-semibold bg-green-600 rounded-lg shadow-md hover:bg-purple-700 transition duration-200 blink">
                    Create account for your league
                </a>
            </div>
            <div class=" glass">

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
                        <td class="whitespace-nowrap px-6 py-4"> {{ $league->name }}</td>
                        <td class="whitespace-nowrap px-6 py-4 font-bold"> <a
                                href="{{ route('public.stats.show', ['slug' => $league->slug]) }}"
                                class="text-indigo-400 hover:text-indigo-300 font-bold px-3 py-1 rounded-md border border-indigo-400 hover:border-indigo-300 transition duration-150">
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



        <x-adsense />


    </main>
    <x-consent-banner />

    <footer class="py-6 mt-8 text-center text-gray-500 text-sm border-t border-gray-800">
        © <span id="year"></span>
        <a href="https://techtowerinc.com" class="text-gray-400 hover:text-white transition">TechTower Inc.</a>. All
        rights reserved.
    </footer>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
</body>

</html>