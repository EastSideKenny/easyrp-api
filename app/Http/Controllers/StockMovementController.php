<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        return response()->json(StockMovement::where('tenant_id', $tenant->id)->get());
    }

    public function show(Request $request, StockMovement $stockMovement): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($stockMovement->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($stockMovement);
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

        return response()->json($movement, 201);
    }
}
