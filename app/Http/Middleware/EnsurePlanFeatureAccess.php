<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanFeatureAccess
{
    /**
     * Require an authenticated tenant whose active plan includes the given feature pivot.
     *
     * @param  string  $featureCode  Feature code from `features.code` / plan_features pivot.
     */
    public function handle(Request $request, Closure $next, string $featureCode): Response
    {
        $user = $request->user();

        if (! $user?->tenant) {
            return response()->json([
                'message' => 'No workspace context.',
                'error' => 'no_tenant',
            ], 403);
        }

        if (! $user->tenant->hasFeature($featureCode)) {
            return response()->json([
                'message' => 'This capability is not included in your current plan. Upgrade under Plan & Billing to unlock it.',
                'error' => 'feature_not_enabled',
                'feature' => $featureCode,
            ], 403);
        }

        return $next($request);
    }
}
