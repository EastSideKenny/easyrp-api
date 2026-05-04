<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;

class PlanLimitService
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

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
     * Get the limit for a feature on the tenant's active plan.
     * Returns null when the feature is unlimited, 0 when the tenant has no access.
     */
    public function getLimit(Tenant $tenant, string $featureCode): ?int
    {
        if (! $this->subscriptions->tenantHasActiveAccess($tenant)) {
            return 0;
        }

        $plan = $this->subscriptions->resolveActivePlan($tenant);

        if (! $plan) {
            return 0;
        }

        $feature = $plan->features->firstWhere('code', $featureCode);

        if (! $feature) {
            return 0;
        }

        return $feature->pivot->limit;
    }

    public function getUsage(Tenant $tenant, string $featureCode): int
    {
        $model = self::MODEL_MAP[$featureCode] ?? null;

        if (! $model) {
            return 0;
        }

        return $model::count();
    }

    public function isAtLimit(Tenant $tenant, string $featureCode): bool
    {
        $limit = $this->getLimit($tenant, $featureCode);

        if ($limit === null) {
            return false;
        }

        return $this->getUsage($tenant, $featureCode) >= $limit;
    }

    /**
     * Per-feature usage snapshot for the tenant's active plan. Empty when
     * the tenant has no active subscription.
     */
    public function getUsageSnapshot(Tenant $tenant): array
    {
        if (! $this->subscriptions->tenantHasActiveAccess($tenant)) {
            return [];
        }

        $plan = $this->subscriptions->resolveActivePlan($tenant);

        if (! $plan) {
            return [];
        }

        $snapshot = [];

        foreach ($plan->features as $feature) {
            $code = $feature->code;

            if (! array_key_exists($code, self::MODEL_MAP)) {
                continue;
            }

            $limit = $feature->pivot->limit;
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
