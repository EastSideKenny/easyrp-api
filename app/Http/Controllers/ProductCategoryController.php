<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 25);

        return response()->json(
            ProductCategory::orderBy('name')
                ->paginate($perPage)
        );
    }

    public function show(Request $request, ProductCategory $productCategory): JsonResponse
    {
        return response()->json($productCategory);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category = ProductCategory::create($validated);

        return response()->json($category, 201);
    }

    public function update(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $productCategory->update($validated);

        return response()->json($productCategory);
    }

    public function destroy(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $productCategory->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}
