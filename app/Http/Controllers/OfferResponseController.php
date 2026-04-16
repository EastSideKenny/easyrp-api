<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Services\TenantDatabaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfferResponseController extends Controller
{
    public function respond(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:accept,decline'],
            'token' => ['required', 'string'],
        ]);

        // Look up the tenant and offer from the token lookup table
        $lookup = DB::table('offer_token_lookup')
            ->where('token', $request->token)
            ->first();

        if (! $lookup) {
            return response()->json(['message' => 'Invalid or expired token.'], 403);
        }

        // Switch to the tenant's schema
        TenantDatabaseService::switchTo($lookup->tenant_id);

        try {
            $offer = Offer::find($lookup->offer_id);

            if (! $offer || $offer->token !== $request->token) {
                return response()->json(['message' => 'Invalid or expired token.'], 403);
            }

            if ($offer->status === 'expired' || ($offer->valid_until && now()->greaterThan($offer->valid_until))) {
                if ($offer->status !== 'expired') {
                    $offer->update(['status' => 'expired']);
                }
                return response()->json(['message' => 'This offer has expired.', 'reason' => 'expired'], 422);
            }

            if (in_array($offer->status, ['accepted', 'declined'])) {
                return response()->json([
                    'message' => "This offer has already been {$offer->status}.",
                    'reason' => 'already_responded',
                ], 422);
            }

            $invoice = DB::connection('tenant')->transaction(function () use ($request, $offer) {
                $status = $request->action === 'accept' ? 'accepted' : 'declined';
                $offer->update(['status' => $status]);

                // Record the response for audit/proof purposes
                \App\Models\OfferResponse::create([
                    'offer_id' => $offer->id,
                    'action' => $status,
                    'channel' => 'email',
                    'performed_by' => null,
                    'performed_by_email' => $offer->customer?->email,
                    'responded_at' => now(),
                ]);

                if ($status !== 'accepted' || $offer->invoice_id) {
                    return null;
                }

                $invoice = \App\Models\Invoice::create([
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

                foreach ($offer->items as $item) {
                    \App\Models\InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $item->product_id,
                        'description' => $item->description,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_rate' => $item->tax_rate,
                        'line_total' => $item->line_total,
                    ]);
                }

                $offer->update(['invoice_id' => $invoice->id]);

                return $invoice;
            });

            $status = $offer->status;

            return response()->json([
                'message' => "Offer has been {$status}." . ($invoice ? ' Invoice created.' : ''),
                'offer_number' => $offer->offer_number,
                'status' => $status,
                'invoice_id' => $invoice ? $invoice->id : $offer->invoice_id,
            ]);
        } finally {
            TenantDatabaseService::reset();
        }
    }
}
