@component('mail::message')

# Welcome

Hello {{ $name }},

Thank you for creating an account with **{{ $appName }}**. We are thrilled to have you onboard and hope you enjoy the experience.

To help you get started, especially with setting up or importing your league, we have provided some resources below.

@component('mail::panel')
### Need a quick start?

@component('mail::button', ['url' => $findRoute])
How to Find my League ID
@endcomponent
@endcomponent

### Watch the Instruction Video


[![Video](https://img.youtube.com/vi/{{ substr($youtubeLink, strrpos($youtubeLink, '/') + 1) }}/hqdefault.jpg)]({{ str_replace('/embed/', '/watch?v=', $youtubeLink) }})

If you have any questions, feel free to reply to this email.

Thanks,  
{{ $appName }} Team

@endcomponent
