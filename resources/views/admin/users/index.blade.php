<x-app-layout>
    <x-page-title title="Users" />

    <!-- Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                </div>

                <input type="text" id="searchInput"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Search by name...">


            </div>

        </div>
        <div class="flex gap-3">

            @if(auth()->user()->isAdmin())

            <form action="{{ route('run.league.update') }}" method="POST">
                @csrf
                <button class="btn ">
                    Run League Update <i data-lucide="calendar-sync" class="w-4 h-4 ml-2 "></i>
                </button>
            </form>

            <form action="{{ route('admin.send.league.reminders') }}" method="POST">
                @csrf
                <button type="submit" class="btn">
                    Send  Reminder Emails <i data-lucide="calendar-sync" class="w-4 h-4 ml-2 "></i>
                </button>
            </form>

            @endif



            <!-- Export to Excel Button -->
            <button class="btn">
                <i data-lucide="sheet" class="w-4 h-4 "></i>
            </button>
        </div>

    </div>

    <!-- Table -->
    <x-table :headers="['#', 'User', 'League', 'Status', 'Signup', 'Date']" showActions="false">
        @foreach ($users as $index => $user)
        <x-table.row>
            <x-table.cell>{{ $index + 1 }}</x-table.cell>

            {{-- User --}}
            <x-table.cell>
                <div class="flex items-center gap-3">
                    @php
                    $photo = $user->profile_photo_path;
                    @endphp

                    @if ($photo)
                    @if (Str::startsWith($photo, ['http://', 'https://']))
                    <img src="{{ $photo }}" alt="Profile" class="w-10 h-10 rounded-full object-cover">
                    @else
                    <img src="{{ asset('storage/' . $photo) }}" alt="Profile"
                        class="w-10 h-10 rounded-full object-cover">
                    @endif
                    @else
                    <img src="{{ asset('default-avatar.png') }}" alt="Profile"
                        class="w-10 h-10 rounded-full object-cover">
                    @endif

                    <span>
                        {{ ucfirst($user->name ?? 'Unknown') }} <br>
                        {{ $user->email ?? 'No email' }}
                    </span>
                </div>
            </x-table.cell>

            {{-- League --}}
            <x-table.cell>
                <div class="flex items-center">
                    <div class="ml-4">
                        <div class="text-sm font-medium">
                            {{ $user->league->name ?? 'No League' }}
                        </div>

                        <div class="text-sm font-medium">
                            ID: {{ $user->league->league_id ?? 'N/A' }}
                        </div>

                        <div class="text-sm font-medium">
                            Short Code: {{ $user->league->shortcode ?? '-' }}
                        </div>

                        <div class="text-sm font-medium">
                            Managers: {{ $user->league?->managers?->count() ?? 0 }}
                        </div>

                        <div class="text-sm font-medium">
                            Last Sync: {{ optional($user->league?->last_synced_at)->diffForHumans() ?? 'Never' }}
                        </div>

                        <div class="text-sm font-medium">
                            Sync Status: {{ $user->league->sync_status ?? '-' }}
                        </div>

                        <div class="text-sm font-medium">
                            Sync Msg:
                            {{ $user->league->sync_message ?? '-' }}
                        </div>

                        <div class="text-sm font-medium">
                            Synced: {{ $user->league->synced_managers ?? '-' }}/
                            {{ $user->league->total_managers ?? '-' }} Managers
                        </div>
                    </div>
                </div>
            </x-table.cell>

            {{-- Status --}}
            <x-table.cell>
                <x-status-badge :status="$user->status ?? 'unknown'" />
            </x-table.cell>

            {{-- Signup --}}
            <x-table.cell>
                <div class="flex flex-col">
                    <span class="text-sm font-medium">
                        Method: {{ ucfirst($user->signup_method ?? 'Unknown') }}
                    </span>

                    <span class="text-sm font-medium">
                        Role: {{ ucfirst($user->role ?? 'user') }}
                    </span>

                    <span class="text-sm font-medium">
                        Google ID: {{ $user->google_id ?? 'N/A' }}
                    </span>
                </div>
            </x-table.cell>

            {{-- Date --}}
            <x-table.cell>
                <div class="flex flex-col">
                    <span class="text-sm font-medium">
                        Created:
                        {{ optional($user->created_at)->diffForHumans() ?? 'Unknown' }}
                    </span>

                    <span class="text-sm font-medium">
                        Verified:
                        {{ optional($user->email_verified_at)->diffForHumans() ?? 'Not verified' }}
                    </span>

                     <span class="text-sm font-medium">
                        Reminder:
                        {{ optional($user->league_reminder_sent_at)->diffForHumans() ?? 'Null' }}
                    </span>
                </div>
            </x-table.cell>

            {{-- Actions --}}
            <x-table.cell>
                <div class="flex items-center gap-2">
                    @include('admin.users.partials.edit')

                    @if($user->role !== 'admin')
                    <x-confirm-modal :action="route('admin.destroy', $user->id)" method="DELETE"
                        warning="Are you sure you want to delete this user? This action cannot be undone."
                        triggerIcon="trash" />
                    @endif
                </div>
            </x-table.cell>

        </x-table.row>
        @endforeach
    </x-table>

</x-app-layout>