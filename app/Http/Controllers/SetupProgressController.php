<?php

namespace App\Http\Controllers;

use App\Models\SetupProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetupProgressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(SetupProgress::all());
    }

    public function update(Request $request, string $step): JsonResponse
    {
        $validated = $request->validate([
            'is_completed' => ['required', 'boolean'],
        ]);

        $progress = SetupProgress::updateOrCreate(
            ['step' => $step],
            ['is_completed' => $validated['is_completed']]
        );

        return response()->json($progress);
    }
}
