<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferResponseController extends Controller
{
    public function respond(Request $request, Offer $offer): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:accept,decline'],
            'token' => ['required', 'string'],
        ]);

        if (! $offer->token || $request->token !== $offer->token) {
            return response()->json(['message' => 'Invalid or expired token.'], 403);
        }

        if (in_array($offer->status, ['accepted', 'declined'])) {
            return response()->json([
                'message' => "This offer has already been {$offer->status}.",
            ], 422);
        }

        if ($offer->valid_until && now()->greaterThan($offer->valid_until)) {
            $offer->update(['status' => 'expired']);
            return response()->json(['message' => 'This offer has expired.'], 422);
        }

        $status = $request->action === 'accept' ? 'accepted' : 'declined';
        $offer->update(['status' => $status]);

        $invoice = null;
        if ($status === 'accepted' && !$offer->invoice_id) {
            // Create invoice from offer
            $invoice = \App\Models\Invoice::create([
                'tenant_id' => $offer->tenant_id,
                'invoice_number' => 'INV-' . date('Y') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'customer_id' => $offer->customer_id,
                'status' => 'draft',
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $offer->subtotal,
                'tax_total' => $offer->tax_total,
                'total' => $offer->total,
                'currency' => $offer->currency,
                'created_by' => $offer->created_by,
            ]);

            // Copy offer items to invoice items
            foreach ($offer->items as $item) {
                \App\Models\InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'line_total' => $item->line_total,
                ]);
            }

            // Link invoice to offer
            $offer->invoice_id = $invoice->id;
            $offer->save();
        }

        return response()->json([
            'message' => "Offer has been {$status}." . ($invoice ? ' Invoice created.' : ''),
            'offer_number' => $offer->offer_number,
            'status' => $status,
            'invoice_id' => $invoice ? $invoice->id : $offer->invoice_id,
        ]);
    }
}
