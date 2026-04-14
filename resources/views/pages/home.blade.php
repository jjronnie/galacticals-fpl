<x-app-layout>
    <main class="mx-auto max-w-7xl">
        <x-adsense />

        <section class="mt-6 px-2 relative overflow-hidden rounded-2xl bg-card border border-gray-800 shadow-lg">
            <div class="flex flex-col md:flex-row items-center">
                <div class="p-6 md:w-1/2 z-10">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight mb-3 leading-tight">
                        Master Your FPL League
                    </h1>
                    <p class="text-gray-400 text-sm mb-5 leading-relaxed">
                        Go beyond the basics. Monitor live standings, dive deep into personal analytics, and track every gameweek movement.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-[#0A8935] px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-green-600 transition-colors">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-lg bg-[#0A8935] px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-green-600 transition-colors">
                                Sign Up Free
                            </a>
                            <a href="{{ route('login') }}" class="rounded-lg border border-gray-600 bg-transparent px-5 py-2.5 text-sm font-bold text-gray-300 hover:border-[#0A8935] hover:text-white transition-colors">
                                Log In
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="md:w-1/2 w-full  relative min-h-[200px]">
                    <img src="{{ asset('assets/img/home/4.png') }}" alt="FPL Dashboard Preview" class="absolute inset-0 w-full h-full object-cover object-left rounded-r-2xl" />
                </div>
            </div>
        </section>

        <section class="mt-6 px-2 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="group flex flex-col overflow-hidden rounded-2xl bg-card border border-gray-800 hover:border-gray-700 transition-all">
                <div class="h-40 bg-gray-800/50 relative overflow-hidden">
                    <img src="{{ asset('assets/img/home/1.png') }}" alt="League Tracking" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                </div>
                <div class="p-4 flex-1 flex flex-col">
                    <h2 class="text-base font-bold text-white">Live League Tracking</h2>
                    <p class="mt-2 text-gray-400 text-sm leading-relaxed flex-1">
                        Add your classic leagues to monitor live standings, track weekly rank movements, and review historical performance.
                    </p>
                </div>
            </div>

            <div class="group flex flex-col overflow-hidden rounded-2xl bg-card border border-gray-800 hover:border-gray-700 transition-all">
                <div class="h-40 bg-gray-800/50 relative overflow-hidden">
                    <img src="{{ asset('assets/img/home/3.png') }}" alt="Personal Analytics" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                </div>
                <div class="p-4 flex-1 flex flex-col">
                    <h2 class="text-base font-bold text-white">Deep Personal Analytics</h2>
                    <p class="mt-2 text-gray-400 text-sm leading-relaxed flex-1">
                        Claim your team to track points, analyze transfers, monitor chip usage, and visualize performance trends.
                    </p>
                </div>
            </div>

            <div class="group flex flex-col overflow-hidden rounded-2xl bg-card border border-gray-800 hover:border-gray-700 transition-all sm:col-span-2 lg:col-span-1">
                <div class="h-40 bg-gray-800/50 relative overflow-hidden">
                    <img src="{{ asset('assets/img/home/2.webp') }}" alt="Actionable Insights" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                </div>
                <div class="p-4 flex-1 flex flex-col">
                    <h2 class="text-base font-bold text-white">Actionable FPL Insights</h2>
                    <p class="mt-2 text-gray-400 text-sm leading-relaxed flex-1">
                        Discover player ownership trends, uncover the most-captained assets, and analyze transfer shifts.
                    </p>
                </div>
            </div>
        </section>

        <section class="mt-6 px-2 grid gap-6 md:grid-cols-2 bg-card p-5 rounded-2xl border border-gray-800">
            <div>
                <h2 class="text-lg font-bold text-white mb-4">How to get started</h2>
                <ol class="space-y-3">
                    <li class="flex items-start">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-[#0A8935] text-white font-bold text-xs shrink-0 mr-3">1</span>
                        <p class="text-gray-300 text-sm mt-0.5">Create your free account</p>
                    </li>
                    <li class="flex items-start">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-[#0A8935] text-white font-bold text-xs shrink-0 mr-3">2</span>
                        <p class="text-gray-300 text-sm mt-0.5">Enter your league ID to sync your data</p>
                    </li>
                    <li class="flex items-start">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-[#0A8935] text-white font-bold text-xs shrink-0 mr-3">3</span>
                        <p class="text-gray-300 text-sm mt-0.5">Search and claim your specific FPL team</p>
                    </li>
                    <li class="flex items-start">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-[#0A8935] text-white font-bold text-xs shrink-0 mr-3">4</span>
                        <p class="text-gray-300 text-sm mt-0.5">Start tracking and dominating your mini-league</p>
                    </li>
                </ol>
                <div class="mt-4">
                    <a href="{{ route('find') }}" class="text-[#0A8935] hover:text-green-400 font-medium text-xs flex items-center hover:underline">
                        Need help finding your league ID?
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>

            <div class="flex flex-col justify-center items-center md:items-start md:pl-5 md:border-l border-gray-800">
                <div class="w-full flex justify-between md:justify-start md:gap-8 mb-5 text-center md:text-left">
                    <div>
                        <p class="text-2xl font-bold text-white">500+</p>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Leagues</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">10K+</p>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Managers</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">50K+</p>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Gameweeks</p>
                    </div>
                </div>

                <div class="text-center md:text-left w-full">
                    <p class="text-gray-400 text-sm mb-4">Join thousands tracking their FPL performance today.</p>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-block rounded-lg bg-[#0A8935] px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-green-600 transition-colors w-full md:w-auto text-center">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="inline-block rounded-lg bg-[#0A8935] px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-green-600 transition-colors w-full md:w-auto text-center">
                            Create Free Account
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        <div class="mt-6 px-2">
            <x-adsense />
        </div>
        <x-back-to-top />
    </main>
</x-app-layout>
