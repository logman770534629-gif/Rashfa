<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoryPhaseRequest extends FormRequest
{
    public function authorize(): bool { return $this->user('admin')?->canManageContent() ?? false; }
    public function rules(): array
    {
        return ['media_id'=>['nullable','integer',Rule::exists('media_library','id')],'phase_key'=>['required',Rule::in(['harvest','press','sip'])],'title_ar'=>['required','string','max:160'],'title_en'=>['required','string','max:160'],'body_ar'=>['required','string'],'body_en'=>['required','string'],'background_color'=>['required','regex:/^#[0-9A-Fa-f]{6}$/'],'phase_order'=>['required','integer','between:1,3']];
    }
}
