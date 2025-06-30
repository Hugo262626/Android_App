<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: base_path('routes/web.php'),
        api: base_path('routes/api.php'),
        apiPrefix: 'api',
        commands: base_path('routes/console.php'),
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JWTAuthMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/login',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Exception $e, $request) {
            \Illuminate\Support\Facades\Log::error('Exception capturée: ' . $e->getMessage(), [
                'url' => $request->url(),
                'method' => $request->method(),
                'input' => $request->all(),
            ]);
            return null;
        });
    })->create();
