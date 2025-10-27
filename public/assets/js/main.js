
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/serviceworker.js')
                .then(() => console.log("Service worker registered"))
                .catch((e) => console.error("Service worker error:", e));
        }
   

document.addEventListener("DOMContentLoaded", () => {
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

    // Sidebar Toggle
    const menuBtn = document.getElementById("menuBtn");
    const sidebar = document.getElementById("sidebar");
    const sidebarOverlay = document.getElementById("sidebarOverlay");
    const closeSidebar = document.getElementById("closeSidebar");

    function toggleSidebar() {
        sidebar.classList.toggle("-translate-x-full");
        sidebarOverlay.classList.toggle("opacity-0");
        sidebarOverlay.classList.toggle("pointer-events-none");
    }

    menuBtn?.addEventListener("click", toggleSidebar);
    closeSidebar?.addEventListener("click", toggleSidebar);
    sidebarOverlay?.addEventListener("click", toggleSidebar);
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

    // Re-run Lucide replace to update icon
    if (window.lucide) {
        lucide.replace({
            parent: icon.parentNode
        });
    }
}


//notification sound


const toggleButton = document.getElementById('toggleSound');
    const toggleThumb = document.getElementById('toggleThumb');

    // Load initial state
    const soundSetting = localStorage.getItem('alertSound');
    let isOn = soundSetting !== 'off';

    if (!isOn) {
        toggleButton.classList.remove('bg-green-500');
        toggleButton.classList.add('bg-gray-300');
        toggleThumb.classList.remove('translate-x-5');
        toggleThumb.classList.add('translate-x-0');
        toggleButton.setAttribute('aria-checked', 'false');
    }

    // Toggle click
    toggleButton.addEventListener('click', () => {
        isOn = !isOn;

        if (isOn) {
            toggleButton.classList.remove('bg-gray-300');
            toggleButton.classList.add('bg-green-500');
            toggleThumb.classList.remove('translate-x-0');
            toggleThumb.classList.add('translate-x-5');
            localStorage.setItem('alertSound', 'on');
            toggleButton.setAttribute('aria-checked', 'true');
        } else {
            toggleButton.classList.remove('bg-green-500');
            toggleButton.classList.add('bg-gray-300');
            toggleThumb.classList.remove('translate-x-5');
            toggleThumb.classList.add('translate-x-0');
            localStorage.setItem('alertSound', 'off');
            toggleButton.setAttribute('aria-checked', 'false');
        }
    });

