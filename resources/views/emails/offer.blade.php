@component('mail::message')
# Offer {{ $offer->offer_number }}

Hi {{ $offer->customer?->name ?? 'there' }},

We're pleased to present you with the following offer. A detailed PDF is attached for your records.

@component('mail::table')
| | |
|:---|---:|
| **Offer Number** | {{ $offer->offer_number }} |
| **Issue Date** | {{ $offer->issue_date?->format('F j, Y') ?? '—' }} |
| **Valid Until** | {{ $offer->valid_until?->format('F j, Y') ?? '—' }} |
| **Total** | **{{ number_format($offer->total, 2) }} {{ $offer->currency }}** |
@endcomponent

You can respond to this offer directly using the buttons below:

@component('mail::button', ['url' => $acceptUrl, 'color' => 'success'])
Accept Offer
@endcomponent

@component('mail::button', ['url' => $declineUrl, 'color' => 'error'])
Decline Offer
@endcomponent

If you have any questions or would like to discuss modifications, simply reply to this email.

We look forward to working with you!

Regards,<br>
{{ config('app.name') }}
@endcomponent