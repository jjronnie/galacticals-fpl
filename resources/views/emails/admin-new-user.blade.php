@component('mail::message')
# New User Registered

A new user has signed up to **{{ $appName }}**.

Here are the details:

@component('mail::table')
| Detail | Value |
| :------------- | :------------- |
| **Name** | {{ $user->name }} |
| **Email** | {{ $user->email }} |
| **Sign Up Method** | {{ $user->signup_method }} |
| **Status** | <span style="color: green; font-weight: bold;">{{ ucfirst($user->status) }}</span> |
| **Role** | {{ ucfirst($user->role) }} |
@endcomponent

@component('mail::button', ['url' => route('dashboard')])
View User Profile
@endcomponent

Thanks,<br>
Admin System Notifications
@endcomponent