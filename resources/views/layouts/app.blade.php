@extends('layouts.main')
@section('content')
    <div class="flex min-h-screen">
        @include('layouts.sidebar')
        <div class="flex min-w-0 flex-1 flex-col">
            @include('layouts.nav')
          
            <main class="flex-1 px-2 pb-24 sm:pb-24 lg:pb-24">
                @include('layouts.profile-intro-banner')
                {{ $slot }}
            </main>
           
            @include('layouts.footer')
        </div>
    </div>

    <!-- Global Logout Modal -->
    <div x-show="logoutModalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm"
        style="display: none;"
        @keydown.escape.window="logoutModalOpen = false">
        <div @click.away="logoutModalOpen = false" class="w-full max-w-sm mx-4 bg-card rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-red-500/20 flex items-center justify-center">
                    <i data-lucide="log-out" class="w-7 h-7 text-red-500"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Log out</h3>
                <p class="text-gray-400 text-sm mb-6">Are you sure you want to log out of your account?</p>
                <div class="flex gap-3">
                    <button @click="logoutModalOpen = false" class="flex-1 px-4 py-3 rounded-xl font-medium text-gray-300 bg-white/5 hover:bg-white/10 transition">
                        Cancel
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-3 rounded-xl font-medium bg-red-600 hover:bg-red-500 text-white transition">
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
