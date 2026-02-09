<x-app-layout>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-green-700 bg-green-900/30 px-4 py-3 text-sm text-green-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-700 bg-red-900/30 px-4 py-3 text-sm text-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="rounded-2xl border border-gray-700 bg-card p-6">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-bold text-white">Profile Verification</h1>
                @if ($claimedManager->isVerified())
                    <x-verified-badge />
                @endif
            </div>
            <p class="mt-2 text-sm text-gray-300">
                Team: <span class="font-semibold text-white">{{ $claimedManager->team_name }}</span>
                <span class="text-gray-500">/ Entry {{ $claimedManager->entry_id }}</span>
            </p>
            <p class="mt-2 text-sm text-gray-300">
                Upload one screenshot from the official FPL app while logged in to this team. The team name must be clearly visible.
            </p>
        </section>

        <section class="rounded-2xl border border-cyan-700/50 bg-cyan-900/20 p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Submission Instructions</h2>
            <ul class="mt-3 space-y-2 text-sm text-cyan-100/90">
                <li>1. Open the official Fantasy Premier League app and log in to your team account.</li>
                <li>2. Capture a screenshot where the team name is clearly visible.</li>
                <li>3. Upload only one image. Avoid cropping out the team identity details.</li>
            </ul>
        </section>

        @if ($latestSubmission?->status === 'pending')
            <section class="rounded-2xl border border-amber-600/60 bg-amber-900/20 p-6">
                <h2 class="text-lg font-semibold text-amber-100">Verification Pending</h2>
                <p class="mt-2 text-sm text-amber-100/90">
                    Your verification was submitted on {{ $latestSubmission->created_at?->format('M j, Y g:i A') }}.
                    Please wait for admin review.
                </p>
                <a href="{{ route('profile.index') }}" class="mt-4 inline-flex rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary">
                    Back to Profile
                </a>
            </section>
        @else
            @if ($latestSubmission?->status === 'rejected' && $latestSubmission->rejection_reason)
                <section class="rounded-2xl border border-red-700 bg-red-900/20 p-6">
                    <h2 class="text-lg font-semibold text-red-200">Previous Request Rejected</h2>
                    <p class="mt-2 text-sm text-red-100/90">{{ $latestSubmission->rejection_reason }}</p>
                    <p class="mt-2 text-xs text-red-100/80">Submit a clearer screenshot and try again.</p>
                </section>
            @endif

            <section class="rounded-2xl border border-gray-700 bg-card p-6">
                <form
                    method="POST"
                    action="{{ route('profile.verification.store') }}"
                    enctype="multipart/form-data"
                    class="space-y-5"
                    x-data="{ previewUrl: null, fileName: '' }"
                >
                    @csrf

                    <div class="space-y-2">
                        <label for="verification-screenshot" class="block text-xs font-semibold uppercase tracking-wide text-gray-400">
                            Screenshot (Required)
                        </label>
                        <label for="verification-screenshot" class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-cyan-600/60 bg-primary px-6 py-10 text-center hover:border-cyan-400/70">
                            <i data-lucide="image-up" class="h-8 w-8 text-cyan-300"></i>
                            <span class="text-sm font-semibold text-white">Click to Upload Screenshot</span>
                            <span class="text-xs text-gray-400">JPG, PNG, WEBP up to 5MB</span>
                            <span x-show="fileName" x-text="fileName" class="text-xs font-medium text-cyan-200"></span>
                        </label>
                        <input
                            id="verification-screenshot"
                            name="screenshot"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            class="hidden"
                            required
                            @change="
                                const file = $event.target.files[0];
                                if (!file) { previewUrl = null; fileName = ''; return; }
                                fileName = file.name;
                                previewUrl = URL.createObjectURL(file);
                            "
                        >

                        <div x-show="previewUrl" x-cloak class="rounded-xl border border-gray-700 bg-primary p-3">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Preview</p>
                            <img :src="previewUrl" alt="Screenshot preview" class="max-h-80 w-full rounded-lg object-contain">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="verification-notes" class="block text-xs font-semibold uppercase tracking-wide text-gray-400">
                            Notes (Optional)
                        </label>
                        <textarea
                            id="verification-notes"
                            name="notes"
                            rows="4"
                            maxlength="2000"
                            class="w-full rounded-lg border border-gray-600 bg-primary px-3 py-2 text-sm text-white placeholder:text-gray-400 focus:border-accent focus:ring-accent"
                            placeholder="Add any context that may help admin review quickly."
                        >{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-primary hover:bg-cyan-300">
                            Submit Verification
                        </button>
                        <a href="{{ route('profile.index') }}" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </section>
        @endif
    </div>
</x-app-layout>
