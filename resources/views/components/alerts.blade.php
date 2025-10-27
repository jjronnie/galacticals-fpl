<div x-data="{
    colors: {
        'success': {
            text: 'text-green-600',
            icon: 'circle-check-big',
            progress: 'bg-green-600'
        },
        'info': {
            text: 'text-blue-600',
            icon: 'info',
            progress: 'bg-blue-600'
        },
        'warning': {
            text: 'text-yellow-600',
            icon: 'alert-triangle',
            progress: 'bg-yellow-600'
        },
        'error': {
            text: 'text-red-600',
            icon: 'x-circle',
            progress: 'bg-red-600'
        },
        'neutral': {
            text: 'text-gray-600',
            icon: 'circle-help',
            progress: 'bg-gray-600'
        }
    },
    alerts: [],
    add(message, type = 'neutral', duration = 5000) {
        this.alerts.push({
            id: Date.now(),
            message,
            type,
            duration,
            remaining: duration,
            isPaused: false,
            isVisible: false,
        });
        this.updateAlerts();
    },
    updateAlerts() {
        this.alerts.forEach((alert, index) => {
            if (!alert.isVisible) {
                setTimeout(() => {
                    this.alerts[index].isVisible = true;
                    this.startTimer(alert.id);
                }, index * 80);
            }
        });
    },
    startTimer(id) {
        const alert = this.alerts.find(a => a.id === id);
        if (!alert || alert.isPaused) return;

        let start = null;
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const elapsed = timestamp - start;

            if (!alert.isPaused) {
                alert.remaining = Math.max(0, alert.remaining - elapsed);
            }

            if (alert.remaining > 0) {
                start = timestamp;
                if (!alert.isPaused) requestAnimationFrame(animate);
            } else {
                this.remove(alert.id);
            }
        };
        requestAnimationFrame(animate);
    },
    togglePause(id) {
        const alert = this.alerts.find(a => a.id === id);
        if (alert) {
            alert.isPaused = !alert.isPaused;
            if (!alert.isPaused) this.startTimer(id);
        }
    },
    remove(id) {
        const alert = this.alerts.find(a => a.id === id);
        if (alert) {
            // Set visibility to false to start the leave transition
            alert.isVisible = false;
            // Remove the alert from the array after the transition completes
            setTimeout(() => {
                this.alerts = this.alerts.filter(a => a.id !== id);
            }, 300);
        }
    }
}"
x-init="
@if(session()->has('success')) add('{{ session('success') }}','success'); @endif
@if(session()->has('info')) add('{{ session('info') }}','info'); @endif
@if(session()->has('warning')) add('{{ session('warning') }}','warning'); @endif
@if(session()->has('error')) add('{{ session('error') }}','error'); @endif
@if(session()->has('status')) add('{{ session('status') }}','success'); @endif
"
class="fixed inset-x-0 top-4 z-[9999] flex flex-col items-center p-4 space-y-2 pointer-events-none">

    <template x-for="alert in alerts" :key="alert.id">
        <div x-show="alert.isVisible"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 -translate-y-full"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300 transform"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-full"
            @mouseenter="togglePause(alert.id)"
            @mouseleave="togglePause(alert.id)"
            class="pointer-events-auto max-w-sm w-full bg-white rounded-xl shadow-lg border border-gray-300 overflow-hidden"
            role="alert">

            <div class="p-4 flex items-center justify-between space-x-4">
                <div class="flex-shrink-0" :class="colors[alert.type].text">
                    <i :data-lucide="colors[alert.type].icon" class="w-6 h-6"></i>
                </div>

                <div class="flex-1 text-sm font-medium pr-4 text-gray-800"
                    x-text="alert.message"></div>

                <div class="flex-shrink-0">
                    <button @click="remove(alert.id)"
                        :class="colors[alert.type].text + ' hover:opacity-70 transition-colors duration-200 focus:outline-none p-1 -m-1'">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <div class="h-1" :class="colors[alert.type].progress"
                :style="{ width: (alert.remaining / alert.duration) * 100 + '%' }"></div>
        </div>
    </template>
</div>