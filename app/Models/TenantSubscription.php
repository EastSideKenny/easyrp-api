<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'plan_id', 'stripe_subscription_id', 'status', 'current_period_end', 'trial_ends_at'])]
class TenantSubscription extends Model
{
    protected function casts(): array
    {
        return [
            'current_period_end' => 'datetime',
            'trial_ends_at'      => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Whether this subscription is in a trialing state.
     */
    public function isTrialing(): bool
    {
        return $this->status === 'trialing';
    }

    /**
     * Whether the trial period has expired (trialing but past trial_ends_at).
     */
    public function isTrialExpired(): bool
    {
        return $this->isTrialing()
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isPast();
    }

    /**
     * Whether this subscription grants active access.
     * Active paid subscriptions always pass; trialing only passes if within the trial window.
     */
    public function hasActiveAccess(): bool
    {
        if ($this->status === 'active') {
            return true;
        }

        if ($this->isTrialing()) {
            return ! $this->isTrialExpired();
        }

        return false;
    }
}
