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
    ->withMiddleware(function (Middleware $middleware) {
        // Agregar middleware CORS personalizado globalmente (para web y API)
        $middleware->web(append: [
            \App\Http\Middleware\CustomCors::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\CustomCors::class, // Al principio de API
            // No necesitamos EnsureFrontendRequestsAreStateful porque usamos tokens Bearer
        ]);

        $middleware->alias([
            'vendor.admin' => \App\Http\Middleware\CheckVendorOrAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
