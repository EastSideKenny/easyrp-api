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
        $query = Payment::with('invoice:id,invoice_number,customer_id,total', 'invoice.customer:id,name');

        if ($request->filled('customer_id')) {
            $query->whereHas('invoice', function ($q) use ($request) {
                $q->where('customer_id', $request->integer('customer_id'));
            });
        }

        $perPage = $request->integer('per_page', 25);

        return response()->json($query->latest()->paginate($perPage));
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        return response()->json($payment->load('invoice:id,invoice_number,customer_id,total,due_date', 'invoice.customer:id,name'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:tenant.invoices,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'in:cash,bank,stripe'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $payment = Payment::create($validated);

        // Auto-update invoice status to 'paid' when fully paid
        $invoice = Invoice::findOrFail($validated['invoice_id']);

        $totalPaid = $invoice->payments()->sum('amount');

        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);

            // Mark attached order as paid
            if ($invoice->order_id) {
                $invoice->order()->update(['status' => 'paid', 'payment_status' => 'paid']);
            }
        }

        return response()->json($payment->load('invoice:id,invoice_number,customer_id', 'invoice.customer:id,name'), 201);
    }

    public function destroy(Request $request, Payment $payment): JsonResponse
    {
        return response()->json([
            'message' => 'Payments cannot be deleted. They form part of the financial audit trail.',
            'error'   => 'payment_protected',
        ], 422);
    }
}
