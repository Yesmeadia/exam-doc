<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign teachers');
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'exists:users,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('teacher_assignments')->where(function ($query) {
                    return $query->where('teacher_id', $this->teacher_id)
                        ->where('academic_year_id', $this->academic_year_id)
                        ->where('class_id', $this->class_id)
                        ->where('section_id', $this->section_id);
                }),
            ],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.unique' => 'This teacher is already assigned to this Class, Section, and Subject for this Academic Year.',
        ];
    }
}
