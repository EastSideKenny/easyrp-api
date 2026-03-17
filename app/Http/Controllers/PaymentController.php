<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        return response()->json(Payment::where('tenant_id', $tenant->id)->get());
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($payment->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($payment);
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

        return response()->json($payment, 201);
    }
}
