<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrialNotExpired
{
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

        $activeSubscription = $tenant->subscriptions()
            ->with('plan')
            ->get()
            ->first(fn($sub) => $sub->hasActiveAccess());

        if (! $activeSubscription) {
            // Check if there is at least one subscription (expired trial)
            $latestSub = $tenant->subscriptions()->latest()->first();

            $trialEndsAt = $latestSub?->trial_ends_at;

            return response()->json([
                'message'       => 'Your free trial has expired. Please upgrade to continue.',
                'error'         => 'trial_expired',
                'trial_ends_at' => $trialEndsAt?->toIso8601String(),
            ], 403);
        }

        return $next($request);
    }
}
