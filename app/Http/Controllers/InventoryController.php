<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $query = Product::where('tenant_id', $tenant->id)
            ->where('track_inventory', true)
            ->where('is_active', true);

        // Search by product name or SKU
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->query('sort_by', 'name');
        $sortDir = $request->query('sort_dir', 'asc');
        $allowedSorts = ['name', 'type', 'stock_quantity', 'low_stock_threshold', 'cost_price', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $products = $query->get();

        // Build inventory items with computed fields
        $items = $products->map(function (Product $product) {
            $qty = $product->stock_quantity;
            $threshold = $product->low_stock_threshold;
            $cost = $product->cost_price ?? 0;
            $value = $qty * $cost;

            if ($qty <= 0) {
                $status = 'out_of_stock';
            } elseif ($threshold > 0 && $qty <= $threshold) {
                $status = 'low_stock';
            } else {
                $status = 'in_stock';
            }

            return [
                'id' => $product->id,
                'product' => $product->name,
                'sku' => $product->sku,
                'type' => $product->type,
                'qty' => $qty,
                'threshold' => $threshold,
                'cost' => round($cost, 2),
                'value' => round($value, 2),
                'status' => $status,
            ];
        });

        // Summary stats
        $totalSkus = $items->count();
        $totalUnits = $items->sum('qty');
        $lowStockCount = $items->where('status', 'low_stock')->count();
        $outOfStockCount = $items->where('status', 'out_of_stock')->count();

        return response()->json([
            'summary' => [
                'total_skus' => $totalSkus,
                'total_units' => $totalUnits,
                'low_stock' => $lowStockCount,
                'out_of_stock' => $outOfStockCount,
            ],
            'items' => $items->values(),
        ]);
    }
}
