<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NonAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->hasAnyRole(['super-admin', 'admin']) || $user->type === 'admin')) {
            throw new AuthorizationException('Admins must use admin API endpoints.');
        }

        return $next($request);
    }
}
