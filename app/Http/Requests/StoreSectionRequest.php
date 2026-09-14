<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage sections');
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'name' => ['required', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'compulsory_subject_ids' => ['nullable', 'array'],
            'compulsory_subject_ids.*' => ['exists:subjects,id'],
            'optional_subject_ids' => ['nullable', 'array'],
            'optional_subject_ids.*' => ['exists:subjects,id'],
        ];
    }
}
