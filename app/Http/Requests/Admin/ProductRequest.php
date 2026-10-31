<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user('admin')?->canManageContent() ?? false; }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:160'], 'name_en' => ['required', 'string', 'max:160'],
            'short_desc_ar' => ['required', 'string', 'max:500'], 'short_desc_en' => ['required', 'string', 'max:500'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'tags_json' => ['nullable', 'json'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'image_effects' => ['nullable', 'array'], 'image_effects.*' => [Rule::in(['glass', 'transparent', 'cutout'])],
            // زر واحد واضح: "إظهار في الموقع" (يضبط is_active و is_published معاً) + "نكهة مميزة".
            'is_visible' => ['nullable', 'boolean'], 'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
