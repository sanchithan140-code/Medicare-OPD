<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SriLankanMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^07[0-9]{8}$/', $value)) {
            $fail('The :attribute must be a valid 10-digit Sri Lankan mobile number beginning with 07.');
        }
    }
}
