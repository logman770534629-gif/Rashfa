<?php

namespace App\Services;

use App\Models\MediaLibrary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploader
{
    /** يرفع الصورة إلى المكتبة ويعيد سجلها، دون أن يضطر المدير لإدخال مسارات أو أرقام. */
    public function store(UploadedFile $file): MediaLibrary
    {
        $dimensions = @getimagesize($file->getRealPath());
        abort_unless($dimensions, 422, 'تعذر التحقق من أبعاد الصورة.');
        $path = $file->storeAs('rashfa/' . now()->format('Y/m'), Str::uuid() . '.' . $file->extension(), 'public');

        return MediaLibrary::create([
            'file_name' => $file->getClientOriginalName(),
            'storage_path' => Storage::disk('public')->url($path),
            'mime_type' => $file->getMimeType(),
            'file_size_bytes' => $file->getSize(),
            'width_px' => $dimensions[0],
            'height_px' => $dimensions[1],
            'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
        ]);
    }
}
