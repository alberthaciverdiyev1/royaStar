<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_approved || !($user->hasAnyRole(['super-admin', 'admin']) || $user->type === 'admin')) {
            throw new AuthorizationException('Admin access required.');
        }

        // Flag admin view so resources/models skip translations globally
        app()->instance('admin_view', true);

        return $next($request);
    }
}
