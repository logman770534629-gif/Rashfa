<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * لوحة التحكم عربية دائماً، بغضّ النظر عن APP_LOCALE في .env.
 * هذا يضمن أن الحقل العربي يقرأ ويحفظ القيمة العربية فقط، والإنجليزي في f_en فقط.
 */
class ForceArabicLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale('ar');
        $request->setLocale('ar');

        return $next($request);
    }
}
