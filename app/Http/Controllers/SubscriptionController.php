<?php

namespace App\Http\Controllers;

use App\Models\TenantSubscription;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private readonly PlanLimitService $limits) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        $subscriptions = TenantSubscription::where('tenant_id', $tenant->id)
            ->with('plan.features')
            ->get();

        $activeSubscription = $subscriptions->first(fn($sub) => $sub->hasActiveAccess());

        return response()->json([
            'subscriptions' => $subscriptions,
            'usage'         => $this->limits->getUsageSnapshot($tenant),
            'trial_ends_at' => $activeSubscription?->trial_ends_at?->toIso8601String(),
            'status'        => $activeSubscription?->status ?? 'expired',
        ]);
    }

    public function show(Request $request, TenantSubscription $subscription): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant || $subscription->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $subscription->load('plan.features');

        return response()->json([
            'subscription' => $subscription,
            'usage'        => $this->limits->getUsageSnapshot($tenant),
        ]);
    }
}
