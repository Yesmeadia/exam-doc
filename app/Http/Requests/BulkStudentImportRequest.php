<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStudentImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('bulk import students');
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id'         => ['required', 'exists:classes,id'],
            'section_id'       => ['required', 'exists:sections,id'],
            'file'             => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'], // 5MB max
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'The file must be an Excel file (.xlsx, .xls) or CSV file.',
            'file.max' => 'The file size must not exceed 5MB.',
        ];
    }
}
