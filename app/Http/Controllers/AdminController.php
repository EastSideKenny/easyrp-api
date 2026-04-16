<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Overview stats for the admin dashboard.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'total_subscriptions' => TenantSubscription::count(),
            'active_subscriptions' => TenantSubscription::whereIn('status', ['active', 'trialing'])->count(),
            'plans' => Plan::withCount('tenants')->get(['id', 'name', 'slug', 'is_active']),
        ]);
    }

    /**
     * List all tenants with their plan & subscription status.
     * GDPR: only tenant-level data, no user PII (no names, emails, etc.).
     */
    public function tenants(Request $request): JsonResponse
    {
        $query = Tenant::with(['plan:id,name,slug', 'subscriptions:id,tenant_id,plan_id,status,trial_ends_at,current_period_end'])
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

    /**
     * Show a single tenant's details (no user PII).
     */
    public function showTenant(Tenant $tenant): JsonResponse
    {
        $tenant->load([
            'plan:id,name,slug,price_monthly,price_yearly',
            'subscriptions:id,tenant_id,plan_id,status,trial_ends_at,current_period_end',
            'subscriptions.plan:id,name,slug',
        ])->loadCount('users');

        return response()->json($tenant);
    }

    /**
     * Update a tenant's plan by creating/updating their subscription.
     */
    public function updateTenantPlan(Request $request, Tenant $tenant): JsonResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        // Update the tenant's plan_id
        $tenant->update(['plan_id' => $plan->id]);

        // Update or create the subscription
        $subscription = $tenant->subscriptions()->latest()->first();

        if ($subscription) {
            $subscription->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'current_period_end' => null,
                'trial_ends_at' => null,
            ]);
        } else {
            TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
            ]);
        }

        $tenant->load(['plan:id,name,slug', 'subscriptions:id,tenant_id,plan_id,status,trial_ends_at,current_period_end']);

        return response()->json([
            'message' => "Plan updated to {$plan->name}.",
            'tenant' => $tenant,
        ]);
    }

    /**
     * Toggle a tenant's active status.
     */
    public function toggleTenantStatus(Tenant $tenant): JsonResponse
    {
        $tenant->update(['is_active' => !$tenant->is_active]);

        return response()->json([
            'message' => $tenant->is_active ? 'Tenant activated.' : 'Tenant deactivated.',
            'tenant' => $tenant,
        ]);
    }

    /**
     * List all plans (including inactive ones like Exclusive).
     */
    public function plans(): JsonResponse
    {
        $plans = Plan::withCount('tenants')->with('features')->get();

        return response()->json($plans);
    }
}
