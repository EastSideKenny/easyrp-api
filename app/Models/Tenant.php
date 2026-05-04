<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Billable;

#[Fillable(['name', 'slug', 'subdomain', 'industry', 'team_size', 'modules', 'currency', 'plan_id', 'is_active', 'logo_path', 'theme', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at', 'free_trial_used_at'])]
class Tenant extends Model
{
    /**
     * Cashier's `Billable` adds:
     *   - subscriptions()              HasMany via tenant_id (we override Cashier's default user_id)
     *   - subscription('default')      Resolved active subscription
     *   - newSubscription(...)         SubscriptionBuilder
     *   - createAsStripeCustomer(...)  etc.
     *
     * It also defines `onGenericTrial()` based on tenants.trial_ends_at, which is what we
     * use for free-plan trials that never hit Stripe.
     */
    use Billable;

    protected $appends = ['logo_url'];

    protected function casts(): array
    {
        return [
            'modules' => 'array',
            'is_active' => 'boolean',
            'trial_ends_at' => 'datetime',
            'free_trial_used_at' => 'datetime',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * True when the tenant currently has access to the given feature code.
     * Considers paid Cashier subscriptions and free-plan generic trials.
     */
    public function hasFeature(string $featureCode): bool
    {
        $service = app(\App\Services\SubscriptionService::class);
        if (! $service->tenantHasActiveAccess($this)) {
            return false;
        }

        $plan = $service->resolveActivePlan($this);
        if (! $plan) {
            return false;
        }

        return $plan->features->contains('code', $featureCode);
    }
}
