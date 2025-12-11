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

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">
            <a href="{{ str_replace('/embed/', '/watch?v=', $youtubeLink) }}" target="_blank" style="text-decoration:none;">
                <img 
                    src="https://img.youtube.com/vi/{{ substr($youtubeLink, strrpos($youtubeLink, '/') + 1) }}/hqdefault.jpg"
                    alt="Watch Video"
                    style="display:block; width:100%; max-width:600px; height:auto; margin:0 auto; border-radius:8px;"
                >
            </a>
        </td>
    </tr>
</table>

If you have any questions, feel free to reply to this email.

Thanks,  
{{ $appName }} Team

@endcomponent
