   <!-- Preloader Overlay -->
    <div id="refreshPreloader" class="fixed inset-0 bg-white  flex items-center justify-center z-[9999] hidden">
        <div class="text-center">
            <!-- Spinner -->
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <!-- Loading Text -->
            <p class="text-gray-600 text-sm font-medium">Loading...</p>
        </div>
    </div>



 <script>
    const preloader = document.getElementById('refreshPreloader');

    // Show preloader before unload
    window.addEventListener('beforeunload', () => {
        preloader.classList.remove('hidden');
    });

    // Hide preloader when coming back via history (back button, forward button)
    window.addEventListener('pageshow', (event) => {
        // If restored from bfcache (browser cache), hide preloader
        if (event.persisted) {
            preloader.classList.add('hidden');
        }
    });

    // Also handle popstate for SPA/PWA navigation
    window.addEventListener('popstate', () => {
        preloader.classList.add('hidden');
    });
</script>
