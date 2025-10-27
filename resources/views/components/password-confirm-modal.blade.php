<div 
    x-data="{ show: false, password: '', error: '', loading: false }"
    x-cloak
>
    <!-- Trigger Button -->
    <button 
        type="button"
        @click="show = true"
        class="{{ $triggerClass }}">
        {!! $trigger !!}
    </button>

    <!-- Modal -->
    <div 
        x-show="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        x-transition
        @keydown.escape.window="show = false"
    >
        <div class="bg-white rounded-lg p-6 shadow-xl max-w-md w-full">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ $title ?? 'Confirm Deletion' }}</h2>
            <p class="text-sm text-gray-600 mb-4">{{ $message ?? 'Enter your password to confirm this action.' }}</p>

            <!-- Password Input -->
            <input type="password" 
                x-model="password"
                placeholder="Enter your password"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-2"
            >
            <p x-text="error" class="text-red-600 text-sm mb-2"></p>

            <div class="flex justify-end space-x-2">
                <button type="button" 
                    @click="show = false"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-sm rounded-lg">
                    Cancel
                </button>

                <button 
                    @click="confirmDelete"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg"
                    x-text="loading ? 'Verifying...' : '{{ $confirmText ?? 'Confirm Delete' }}'"
                    :disabled="loading"
                ></button>
            </div>
        </div>
    </div>

    <!-- Hidden Form -->
    <form 
        x-ref="deleteForm"
        method="POST" 
        action="{{ $action }}" 
        class="hidden">
        @csrf
        @method($method ?? 'POST')
    </form>

    <script>
        function confirmDelete() {
            this.loading = true
            this.error = ''

            fetch("{{ route('password.check') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ password: this.password })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false
                if (data.success) {
                    this.$refs.deleteForm.submit()
                } else {
                    this.error = data.message || 'Password incorrect.'
                }
            })
            .catch(() => {
                this.loading = false
                this.error = 'Something went wrong. Try again.'
            })
        }
    </script>
</div>
