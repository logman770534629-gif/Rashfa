<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['setting_key', 'setting_value', 'description_ar', 'is_public'];

    protected function casts(): array { return ['setting_value' => 'array', 'is_public' => 'boolean']; }
}
