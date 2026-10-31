<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = ['name_ar', 'name_en', 'slug', 'description_ar', 'description_en', 'is_active', 'image_media_id', 'image_effects'];

    protected function casts(): array { return ['is_active' => 'boolean', 'image_effects' => 'array']; }

    public function productIngredients() { return $this->hasMany(ProductIngredient::class); }
    public function products() { return $this->belongsToMany(Product::class, 'product_ingredients')->using(ProductIngredient::class)->withPivot(['id', 'percentage', 'sort_order'])->withTimestamps(); }
    public function image() { return $this->belongsTo(MediaLibrary::class, 'image_media_id'); }
}
