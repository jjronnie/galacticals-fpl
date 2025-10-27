// resources/js/sidebar.js
import Alpine from 'alpinejs';

Alpine.store('sidebar', {
    open: null,
    toggle(id) {
        this.open = this.open === id ? null : id;
    }
});

window.Alpine = Alpine;
Alpine.start();
