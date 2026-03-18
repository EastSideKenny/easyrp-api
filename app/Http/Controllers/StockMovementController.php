<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $perPage = $request->integer('per_page', 25);

        return response()->json(
            StockMovement::where('tenant_id', $tenant->id)
                ->with('product:id,name')
                ->latest()
                ->paginate($perPage)
        );
    }

    public function show(Request $request, StockMovement $stockMovement): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($stockMovement->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($stockMovement->load('product:id,name'));
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:sale,manual_adjustment'],
            'quantity_change' => ['required', 'integer'],
            'reference_id' => ['nullable', 'integer'],
        ]);

        $movement = StockMovement::create(array_merge($validated, ['tenant_id' => $tenant->id]));

        // Update the product's stock_quantity
        $product = Product::where('id', $validated['product_id'])
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $change = $validated['quantity_change'];

        if ($validated['type'] === 'sale') {
            $product->decrement('stock_quantity', $change);
        } else {
            $product->increment('stock_quantity', $change);
        }

        return response()->json($movement, 201);
    }
}
