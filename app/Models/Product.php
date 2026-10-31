<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name_ar', 'name_en', 'slug', 'short_desc_ar', 'short_desc_en',
        'long_desc_ar', 'long_desc_en', 'sip_story_ar', 'sip_story_en', 'base_color',
        'accent_color', 'nutrition_json', 'tags_json', 'sort_order', 'is_published',
        'is_featured', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'nutrition_json' => 'array',
            'tags_json' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function images() { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function primaryImage() { return $this->hasOne(ProductImage::class)->where('is_primary', true); }
    public function productIngredients() { return $this->hasMany(ProductIngredient::class)->orderBy('sort_order'); }
    public function ingredients() { return $this->belongsToMany(Ingredient::class, 'product_ingredients')->using(ProductIngredient::class)->withPivot(['id', 'percentage', 'sort_order'])->withTimestamps()->orderByPivot('sort_order'); }
    public function variants() { return $this->hasMany(ProductVariant::class)->orderBy('sort_order'); }
    public function storyPhases() { return $this->hasMany(ProductStoryPhase::class)->orderBy('phase_order'); }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('products.is_active', true)
            ->where('products.is_published', true);
    }
}
