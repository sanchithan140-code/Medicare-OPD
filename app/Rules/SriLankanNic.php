<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SriLankanNic implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || ! preg_match('/^(?:[0-9]{9}[VvXx]|[0-9]{12})$/', $value)) {
            $fail('The :attribute must be a valid 10-character old NIC or 12-digit new NIC.');
        }
    }
}
