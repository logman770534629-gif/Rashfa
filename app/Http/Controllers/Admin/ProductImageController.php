<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaLibrary;
use App\Services\MediaUploader;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductImageController extends Controller
{
    public function index(Product $product): View
    {
        return view('admin.media.product-images', ['product' => $product, 'images' => $product->images()->with('media')->get(), 'media' => MediaLibrary::latest()->take(100)->get()]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'alt_ar' => ['nullable', 'string', 'max:300'],
            'effects' => ['nullable', 'array'],
            'effects.*' => [Rule::in(['glass', 'transparent', 'cutout'])],
            'is_primary' => ['nullable', 'boolean'],
        ]);
        $media = app(MediaUploader::class)->store($request->file('file'));
        $makePrimary = $request->boolean('is_primary') || ! $product->images()->exists();

        DB::transaction(function () use ($product, $data, $media, $makePrimary) {
            Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            if ($makePrimary) ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
            ProductImage::create([
                'product_id' => $product->id,
                'media_id' => $media->id,
                'image_type' => $makePrimary ? 'hero' : 'gallery',
                'alt_ar' => $data['alt_ar'] ?: $product->name_ar,
                'is_primary' => $makePrimary,
                'sort_order' => ((int) $product->images()->max('sort_order')) + 1,
                'effects_json' => array_values($data['effects'] ?? []),
            ]);
        });

        return back()->with('status', 'تم رفع الصورة وربطها بالنكهة.');
    }

    public function effects(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);
        $data = $request->validate(['effects' => ['nullable', 'array'], 'effects.*' => [Rule::in(['glass', 'transparent', 'cutout'])]]);
        $image->update(['effects_json' => array_values($data['effects'] ?? [])]);

        return back()->with('status', 'تم حفظ تأثيرات الصورة.');
    }

    public function primary(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);
        DB::transaction(function () use ($product, $image) {
            Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            ProductImage::where('product_id', $product->id)->update(['is_primary'=>false]);
            $image->update(['is_primary'=>true]);
        });
        return back()->with('status','تم تعيين الصورة الرئيسية.');
    }

    public function destroy(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);
        $wasPrimary = (bool) $image->is_primary;
        $image->delete();

        // لا تبقى النكهة بلا صورة رئيسية: نرقّي أول صورة متبقية تلقائياً.
        if ($wasPrimary) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return back()->with('status','تم فصل الصورة عن المنتج.');
    }
}
