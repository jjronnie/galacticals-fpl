<x-app-layout>




    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-card shadow sm:rounded-lg">
                <div class="max-w-xl">

                    @php
                    $links = [
                    ['action' => 'How to find your league ID', 'route' => 'find'],
                    ['action' => 'FPL Galaxy Terms and conditions', 'route' => 'terms'],
                    ['action' => 'Privacy Policy', 'route' => 'privacy.policy'],
                    ['action' => 'How it works', 'route' => 'home'],
                    // add more items here
                    ];
                    @endphp

                    <x-table :headers="['More', ]">
                        @foreach($links as $link)
                        <x-table.row>

                            <x-table.cell>
                                <a href="{{ route($link['route']) }}" class="btn">
                                    {{ $link['action'] }} <i data-lucide="square-arrow-out-up-right"
                                        class="w-5 h-5 ml-2"></i>
                                </a>
                            </x-table.cell>
                        </x-table.row>
                        @endforeach
                    </x-table>



                </div>
            </div>











            <div class="p-4 sm:p-8 bg-card shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <div
                        class=" px-4 md:px-6 flex flex-col sm:flex-row justify-between items-center text-sm text-gray-200 space-y-2 sm:space-y-0">
                        <p>&copy; {{ date('Y') }} <a href="http://techtowerinc.com" target="_blank"
                                rel="noopener noreferrer"> TechTower Inc. </a>| All rights reserved.</p>

                        <p class="text-end">Version 1.0.0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>