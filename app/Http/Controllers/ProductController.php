<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 25);

        return response()->json(
            Product::latest()->paginate($perPage)
        );
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        return response()->json($product);
    }

    public function store(Request $request, PlanLimitService $limits): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($limits->isAtLimit($tenant, 'products')) {
            return response()->json([
                'message'  => 'You have reached the product limit for your plan. Please upgrade to add more.',
                'error'    => 'limit_reached',
                'resource' => 'products',
                'limit'    => $limits->getLimit($tenant, 'products'),
                'used'     => $limits->getUsage($tenant, 'products'),
            ], 403);
        }

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

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
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
        $inUse = DB::connection('tenant')->table('order_items')->where('product_id', $product->id)->exists()
            || DB::connection('tenant')->table('invoice_items')->where('product_id', $product->id)->exists()
            || DB::connection('tenant')->table('offer_items')->where('product_id', $product->id)->exists();

        if ($inUse) {
            return response()->json([
                'message' => 'This product cannot be deleted because it is referenced in orders, invoices, or offers.',
                'error'   => 'product_in_use',
            ], 422);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    public function inUse(Request $request, Product $product): JsonResponse
    {
        $inUse = DB::connection('tenant')->table('order_items')->where('product_id', $product->id)->exists()
            || DB::connection('tenant')->table('invoice_items')->where('product_id', $product->id)->exists()
            || DB::connection('tenant')->table('offer_items')->where('product_id', $product->id)->exists();

        return response()->json(['in_use' => $inUse]);
    }
}
