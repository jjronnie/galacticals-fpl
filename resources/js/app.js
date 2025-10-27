import './bootstrap'
import './custom'

import Alpine from 'alpinejs'
import { createIcons, icons } from 'lucide'

import './custom.js';



window.Alpine = Alpine
Alpine.start()

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons })
})











