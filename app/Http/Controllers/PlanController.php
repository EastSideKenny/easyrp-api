<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::where('is_active', true)->with('features')->get();

        return response()->json($plans);
    }

    public function show(Plan $plan): JsonResponse
    {
        $plan->load('features');

        return response()->json($plan);
    }
}
