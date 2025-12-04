
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/serviceworker.js')
        .then(() => console.log("Service worker registered"))
        .catch((e) => console.error("Service worker error:", e));
}





document.addEventListener("DOMContentLoaded", () => {

      // === Reveal GIF on load ===
    const gif = document.getElementById('adGif');
    const img = new Image();
    img.src = gif?.src;
    img.onload = () => {
        gif.classList.remove('hidden', 'opacity-0');
        gif.classList.add('opacity-100');
    };

    // === Initialize on load ===
    updateReadingProgress();
    highlightActiveSection();


    // Clock Display
    const clockDisplay = document.getElementById("clockDisplay");
    function updateClock() {
        const now = new Date();
        const options = {
            weekday: "short",
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            hour12: true,
        };
        if (clockDisplay) {
            clockDisplay.textContent = now.toLocaleDateString("en-US", options);
        }
    }
    updateClock();
    setInterval(updateClock, 1000);


});








function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eye-icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }


}




