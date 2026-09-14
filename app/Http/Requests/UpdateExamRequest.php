<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit exams');
    }

    public function rules(): array
    {
        $exam = $this->route('exam');

        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'exam_name' => ['required', 'string', 'max:150'],
            'exam_code' => ['nullable', 'string', 'max:50', Rule::unique('exams', 'exam_code')->ignore($exam->id)],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string', 'in:Draft,Active,Mark Entry Open,Mark Entry Closed,Verification,Locked,Published,Archived'],
        ];
    }
}
