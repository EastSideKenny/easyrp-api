@component('mail::message')
# Payment Received

Hi {{ $invoice->customer?->name ?? 'there' }},

Great news — we've successfully received your payment! Here are the details:

@component('mail::table')
| | |
|:---|---:|
| **Invoice** | {{ $invoice->invoice_number }} |
| **Amount Paid** | **{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}** |
| **Payment Date** | {{ now()->format('F j, Y') }} |
| **Status** | Paid |
@endcomponent

No further action is required. If you need a copy of your invoice, it was included with the original invoice email.

Thank you for your prompt payment!

Regards,<br>
{{ config('app.name') }}
@endcomponent