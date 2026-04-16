@component('mail::message')
# Invoice {{ $invoice->invoice_number }}

Hi {{ $invoice->customer?->name ?? 'there' }},

Please find your invoice attached to this email.

@component('mail::table')
| | |
|:---|---:|
| **Invoice Number** | {{ $invoice->invoice_number }} |
| **Issue Date** | {{ $invoice->issue_date?->format('F j, Y') ?? '—' }} |
| **Due Date** | {{ $invoice->due_date?->format('F j, Y') ?? '—' }} |
| **Total Due** | **{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}** |
@endcomponent

@if($invoice->status === 'sent')
Please process the payment before the due date. If you've already paid, kindly disregard this notice.
@endif

If you have any questions regarding this invoice, please don't hesitate to reply to this email.

Thank you for your business!

Regards,<br>
{{ $tenantName }}
@endcomponent