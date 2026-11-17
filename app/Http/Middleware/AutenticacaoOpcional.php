<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AutenticacaoOpcional
{
    /**
     * Public routes that still honour a bearer token, so policies can tell
     * an owner from a guest. A missing or invalid token stays anonymous.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() && Auth::guard('sanctum')->user()) {
            Auth::shouldUse('sanctum');
        }

        return $next($request);
    }
}
