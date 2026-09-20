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
        $rules = [
            'teacher_id' => ['required', 'exists:users,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_ids' => ['required_without:section_id', 'array', 'min:1'],
            'section_ids.*' => ['exists:sections,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];

        // If single section_id is provided, apply the unique constraint validation
        if ($this->filled('section_id') && !$this->filled('section_ids')) {
            $rules['subject_id'][] = Rule::unique('teacher_assignments')->where(function ($query) {
                return $query->where('teacher_id', $this->teacher_id)
                    ->where('academic_year_id', $this->academic_year_id)
                    ->where('class_id', $this->class_id)
                    ->where('section_id', $this->section_id);
            });
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'section_ids.required_without' => 'Please select at least one section.',
            'section_ids.min' => 'Please select at least one section.',
            'subject_id.required' => 'Please select a subject for the assigned section(s).',
            'subject_id.unique' => 'This teacher is already assigned to this Class, Section, and Subject for this Academic Year.',
        ];
    }
}
