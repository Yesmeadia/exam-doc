<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
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
        $student = $this->route('student');

        return [
            'student_id' => ['required', 'string', 'max:50', Rule::unique('students', 'student_id')->ignore($student->id)],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'roll_no' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('students')->where(function ($query) use ($student) {
                    return $query->where('academic_year_id', $this->academic_year_id)
                        ->where('class_id', $this->class_id)
                        ->where('section_id', $this->section_id);
                })->ignore($student->id),
            ],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'filter_academic_year_id' => ['nullable'],
            'class_filter_id' => ['nullable'],
            'search' => ['nullable', 'string'],
            'page' => ['nullable', 'integer'],
        ];
    }
}
