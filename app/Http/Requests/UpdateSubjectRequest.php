<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage subjects');
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
            'maximum_marks' => ['required', 'numeric', 'min:1', 'max:1000'],
            'pass_marks' => ['required', 'numeric', 'min:0', 'lte:maximum_marks'],
            'display_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:classes,id'],
        ];
    }
}
