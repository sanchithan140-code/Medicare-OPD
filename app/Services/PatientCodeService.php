<?php

namespace App\Services;

use App\Models\Patient;
use RuntimeException;

class PatientCodeService
{
    public function generate(): string
    {
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $code = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            if (! Patient::where('patient_code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Unable to generate a unique four-digit patient code.');
    }
}
