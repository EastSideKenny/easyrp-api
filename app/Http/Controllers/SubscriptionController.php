<?php

namespace App\Http\Controllers;

use App\Models\TenantSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        $subscriptions = TenantSubscription::where('tenant_id', $tenant->id)
            ->with('plan')
            ->get();

        return response()->json($subscriptions);
    }

    public function show(Request $request, TenantSubscription $subscription): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant || $subscription->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $subscription->load('plan');

        return response()->json($subscription);
    }
}
