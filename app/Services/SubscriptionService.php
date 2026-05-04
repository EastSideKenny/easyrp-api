<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Tenant;
use Carbon\Carbon;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Cashier\Subscription;
use Stripe\Subscription as StripeSubscription;

class SubscriptionService
{
    public const SUBSCRIPTION_TYPE = 'default';

    /**
     * Resolve a local Plan id from one or more Stripe Price ids (subscription line items).
     *
     * @param  array<int, string|null>  $stripePriceIds
     */
    public function resolvePlanIdFromStripePriceIds(array $stripePriceIds): ?int
    {
        foreach (array_unique(array_filter($stripePriceIds)) as $priceId) {
            $plan = Plan::query()
                ->where(function ($q) use ($priceId) {
                    $q->where('stripe_price_monthly_id', $priceId)
                        ->orWhere('stripe_price_yearly_id', $priceId);
                })
                ->first();

            if ($plan) {
                return $plan->id;
            }
        }

        return null;
    }

    /**
     * Keep `subscriptions.plan_id` and `tenants.plan_id` aligned with what Stripe is billing.
     * Used when plans change in the Dashboard or metadata is stale.
     *
     * @param  array<string, mixed>|null  $stripeSubscriptionObject  Webhook/API subscription object; optional.
     */
    public function syncSubscriptionPlanFromStripe(Tenant $tenant, Subscription $subscription, ?array $stripeSubscriptionObject = null): void
    {
        $priceIds = [];

        if ($stripeSubscriptionObject !== null) {
            foreach ($stripeSubscriptionObject['items']['data'] ?? [] as $item) {
                if (! empty($item['price']['id'])) {
                    $priceIds[] = $item['price']['id'];
                }
            }
        }

        if ($subscription->stripe_price) {
            $priceIds[] = $subscription->stripe_price;
        }

        $subscription->loadMissing('items');
        foreach ($subscription->items as $item) {
            if ($item->stripe_price) {
                $priceIds[] = $item->stripe_price;
            }
        }

        $planId = $this->resolvePlanIdFromStripePriceIds($priceIds);

        if ($planId === null && $stripeSubscriptionObject !== null && isset($stripeSubscriptionObject['metadata']['plan_id'])) {
            $meta = (int) $stripeSubscriptionObject['metadata']['plan_id'];
            if ($meta > 0) {
                $planId = $meta;
            }
        }

        if ($planId === null) {
            return;
        }

        if ((int) $subscription->plan_id !== (int) $planId) {
            $subscription->forceFill(['plan_id' => $planId])->save();
        }

        if ((int) $tenant->plan_id !== (int) $planId) {
            $tenant->forceFill(['plan_id' => $planId])->save();
        }
    }

