@component('mail::message')
# Reset Your Password

Hi {{ $userName }},

We received a request to reset your password. Click the button below to choose a new one.

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

This link will expire in 60 minutes.

If you didn't request a password reset, no action is needed — your password will remain unchanged.

Thanks,<br>
{{ config('app.name') }}
@endcomponent