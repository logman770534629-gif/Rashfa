<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** يحدّد لغة الزائر (جلسة ← كوكي ← الافتراضي) للواجهة العامة فقط. */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('rashfa.locales', ['ar' => []]));

        $locale = $request->session()->get('locale')
            ?: $request->cookie('locale')
            ?: config('rashfa.default_locale', 'ar');

        if (! in_array($locale, $supported, true)) {
            $locale = 'ar';
        }

        app()->setLocale($locale);
        $request->setLocale($locale);

        return $next($request);
    }
}
