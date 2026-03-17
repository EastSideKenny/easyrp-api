<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        return response()->json(Invoice::where('tenant_id', $tenant->id)->get());
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($invoice->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $invoice->load('items', 'customer', 'payments');

        return response()->json($invoice);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'status' => ['sometimes', 'in:draft,sent,paid,canceled'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'subtotal' => ['sometimes', 'numeric', 'min:0'],
            'tax_total' => ['sometimes', 'numeric', 'min:0'],
            'total' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ]);

        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $invoice = Invoice::create(array_merge($validated, [
            'tenant_id' => $tenant->id,
            'invoice_number' => $invoiceNumber,
            'created_by' => $request->user()->id,
        ]));

        return response()->json($invoice, 201);
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($invoice->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'status' => ['sometimes', 'in:draft,sent,paid,canceled'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'subtotal' => ['sometimes', 'numeric', 'min:0'],
            'tax_total' => ['sometimes', 'numeric', 'min:0'],
            'total' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ]);

        $invoice->update($validated);

        return response()->json($invoice);
    }

    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($invoice->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted.']);
    }

    public function pay(Request $request, Invoice $invoice): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($invoice->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Invoice is already paid.'], 422);
        }

        $validated = $request->validate([
            'payment_method' => ['sometimes', 'in:cash,bank,stripe'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($invoice, $tenant, $validated) {
            $invoice->update(['status' => 'paid']);

            Payment::create([
                'tenant_id' => $tenant->id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'paid_at' => now(),
            ]);

            foreach ($invoice->items()->with('product')->get() as $item) {
                $product = $item->product;
                if (! $product || $product->type !== 'physical' || ! $product->track_inventory) {
                    continue;
                }

                StockMovement::create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'type' => 'sale',
                    'quantity_change' => -$item->quantity,
                    'reference_id' => $invoice->id,
                ]);

                $product->decrement('stock_quantity', $item->quantity);
            }
        });

        return response()->json(['message' => 'Invoice marked as paid.', 'invoice' => $invoice->fresh()]);
    }
}
