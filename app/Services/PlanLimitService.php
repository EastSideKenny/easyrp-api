<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;

class PlanLimitService
{
    /**
     * Maps a feature code to the Eloquent model class used to count records.
     */
    private const MODEL_MAP = [
        'customers' => Customer::class,
        'products'  => Product::class,
        'invoices'  => Invoice::class,
        'orders'    => Order::class,
        'offers'    => Offer::class,
    ];

    /**
     * Get the limit for a feature on the tenant's active subscription.
     * Returns null if unlimited, or an integer cap.
     */
    public function getLimit(Tenant $tenant, string $featureCode): ?int
    {
        $activeSub = $tenant->subscriptions()
            ->with('plan.features')
            ->get()
            ->first(fn($sub) => $sub->hasActiveAccess());

        if (! $activeSub) {
            return 0; // no active access at all
        }

        $feature = $activeSub->plan->features
            ->firstWhere('code', $featureCode);

        if (! $feature) {
            return 0; // feature not on this plan
        }

        // null pivot limit = unlimited
        return $feature->pivot->limit;
    }

    /**
     * Get the current count of a resource for the tenant.
     * Uses the tenant schema connection — schema is already switched.
     */
    public function getUsage(Tenant $tenant, string $featureCode): int
    {
        $model = self::MODEL_MAP[$featureCode] ?? null;

        if (! $model) {
            return 0;
        }

        return $model::count();
    }

    /**
     * Returns true if the tenant is at or over the plan limit for a feature.
     * Always returns false (allowed) when the limit is null (unlimited).
     */
    public function isAtLimit(Tenant $tenant, string $featureCode): bool
    {
        $limit = $this->getLimit($tenant, $featureCode);

        if ($limit === null) {
            return false; // unlimited
        }

        return $this->getUsage($tenant, $featureCode) >= $limit;
    }

    /**
     * Build a usage snapshot scoped to features actually on the tenant's active plan.
     * Only features present in MODEL_MAP are included. Returns an array keyed by
     * feature code with 'limit', 'used', and 'remaining'.
     */
    public function getUsageSnapshot(Tenant $tenant): array
    {
        $activeSub = $tenant->subscriptions()
            ->with('plan.features')
            ->get()
            ->first(fn($sub) => $sub->hasActiveAccess());

        if (! $activeSub) {
            return [];
        }

        $snapshot = [];

        foreach ($activeSub->plan->features as $feature) {
            $code = $feature->code;

            // Only include features we can count
            if (! array_key_exists($code, self::MODEL_MAP)) {
                continue;
            }

            $limit = $feature->pivot->limit; // null = unlimited
            $used  = $this->getUsage($tenant, $code);

            $snapshot[$code] = [
                'limit'     => $limit,
                'used'      => $used,
                'remaining' => $limit === null ? null : max(0, $limit - $used),
            ];
        }

        return $snapshot;
    }
}
