<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

if (!function_exists('isBrowserRequest')) {
    /**
     * Check if the request is from a browser (has royastar.az Origin).
     * App/mobile requests won't have this Origin header.
     */
    function isBrowserRequest(Request $request): bool
    {
        $origin = $request->header('Origin');

        if (!$origin) {
            return false;
        }

        $allowedOrigins = [
            'https://royastar.az',
            'https://www.royastar.az',
            'https://admin.royastar.az',
            'https://royastar.foxsoft.agency',
            'http://localhost',
            'http://localhost:3000',
            'http://localhost:5173',
        ];

        if (app()->environment('local')) {
            // In local dev, also accept any localhost variant
            $allowedOrigins[] = $request->getSchemeAndHttpHost();
        }

        return in_array($origin, $allowedOrigins, true);
    }
}

if (!function_exists('createAuthCookie')) {
    /**
     * Create an HttpOnly cookie with the Sanctum token.
     */
    function createAuthCookie(string $token): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            'royastar_token',
            $token,
            minutes: 60 * 24 * 7, // 7 days
            path: '/',
            domain: null,
            secure: app()->environment('production'),
            httpOnly: true,
            raw: false,
            sameSite: 'strict',
        );
    }
}
