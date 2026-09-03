<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\RequestEntityTooLargeHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        // The value is already a high-entropy opaque token; keeping it unencrypted
        // allows the API middleware to hash and resolve it consistently.
        $middleware->encryptCookies(except: [\App\Services\AuthSessionService::COOKIE]);
        $middleware->alias([
            'customer.auth' => \App\Http\Middleware\AuthenticateCustomerSession::class,
            'frontend.origin' => \App\Http\Middleware\VerifyFrontendOrigin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TooManyRequestsHttpException $exception, $request) {
            if ($request->is('api/auth/*') || $request->is('api/favorites*')) {
                return response()->json(['error' => [
                    'code' => 'RATE_LIMITED',
                    'message' => 'Too many attempts. Please try again later.',
                    'fields' => (object) [],
                ]], 429, $exception->getHeaders());
            }
        });
        $exceptions->render(function (RequestEntityTooLargeHttpException $exception, $request) {
            if ($request->is('api/auth/*') || $request->is('api/favorites*')) {
                return response()->json(['error' => [
                    'code' => 'REQUEST_TOO_LARGE',
                    'message' => 'The request body is too large.',
                    'fields' => (object) [],
                ]], 413);
            }
        });
    })->create();
