<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Mail\PaymentReceivedMail;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Services\InvoicePdfService;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with('customer:id,name');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        $perPage = $request->integer('per_page', 25);

        return response()->json($query->latest()->paginate($perPage));
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $invoice->load('items', 'customer', 'payments');

        return response()->json($invoice);
    }

    public function store(Request $request, PlanLimitService $limits): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($limits->isAtLimit($tenant, 'invoices')) {
            return response()->json([
                'message'  => 'You have reached the invoice limit for your plan. Please upgrade to add more.',
                'error'    => 'limit_reached',
                'resource' => 'invoices',
                'limit'    => $limits->getLimit($tenant, 'invoices'),
                'used'     => $limits->getUsage($tenant, 'invoices'),
            ], 403);
        }

        $currency = $tenant->currency;

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:tenant.customers,id'],
            'order_id' => ['nullable', 'exists:tenant.orders,id'],
            'status' => ['sometimes', 'in:draft,sent,paid,canceled'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:tenant.products,id'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['sometimes', 'numeric', 'min:0'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
        ]);

        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $items = collect($validated['items']);
        $subtotal = $items->sum(fn($item) => $item['unit_price'] * $item['quantity']);
        $taxTotal = $items->sum(fn($item) => $item['unit_price'] * $item['quantity'] * (($item['tax_rate'] ?? 0) / 100));
        $total = $subtotal + $taxTotal;

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'customer_id' => $validated['customer_id'] ?? null,
            'order_id' => $validated['order_id'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'created_by' => $request->user()->id,
            'issue_date' => $validated['issue_date'] ?? now()->toDateString(),
            'due_date' => $validated['due_date'] ?? now()->addDays(30)->toDateString(),
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'total' => round($total, 2),
            'currency' => $currency ?? 'USD',
        ]);

        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'line_total' => $item['line_total'],
            ]);
        }

        return response()->json($invoice, 201);
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        if (! in_array($invoice->status, ['draft', 'canceled'])) {
            return response()->json([
                'message' => 'Only draft or canceled invoices can be edited.',
                'error'   => 'invoice_locked',
                'status'  => $invoice->status,
            ], 422);
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:tenant.customers,id'],
            'order_id' => ['nullable', 'exists:tenant.orders,id'],
            'status' => ['sometimes', 'in:draft,sent,paid,canceled'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'subtotal' => ['sometimes', 'numeric', 'min:0'],
            'tax_total' => ['sometimes', 'numeric', 'min:0'],
            'total' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ]);

        $invoice->update($validated);

        // Send invoice email to customer when status changes to 'sent'
        if (isset($validated['status']) && $validated['status'] === 'sent' && $invoice->customer?->email) {
            $invoice->load('items');
            Mail::to($invoice->customer->email)->queue(new InvoiceMail($invoice));
        }

        return response()->json($invoice);
    }

    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        if (! in_array($invoice->status, ['draft', 'canceled'])) {
            return response()->json([
                'message' => 'Only draft or canceled invoices can be deleted.',
                'error'   => 'invoice_locked',
                'status'  => $invoice->status,
            ], 422);
        }

        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted.']);
    }

    public function pay(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Invoice is already paid.'], 422);
        }

        $validated = $request->validate([
            'payment_method' => ['sometimes', 'in:cash,bank,stripe'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
        ]);

        DB::connection('tenant')->transaction(function () use ($invoice, $validated) {
            $invoice->update(['status' => 'paid']);

            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'paid_at' => now(),
            ]);

            // Mark attached order as paid
            if ($invoice->order_id) {
                $invoice->order()->update(['status' => 'paid', 'payment_status' => 'paid']);
            }

            foreach ($invoice->items()->with('product')->get() as $item) {
                $product = $item->product;
                if (! $product || $product->type !== 'physical' || ! $product->track_inventory) {
                    continue;
                }

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'sale',
                    'quantity_change' => -$item->quantity,
                    'reference_id' => $invoice->id,
                ]);

                $product->decrement('stock_quantity', $item->quantity);
            }
        });

        // Send payment confirmation email to customer
        $invoice->refresh();
        if ($invoice->customer?->email) {
            Mail::to($invoice->customer->email)->queue(new PaymentReceivedMail($invoice));
        }

        return response()->json(['message' => 'Invoice marked as paid.', 'invoice' => $invoice]);
    }

    public function send(Request $request, Invoice $invoice): JsonResponse
    {
        if (! $invoice->customer || ! $invoice->customer->email) {
            return response()->json(['message' => 'Customer email not found.'], 422);
        }

        $invoice->load('items');

        $invoice->update(['status' => 'sent']);
        Mail::to($invoice->customer->email)->queue(new InvoiceMail($invoice));

        return response()->json(['message' => 'Invoice sent to customer.']);
    }

    public function pdf(Request $request, Invoice $invoice, InvoicePdfService $pdfService): Response
    {
        if (! $invoice->pdf_path || ! Storage::disk('public')->exists($invoice->pdf_path)) {
            $invoice = $pdfService->generate($invoice);
        }

        $contents = Storage::disk('public')->get($invoice->pdf_path);
        $filename = $invoice->invoice_number . '.pdf';

        return response($contents, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
