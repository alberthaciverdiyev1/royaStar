<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastPage
{
    /**
     * Learning pages worth reopening the next time the student logs in.
     * Deliberately excludes things like profile/logout/achievements.
     */
    private const TRACKED_ROUTES = [
        'topics',
        'topics.detail',
        'lesson',
        'quiz',
        'quiz.result',
        'exam',
        'exam.grade',
        'exam.detail',
        'exam.start',
        'exam.result',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Persist the visited page after the response has been sent, so the
     * student pays no latency for the extra write.
     */
    public function terminate(Request $request, Response $response): void
    {
        $user = $request->user();

        if (!$user || !$request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return;
        }

        $routeName = $request->route()?->getName();

        if (!$routeName || !in_array($routeName, self::TRACKED_ROUTES, true)) {
            return;
        }

        // Internal path only (never a host), so it can't be replayed as an
        // open redirect after the next login.
        $path = $request->getRequestUri();

        if ($user->last_page_url === $path) {
            return;
        }

        $user->forceFill(['last_page_url' => $path])->saveQuietly();
    }
}
