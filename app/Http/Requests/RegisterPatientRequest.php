<?php

namespace App\Http\Requests;

use App\Rules\SriLankanMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim((string) $this->input('full_name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:3', 'max:150', "regex:/^[\pL\pM .'-]+$/u"],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'phone' => ['required', new SriLankanMobile],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
            'terms' => ['accepted'],
            'existing_patient_id' => ['nullable','string','size:4','regex:/^[0-9]{4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.regex' => 'The full name may contain only letters, spaces, apostrophes, hyphens and full stops.',
            'email.unique' => 'This email address is already registered. Please log in or reset your password.',
            'password.confirmed' => 'The password confirmation does not match.',
            'terms.accepted' => 'You must accept the portal terms and care guidance notice.',
        ];
    }
}
