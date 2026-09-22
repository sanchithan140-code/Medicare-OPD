<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class LabResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('lab_officer') ?? false;
    }

    public function rules(): array
    {
        $maxKb = max(1024, ((int) config('ai.lab_result_max_mb', 8)) * 1024);

        return [
            'result_text' => ['nullable','required_without:report_file','string','max:30000'],
            'abnormal_flag' => ['nullable','boolean'],
            'report_file' => [
                'nullable',
                'required_without:result_text',
                File::types(['pdf','jpg','jpeg','png'])->max($maxKb.'kb'),
            ],
            'verify_now' => ['nullable','boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'result_text.required_without' => 'Enter the laboratory result or upload a report document.',
            'report_file.required_without' => 'Upload a report document or enter the laboratory result.',
            'report_file.mimes' => 'The report must be a PDF, JPG, JPEG or PNG file.',
        ];
    }
}
