<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Create a new tenant (onboarding).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // User already has a tenant
        if ($user->tenant_id !== null) {
            return response()->json([
                'message' => 'You already have a workspace.',
            ], 409);
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'in:retail,manufacturing,wholesale,services,food,construction,healthcare,technology,agriculture,other'],
            'team_size' => ['nullable', 'string', 'in:solo,2-5,6-20,20+'],
            'role'     => ['nullable', 'string', 'in:owner,manager,accountant,operations,sales,other'],
            'modules'  => ['nullable', 'array'],
            'modules.*' => ['string', 'in:invoices,products,customers,reports,storefront'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        // Generate a unique subdomain from the business name
        $subdomain = $this->generateUniqueSubdomain($validated['name']);

        $freePlan = Plan::where('slug', 'free_trial')->where('is_active', true)->firstOrFail();

        $tenant = Tenant::create([
            'name'      => $validated['name'],
            'slug'      => $subdomain,
            'subdomain' => $subdomain,
            'industry'  => $validated['industry'] ?? null,
            'team_size' => $validated['team_size'] ?? null,
            'modules'   => ! empty($validated['modules']) ? $validated['modules'] : ['invoices', 'products'],
            'currency'  => strtoupper($validated['currency'] ?? 'USD'),
            'plan_id'   => $freePlan->id,
        ]);

        // Create a 14-day free trial subscription
        TenantSubscription::create([
            'tenant_id'    => $tenant->id,
            'plan_id'      => $freePlan->id,
            'status'       => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // Associate user with tenant and set role
        $user->update([
            'tenant_id' => $tenant->id,
            'role'      => $validated['role'] ?? 'owner',
        ]);

        $user->load('tenant');

        return response()->json([
            'user'   => $user,
            'tenant' => $tenant,
        ], 201);
    }

    /**
     * Resolve a tenant by subdomain (public).
     */
    public function resolve(string $subdomain): JsonResponse
    {
        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (! $tenant) {
            return response()->json(['message' => 'Tenant not found.'], 404);
        }

        return response()->json($tenant);
    }

    public function show(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        return response()->json($tenant);
    }

    public function update(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'slug'      => ['sometimes', 'string', 'max:255', 'unique:tenants,slug,' . $tenant->id],
            'subdomain' => ['sometimes', 'string', 'max:255', 'unique:tenants,subdomain,' . $tenant->id],
            'currency'  => ['sometimes', 'string', 'size:3'],
        ]);

        if (isset($validated['currency'])) {
            $validated['currency'] = strtoupper($validated['currency']);
        }

        $tenant->update($validated);

        return response()->json($tenant);
    }

    /**
     * Generate a unique subdomain slug from a business name.
     */
    private function generateUniqueSubdomain(string $name): string
    {
        $base = Str::slug($name);

        // Ensure we have something usable
        if (empty($base)) {
            $base = 'workspace';
        }

        $subdomain = $base;
        $suffix = 2;

        while (Tenant::where('subdomain', $subdomain)->exists()) {
            $subdomain = $base . '-' . $suffix;
            $suffix++;
        }

        return $subdomain;
    }
}
