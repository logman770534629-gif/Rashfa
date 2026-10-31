<?php

// Fixed: pagination now uses a themed admin view (resources/views/admin/pagination.blade.php)
// instead of the default Tailwind view, which had no matching styles in this project.

namespace App\Providers;

use App\Http\Middleware\EnsureAdminIsActive;
use App\Models\Admin;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // دوال المحتوى (setting, setting_style_attr, title_html, cms_schema ...)
        require_once app_path('Support/helpers.php');

        if (! config('auth.guards.admin')) {
            config([
                'auth.guards.admin' => ['driver' => 'session', 'provider' => 'admins'],
                'auth.providers.admins' => ['driver' => 'eloquent', 'model' => Admin::class],
            ]);
        }
    }

    public function boot(): void
    {
        $this->app['router']->aliasMiddleware('admin.active', EnsureAdminIsActive::class);

        Paginator::defaultView('admin.pagination');
        Paginator::defaultSimpleView('admin.pagination');

        Authenticate::redirectUsing(fn () => route('admin.login'));
        RedirectIfAuthenticated::redirectUsing(fn () => route('admin.dashboard'));

        Gate::define('manage-content', fn (Admin $admin) => $admin->canManageContent());
        Gate::define('manage-settings', fn (Admin $admin) => $admin->isSuperAdmin());

        RateLimiter::for('admin-login', fn (Request $request) => [
            Limit::perMinute(5)->by('admin-email:'.hash('sha256', mb_strtolower(trim((string) $request->input('email'))))),
            Limit::perMinute(20)->by('admin-ip:'.$request->ip()),
        ]);
    }
}
