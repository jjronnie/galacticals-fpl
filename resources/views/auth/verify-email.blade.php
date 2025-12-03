<x-app-layout>
    <div class="flex flex-col md:flex-row min-h-screen w-full font-sans">

        <div class="w-full md:w-1/3 flex flex-col justify-center items-center p-6 sm:p-10 bg-navgradient rounded-xl">
            <div class="w-full max-w-sm mx-auto p-8 ">

                <div class="md:hidden mb-6 flex items-center justify-center">
                     <x-logo class="w-24 h-24" />
                </div>

                <h1 class="font-bold text-2xl text-white text-center mb-2">Verify Your Email</h1>
                <p class="text-sm text-white text-center mb-8">Just one more step to get started!</p>

                @if (session('status') == 'verification-link-sent')
                <div class="mb-4 p-4 font-medium text-sm text-green-700 bg-green-100 rounded-lg">
                    A new verification link has been sent to the email address you provided.
                    <br>
                    <span class="font-normal text-xs text-green-600">Please check your spam folder as well.</span>
                </div>
                @endif

                <div class="mb-6 text-sm text-white leading-relaxed">
                    {{ __('Thanks for creating an account with FPL Galaxy. We need to verify your email address. Please click
                    the link we just sent to you.') }}
                </div>

                <div class="flex items-center justify-between mt-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit"
                            class="btn">
                            {{ __('Resend Verification Email') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-white hover:text-gray-900 underline ml-4">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @include('auth.right-banner')
    </div>
</x-app-layout>