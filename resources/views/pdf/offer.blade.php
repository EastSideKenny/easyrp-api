<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 36px;
        }

        .company-name {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .doc-label {
            text-align: right;
        }

        .doc-label h1 {
            font-size: 28px;
            font-weight: 700;
            color: #4f46e5;
            letter-spacing: 1px;
        }

        .doc-label .offer-number {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Status badge */
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

        .status-accepted {
            background: #f0fdf4;
            color: #16a34a;
        }

        .status-declined {
            background: #fef2f2;
            color: #dc2626;
        }

        .status-expired {
            background: #fff7ed;
            color: #ea580c;
        }

        /* Meta row */
        .meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 32px;
            gap: 24px;
        }

        .meta-box {
            flex: 1;
        }

        .meta-box .label {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .meta-box .value {
            font-size: 13px;
            color: #1a1a2e;
            line-height: 1.5;
        }

        .meta-box .value strong {
            font-weight: 600;
        }

        /* Dates */
        .dates-row {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
        }

        .date-item {}

        .date-item .label {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .date-item .value {
            font-size: 13px;
            color: #1a1a2e;
        }

        /* Items table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        thead tr {
            background: #4f46e5;
            color: #fff;
        }

        thead th {
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        thead th.right {
            text-align: right;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody td {
            padding: 10px 12px;
            font-size: 13px;
            color: #374151;
            vertical-align: top;
        }

        tbody td.right {
            text-align: right;
        }

        tbody td .sub {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* Totals */
        .totals {
            width: 260px;
            margin-left: auto;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }

        .totals-row:last-child {
            border-bottom: none;
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
            padding-top: 10px;
        }

        .totals-row span:last-child {
            font-weight: 600;
        }

        /* Notes */
        .notes {
            margin-top: 32px;
            padding: 16px;
            background: #f9fafb;
            border-left: 3px solid #4f46e5;
            border-radius: 4px;
        }

        .notes .label {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .notes .value {
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
        <div class="header">
            <div>
                <div class="company-name">{{ $tenant->name }}</div>
            </div>
            <div class="doc-label">
                <h1>OFFER</h1>
                <div class="offer-number">{{ $offer->offer_number }}</div>
                <div style="margin-top:6px;">
                    <span class="status-badge status-{{ $offer->status }}">{{ ucfirst($offer->status) }}</span>
                </div>
            </div>
        </div>

        {{-- Bill To + Dates --}}
        <div class="meta">
            <div class="meta-box">
                <div class="label">Bill To</div>
                <div class="value">
                    @if($offer->customer)
                    <strong>{{ $offer->customer->name }}</strong>
                    @if($offer->customer->email)
                    <br>{{ $offer->customer->email }}
                    @endif
                    @if($offer->customer->phone)
                    <br>{{ $offer->customer->phone }}
                    @endif
                    @if($offer->customer->address_line_1)
                    <br>{{ $offer->customer->address_line_1 }}
                    @endif
                    @if($offer->customer->city)
                    <br>{{ $offer->customer->city }}@if($offer->customer->postal_code), {{ $offer->customer->postal_code }}@endif
                    @endif
                    @if($offer->customer->country)
                    <br>{{ $offer->customer->country }}
                    @endif
                    @else
                    <span style="color:#9ca3af;">—</span>
                    @endif
                </div>
            </div>
            <div style="text-align:right;">
                <div class="date-item" style="margin-bottom:12px;">
                    <div class="label">Issue Date</div>
                    <div class="value">{{ $offer->issue_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="date-item">
                    <div class="label">Valid Until</div>
                    <div class="value" style="{{ $offer->status === 'expired' ? 'color:#dc2626;' : '' }}">
                        {{ $offer->valid_until?->format('d M Y') ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Line Items --}}
        <table>
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
                @foreach($offer->items as $item)
                <tr>
                    <td>
                        {{ $item->description }}
                        @if($item->product)
                        <div class="sub">{{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }} {{ $offer->currency }}</td>
                    <td class="right">{{ $item->tax_rate }}%</td>
                    <td class="right">{{ number_format($item->line_total, 2) }} {{ $offer->currency }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>{{ number_format($offer->subtotal, 2) }} {{ $offer->currency }}</span>
            </div>
            <div class="totals-row">
                <span>Tax</span>
                <span>{{ number_format($offer->tax_total, 2) }} {{ $offer->currency }}</span>
            </div>
            <div class="totals-row">
                <span>Total</span>
                <span>{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
            </div>
        </div>

        {{-- Notes --}}
        @if($offer->notes)
        <div class="notes">
            <div class="label">Notes</div>
            <div class="value">{{ $offer->notes }}</div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            {{ $tenant->name }} &mdash; This offer is valid until {{ $offer->valid_until?->format('d M Y') ?? '—' }}
        </div>

    </div>
</body>

</html>