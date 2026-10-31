<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaLibrary extends Model
{
    protected $table = 'media_library';

    protected $fillable = ['file_name', 'storage_path', 'mime_type', 'file_size_bytes', 'width_px', 'height_px', 'checksum_sha256'];

    public function productImages() { return $this->hasMany(ProductImage::class, 'media_id'); }
    public function storyPhases() { return $this->hasMany(ProductStoryPhase::class, 'media_id'); }
}
