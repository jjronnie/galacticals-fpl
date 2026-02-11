  @if (! request()->routeIs(['login', 'register']))
                <div class="px-4 pb-2 sm:px-6 lg:px-8">
                    <x-adsense />
                </div>
            @endif


<footer class="  py-4">
    <div
        class="max-w-7xl mx-auto px-4 md:px-6 flex flex-col sm:flex-row justify-between items-center text-sm text-gray-500 space-y-2 sm:space-y-0">
        <p>&copy; {{ date('Y') }} <a href="http://techtowerinc.com" target="_blank" rel="noopener noreferrer"> TechTower
                Inc. </a>| All rights reserved.</p>

        <p>Version 2.0.0</p>
    </div>
</footer>
