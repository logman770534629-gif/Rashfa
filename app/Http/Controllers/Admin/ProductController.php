<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Services\ProductContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View { return view('admin.products.index', ['products' => Product::orderBy('sort_order')->orderBy('id')->paginate(12)]); }
    public function create(): View { return view('admin.products.form', $this->formData(new Product())); }
    public function store(ProductRequest $request, ProductContentService $service): RedirectResponse { $product = $service->save($request->validated()); return redirect()->route('admin.products.edit', $product)->with('status', 'تمت إضافة النكهة.'); }
    public function edit(Product $product): View { $product->load(['primaryImage.media']); return view('admin.products.form', $this->formData($product)); }
    public function update(ProductRequest $request, Product $product, ProductContentService $service): RedirectResponse { $service->save($request->validated(), $product); return back()->with('status', 'تم حفظ التغييرات.'); }
    public function destroy(Product $product): RedirectResponse { $product->update(['is_active' => false, 'is_published' => false]); return redirect()->route('admin.products.index')->with('status', 'أُخفيت النكهة من الموقع دون حذف بياناتها.'); }

    /** حذف نهائي للنكهة مع صورها ومراحلها وأحجامها المرتبطة. */
    public function forceDestroy(Product $product, ProductContentService $service): RedirectResponse
    {
        Gate::authorize('manage-content');
        $service->forceDelete($product);

        return redirect()->route('admin.products.index')->with('status', 'تم حذف النكهة نهائياً مع صورها ومراحلها المرتبطة بها.');
    }

    private function formData(Product $product): array { return ['product' => $product, 'nextSort' => ((int) Product::max('sort_order')) + 1]; }
}
