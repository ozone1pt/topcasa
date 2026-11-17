<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidNif implements ValidationRule
{
    /**
     * Validates a Portuguese NIF (Número de Identificação Fiscal) checksum:
     * mod-11 check digit over the first 8 digits, weighted 9..2.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^\d{9}$/', (string) $value)) {
            $fail('O :attribute deve ter 9 dígitos.');

            return;
        }

        $digits = array_map('intval', str_split($value));
        $sum = 0;

        for ($i = 0; $i < 8; $i++) {
            $sum += $digits[$i] * (9 - $i);
        }

        $checkDigit = 11 - ($sum % 11);
        $checkDigit = $checkDigit >= 10 ? 0 : $checkDigit;

        if ($checkDigit !== $digits[8]) {
            $fail('O :attribute não é válido.');
        }
    }
}
