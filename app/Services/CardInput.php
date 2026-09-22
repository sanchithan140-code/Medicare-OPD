<?php

namespace App\Services;

class CardInput
{
    public static function validNumber(string $number): bool
    {
        return preg_match('/^[0-9]{16}$/D', $number) === 1;
    }

    public static function validExpiry(string $expiry): bool
    {
        if (! preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/D', $expiry, $parts)) {
            return false;
        }
        return (2000 + (int) $parts[2]) * 12 + (int) $parts[1] >= now()->year * 12 + now()->month;
    }
}
