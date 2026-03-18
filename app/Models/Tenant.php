<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'slug', 'subdomain', 'industry', 'team_size', 'modules', 'plan_id', 'is_active'])]
class Tenant extends Model
{
    protected function casts(): array
    {
        return [
            'modules' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function webshopSetting(): HasOne
    {
        return $this->hasOne(WebshopSetting::class);
    }

    public function setupProgress(): HasMany
    {
        return $this->hasMany(SetupProgress::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function hasFeature(string $featureCode): bool
    {
        return $this->subscriptions()
            ->get()
            ->filter(fn($sub) => $sub->hasActiveAccess())
            ->flatMap(fn($sub) => $sub->plan->features ?? collect())
            ->contains('code', $featureCode);
    }
}
