<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /** GET /lang/{locale} — يحفظ اللغة (جلسة + كوكي لسنة) ثم يعود للصفحة الرئيسية. */
    public function __invoke(Request $request, string $locale): RedirectResponse|JsonResponse
    {
        abort_unless(array_key_exists($locale, config('rashfa.locales', [])), 404);

        $request->session()->put('locale', $locale);
        $cookie = cookie('locale', $locale, 60 * 24 * 365);

        if ($request->expectsJson()) {
            return response()->json(['locale' => $locale, 'dir' => config("rashfa.locales.$locale.dir")])->withCookie($cookie);
        }

        return redirect()->route('home')->withCookie($cookie);
    }
}
