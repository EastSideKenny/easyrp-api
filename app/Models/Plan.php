<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Subscription;

#[Fillable(['name', 'slug', 'price_monthly', 'price_yearly', 'is_active', 'stripe_product_id', 'stripe_price_monthly_id', 'stripe_price_yearly_id'])]
class Plan extends Model
{
    /**
     * ISO 4217 code for {@see price_monthly} / {@see price_yearly} — matches Cashier / Stripe billing.
     */
    protected $appends = ['currency'];

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')->withPivot('limit');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Cashier subscription rows linked to this plan via the `plan_id` column.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function getCurrencyAttribute(): string
    {
        return strtoupper((string) config('cashier.currency', 'usd'));
    }
}
