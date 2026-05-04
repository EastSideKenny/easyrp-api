<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrialNotExpired
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    /**
     * Block access if the tenant has no active subscription or an expired trial.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (! $tenant) {
            return $next($request);
        }

        if ($this->subscriptions->tenantHasActiveAccess($tenant)) {
            return $next($request);
        }

        $sub = $tenant->subscription(SubscriptionService::SUBSCRIPTION_TYPE);
        $trialEndsAt = $sub?->trial_ends_at ?? $tenant->trial_ends_at;

        return response()->json([
            'message'       => 'Your free trial has expired. Please upgrade to continue.',
            'error'         => 'trial_expired',
            'trial_ends_at' => $trialEndsAt?->toIso8601String(),
        ], 403);
    }
}
