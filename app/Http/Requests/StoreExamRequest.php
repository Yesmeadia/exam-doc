<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create exams');
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'exam_name' => ['required', 'string', 'max:150'],
            'exam_code' => ['nullable', 'string', 'max:50', 'unique:exams,exam_code'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string', 'in:Draft,Active,Mark Entry Open,Mark Entry Closed,Verification,Locked,Published,Archived'],
        ];
    }
}
