<x-app-layout>
    <div class="flex flex-col md:flex-row min-h-screen w-full font-sans">
        
        <div class="w-full md:w-1/3 flex flex-col justify-center items-center p-6 sm:p-10 bg-navgradient rounded-xl">
            <div class="w-full max-w-sm mx-auto p-8 ">

                <div class="md:hidden mb-6 flex items-center justify-center">
                     <x-logo class="w-24 h-24" />
                </div>

                <h1 class="font-bold text-2xl text-white text-center mb-2">Forgot Password?</h1>
                <p class="text-sm text-white text-center mb-6">No problem, we can help with that.</p>

                <div class="mb-4 text-sm text-white leading-relaxed">
                    {{ __('Just enter your email address below, and we\'ll send you a password reset link to create a new one.') }}
                </div>

                <x-auth-session-status class="mb-4 text-sm text-green-700 bg-green-100 rounded-lg p-3" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium  text-white mb-1">Email</label>
                        <input id="email" placeholder="Enter your Email" class="block w-full px-4 pt-6 pb-2 text-white placeholder-transparent bg-navgradient rounded-lg border border-gray-300 peer focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent" type="email" name="email" value="{{ old('email') }}" required autofocus  />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('login') }}" class="btn-gray mr-3">Cancel</a>

                        <button type="submit" class="btn">
                            {{ __('Send Reset Link') }}
                        </button>

                    </div>
                </form>
            </div>
        </div>

         @include('auth.right-banner')
    </div>
</x-app-layout>