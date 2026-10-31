<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductIngredient extends Pivot
{
    protected $table = 'product_ingredients';
    public $incrementing = true;
    protected $fillable = ['product_id', 'ingredient_id', 'percentage', 'sort_order'];

    protected function casts(): array { return ['percentage' => 'decimal:2']; }

    public function product() { return $this->belongsTo(Product::class); }
    public function ingredient() { return $this->belongsTo(Ingredient::class); }
}
