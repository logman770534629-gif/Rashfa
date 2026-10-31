<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $flavors = Product::publiclyVisible()
            ->with(['primaryImage.media'])
            ->orderBy('sort_order')->orderBy('id')->get();

        $storyProduct = $flavors->first();
        $storyPhases = $storyProduct
            ? $storyProduct->storyPhases()->with('media')->orderBy('phase_order')->get()
            : collect();

        // الرقم من لوحة المحتوى (contact.whatsapp)، ثم من الصف القديم contact، ثم من الإعدادات
        $legacy = setting_decode(site_settings_all()['contact'] ?? null);
        $whatsapp = setting('contact.whatsapp')
            ?: (is_array($legacy) ? ($legacy['whatsapp'] ?? null) : null)
            ?: config('rashfa.whatsapp_number');

        return view('home', [
            'flavors' => $flavors,
            'storyPhases' => $storyPhases,
            'ingredients' => Ingredient::query()->with('image')->where('is_active', true)->orderBy('name_ar')->take(20)->get(),
            'whatsapp' => preg_replace('/\D/', '', (string) $whatsapp),
        ]);
    }
}
