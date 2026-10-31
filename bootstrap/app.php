<?php

use App\Http\Middleware\EnsureAdminIsActive;
use App\Http\Middleware\ProtectPreviewData;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['admin.active' => EnsureAdminIsActive::class]);
        $middleware->web(append: [ProtectPreviewData::class]);

        // Trust only the forwarded HTTPS scheme when running behind Vercel.
        // Do not accept forwarded hosts or client IPs from arbitrary callers.
        if (getenv('VERCEL') || getenv('VERCEL_CONTAINER')) {
            $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_PROTO);
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Laravel defaults are sufficient for this catalog application.
    })->create();
