<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #1a1a2e;
            background: #fff;
        }

        .page {
            padding: 40px 48px;
        }

        .layout-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-bottom: 32px;
        }

        .layout-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .company-name {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .doc-title {
            font-size: 28px;
            font-weight: 700;
            color: #4f46e5;
            letter-spacing: 1px;
            text-align: right;
        }

        .doc-number {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-draft {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-sent {
            background: #eff6ff;
            color: #3b82f6;
        }

        .status-paid {
            background: #f0fdf4;
            color: #16a34a;
        }

        .status-canceled {
            background: #fef2f2;
            color: #dc2626;
        }

        .section-label {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .section-value {
            font-size: 13px;
            color: #1a1a2e;
            line-height: 1.5;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .items-table thead tr {
            background: #4f46e5;
            color: #fff;
        }

        .items-table thead th {
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .items-table thead th.right {
            text-align: right;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .items-table tbody td {
            padding: 10px 12px;
            font-size: 13px;
            color: #374151;
            vertical-align: top;
        }

        .items-table tbody td.right {
            text-align: right;
        }

        .sub-text {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* Totals table */
        .totals-table {
            width: 260px;
            margin-left: auto;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 6px 0;
            font-size: 13px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }

        .totals-table td.amount {
            text-align: right;
            font-weight: 600;
        }

        .totals-table tr.total-row td {
            border-bottom: none;
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
            padding-top: 10px;
        }

        /* Payment info */
        .payment-info {
            margin-top: 32px;
            padding: 16px;
            background: #f9fafb;
            border-left: 3px solid #4f46e5;
        }

        .payment-label {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .payment-value {
            font-size: 13px;
            color: #374151;
            line-height: 1.6;
        }

        /* Footer */
        .footer {
            margin-top: 48px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="page">

        {{-- Header --}}
        <table class="layout-table">
            <tr>
                <td style="width:50%;">
                    <div class="company-name">{{ $tenant->name }}</div>
                </td>
                <td style="width:50%; text-align:right;">
                    <div class="doc-title">INVOICE</div>
                    <div class="doc-number">{{ $invoice->invoice_number }}</div>
                    <div style="margin-top:6px; text-align:right;">
                        <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Bill To + Dates --}}
        <table class="layout-table">
            <tr>
                <td style="width:50%;">
                    <div class="section-label">Bill To</div>
                    <div class="section-value">
                        @if($invoice->customer)
                        <strong>{{ $invoice->customer->name }}</strong>
                        @if($invoice->customer->email)<br>{{ $invoice->customer->email }}@endif
                        @if($invoice->customer->phone)<br>{{ $invoice->customer->phone }}@endif
                        @if($invoice->customer->address_line_1)<br>{{ $invoice->customer->address_line_1 }}@endif
                        @if($invoice->customer->city)<br>{{ $invoice->customer->city }}@if($invoice->customer->postal_code), {{ $invoice->customer->postal_code }}@endif @endif
                        @if($invoice->customer->country)<br>{{ $invoice->customer->country }}@endif
                        @else
                        <span style="color:#9ca3af;">&mdash;</span>
                        @endif
                    </div>
                </td>
                <td style="width:50%; text-align:right;">
                    <div style="margin-bottom:12px;">
                        <div class="section-label" style="text-align:right;">Issue Date</div>
                        <div class="section-value" style="text-align:right;">{{ $invoice->issue_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="section-label" style="text-align:right;">Due Date</div>
                        <div class="section-value" style="text-align:right; {{ $invoice->status !== 'paid' && $invoice->due_date?->isPast() ? 'color:#dc2626;' : '' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Line Items --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:40%;">Description</th>
                    <th class="right" style="width:10%;">Qty</th>
                    <th class="right" style="width:18%;">Unit Price</th>
                    <th class="right" style="width:12%;">Tax %</th>
                    <th class="right" style="width:20%;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        {{ $item->description ?? $item->product?->name ?? '—' }}
                        @if($item->product?->sku)
                        <div class="sub-text">{{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }} {{ $invoice->currency }}</td>
                    <td class="right">{{ $item->tax_rate }}%</td>
                    <td class="right">{{ number_format($item->line_total, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td class="amount">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td class="amount">{{ number_format($invoice->tax_total, 2) }} {{ $invoice->currency }}</td>
            </tr>
            <tr class="total-row">
                <td>Total</td>
                <td class="amount">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
            </tr>
        </table>

        {{-- Payment Note --}}
        <div class="payment-info">
            <div class="payment-label">Payment Terms</div>
            <div class="payment-value">
                @if($invoice->status === 'paid')
                This invoice has been paid in full. Thank you!
                @else
                Please process payment by {{ $invoice->due_date?->format('d M Y') ?? 'the agreed date' }}.
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            {{ $tenant->name }} &mdash; Invoice {{ $invoice->invoice_number }}
            @if($invoice->due_date)
            &mdash; Due {{ $invoice->due_date->format('d M Y') }}
            @endif
        </div>

    </div>
</body>

</html>