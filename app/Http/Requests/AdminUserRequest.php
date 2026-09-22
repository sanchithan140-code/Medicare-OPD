<?php

namespace App\Http\Requests;

use App\Rules\TenDigitPhone;
use App\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('admin', 'main_admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')),
            'full_name' => trim((string) $this->input('full_name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'role_id' => ['required', Rule::exists('roles', 'role_id')->whereIn('role_name', Role::CREATABLE_ROLES)],
            'full_name' => ['required','string','min:3','max:150',"regex:/^[\pL\pM .'-]+$/u"],
            'email' => ['required','email:rfc','max:190','unique:users,email'],
            'phone' => ['required', new TenDigitPhone],
            'password' => ['required', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ];
    }
}
