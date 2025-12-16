<?php

namespace App\Support;

class Phone
{
    public static function normalize(?string $phone, string $defaultCountry = '55'): ?string
    {
        if (!$phone) return null;

        // mantém só dígitos
        $digits = preg_replace('/\D+/', '', $phone) ?: '';
        if ($digits === '') return null;

        // Se vier sem DDI e parecer BR, prefixa 55
        // Regra simples: se tem 10-11 dígitos, assume BR (DDD+numero)
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            $digits = $defaultCountry . $digits;
        }

        return $digits;
    }
}
