<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitMarksRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignment = $this->route('assignment');
        return $this->user()->can('submit', $assignment);
    }

    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'exists:exams,id'],
            'marks' => ['required', 'array'],
            'marks.*.marks' => ['nullable', 'numeric', 'min:0'],
            'marks.*.is_absent' => ['nullable', 'boolean'],
            'marks.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }
}
