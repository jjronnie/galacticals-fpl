@component('mail::message')
# Reminder to  Complete Your League Setup

Hello {{ $user->name }},

We noticed that you created an account on **{{ $appName }}**, but you did not complete the steps required to fully import your league.

To help you complete your setup, click the button below for instructions on how to find your League ID.

@component('mail::button', ['url' => route('find')])
Find My League ID
@endcomponent

If you need any special help or have questions, simply reply to this email and our support team will assist you.

Thanks,  
{{ $appName }} Team
@endcomponent
