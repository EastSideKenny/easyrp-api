<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        return response()->json(
            Payment::where('tenant_id', $tenant->id)
                ->with('invoice:id,invoice_number,customer_id', 'invoice.customer:id,name')
                ->get()
        );
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($payment->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($payment->load('invoice:id,invoice_number,customer_id', 'invoice.customer:id,name'));
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'in:cash,bank,stripe'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $payment = Payment::create(array_merge($validated, ['tenant_id' => $tenant->id]));

        // Auto-update invoice status to 'paid' when fully paid
        $invoice = Invoice::where('id', $validated['invoice_id'])
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $totalPaid = $invoice->payments()->sum('amount');

        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        }

        return response()->json($payment->load('invoice:id,invoice_number,customer_id', 'invoice.customer:id,name'), 201);
    }
}
