<button id="google-login"
    class="flex w-full py-3 mt-4 text-gray-700 font-medium bg-white border border-gray-300 rounded-full shadow-sm items-center justify-center gap-3 transition duration-300 select-none">
    <img src="{{ asset('assets/img/google144.png') }}" alt="Google Logo" class="w-5 h-5" />
    <span id="google-text">Continue with Google</span>
    <svg id="google-spinner" class="hidden w-5 h-5 animate-spin text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
</button>

<script>
document.getElementById('google-login').addEventListener('click', function () {
    const btn = this;
    const text = document.getElementById('google-text');
    const spinner = document.getElementById('google-spinner');

    // Disable the button
    btn.disabled = true;

    // Hide text, show spinner
    text.classList.add('hidden');
    spinner.classList.remove('hidden');

    // Redirect to Google OAuth
    window.location.href = "{{ route('google.login') }}";
});
</script>
