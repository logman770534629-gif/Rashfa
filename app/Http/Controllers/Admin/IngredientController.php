<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IngredientRequest;
use App\Models\Ingredient;
use App\Services\MediaUploader;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function index(): View { return view('admin.ingredients.index', ['ingredients' => Ingredient::with('image')->withCount('products')->orderBy('name_ar')->get(), 'editing' => null]); }
    public function edit(Ingredient $ingredient): View { return view('admin.ingredients.index', ['ingredients' => Ingredient::with('image')->withCount('products')->orderBy('name_ar')->get(), 'editing' => $ingredient->loadCount('products')]); }

    public function store(IngredientRequest $request): RedirectResponse
    {
        $data = $this->payload($request);
        // الرابط المختصر يُولَّد تلقائياً ولا يظهر في النموذج.
        $data['slug'] = UniqueSlug::make(Ingredient::class, [$data['name_en'] ?? null, $data['name_ar'] ?? null], null, 'ingredient');
        Ingredient::create($data);

        return back()->with('status', 'تمت إضافة المكوّن.');
    }

    public function update(IngredientRequest $request, Ingredient $ingredient): RedirectResponse
    {
        $data = $this->payload($request);
        if (blank($ingredient->slug)) {
            $data['slug'] = UniqueSlug::make(Ingredient::class, [$data['name_en'] ?? null, $data['name_ar'] ?? null], $ingredient->id, 'ingredient');
        }
        $ingredient->update($data);

        return redirect()->route('admin.ingredients.index')->with('status', 'تم حفظ المكوّن.');
    }

    private function payload(IngredientRequest $request): array
    {
        $data = collect($request->validated())->except(['image', 'effects', 'slug'])->all() + ['is_active' => $request->boolean('is_active'), 'image_effects' => array_values($request->input('effects', []))];
        if ($request->hasFile('image')) {
            $data['image_media_id'] = app(MediaUploader::class)->store($request->file('image'))->id;
        }

        return $data;
    }

    public function destroy(Ingredient $ingredient): RedirectResponse { $ingredient->update(['is_active' => false]); return back()->with('status','تم تعطيل المكوّن مع الحفاظ على تاريخ المنتجات.'); }

    /** حذف نهائي: يفصل المكوّن عن كل النكهات ثم يحذفه. */
    public function forceDestroy(Ingredient $ingredient): RedirectResponse
    {
        Gate::authorize('manage-content');

        DB::transaction(function () use ($ingredient) {
            DB::table('product_ingredients')->where('ingredient_id', $ingredient->id)->delete();
            $ingredient->delete();
        });

        return redirect()->route('admin.ingredients.index')->with('status', 'تم حذف المكوّن نهائياً وفصله عن النكهات.');
    }
}
