@component('mail::message')
# League Update

Hello,

Your league update command has just started running.

@component('mail::panel')
**Started:** {{ now()->toDayDateTimeString() }}
@endcomponent

Regards,  
{{ config('app.name') }} Team

@component('mail::subcopy')
© 2025 {{ config('app.name') }}. All rights reserved.
@endcomponent
@endcomponent
