<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\PreventBrowserCache::class,
            \App\Http\Middleware\ActiveAccount::class,
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['card_number', 'cvv', 'expiry']);
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $exception, \Illuminate\Http\Request $request) {
            if (! $request->routeIs('password.email') || $request->expectsJson()) {
                return null;
            }

            $seconds = max(1, (int) ($exception->getHeaders()['Retry-After'] ?? 600));
            $minutes = (int) ceil($seconds / 60);

            return redirect()->route('password.request')
                ->withInput($request->only('email'))
                ->withErrors(['email' => "Too many reset-code requests. Please wait {$minutes} minute(s) before trying again. If you already received an OTP, use it on the password reset page."]);
        });
    })->create();
