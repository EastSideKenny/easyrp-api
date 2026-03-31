<?php

namespace App\Http\Controllers;

use App\Mail\OfferMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OfferPdfService;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OfferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $query = Offer::where('tenant_id', $tenant->id)
            ->with('customer:id,name');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $perPage = $request->integer('per_page', 25);

        return response()->json($query->latest()->paginate($perPage));
    }

    public function show(Request $request, Offer $offer): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($offer->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $offer->load(['items.product', 'customer', 'invoice:id,invoice_number']);

        return response()->json($offer);
    }

    public function store(Request $request, OfferPdfService $pdfService, PlanLimitService $limits): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($limits->isAtLimit($tenant, 'offers')) {
            return response()->json([
                'message'  => 'You have reached the offer limit for your plan. Please upgrade to add more.',
                'error'    => 'limit_reached',
                'resource' => 'offers',
                'limit'    => $limits->getLimit($tenant, 'offers'),
                'used'     => $limits->getUsage($tenant, 'offers'),
            ], 403);
        }

        $tenantId = $tenant->id;

        $validated = $request->validate([
            'customer_id'       => ['nullable', "exists:customers,id,tenant_id,{$tenantId}"],
            'issue_date'        => ['required', 'date'],
            'valid_until'       => ['required', 'date', 'after_or_equal:issue_date'],
            'currency'          => ['sometimes', 'nullable', 'string', 'size:3'],
            'notes'             => ['sometimes', 'nullable', 'string', 'max:2000'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate'    => ['sometimes', 'numeric', 'min:0'],
            'items.*.line_total'  => ['required', 'numeric', 'min:0'],
        ]);

        $offerNumber = 'OFR-' . date('Y') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $items    = collect($validated['items']);
        $subtotal = $items->sum(fn($i) => $i['unit_price'] * $i['quantity']);
        $taxTotal = $items->sum(fn($i) => $i['unit_price'] * $i['quantity'] * (($i['tax_rate'] ?? 0) / 100));
        $total    = $subtotal + $taxTotal;

        $offer = DB::transaction(function () use ($validated, $tenant, $request, $offerNumber, $subtotal, $taxTotal, $total) {
            $offer = Offer::create([
                'tenant_id'    => $tenant->id,
                'offer_number' => $offerNumber,
                'customer_id'  => $validated['customer_id'] ?? null,
                'status'       => 'draft',
                'issue_date'   => $validated['issue_date'],
                'valid_until'  => $validated['valid_until'],
                'subtotal'     => round($subtotal, 2),
                'tax_total'    => round($taxTotal, 2),
                'total'        => round($total, 2),
                'currency'     => strtoupper($validated['currency'] ?? $tenant->currency ?? 'USD'),
                'notes'        => $validated['notes'] ?? null,
                'created_by'   => $request->user()->id,
            ]);

            foreach ($validated['items'] as $item) {
                OfferItem::create([
                    'offer_id'    => $offer->id,
                    'product_id'  => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'tax_rate'    => $item['tax_rate'] ?? 0,
                    'line_total'  => $item['line_total'],
                ]);
            }

            return $offer;
        });

        // Generate PDF after transaction so all items exist.
        $pdfError = null;
        try {
            $offer = $pdfService->generate($offer);
        } catch (\Throwable $e) {
            $pdfError = $e->getMessage();
            report($e);
        }

        $offer->load(['items.product', 'customer']);

        $response = $offer->toArray();
        if ($pdfError !== null) {
            $response['pdf_warning'] = 'PDF generation failed: ' . $pdfError;
        }

        return response()->json($response, 201);
    }

    public function update(Request $request, Offer $offer, OfferPdfService $pdfService): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($offer->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if ($offer->status === 'accepted') {
            return response()->json([
                'message' => 'Accepted offers cannot be edited.',
                'error'   => 'offer_locked',
                'status'  => $offer->status,
            ], 422);
        }

        $tenantId = $tenant->id;

        $validated = $request->validate([
            'customer_id'       => ['sometimes', 'nullable', "exists:customers,id,tenant_id,{$tenantId}"],
            'status'            => ['sometimes', 'in:draft,sent,accepted,declined,expired'],
            'issue_date'        => ['sometimes', 'date'],
            'valid_until'       => ['sometimes', 'date'],
            'currency'          => ['sometimes', 'nullable', 'string', 'size:3'],
            'notes'             => ['sometimes', 'nullable', 'string', 'max:2000'],
            'items'             => ['sometimes', 'array', 'min:1'],
            'items.*.product_id'  => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity'    => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required_with:items', 'numeric', 'min:0'],
            'items.*.tax_rate'    => ['sometimes', 'numeric', 'min:0'],
            'items.*.line_total'  => ['required_with:items', 'numeric', 'min:0'],
        ]);

        $regeneratePdf = false;

        DB::transaction(function () use (&$offer, &$regeneratePdf, $validated) {
            $scalarFields = collect($validated)->except('items')->toArray();

            if (isset($scalarFields['currency'])) {
                $scalarFields['currency'] = strtoupper($scalarFields['currency']);
            }

            if ($scalarFields) {
                $offer->update($scalarFields);
            }

            if (isset($validated['items'])) {
                $items    = collect($validated['items']);
                $subtotal = $items->sum(fn($i) => $i['unit_price'] * $i['quantity']);
                $taxTotal = $items->sum(fn($i) => $i['unit_price'] * $i['quantity'] * (($i['tax_rate'] ?? 0) / 100));
                $total    = $subtotal + $taxTotal;

                $offer->items()->delete();

                foreach ($validated['items'] as $item) {
                    OfferItem::create([
                        'offer_id'    => $offer->id,
                        'product_id'  => $item['product_id'] ?? null,
                        'description' => $item['description'],
                        'quantity'    => $item['quantity'],
                        'unit_price'  => $item['unit_price'],
                        'tax_rate'    => $item['tax_rate'] ?? 0,
                        'line_total'  => $item['line_total'],
                    ]);
                }

                $offer->update([
                    'subtotal'  => round($subtotal, 2),
                    'tax_total' => round($taxTotal, 2),
                    'total'     => round($total, 2),
                ]);

                $regeneratePdf = true;
            }
        });

        $offer->refresh();

        if ($regeneratePdf) {
            $pdfError = null;
            try {
                $offer = $pdfService->generate($offer);
            } catch (\Throwable $e) {
                $pdfError = $e->getMessage();
                report($e);
            }
        }

        // Send offer email to customer when status changes to 'sent'
        if (isset($validated['status']) && $validated['status'] === 'sent' && $offer->customer?->email) {
            if (! $offer->token) {
                $offer->update(['token' => Str::random(64)]);
            }
            $offer->load('items');
            Mail::to($offer->customer->email)->queue(new OfferMail($offer));
        }

        $offer->load(['items.product', 'customer']);

        $response = $offer->toArray();
        if (isset($pdfError) && $pdfError !== null) {
            $response['pdf_warning'] = 'PDF generation failed: ' . $pdfError;
        }

        return response()->json($response);
    }

    public function destroy(Request $request, Offer $offer, OfferPdfService $pdfService): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($offer->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if ($offer->status === 'accepted') {
            return response()->json([
                'message' => 'Accepted offers cannot be deleted.',
                'error'   => 'offer_locked',
                'status'  => $offer->status,
            ], 422);
        }

        $pdfService->delete($offer);
        $offer->delete();

        return response()->json(['message' => 'Offer deleted.']);
    }

    public function send(Request $request, Offer $offer): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($offer->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if (in_array($offer->status, ['accepted', 'declined'])) {
            return response()->json([
                'message' => "Cannot send an offer that is already {$offer->status}.",
            ], 422);
        }

        if (! $offer->customer?->email) {
            return response()->json([
                'message' => 'Offer has no customer with an email address.',
            ], 422);
        }

        if (! $offer->token) {
            $offer->update(['token' => Str::random(64)]);
        }

        $offer->update(['status' => 'sent']);
        $offer->load(['items', 'customer']);

        // Ensure PDF exists before sending
        $pdfService = app(\App\Services\OfferPdfService::class);
        if (!$offer->pdf_path || !\Storage::disk('public')->exists($offer->pdf_path)) {
            $pdfService->generate($offer);
        }

        // DEBUG: Send synchronously to test logging and attachment
        Mail::to($offer->customer->email)->send(new OfferMail($offer));

        return response()->json([
            'message' => 'Offer sent to ' . $offer->customer->email,
            'offer' => $offer,
        ]);
    }

    public function convertToInvoice(Request $request, Offer $offer): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($offer->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if ($offer->invoice_id) {
            return response()->json([
                'message'    => 'This offer has already been converted to an invoice.',
                'invoice_id' => $offer->invoice_id,
            ], 422);
        }

        $offer->loadMissing('items');

        $invoice = DB::transaction(function () use ($offer, $tenant, $request) {
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'tenant_id'      => $tenant->id,
                'invoice_number' => $invoiceNumber,
                'customer_id'    => $offer->customer_id,
                'status'         => 'draft',
                'issue_date'     => now()->toDateString(),
                'due_date'       => now()->addDays(30)->toDateString(),
                'subtotal'       => $offer->subtotal,
                'tax_total'      => $offer->tax_total,
                'total'          => $offer->total,
                'currency'       => $offer->currency,
                'created_by'     => $request->user()->id,
            ]);

            foreach ($offer->items as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item->product_id,
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'tax_rate'    => $item->tax_rate,
                    'line_total'  => $item->line_total,
                ]);
            }

            $offer->update([
                'invoice_id' => $invoice->id,
                'status'     => 'accepted',
            ]);

            return $invoice;
        });

        return response()->json([
            'message' => 'Offer converted to invoice.',
            'invoice' => $invoice->load('items'),
        ], 201);
    }

    public function pdf(Request $request, Offer $offer, OfferPdfService $pdfService): Response
    {
        $tenant = $request->user()->tenant;

        if ($offer->tenant_id !== $tenant->id) {
            abort(404);
        }

        // Regenerate if somehow missing.
        if (! $offer->pdf_path || ! Storage::disk('public')->exists($offer->pdf_path)) {
            $offer = $pdfService->generate($offer);
        }

        $contents = Storage::disk('public')->get($offer->pdf_path);
        $filename  = $offer->offer_number . '.pdf';

        return response($contents, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function accept(Request $request, Offer $offer): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($offer->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if (in_array($offer->status, ['accepted', 'declined'])) {
            return response()->json([
                'message' => "Offer is already {$offer->status} and cannot be accepted.",
            ], 422);
        }

        $offer->loadMissing('items');

        $invoice = DB::transaction(function () use ($offer, $tenant, $request) {
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'tenant_id'      => $tenant->id,
                'invoice_number' => $invoiceNumber,
                'customer_id'    => $offer->customer_id,
                'status'         => 'draft',
                'issue_date'     => now()->toDateString(),
                'due_date'       => now()->addDays(30)->toDateString(),
                'subtotal'       => $offer->subtotal,
                'tax_total'      => $offer->tax_total,
                'total'          => $offer->total,
                'currency'       => $offer->currency,
                'created_by'     => $request->user()->id,
            ]);

            foreach ($offer->items as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item->product_id,
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'tax_rate'    => $item->tax_rate,
                    'line_total'  => $item->line_total,
                ]);
            }

            $offer->update([
                'status'     => 'accepted',
                'invoice_id' => $invoice->id,
            ]);

            return $invoice;
        });

        $invoice->load('items');

        return response()->json([
            'message' => 'Offer accepted. Invoice has been created.',
            'invoice' => $invoice,
        ], 201);
    }
}
