@component('mail::message')
# sup {{ $data['username'] }},

**welcome to twitir!** please click the button below to verify your email

@component('mail::button', ['url' => $data['verify_link']])
verify email
@endcomponent

thanks,<br>
caltron @ {{ config('app.name') }}
@endcomponent
