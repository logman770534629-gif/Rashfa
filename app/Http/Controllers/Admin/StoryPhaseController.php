<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoryPhaseRequest;
use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\ProductStoryPhase;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StoryPhaseController extends Controller
{
    public function index(Product $product): View { return view('admin.story-phases.index', ['product' => $product, 'phases' => $product->storyPhases()->with('media')->get(), 'media' => MediaLibrary::latest()->take(80)->get()]); }
    public function store(StoryPhaseRequest $request, Product $product): RedirectResponse { $product->storyPhases()->create($request->validated()); return back()->with('status','تمت إضافة مرحلة القصة.'); }
    public function update(StoryPhaseRequest $request, Product $product, ProductStoryPhase $storyPhase): RedirectResponse { abort_unless($storyPhase->product_id === $product->id, 404); $storyPhase->update($request->validated()); return back()->with('status','تم حفظ مرحلة القصة.'); }
    public function destroy(Product $product, ProductStoryPhase $storyPhase): RedirectResponse { abort_unless($storyPhase->product_id === $product->id, 404); $storyPhase->delete(); return back()->with('status','تم حذف مرحلة القصة.'); }
}
