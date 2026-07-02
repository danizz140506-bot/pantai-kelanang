<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
        // CHIP posts to this endpoint server-to-server without a CSRF token.
        $middleware->validateCsrfTokens(except: ['webhooks/chip']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for API routes and for any AJAX/fetch request that asks
        // for JSON (so the in-app fetch endpoints receive 422/JSON, not a redirect).
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $e) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
