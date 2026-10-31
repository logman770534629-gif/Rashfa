<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\Ingredient;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', ['stats' => [
            ['label' => 'إجمالي النكهات', 'value' => Product::count(), 'hint' => 'في الكتالوج'],
            ['label' => 'منشور الآن', 'value' => Product::where('is_active', true)->where('is_published', true)->count(), 'hint' => 'ظاهر للزوار'],
            ['label' => 'مكوّنات', 'value' => Ingredient::where('is_active', true)->count(), 'hint' => 'تظهر في قسم المكوّنات'],
            ['label' => 'وسائط', 'value' => MediaLibrary::count(), 'hint' => 'في المكتبة'],
        ], 'categories' => Category::withCount('products')->orderBy('sort_order')->get()]);
    }
}
