<div class="space-y-3 mt-4">

    <!-- Google -->
    <button data-url="{{ route('social.login', ['provider' => 'google']) }}"
        class="social-login-btn flex w-full py-3 text-gray-700 font-medium bg-white border border-gray-300 rounded-full shadow-sm items-center justify-center gap-3 transition duration-300 select-none">
        <i class="fa-brands fa-google text-red-500 text-lg"></i>
        <span class="social-text">Continue with Google</span>
        <i class="fa-solid fa-spinner fa-spin hidden social-spinner"></i>
    </button>

    <!-- Facebook -->
    <button data-url="{{ route('social.login', ['provider' => 'facebook']) }}"
        class="social-login-btn flex w-full py-3 text-gray-700 font-medium bg-white border border-gray-300 rounded-full shadow-sm items-center justify-center gap-3 transition duration-300 select-none">
        <i class="fa-brands fa-facebook text-blue-600 text-lg"></i>
        <span class="social-text">Continue with Facebook</span>
        <i class="fa-solid fa-spinner fa-spin hidden social-spinner"></i>
    </button>

</div>

<hr class="border-t border-gray-200 my-4">

<p class="text-center mt-2 text-sm text-gray-500">OR</p>


<script>
    document.querySelectorAll('.social-login-btn').forEach(button => {
        button.addEventListener('click', function() {
            const text = this.querySelector('.social-text');
            const spinner = this.querySelector('.social-spinner');

            this.disabled = true;
            text.classList.add('hidden');
            spinner.classList.remove('hidden');

            window.location.href = this.dataset.url;
        });
    });
</script>
