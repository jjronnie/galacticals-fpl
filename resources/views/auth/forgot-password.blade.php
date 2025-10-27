<x-guest-layout>
    <div class="flex flex-col md:flex-row min-h-screen w-full font-sans">
        
        <div class="w-full md:w-1/3 flex flex-col justify-center items-center p-6 sm:p-10 bg-gray-50">
            <div class="w-full max-w-sm mx-auto p-8 bg-white rounded-xl shadow-lg">

                <div class="md:hidden mb-6 flex items-center justify-center">
                    <x-application-logo/>
                </div>

                <h1 class="font-bold text-2xl text-gray-800 text-center mb-2">Forgot Password?</h1>
                <p class="text-sm text-gray-500 text-center mb-6">No problem, we can help with that.</p>

                <div class="mb-4 text-sm text-gray-700 leading-relaxed">
                    {{ __('Just enter your email address below, and we\'ll send you a password reset link to create a new one.') }}
                </div>

                <x-auth-session-status class="mb-4 text-sm text-green-700 bg-green-100 rounded-lg p-3" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input id="email" class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out" type="email" name="email" value="{{ old('email') }}" required autofocus  />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="btn">
                            {{ __('Email Password Reset Link') }}
                        </button>
                        <a href="{{ route('login') }}" class="btn-gray ml-3">Cancel</a>

                    </div>
                </form>
            </div>
        </div>

         @include('auth.right-banner')
    </div>
</x-guest-layout>