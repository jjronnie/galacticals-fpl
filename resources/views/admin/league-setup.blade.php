<x-app-layout>
    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 ">
                    
                    <x-page-title>
                        {{ __('Create Your FPL Mini-League') }}
                    </x-page-title>

                    <p class="mb-6 text-sm text-gray-500 ">
                        Welcome! Before we start tracking stats, please name your mini-league.
                    </p>

                    <form method="POST" action="{{ route('admin.league.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('League Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Create League') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>