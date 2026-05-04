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
     * Block ERP features when billing is inactive: expired trial, failed payment, canceled sub, etc.
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

        return response()->json($this->subscriptions->describeAccessDenial($tenant), 403);
    }
}
