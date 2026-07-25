<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenFromCookie
{
    /**
     * If no Bearer token is present but the royastar_token cookie exists,
     * extract the token and set it as the Authorization header
     * so Sanctum can authenticate the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->bearerToken() && $token = $request->cookie('royastar_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