    /**
     * Subscribe a tenant to a paid plan via Cashier.
     */
    public function subscribeToPaidPlan(
        Tenant $tenant,
        Plan $plan,
        string $paymentMethodId,
        string $billingCycle = 'monthly',
        int $trialDays = 0
    ): Subscription {
        if ($trialDays !== 0) {
            throw new \InvalidArgumentException('Paid Starter and Pro subscriptions bill immediately — no trial period.');
        }

        if (! $plan->stripe_product_id) {
            throw new \InvalidArgumentException("Plan {$plan->name} does not have a Stripe product configured.");
        }

        $priceId = $billingCycle === 'yearly'
            ? $plan->stripe_price_yearly_id
            : $plan->stripe_price_monthly_id;

        if (! $priceId) {
            throw new \InvalidArgumentException("Plan {$plan->name} does not have a Stripe price for {$billingCycle} billing.");
        }

        try {
            if (! $tenant->stripe_id) {
                $tenant->createAsStripeCustomer([
                    'name' => $tenant->name,
                    'description' => "Tenant: {$tenant->name} (ID: {$tenant->id})",
                    'metadata' => [
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                    ],
                ]);
            }

            $tenant->addPaymentMethod($paymentMethodId);
            $tenant->updateDefaultPaymentMethod($paymentMethodId);

            // Cancel any other Cashier subscription on the same type so we keep one active row.
            foreach ($tenant->subscriptions()->where('type', self::SUBSCRIPTION_TYPE)->get() as $existing) {
                if (! $existing->ended()) {
                    $existing->cancelNow();
                }
            }

            $subscription = $tenant->newSubscription(self::SUBSCRIPTION_TYPE, $priceId)
                ->withMetadata([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                ])
                ->create($paymentMethodId);

            // Track which Plan this Cashier sub corresponds to.
            $subscription->forceFill(['plan_id' => $plan->id])->save();

            // Free-plan generic trial no longer applies once Stripe is in charge.
            $tenant->forceFill([
                'plan_id' => $plan->id,
                'trial_ends_at' => null,
            ])->save();

            return $subscription;
        } catch (IncompletePayment $e) {
            throw new \RuntimeException("Payment incomplete for subscription: {$e->getMessage()}");
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to create subscription: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Subscribe a tenant to a free plan. No Stripe involvement.
     *
     * Timed trials are applied only once during workspace registration ({@see TenantController::store});
     * this path never starts a new trial window.
     */
    public function subscribeToFreePlan(
        Tenant $tenant,
        Plan $plan,
        int $trialDays = 0
    ): Tenant {
        if ($trialDays !== 0) {
            throw new \InvalidArgumentException(
                'You already used your signup trial or chose a paid plan. Switching plans here does not restart a free trial.'
            );
        }

        if ($plan->stripe_product_id) {
            throw new \InvalidArgumentException("Plan {$plan->name} is a paid plan. Use subscribeToPaidPlan instead.");
        }

        // Tear down any existing paid Cashier subscription if the tenant is downgrading.
        foreach ($tenant->subscriptions()->where('type', self::SUBSCRIPTION_TYPE)->get() as $existing) {
            if (! $existing->ended()) {
                $existing->cancelNow();
            }
        }

        $tenant->forceFill([
            'plan_id' => $plan->id,
            'trial_ends_at' => null,
        ])->save();

        return $tenant->refresh();
    }

    /**
     * Move a tenant from one plan to another.
     */
    public function changePlan(
        Tenant $tenant,
        Plan $newPlan,
        string $billingCycle = 'monthly',
        ?string $paymentMethodId = null,
        int $trialDays = 0
    ): array {
        if ($trialDays !== 0) {
            throw new \InvalidArgumentException(
                'Plan changes do not include a trial period. Signup is the only place a timed free trial can start.'
            );
        }

        $current = $tenant->subscription(self::SUBSCRIPTION_TYPE);

        // Free → free (or no current subscription)
        if (! $newPlan->stripe_product_id) {
            $this->subscribeToFreePlan($tenant, $newPlan, 0);
            return ['subscription' => null, 'tenant' => $tenant->refresh()];
        }

        // Paid target — need a Stripe price for the chosen cycle.
        $priceId = $billingCycle === 'yearly'
            ? $newPlan->stripe_price_yearly_id
            : $newPlan->stripe_price_monthly_id;

        if (! $priceId) {
            throw new \InvalidArgumentException("Plan {$newPlan->name} does not have a Stripe price for {$billingCycle} billing.");
        }

        // Already on a Cashier subscription — swap in place to preserve billing history.
        if ($current && ! $current->ended()) {
            $current->swap($priceId, ['proration_behavior' => 'create_prorations']);
            $current->forceFill(['plan_id' => $newPlan->id])->save();

            $tenant->forceFill([
                'plan_id' => $newPlan->id,
                'trial_ends_at' => null,
            ])->save();

            return ['subscription' => $current->refresh(), 'tenant' => $tenant->refresh()];
        }

        // Free → paid: requires a payment method.
        if (! $paymentMethodId) {
            throw new \InvalidArgumentException('A payment method is required to upgrade to a paid plan.');
        }

        $subscription = $this->subscribeToPaidPlan(
            $tenant,
            $newPlan,
            $paymentMethodId,
            $billingCycle,
            0
        );

        return ['subscription' => $subscription, 'tenant' => $tenant->refresh()];
    }

    /**
     * Cancel the tenant's subscription. Free plans have no Cashier row; we just clear
     * `tenants.plan_id` and the generic trial.
     */
    public function cancelSubscription(Tenant $tenant, bool $immediately = false): void
    {
        $current = $tenant->subscription(self::SUBSCRIPTION_TYPE);

        if ($current && ! $current->ended()) {
            $immediately ? $current->cancelNow() : $current->cancel();
        }

        // For a free plan we just expire access. Keep plan_id so we can show what they had.
        $tenant->forceFill([
            'trial_ends_at' => $current ? $tenant->trial_ends_at : now()->subSecond(),
        ])->save();
    }

    /**
     * Undo a cancel-at-period-end while the Cashier grace window is still open.
     *
     * @throws \LogicException|\RuntimeException
     */
    public function resumeSubscription(Tenant $tenant): Subscription
    {
        $current = $tenant->subscription(self::SUBSCRIPTION_TYPE);

        if (! $current) {
            throw new \RuntimeException('No subscription found.');
        }

        return $current->resume();
    }

    /**
     * Whether the tenant currently has access — paid Cashier sub valid OR generic trial active OR
     * a free plan with no expiry.
     */
    public function tenantHasActiveAccess(Tenant $tenant): bool
    {
        $sub = $tenant->subscription(self::SUBSCRIPTION_TYPE);

        if ($sub) {
            return $sub->valid();
        }

        if (! $tenant->plan_id) {
            return false;
        }

        $plan = Plan::query()->find($tenant->plan_id);

        // Paid Stripe plan but no usable Cashier row — incomplete checkout, churned sub, etc.
        if ($plan && $plan->stripe_product_id) {
            return false;
        }

        // Free plan — check the generic trial. Null trial = no expiry (e.g. post-downgrade free tier).
        if (! $tenant->trial_ends_at) {
            return true;
        }

        return $tenant->trial_ends_at->isFuture();
    }

    /**
     * Human-readable denial for middleware when {@see tenantHasActiveAccess} is false.
     *
     * @return array{message: string, error: string, reason?: string, trial_ends_at?: string}
     */
    public function describeAccessDenial(Tenant $tenant): array
    {
        $sub = $tenant->subscription(self::SUBSCRIPTION_TYPE);

        if ($sub && ! $sub->valid()) {
            $status = $sub->stripe_status;

            if ($sub->pastDue() || $status === StripeSubscription::STATUS_PAST_DUE) {
                return [
                    'message' => 'Your last subscription payment did not go through. Update your payment method under Plan & Billing to restore full access.',
                    'error' => 'billing_required',
                    'reason' => 'past_due',
                ];
            }

            if ($status === StripeSubscription::STATUS_UNPAID) {
                return [
                    'message' => 'Your subscription has an unpaid invoice. Visit Plan & Billing to settle payment and continue using EasyRP.',
                    'error' => 'billing_required',
                    'reason' => 'unpaid',
                ];
            }

            if ($sub->incomplete() || $status === StripeSubscription::STATUS_INCOMPLETE) {
                return [
                    'message' => 'Your subscription setup was not finished. Open Plan & Billing to complete checkout.',
                    'error' => 'billing_required',
                    'reason' => 'incomplete',
                ];
            }

            if ($sub->ended()) {
                return [
                    'message' => 'This workspace no longer has an active subscription. Choose a plan under Plan & Billing to continue.',
                    'error' => 'billing_required',
                    'reason' => 'canceled',
                ];
            }

            return [
                'message' => 'Your subscription is not active. Visit Plan & Billing to fix billing or choose a plan.',
                'error' => 'billing_required',
                'reason' => $status ?: 'inactive',
            ];
        }

        if (! $tenant->plan_id) {
            return [
                'message' => 'Choose a plan for this workspace to continue.',
                'error' => 'subscription_required',
                'reason' => 'no_plan',
            ];
        }

        $plan = Plan::query()->find($tenant->plan_id);

        if ($plan && $plan->stripe_product_id) {
            return [
                'message' => 'Activate or renew your subscription under Plan & Billing to keep using EasyRP.',
                'error' => 'billing_required',
                'reason' => 'no_active_subscription',
            ];
        }

        if ($tenant->trial_ends_at && $tenant->trial_ends_at->isPast()) {
            return [
                'message' => 'Your free trial has ended. Upgrade under Plan & Billing to keep your data and restore access.',
                'error' => 'trial_expired',
                'reason' => 'trial_ended',
                'trial_ends_at' => $tenant->trial_ends_at->toIso8601String(),
            ];
        }

        return [
            'message' => 'Subscription access could not be verified. Open Plan & Billing to review your workspace.',
            'error' => 'subscription_required',
            'reason' => 'unknown',
        ];
    }

    /**
     * Plan that should be considered "current" for limit/feature checks. Falls back to the
     * tenant's `plan_id` so usage snapshots still work for free trials.
     */
    public function resolveActivePlan(Tenant $tenant): ?Plan
    {
        $sub = $tenant->subscription(self::SUBSCRIPTION_TYPE);

        if ($sub && $sub->valid() && $sub->plan_id) {
            return Plan::with('features')->find($sub->plan_id);
        }

        if ($sub) {
            return null;
        }

        if (! $tenant->plan_id) {
            return null;
        }

        $plan = Plan::with('features')->find($tenant->plan_id);

        if ($plan && $plan->stripe_product_id) {
            return null;
        }

        return $plan;
    }

    /**
     * Map Cashier `stripe_status` (+ ends_at / trial_ends_at) onto the simpler enum the
     * frontend already understands.
     */
    public function mapStatus(?Subscription $sub, Tenant $tenant): string
    {
        if ($sub) {
            if ($sub->ended()) {
                return 'canceled';
            }

            // Prefer Stripe's `status` over local `trial_ends_at`: after "end trial" or
            // Dashboard edits, `trial_ends_at` can still be future until webhooks run.
            $stripeStatus = $sub->stripe_status;

            // Cancel at period end — Cashier keeps access until `ends_at` ({@see Subscription::valid()}).
            if ($sub->onGracePeriod()) {
                return 'canceling';
            }

            if ($stripeStatus === 'trialing') {
                return 'trialing';
            }
            if ($stripeStatus === 'past_due' || $sub->pastDue()) {
                return 'past_due';
            }
            if ($stripeStatus === 'active') {
                return 'active';
            }
            if ($sub->onTrial()) {
                return 'trialing';
            }
            if ($sub->active()) {
                return 'active';
            }

            return $stripeStatus ?? 'canceled';
        }

        if (! $tenant->plan_id) {
            return 'expired';
        }

        if (! $tenant->trial_ends_at) {
            return 'active';
        }

        return $tenant->trial_ends_at->isFuture() ? 'trialing' : 'expired';
    }

    /**
     * Build the payload the frontend expects ({ id, plan_id, status, trial_ends_at, plan }).
     * Returns null when the tenant has neither a Cashier sub nor a tenant-level plan.
     */
    public function serializeSubscription(Tenant $tenant): ?array
    {
        $sub = $tenant->subscription(self::SUBSCRIPTION_TYPE);

        if ($sub) {
            $this->syncSubscriptionPlanFromStripe($tenant, $sub, null);
            $tenant->refresh();
            $sub->refresh();

            $plan = $sub->plan_id
                ? Plan::with('features')->find($sub->plan_id)
                : null;

            return [
                'id' => $sub->id,
                'tenant_id' => $tenant->id,
                'plan_id' => $sub->plan_id,
                'stripe_subscription_id' => $sub->stripe_id,
                'status' => $this->mapStatus($sub, $tenant),
                'trial_ends_at' => $sub->trial_ends_at?->toIso8601String(),
                'current_period_end' => $sub->ends_at?->toIso8601String(),
                'plan' => $plan ? $this->serializePlanForSubscription($plan) : null,
                'created_at' => $sub->created_at?->toIso8601String(),
                'updated_at' => $sub->updated_at?->toIso8601String(),
            ];
        }

        if (! $tenant->plan_id) {
            return null;
        }

        $plan = Plan::with('features')->find($tenant->plan_id);

        return [
            'id' => 0, // synthetic — there is no Cashier row for a free plan
            'tenant_id' => $tenant->id,
            'plan_id' => $tenant->plan_id,
            'stripe_subscription_id' => null,
            'status' => $this->mapStatus(null, $tenant),
            'trial_ends_at' => $tenant->trial_ends_at?->toIso8601String(),
            'current_period_end' => null,
            'plan' => $plan ? $this->serializePlanForSubscription($plan) : null,
            'created_at' => $tenant->created_at?->toIso8601String(),
            'updated_at' => $tenant->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Nested plan payload for JSON responses — guarantees currency + is_free for the SPA.
     *
     * @return array<string, mixed>
     */
    private function serializePlanForSubscription(Plan $plan): array
    {
        $plan->loadMissing('features');

        return array_merge($plan->toArray(), [
            'currency' => strtoupper((string) config('cashier.currency', 'usd')),
            'is_free' => ! $plan->stripe_product_id,
        ]);
    }

    public function getPlanPricing(Plan $plan): array
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'currency' => strtoupper((string) config('cashier.currency', 'usd')),
            'is_free' => ! $plan->stripe_product_id,
            'monthly' => [
                'price' => $plan->price_monthly,
                'stripe_price_id' => $plan->stripe_price_monthly_id,
            ],
            'yearly' => [
                'price' => $plan->price_yearly,
                'stripe_price_id' => $plan->stripe_price_yearly_id,
            ],
            'features' => $plan->features()->get(),
        ];
    }

    public function getAllPlans(): array
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn ($plan) => $this->getPlanPricing($plan))
            ->toArray();
    }
}
