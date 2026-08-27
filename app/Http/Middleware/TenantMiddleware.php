<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json([
                'message' => 'No tenant associated with user',
            ], 403);
        }

        // Set tenant context for global scopes
        app()->instance('current_tenant_id', $user->tenant_id);
        
        // Add tenant to request for easy access
        $request->merge(['tenant_id' => $user->tenant_id]);

        return $next($request);
    }
}