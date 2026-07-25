<?php

use App\Modules\User\Events\UserRegistered;
use App\Modules\User\Listeners\CreateUserNotificationSettings;
use App\Modules\User\Listeners\CreateUserProfile;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withEvents(discover: [
        __DIR__.'/../app/Modules/User/Events',
        __DIR__.'/../app/Modules/User/Listeners',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\TokenFromCookie::class,
        ]);

        $middleware->encryptCookies(except: [
            'royastar_token',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'non-admin' => \App\Http\Middleware\NonAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // 401 — unauthenticated
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return apiResponse(statusCode: 401, message: __('crud.unauthenticated'));
        });

        // 403 — unauthorized
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return apiResponse(statusCode: 403, message: $e->getMessage() ?: __('crud.forbidden'));
            }
        });

        // 404 — model not found (from findOrFail in actions)
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return apiResponse(statusCode: 404, message: __('crud.not_found'));
            }
        });

        // 404 — route/url not found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return apiResponse(statusCode: 404, message: __('crud.not_found'));
            }
        });

        // 422 — validation errors
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'status_code' => 422,
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // 429 — too many requests
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                return apiResponse(statusCode: 429, message: __('crud.too_many_requests'));
            }
        });

        // HTTP errors from abort() or other HttpException instances
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $message = $e->getMessage() ?: null;
                return apiResponse(statusCode: $e->getStatusCode(), message: $message);
            }
        });

        // Generic fallback for all other exceptions
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                Log::error($e->getMessage(), ['exception' => $e]);

                $message = __('crud.server_error');
                if (config('app.debug')) {
                    $message = $e->getMessage();
                }

                $statusCode = 500;
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    $statusCode = $e->getStatusCode();
                }

                return apiResponse(statusCode: $statusCode, message: $message);
            }
        });
    })->create();
