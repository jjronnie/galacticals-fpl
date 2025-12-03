<x-app-layout>
  <div class="flex flex-col min-h-screen w-full lg:flex-row">
    <!-- Left: Reset Form -->
    <div
      class="flex flex-col w-full justify-center lg:w-1/3 bg-navgradient rounded-xl"
    >
      <div class="w-full mx-auto py-10 px-10">
        <div class="flex mb-2 mx-auto items-center justify-center lg:hidden">
          <x-logo class="w-24 h-24" />
        </div>

        <div class="text-center">
          <h1 class="mb-3 font-bold text-xl">Reset your password</h1>
          <p class="mb-6 text-sm">Enter your  new password below</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
          <div class="mb-4 text-sm text-white text-center">
            {{ session('status') }}
          </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
          @csrf

          <!-- Password Reset Token -->
          <input type="hidden" name="token" value="{{ $request->route('token') }}">

          <!-- Email -->
          <div class="relative">
            <input
              type="email"
              id="email"
              name="email"
              value="{{ old('email', $request->email) }}"
              required
              autofocus
              placeholder="Email"
              class="w-full px-4 pt-6 pb-2 text-white placeholder-transparent bg-navgradient rounded-lg border border-gray-300 peer focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent"
            />
            <label
              for="email"
              class="text-sm transition-all absolute left-4 top-2 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-white"
            >
              Email
            </label>
            @error('email')
              <p class="mt-2 text-white bg-primary rounded-xl p-2 text-center text-sm">{{ $message }}</p>
            @enderror
          </div>

          <!-- Password -->
          <div class="relative">
            <input
              type="text"
              id="password"
              name="password"
              required
              placeholder="Password"
              class="w-full px-4 pt-6 pb-2 text-white placeholder-transparent bg-navgradient rounded-lg border border-gray-300 peer focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent"
              autocomplete="new-password"
            />
            <label
              for="password"
              class="text-sm transition-all absolute left-4 top-2 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-white"
            >
              New Password
            </label>

            <!-- Toggle Eye Icon -->
            <button
              type="button"
              onclick="togglePassword('password','eye-icon-password')"
              class="text-white absolute right-4 top-4 focus:outline-none"
            >
            </button>

            @error('password')
              <p class="mt-2 text-white bg-primary rounded-xl p-2 text-center text-sm">{{ $message }}</p>
            @enderror
          </div>

          <!-- Confirm Password -->
          <div class="relative">
            <input
              type="text"
              id="password_confirmation"
              name="password_confirmation"
              required
              placeholder="Confirm Password"
              class="w-full px-4 pt-6 pb-2 text-white placeholder-transparent bg-navgradient rounded-lg border border-gray-300 peer focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent"
              autocomplete="new-password"
            />
            <label
              for="password_confirmation"
              class="text-sm transition-all absolute left-4 top-2 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-white"
            >
              Confirm Password
            </label>

          

            @error('password_confirmation')
              <p class="mt-2 text-white bg-primary rounded-xl p-2 text-center text-sm">{{ $message }}</p>
            @enderror
          </div>

          <div class="flex items-center justify-end mt-4">
            <button
              type="submit"
              class="flex w-full py-3 text-white font-semibold bg-card rounded-full reset-button items-center justify-center gap-2 transition duration-300 hover:bg-purple-700"
            >
              <span>Reset Password</span>
            </button>
          </div>
        </form>

        <p class="mt-4 text-sm text-center">
          Remembered your password?
          <span class="text-white underline">
            <a href="{{ route('login') }}">Sign in</a>
          </span>
        </p>

        <x-adsense />
      </div>
    </div>

    <!-- Right: Banner -->
    @include('auth.right-banner')
  </div>

  <script>
    // Add loading state to form submit
    document.querySelector('form').addEventListener('submit', function (e) {
      const button = document.querySelector('.reset-button');
      if (!button) return;
      button.innerHTML = `
        <svg class="inline h-5 w-5 mr-3 text-white animate-spin -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Resetting...
      `;
      button.disabled = true;
    });

   
  </script>
</x-app-layout>
