<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        return response()->json(Order::where('tenant_id', $tenant->id)->get());
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $order->load('items', 'customer');

        return response()->json($order);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'status' => ['sometimes', 'in:pending,paid,canceled'],
            'subtotal' => ['sometimes', 'numeric', 'min:0'],
            'tax_total' => ['sometimes', 'numeric', 'min:0'],
            'total' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'payment_status' => ['sometimes', 'in:pending,paid,failed'],
        ]);

        $orderNumber = 'ORD-' . date('Y') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $order = Order::create(array_merge($validated, [
            'tenant_id' => $tenant->id,
            'order_number' => $orderNumber,
        ]));

        return response()->json($order, 201);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'status' => ['sometimes', 'in:pending,paid,canceled'],
            'subtotal' => ['sometimes', 'numeric', 'min:0'],
            'tax_total' => ['sometimes', 'numeric', 'min:0'],
            'total' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'payment_status' => ['sometimes', 'in:pending,paid,failed'],
        ]);

        $order->update($validated);

        return response()->json($order);
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted.']);
    }
}
