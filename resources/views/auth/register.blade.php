<x-app-layout>
   
    <div class="flex flex-col lg:flex-row min-h-screen w-full">
        <!-- Left: Register Form -->
        <div class="w-full lg:w-1/3 flex flex-col justify-center px-6 py-12 sm:px-10 bg-primary rounded-xl">
            <div class="w-full max-w-md mx-auto ">
                <div class="lg:hidden mb-2 mx-auto flex items-center justify-center">
                     <x-logo class="w-24 h-24" />
                </div>
                   <div class="text-center">

                <h1 class="font-bold mb-3  text-xl">Create Account</h1>

                   <!-- Google Sign In Button -->

          @include('auth.google-button')

               

                <p class="mb-6  text-sm">
                    Enter your credentials to create a free account and Make your mini-league fun
                </p>
                   </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 text-sm text-purple-600 text-center">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Register Form -->
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    


                    <!--  Name -->
                    <div class="relative">
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            autofocus placeholder="Enter Name"
                            class="w-full px-4 pt-6 pb-2 text-white placeholder-transparent bg-navgradient rounded-lg border border-gray-300 peer focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent" />
                        <label for="name"
                            class="absolute left-4 top-2 text-sm transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-white">
                            Your Name
                        </label>
                        @error('name')
                            <p class="text-white bg-primary rounded-xl p-2 text-centertext-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="relative">
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            placeholder="Email"
                            class="peer w-full px-4 pt-6 pb-2 text-white placeholder-transparent bg-navgradient rounded-lg border border-gray-300 peer focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent" />
                        <label for="email"
                            class="absolute left-4 top-2 text-sm transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-white">
                            Email
                        </label>
                        @error('email')
                            <p class="text-white bg-primary rounded-xl p-2 text-centertext-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>              

                    <!-- Password -->
                    <div class="relative" x-data="{ showPassword: false }">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required placeholder="Password"
                            class="peer w-full px-4 pt-6 pb-2 pr-12 text-white placeholder-transparent bg-navgradient rounded-lg border border-gray-300 peer focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent" />
                        <label for="password"
                            class="absolute left-4 top-2 text-sm transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-white">
                            Password
                        </label>
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="text-white absolute right-4 top-4 focus:outline-none"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                        >
                            <i data-lucide="eye" x-show="!showPassword" class="w-5 h-5"></i>
                            <i data-lucide="eye-off" x-show="showPassword" x-cloak class="w-5 h-5"></i>
                        </button>
                        @error('password')
                            <p class="text-white bg-primary rounded-xl p-2 text-centertext-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="relative" x-data="{ showPasswordConfirmation: false }">
                        <input :type="showPasswordConfirmation ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required placeholder="Confirm Password"
                            class="peer w-full px-4 pt-6 pb-2 pr-12 text-white placeholder-transparent bg-navgradient rounded-lg border border-gray-300 peer focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent" />
                        <label for="password_confirmation"
                            class="absolute left-4 top-2 text-sm transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-white">
                            Confirm Password
                        </label>
                        <button
                            type="button"
                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="text-white absolute right-4 top-4 focus:outline-none"
                            :aria-label="showPasswordConfirmation ? 'Hide password confirmation' : 'Show password confirmation'"
                        >
                            <i data-lucide="eye" x-show="!showPasswordConfirmation" class="w-5 h-5"></i>
                            <i data-lucide="eye-off" x-show="showPasswordConfirmation" x-cloak class="w-5 h-5"></i>
                        </button>
                        @error('password_confirmation')
                            <p class="text-white bg-primary rounded-xl p-2 text-centertext-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Terms -->
                    <div>
                        <label for="terms-and-conditions" class="group inline-flex items-start gap-3 cursor-pointer">
                            <span class="relative mt-0.5 flex h-5 w-5 items-center justify-center rounded border-2 border-cyan-300 bg-primary transition group-hover:border-cyan-200">
                                <input
                                    type="checkbox"
                                    id="terms-and-conditions"
                                    name="terms-and-conditions"
                                    value="1"
                                    @checked(old('terms-and-conditions'))
                                    required
                                    class="peer absolute inset-0 h-full w-full cursor-pointer appearance-none rounded checked:border-accent checked:bg-accent focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-0"
                                />
                                <i data-lucide="check" class="pointer-events-none h-3.5 w-3.5 text-primary opacity-0 transition peer-checked:opacity-100"></i>
                            </span>
                            <span class="text-sm text-white leading-5">
                            I agree to the
                            <a href="/terms-and-conditions" class="text-white underline">
                               Terms and Conditions of {{ config('app.name') }}

                            </a>
                            </span>
                        </label>
                        @error('terms-and-conditions')
                            <p class="text-white bg-primary rounded-xl p-2 text-center text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                        class="w-full py-3 register-button bg-card hover:bg-purple-700 border text-white font-semibold rounded-lg transition duration-200">
                        Create Account
                    </button>
                </form>

               

            </div>
            <p class="text-sm mt-4 text-center">
                Already Registered?
                <span class="text-white underline">
                    <a href="{{ route('login') }}"> Login Here</a>
                </span>
            </p>
        </div>

        <!-- Right: Banner -->
         @include('auth.right-banner')
        
    </div>

    <script>
        // Add loading state to register button
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = document.querySelector('.register-button');
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creating Account...
            `;
            button.disabled = true;
        });
    </script>
</x-app-layout>
