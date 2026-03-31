@component('mail::message')
# Order Confirmed

Hi {{ $order->customer?->name ?? 'there' }},

Thank you for your order! Here's a summary of **{{ $order->order_number }}**:

@component('mail::table')
| Description | Qty | Unit Price | Total |
|:------------|----:|-----------:|------:|
@foreach ($order->items as $item)
| {{ $item->description }} | {{ $item->quantity }} | {{ number_format($item->unit_price, 2) }} {{ $order->currency }} | {{ number_format($item->line_total, 2) }} {{ $order->currency }} |
@endforeach
@endcomponent

@component('mail::table')
| | |
|:---|---:|
| **Subtotal** | {{ number_format($order->subtotal, 2) }} {{ $order->currency }} |
| **Tax** | {{ number_format($order->tax_total, 2) }} {{ $order->currency }} |
| **Total** | **{{ number_format($order->total, 2) }} {{ $order->currency }}** |
@endcomponent

We'll keep you updated on the progress of your order. If you have any questions, simply reply to this email.

Thank you for your business!

Regards,<br>
{{ config('app.name') }}
@endcomponent