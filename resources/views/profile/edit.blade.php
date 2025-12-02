<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-card shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-card shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-card shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
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