<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'size_ml', 'label_ar', 'label_en', 'price', 'compare_at_price', 'sku', 'stock_quantity', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'compare_at_price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function product() { return $this->belongsTo(Product::class); }
}
