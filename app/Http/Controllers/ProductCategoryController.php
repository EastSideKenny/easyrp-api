<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $perPage = $request->integer('per_page', 25);

        return response()->json(
            ProductCategory::where('tenant_id', $tenant->id)
                ->orderBy('name')
                ->paginate($perPage)
        );
    }

    public function show(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($productCategory->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($productCategory);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category = ProductCategory::create(array_merge($validated, ['tenant_id' => $tenant->id]));

        return response()->json($category, 201);
    }

    public function update(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($productCategory->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $productCategory->update($validated);

        return response()->json($productCategory);
    }

    public function destroy(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($productCategory->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $productCategory->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}
