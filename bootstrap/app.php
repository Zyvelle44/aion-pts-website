<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Http\Middleware\EnsureAionAuthenticated;
use App\Http\Middleware\RedirectIfAionAuthenticated;
use App\Http\Middleware\EnsureWebsiteAvailable;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\EnsureInstalledPluginEnabled;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.aion' => EnsureAionAuthenticated::class,
            'guest.aion' => RedirectIfAionAuthenticated::class,
            'website.available' => EnsureWebsiteAvailable::class,
            'feature.enabled' => EnsureFeatureEnabled::class,
            'plugin.enabled' => EnsureInstalledPluginEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            // Preserve Laravel's detailed exception screen in local debug mode.
            if ($status === 500 && config('app.debug')) {
                return null;
            }

            if ($status < 400 || $status > 599) {
                return null;
            }

            $view = view()->exists('errors.' . $status) ? 'errors.' . $status : 'errors.generic';
            return response()->view($view, ['status' => $status], $status);
        });
    })->create();
