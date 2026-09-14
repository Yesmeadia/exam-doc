<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $student = $this->route('student');
        if ($student && !$student->allowsIndividualSubjectAllocation()) {
            return false;
        }

        return $this->user()->can('assign special subjects') || $this->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['exists:subjects,id'],
            'is_special' => ['nullable', 'boolean'],
            'academic_year_id' => ['nullable'],
            'class_id' => ['nullable'],
            'section_id' => ['nullable'],
            'search' => ['nullable', 'string'],
            'page' => ['nullable', 'integer'],
        ];
    }
}
