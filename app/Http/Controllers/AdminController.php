<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Cashier\Subscription;
use Stripe\Subscription as StripeSubscription;

class AdminController extends Controller
{
    /**
     * Overview stats for the admin dashboard.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'total_tenants'        => Tenant::count(),
            'active_tenants'       => Tenant::where('is_active', true)->count(),
            'total_subscriptions'  => Subscription::count(),
            'active_subscriptions' => Subscription::query()
                ->where('stripe_status', '!=', StripeSubscription::STATUS_CANCELED)
                ->where('stripe_status', '!=', StripeSubscription::STATUS_INCOMPLETE_EXPIRED)
                ->count(),
            'plans'                => Plan::withCount('tenants')->get(['id', 'name', 'slug', 'is_active']),
        ]);
    }

    /**
     * List all tenants with their plan & subscription status.
     */
    public function tenants(Request $request): JsonResponse
    {
        $query = Tenant::with(['plan:id,name,slug', 'subscriptions'])
            ->withCount('users');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('subdomain', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $tenants = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($tenants);
    }

    public function showTenant(Tenant $tenant): JsonResponse
    {
        $tenant->load([
            'plan:id,name,slug,price_monthly,price_yearly',
            'subscriptions',
        ])->loadCount('users');

        return response()->json($tenant);
    }

    /**
     * Update a tenant's plan as the site admin. Free plans only — billing changes
     * for paid plans must come from the tenant via /api/subscriptions to keep Stripe
     * in sync.
     */
    public function updateTenantPlan(Request $request, Tenant $tenant): JsonResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        if ($plan->stripe_product_id) {
            return response()->json([
                'message' => 'Paid plans must be activated by the tenant via the billing flow.',
            ], 422);
        }

        $tenant->forceFill([
            'plan_id'       => $plan->id,
            'trial_ends_at' => null,
        ])->save();

        $tenant->load(['plan:id,name,slug', 'subscriptions']);

        return response()->json([
            'message' => "Plan updated to {$plan->name}.",
            'tenant'  => $tenant,
        ]);
    }

    public function toggleTenantStatus(Tenant $tenant): JsonResponse
    {
        $tenant->update(['is_active' => ! $tenant->is_active]);

        return response()->json([
            'message' => $tenant->is_active ? 'Tenant activated.' : 'Tenant deactivated.',
            'tenant'  => $tenant,
        ]);
    }

    public function plans(): JsonResponse
    {
        $plans = Plan::withCount('tenants')->with('features')->get();

        return response()->json($plans);
    }
}
