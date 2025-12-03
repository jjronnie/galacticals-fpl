<button id="backToTop" 
    class="hidden fixed bottom-6 right-6 p-3 rounded-full text-white bg-navgradient shadow-lg cursor-pointer z-50">
    <i data-lucide="arrow-up" class="w-5 h-5"></i>
</button>

<script>
    const btn = document.getElementById('backToTop');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 200) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    });

    btn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
</script>