<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'subdomain', 'industry', 'team_size', 'modules', 'currency', 'plan_id', 'is_active'])]
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
