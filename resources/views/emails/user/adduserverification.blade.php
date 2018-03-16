@component('mail::message')
# sup {{ $username }},

**you are awesome!** please click the button below to verify your email

@component('mail::button', ['url' => $verify_link])
verify email
@endcomponent

validation key: <{{ $verification_key }}>

thanks,<br>
carlton @ {{ config('app.name') }}
@endcomponent
