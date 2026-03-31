@component('mail::message')
# Welcome to {{ config('app.name') }}!

Hi {{ $user->name }},

Your account has been created and you're all set to get started. We're excited to have you on board!

Here's what you can do next:
- **Set up your company profile** — Add your business details
- **Add your first product** — Start building your catalog
- **Create your first invoice** — Get paid faster

@component('mail::button', ['url' => config('app.frontend_url')])
Go to Dashboard
@endcomponent

If you have any questions or need support, simply reply to this email — we're happy to help.

Welcome aboard!

Regards,<br>
{{ config('app.name') }}
@endcomponent