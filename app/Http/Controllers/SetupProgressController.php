<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetupProgressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        return response()->json($tenant->setupProgress);
    }

    public function update(Request $request, string $step): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        $validated = $request->validate([
            'is_completed' => ['required', 'boolean'],
        ]);

        $progress = $tenant->setupProgress()->updateOrCreate(
            ['step' => $step],
            ['is_completed' => $validated['is_completed']]
        );

        return response()->json($progress);
    }
}
