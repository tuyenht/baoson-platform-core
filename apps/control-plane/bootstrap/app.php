<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Exceptions\UnauthorizedException as SpatieUnauthorizedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\LogContext::class,
            \App\Http\Middleware\RequireSetup::class,
            \App\Http\Middleware\ResolveTenant::class,
            \App\Http\Middleware\ResolveSite::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Route middleware aliases (Laravel 12 skeleton has no Http\Kernel.php)
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'feature' => \App\Http\Middleware\RequireFeatureFlag::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthorizationException|SpatieUnauthorizedException $e, Request $request) {
            if ($request->header('X-Inertia')) {
                return Inertia::render('Errors/Forbidden', [
                    'message' => $e->getMessage() ?: 'Forbidden',
                ])->toResponse($request)->setStatusCode(403);
            }

            return response('Forbidden', 403);
        });
    })->create();
