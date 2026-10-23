<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Forzar HTTPS - la app corre detras de nginx-proxy
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: [
            'location',
            'location_enabled',
        ]);
        
        $middleware->validateCsrfTokens(except: [
            'api/cameras/*',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckLocationCookie::class,
        ]);

        $middleware->alias([
            'camera.token' => \App\Http\Middleware\VerifyCameraToken::class,
            'cnn.token' => \App\Http\Middleware\VerifyCnnToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
