<x-guest-layout>
    <div class="flex flex-col md:flex-row min-h-screen w-full font-sans">

        <div class="w-full md:w-1/3 flex flex-col justify-center items-center p-6 sm:p-10 bg-gray-50">
            <div class="w-full max-w-sm mx-auto p-8 bg-white rounded-xl shadow-lg">

                <div class="md:hidden mb-6 flex items-center justify-center">
                     <x-application-logo/>
                </div>

                <h1 class="font-bold text-2xl text-gray-800 text-center mb-2">Verify Your Email</h1>
                <p class="text-sm text-gray-500 text-center mb-8">Just one more step to get started!</p>

                @if (session('status') == 'verification-link-sent')
                <div class="mb-4 p-4 font-medium text-sm text-green-700 bg-green-100 rounded-lg">
                    A new verification link has been sent to the email address you provided.
                    <br>
                    <span class="font-normal text-xs text-green-600">Please check your spam folder as well.</span>
                </div>
                @endif

                <div class="mb-6 text-sm text-gray-700 leading-relaxed">
                    {{ __('Thanks for creating an account with TPOS! We need to verify your email address. Please click
                    the link we just sent to you.') }}
                </div>

                <div class="flex items-center justify-between mt-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('Resend Verification Email') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-900 underline ml-4">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @include('auth.right-banner')
    </div>
</x-guest-layout>