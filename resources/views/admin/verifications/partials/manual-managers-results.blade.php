@if ($managerSearch === '')
    <p class="mt-4 text-xs text-gray-400">Type to search claimed profiles.</p>
@else
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm text-gray-200">
            <thead>
                <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                    <th class="px-3 py-2 text-left">Profile</th>
                    <th class="px-3 py-2 text-left">Claimed By</th>
                    <th class="px-3 py-2 text-left">Verification</th>
                    <th class="px-3 py-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($manualManagers as $manager)
                    <tr class="border-b border-gray-800/80 align-top">
                        <td class="px-3 py-3 text-xs text-gray-300">
                            <p class="font-semibold text-white">{{ $manager->team_name }}</p>
                            <p>{{ $manager->player_name }}</p>
                            <p>Entry {{ $manager->entry_id }}</p>
                            <a
                                href="{{ route('managers.show', ['entryId' => $manager->entry_id]) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-2 inline-flex rounded bg-primary px-2 py-1 text-[11px] font-semibold text-cyan-200 hover:bg-secondary"
                            >
                                Open Public Profile
                            </a>
                        </td>
                        <td class="px-3 py-3 text-xs text-gray-300">
                            <p class="font-semibold text-white">{{ $manager->user?->name ?? 'Unknown' }}</p>
                            <p>{{ $manager->user?->email ?? 'No email' }}</p>
                            <p class="mt-1 text-gray-500">Claimed {{ optional($manager->claimed_at)->diffForHumans() ?? 'Unknown' }}</p>
                        </td>
                        <td class="px-3 py-3 text-xs text-gray-300">
                            @if ($manager->isVerified())
                                <span class="rounded-full bg-green-900/40 px-2 py-1 text-xs font-semibold text-green-300">Verified</span>
                                <p class="mt-2 text-gray-500">
                                    By {{ $manager->verifiedBy?->name ?? 'Unknown' }}
                                    {{ optional($manager->verified_at)?->diffForHumans() }}
                                </p>
                            @else
                                <span class="rounded-full bg-yellow-900/40 px-2 py-1 text-xs font-semibold text-yellow-300">Not Verified</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            @if ($manager->isVerified())
                                <x-confirm-modal
                                    :action="route('admin.verifications.managers.revoke', $manager)"
                                    method="PATCH"
                                    buttonText="Revoke"
                                    :warning="'Revoke verification for '.$manager->team_name.' (Entry '.$manager->entry_id.')?'"
                                    triggerText="Revoke Verification"
                                    triggerClass="rounded-lg bg-red-700 px-3 py-2 text-xs font-semibold text-white hover:bg-red-600"
                                    title="Revoke manager verification"
                                />
                            @else
                                <x-confirm-modal
                                    :action="route('admin.verifications.managers.verify', $manager)"
                                    method="PATCH"
                                    buttonText="Verify"
                                    :warning="'Verify '.$manager->team_name.' (Entry '.$manager->entry_id.') as a valid claim?'"
                                    triggerText="Verify Profile"
                                    triggerClass="rounded-lg bg-green-700 px-3 py-2 text-xs font-semibold text-white hover:bg-green-600"
                                    title="Verify manager profile"
                                />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-gray-400">No claimed profiles match this search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
