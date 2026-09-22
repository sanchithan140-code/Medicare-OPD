<?php

namespace App\Http\Requests;

use App\Rules\TenDigitPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('office_staff','admin','main_admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'account_email' => mb_strtolower(trim((string) $this->input('account_email'))),
            'phone' => $this->filled('phone') ? preg_replace('/\D+/', '', (string) $this->input('phone')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'account_email'=>['required','email:rfc','exists:users,email'],
            'first_name'=>['required','string','min:2','max:80',"regex:/^[\pL\pM .'-]+$/u"],
            'last_name'=>['required','string','min:2','max:80',"regex:/^[\pL\pM .'-]+$/u"],
            'relationship_to_account'=>['required', Rule::in(['Self','Mother','Father','Spouse','Son','Daughter','Child','Sibling','Other'])],
            'dob'=>['nullable','date','before_or_equal:today','after_or_equal:1900-01-01'],
            'sex'=>['required',Rule::in(['MALE','FEMALE','OTHER','UNKNOWN'])],
            'phone'=>['nullable',new TenDigitPhone],
        ];
    }
}
