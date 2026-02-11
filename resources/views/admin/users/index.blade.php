<x-app-layout>
    <div class="space-y-6">
        <x-page-title title="Admin Dashboard" />

        @if (session('status'))
            <div class="rounded-xl border border-green-700 bg-green-900/30 px-4 py-3 text-sm text-green-200">
                {{ session('status') }}
            </div>
        @endif

        @if (session('success'))
            <div class="rounded-xl border border-green-700 bg-green-900/30 px-4 py-3 text-sm text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-red-700 bg-red-900/30 px-4 py-3 text-sm text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 ">
            <x-stat-card title="Total Users" :value="$totalUsers" icon="users" />
            <x-stat-card title="Verified Users" :value="$verifiedUsers" icon="user-check" />
            <x-stat-card title="Unverified Users" :value="$unverifiedUsers" icon="user-x" />
            <x-stat-card title="Total Leagues" :value="$totalLeagues" icon="trophy" />
            <x-stat-card title="Total Managers" :value="$totalManagers" icon="briefcase" />
            <x-stat-card title="Claimed Profiles" :value="$claimedManagers" icon="id-card" />
            <x-stat-card title="Suspended Profiles" :value="$suspendedProfiles" icon="shield-x" />
            <x-stat-card title="Open Complaints" :value="$openComplaints" icon="message-circle-warning" />

            @foreach ($usersBySignupMethod as $method => $count)
                <x-stat-card :title="ucfirst($method) . ' Signups'" :value="$count" icon="log-in" />
            @endforeach
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">Admin Tools</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:max-w-2xl">
                <form action="{{ route('run.league.update') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white hover:bg-secondary">
                        Run League Update
                    </button>
                </form>

                <form action="{{ route('admin.data.computeGameweeks') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white hover:bg-secondary">
                        Compute All GW Tables
                    </button>
                </form>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <h2 class="text-lg font-semibold text-white">Users</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">User</th>
                            <th class="px-3 py-2 text-left">League</th>
                            <th class="px-3 py-2 text-left">Account</th>
                            <th class="px-3 py-2 text-left">Profile</th>
                            <th class="px-3 py-2 text-left">Dates</th>
                            <th class="px-3 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-b border-gray-800/80 align-top">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $photo = $user->profile_photo_path;
                                        @endphp

                                        @if ($photo)
                                            @if (Str::startsWith($photo, ['http://', 'https://']))
                                                <img src="{{ $photo }}" alt="Profile" class="h-10 w-10 rounded-full object-cover">
                                            @else
                                                <img src="{{ asset('storage/' . $photo) }}" alt="Profile" class="h-10 w-10 rounded-full object-cover">
                                            @endif
                                        @else
                                            <img src="{{ asset('default-avatar.png') }}" alt="Profile" class="h-10 w-10 rounded-full object-cover">
                                        @endif

                                        <div>
                                            @if ($user->claimed_profile_entry_id)
                                                <a
                                                    href="{{ route('managers.show', ['entryId' => (int) $user->claimed_profile_entry_id]) }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="font-semibold text-white hover:text-accent hover:underline"
                                                >
                                                    {{ ucfirst($user->name ?? 'Unknown') }}
                                                </a>
                                            @else
                                                <p class="font-semibold text-white">{{ ucfirst($user->name ?? 'Unknown') }}</p>
                                            @endif
                                            <p class="text-xs text-gray-400">{{ $user->email ?? 'No email' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <p>{{ $user->league->name ?? 'No League' }}</p>
                                    <p>ID: {{ $user->league->league_id ?? 'N/A' }}</p>
                                    <p>Short Code: {{ $user->league->shortcode ?? '-' }}</p>
                                    <p>Managers: {{ $user->league?->managers_count ?? 0 }}</p>
                                    <p>Sync: {{ $user->league->sync_status ?? '-' }}</p>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <p>Role: {{ ucfirst($user->role ?? 'user') }}</p>
                                    <p>Status: {{ ucfirst($user->status ?? 'unknown') }}</p>
                                    <p>Signup: {{ ucfirst($user->signup_method ?? 'unknown') }}</p>
                                    <p>Claims: {{ (int) ($user->claimed_profiles_count ?? 0) }}</p>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    @if ($user->claimed_profile_entry_id)
                                        <a
                                            href="{{ route('managers.show', ['entryId' => (int) $user->claimed_profile_entry_id]) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="font-semibold text-accent hover:underline"
                                        >
                                            {{ $user->claimed_profile_team_name }}
                                        </a>
                                        <p>{{ $user->claimed_profile_player_name }}</p>
                                        <p class="text-gray-500">
                                            Claimed
                                            {{ $user->claimed_profile_claimed_at ? \Illuminate\Support\Carbon::parse($user->claimed_profile_claimed_at)->diffForHumans() : '-' }}
                                        </p>
                                    @else
                                        <p>-</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    <p>Created: {{ optional($user->created_at)->diffForHumans() ?? '-' }}</p>
                                    <p>Verified: {{ optional($user->email_verified_at)->diffForHumans() ?? 'Not verified' }}</p>
                                    <p>Reminder: {{ optional($user->league_reminder_sent_at)->diffForHumans() ?? 'Never' }}</p>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @include('admin.users.partials.edit')

                                        @if($user->role !== 'admin')
                                            <x-confirm-modal
                                                :action="route('admin.destroy', $user->id)"
                                                method="DELETE"
                                                warning="Are you sure you want to delete this user? This action cannot be undone."
                                                triggerIcon="trash"
                                            />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
