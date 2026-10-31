<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('admin');

        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->is_active || $admin->isLocked()) {
            // This logout method does not rotate database remember tokens.
            Auth::guard('admin')->logoutCurrentDevice();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
