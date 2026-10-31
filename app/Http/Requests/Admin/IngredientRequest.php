<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IngredientRequest extends FormRequest
{
    public function authorize(): bool { return $this->user('admin')?->canManageContent() ?? false; }
    public function rules(): array
    {
        return ['name_ar'=>['required','string','max:120'],'name_en'=>['required','string','max:120'],'description_ar'=>['nullable','string','max:500'],'description_en'=>['nullable','string','max:500'],'is_active'=>['nullable','boolean'],'image'=>['nullable','file','image','mimes:jpg,jpeg,png,webp','max:6144'],'effects'=>['nullable','array'],'effects.*'=>[Rule::in(['glass','transparent','cutout'])]];
    }
}
