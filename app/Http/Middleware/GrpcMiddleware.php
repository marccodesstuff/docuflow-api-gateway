<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GrpcMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Add gRPC-specific headers for downstream services
        $request->headers->set('X-Request-ID', $request->header('X-Request-ID') ?? \Illuminate\Support\Str::uuid());

        return $next($request);
    }
}