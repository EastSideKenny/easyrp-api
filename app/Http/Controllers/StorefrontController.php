<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Tenant;
use App\Models\WebshopSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Get public webshop settings for a store.
     * Schema is already set by the SetTenantSchema middleware via subdomain.
     */
    public function settings(string $subdomain): JsonResponse
    {
        $settings = WebshopSetting::first();

        if (! $settings) {
            return response()->json(['message' => 'Store not configured.'], 404);
        }

        return response()->json($settings);
    }

    /**
     * List active products for a store.
     */
    public function products(Request $request, string $subdomain): JsonResponse
    {
        $query = Product::where('is_active', true);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        if ($categoryId = $request->query('category')) {
            $query->whereHas('categories', fn($q) => $q->where('product_categories.id', $categoryId));
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $products = $query->with('categories:id,name')->orderBy('name')->get()
            ->map(fn(Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'sku' => $product->sku,
                'type' => $product->type,
                'price' => round((float) $product->price, 2),
                'tax_rate' => round((float) $product->tax_rate, 2),
                'in_stock' => ! $product->track_inventory || $product->stock_quantity > 0,
                'stock_quantity' => $product->track_inventory ? $product->stock_quantity : null,
                'categories' => $product->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
            ]);

        return response()->json($products);
    }

    /**
     * Show a single product for a store.
     */
    public function product(string $subdomain, Product $product): JsonResponse
    {
        if (! $product->is_active) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'sku' => $product->sku,
            'type' => $product->type,
            'price' => round((float) $product->price, 2),
            'tax_rate' => round((float) $product->tax_rate, 2),
            'in_stock' => ! $product->track_inventory || $product->stock_quantity > 0,
            'stock_quantity' => $product->track_inventory ? $product->stock_quantity : null,
            'categories' => $product->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
        ]);
    }

    /**
     * List product categories for a store.
     */
    public function categories(string $subdomain): JsonResponse
    {
        $categories = ProductCategory::orderBy('name')->get(['id', 'name']);

        return response()->json($categories);
    }
}
