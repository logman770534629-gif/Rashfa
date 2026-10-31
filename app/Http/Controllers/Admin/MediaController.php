<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View { return view('admin.media.index', ['media' => MediaLibrary::latest()->paginate(24)]); }
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144']]);
        app(\App\Services\MediaUploader::class)->store($request->file('file'));
        return back()->with('status', 'تم رفع الوسيط إلى المكتبة.');
    }

    public function destroy(MediaLibrary $media): RedirectResponse
    {
        if ($media->productImages()->exists() || $media->storyPhases()->exists()) {
            return back()->withErrors(['media' => 'لا يمكن حذف وسيط مرتبط بمنتج أو مرحلة قصة. افصل الارتباط أولاً.']);
        }
        if (str_starts_with($media->storage_path, '/storage/')) {
            Storage::disk('public')->delete(ltrim(substr($media->storage_path, strlen('/storage/')), '/'));
        }
        $media->delete();
        return back()->with('status', 'تم حذف الوسيط غير المرتبط.');
    }
}
