<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'media_id', 'image_type', 'alt_ar', 'is_primary', 'sort_order', 'effects_json'];

    protected function casts(): array { return ['is_primary' => 'boolean', 'effects_json' => 'array']; }

    public function product() { return $this->belongsTo(Product::class); }
    public function media() { return $this->belongsTo(MediaLibrary::class, 'media_id'); }
}
