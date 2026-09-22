<?php

namespace App\Http\Requests;

use App\Rules\SriLankanNic;
use App\Rules\TenDigitPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $phoneFields = ['phone', 'emergency_contact_phone'];
        $merge = [];
        foreach ($phoneFields as $field) {
            if ($this->filled($field)) {
                $merge[$field] = preg_replace('/\D+/', '', (string) $this->input($field));
            }
        }
        if ($this->filled('email')) {
            $merge['email'] = mb_strtolower(trim((string) $this->input('email')));
        }
        $this->merge($merge);
    }

    public function rules(): array
    {
        $patient = $this->route('patient');
        $patientId = $patient instanceof \App\Models\Patient ? $patient->patient_id : null;

        return [
            'relationship_to_account' => ['required', Rule::in(['Self','Mother','Father','Spouse','Son','Daughter','Child','Sibling','Other'])],
            'first_name' => ['required','string','min:2','max:80',"regex:/^[\pL\pM .'-]+$/u"],
            'last_name' => ['required','string','min:2','max:80',"regex:/^[\pL\pM .'-]+$/u"],
            'dob' => ['nullable','date','before_or_equal:today','after_or_equal:1900-01-01'],
            'sex' => ['required', Rule::in(['MALE','FEMALE','OTHER','UNKNOWN'])],
            'nic_passport' => ['nullable','string','max:30', new SriLankanNic, Rule::unique('patients','nic_passport')->ignore($patientId, 'patient_id')],
            'email' => ['nullable','email:rfc','max:190'],
            'phone' => ['nullable', new TenDigitPhone],
            'address' => ['nullable','string','max:500'],
            'blood_group' => ['nullable', Rule::in(['A+','A-','B+','B-','AB+','AB-','O+','O-','UNKNOWN'])],
            'emergency_contact_name' => ['nullable','required_with:emergency_contact_phone','string','max:120',"regex:/^[\pL\pM .'-]+$/u"],
            'emergency_contact_phone' => ['nullable','required_with:emergency_contact_name', new TenDigitPhone],
            'allergen' => ['nullable','string','max:150'],
            'allergy_reaction' => ['nullable','string','max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'relationship_to_account.in' => 'Please select a valid relationship.',
            'first_name.regex' => 'The first name contains unsupported characters.',
            'last_name.regex' => 'The last name contains unsupported characters.',
            'dob.after_or_equal' => 'Please enter a realistic date of birth.',
            'nic_passport.unique' => 'This NIC is already connected to another patient profile.',
        ];
    }
}
