<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $products = Product::where('tenant_id', $tenant->id)->get();

        return response()->json($products);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($product->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($product);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'in:physical,service'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'track_inventory' => ['sometimes', 'boolean'],
            'stock_quantity' => ['sometimes', 'integer'],
            'low_stock_threshold' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $product = Product::create(array_merge($validated, ['tenant_id' => $tenant->id]));

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($product->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'in:physical,service'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'track_inventory' => ['sometimes', 'boolean'],
            'stock_quantity' => ['sometimes', 'integer'],
            'low_stock_threshold' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($product->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }
}
