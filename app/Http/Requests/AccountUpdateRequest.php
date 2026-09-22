<?php

namespace App\Http\Requests;

use App\Rules\TenDigitPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim((string) $this->input('full_name')),
            'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')),
        ]);
    }

    public function messages(): array
    {
        return ['email.in' => 'Registered email cannot be changed.'];
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required','string','min:3','max:150',"regex:/^[\pL\pM .'-]+$/u"],
            'email' => ['sometimes','required',Rule::in([$this->user()->email])],
            'phone' => ['required',new TenDigitPhone],
            'current_password' => ['nullable','required_with:password','current_password'],
            'password' => ['nullable','confirmed',Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ];
    }
}
