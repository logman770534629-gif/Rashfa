<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStoryPhase extends Model
{
    protected $fillable = ['product_id', 'media_id', 'phase_key', 'title_ar', 'title_en', 'body_ar', 'body_en', 'background_color', 'phase_order'];

    public function product() { return $this->belongsTo(Product::class); }
    public function media() { return $this->belongsTo(MediaLibrary::class, 'media_id'); }
}
