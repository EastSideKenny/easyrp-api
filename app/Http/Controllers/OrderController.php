<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $order->load('items', 'customer', 'invoices.payments');

        return response()->json($order);
    }

    public function store(Request $request, PlanLimitService $limits): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($limits->isAtLimit($tenant, 'orders')) {
            return response()->json([
                'message'  => 'You have reached the order limit for your plan. Please upgrade to add more.',
                'error'    => 'limit_reached',
                'resource' => 'orders',
                'limit'    => $limits->getLimit($tenant, 'orders'),
                'used'     => $limits->getUsage($tenant, 'orders'),
            ], 403);
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'status' => ['sometimes', 'in:pending,paid,done,canceled'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'payment_status' => ['sometimes', 'in:pending,paid,failed'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['sometimes', 'numeric', 'min:0'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
        ]);

        $orderNumber = 'ORD-' . date('Y') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $items = collect($validated['items']);
        $subtotal = $items->sum(fn($item) => $item['unit_price'] * $item['quantity']);
        $taxTotal = $items->sum(fn($item) => $item['unit_price'] * $item['quantity'] * (($item['tax_rate'] ?? 0) / 100));
        $total = $subtotal + $taxTotal;

        $order = DB::transaction(function () use ($validated, $tenant, $orderNumber, $subtotal, $taxTotal, $total) {
            $order = Order::create([
                'tenant_id' => $tenant->id,
                'order_number' => $orderNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'status' => $validated['status'] ?? 'pending',
                'subtotal' => round($subtotal, 2),
                'tax_total' => round($taxTotal, 2),
                'total' => round($total, 2),
                'currency' => $validated['currency'] ?? 'USD',
                'payment_status' => $validated['payment_status'] ?? 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'line_total' => $item['line_total'],
                ]);
            }

            return $order;
        });

        return response()->json($order->load('items', 'customer'), 201);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'status' => ['sometimes', 'in:pending,paid,done,canceled'],
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
