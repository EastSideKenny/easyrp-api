<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Forbidden. Owner or admin access required.'], 403);
        }

        return $next($request);
    }
}
