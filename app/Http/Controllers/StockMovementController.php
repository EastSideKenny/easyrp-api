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
        $perPage = $request->integer('per_page', 25);

        return response()->json(
            StockMovement::with('product:id,name')
                ->latest()
                ->paginate($perPage)
        );
    }

    public function show(Request $request, StockMovement $stockMovement): JsonResponse
    {
        return response()->json($stockMovement->load('product:id,name'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:tenant.products,id'],
            'type' => ['required', 'in:sale,manual_adjustment'],
            'quantity_change' => ['required', 'integer'],
            'reference_id' => ['nullable', 'integer'],
        ]);

        $movement = StockMovement::create($validated);

        // Update the product's stock_quantity
        $product = Product::findOrFail($validated['product_id']);

        $change = $validated['quantity_change'];

        if ($validated['type'] === 'sale') {
            $product->decrement('stock_quantity', $change);
        } else {
            $product->increment('stock_quantity', $change);
        }

        return response()->json($movement, 201);
    }
}
