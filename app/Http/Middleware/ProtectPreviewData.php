<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ProtectPreviewData
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe() && ! $request->routeIs('admin.login.attempt', 'admin.logout')) {
            abort_if(config('rashfa.preview_read_only'), 403, 'This preview is read-only. Changes and uploads are disabled.');

            if ($request->routeIs('admin.*')) {
                Gate::forUser($request->user('admin'))->authorize(
                    $request->routeIs('admin.settings.*') ? 'manage-settings' : 'manage-content'
                );
            }
        }

        return $next($request);
    }
}
