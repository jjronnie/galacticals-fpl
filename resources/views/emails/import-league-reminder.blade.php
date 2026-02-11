@component('mail::message')
# Complete Your FPL Setup

Hello {{ $user->name }},

You created an account on **{{ $appName }}**, but your setup is still incomplete.

To get started, complete at least one of these:

- Import your league
- Add your personal profile by searching and claiming your FPL team

@component('mail::button', ['url' => route('find')])
Find My League ID
@endcomponent

@component('mail::button', ['url' => route('profile.search')])
Add Personal Profile
@endcomponent

If you need help, reply to this email and we will assist you.

Thanks,
{{ $appName }} Team
@endcomponent
