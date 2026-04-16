<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantDatabaseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantSchema
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            TenantDatabaseService::switchTo($tenant);
        }

        try {
            return $next($request);
        } finally {
            TenantDatabaseService::reset();
        }
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        // Try from authenticated user first
        if ($request->user()?->tenant) {
            return $request->user()->tenant;
        }

        // Try from subdomain route parameter (storefront routes)
        $subdomain = $request->route('subdomain');
        if ($subdomain) {
            return Tenant::where('subdomain', $subdomain)->where('is_active', true)->first();
        }

        return null;
    }
}
