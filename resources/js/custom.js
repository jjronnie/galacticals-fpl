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

    function toggleSidebar(open = null) {
        const isOpen = !sidebar.classList.contains("-translate-x-full");
        const shouldOpen = open ?? !isOpen;

        if (shouldOpen) {
            sidebar.classList.remove("-translate-x-full");
            sidebarOverlay.classList.remove("opacity-0", "pointer-events-none");
        } else {
            sidebar.classList.add("-translate-x-full");
            sidebarOverlay.classList.add("opacity-0", "pointer-events-none");
        }
    }

    // Click events
    menuBtn?.addEventListener("click", () => toggleSidebar(true));
    closeSidebar?.addEventListener("click", () => toggleSidebar(false));
    sidebarOverlay?.addEventListener("click", () => toggleSidebar(false));

    // Spacebar toggle
    document.addEventListener("keydown", (e) => {
        if (e.code === "Space" && !sidebar.classList.contains("-translate-x-full")) {
            e.preventDefault();
            toggleSidebar(false);
        }
    });

    // Swipe detection
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener("touchstart", (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });

    document.addEventListener("touchend", (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleGesture();
    });

    function handleGesture() {
        const deltaX = touchEndX - touchStartX;

        // Swipe right to open (from left edge)
        if (deltaX > 50 && sidebar.classList.contains("-translate-x-full") && touchStartX < 50) {
            toggleSidebar(true);
        }
        // Swipe left to close
        if (deltaX < -50 && !sidebar.classList.contains("-translate-x-full")) {
            toggleSidebar(false);
        }
    }



});