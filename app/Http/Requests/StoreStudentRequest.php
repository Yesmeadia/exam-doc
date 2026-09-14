<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage students');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('student_id') && is_string($this->student_id)) {
            $this->merge([
                'student_id' => strtoupper(trim($this->student_id)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'string', 'max:50', 'unique:students,student_id'],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'roll_no' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('students')->where(function ($query) {
                    return $query->where('academic_year_id', $this->academic_year_id)
                        ->where('class_id', $this->class_id)
                        ->where('section_id', $this->section_id);
                }),
            ],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ];
    }
}
