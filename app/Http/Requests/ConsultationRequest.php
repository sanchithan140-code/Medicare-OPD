<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('doctor') ?? false;
    }

    public function rules(): array
    {
        return [
            'chief_complaint'=>['nullable','string','max:5000'],
            'clinical_notes'=>['required','string','min:10','max:20000'],
            'diagnosis_text'=>['required','string','min:3','max:5000'],
            'treatment_plan'=>['required','string','min:3','max:10000'],
            'patient_summary'=>['nullable','string','max:5000'],
            'follow_up_date'=>['nullable','date','after_or_equal:today'],
            'condition_ids'=>['nullable','array','max:20'],
            'condition_ids.*'=>['integer','distinct','exists:diagnosis_conditions,condition_id'],
            'lab_test_ids'=>['nullable','array','max:20'],
            'lab_test_ids.*'=>['integer','distinct','exists:lab_tests,lab_test_id'],
            'medicine_name'=>['nullable','required_with:dosage,frequency,duration_days','string','max:150'],
            'dosage'=>['nullable','required_with:medicine_name','string','max:100'],
            'frequency'=>['nullable','required_with:medicine_name','string','max:100'],
            'duration_days'=>['nullable','required_with:medicine_name','integer','min:1','max:365'],
        ];
    }
}
