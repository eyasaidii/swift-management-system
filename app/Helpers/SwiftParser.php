<?php

namespace App\Helpers;

class SwiftParser
{
    /**
     * Parse le tag 32A (Date/Devise/Montant)
     * Format attendu : JJMMAA DEV MONTANT (ex: 060326EUR1250,50)
     * Retourne [date, devise, montant]
     */
    public static function parse32A($value)
    {
        // Supprimer les espaces
        $value = preg_replace('/\s+/', '', $value);
        // Format: 2j 2m 2a 3 lettres devise montant (avec virgule décimale)
        $pattern = '/^(\d{2})(\d{2})(\d{2})([A-Z]{3})([\d,]+)$/';
        if (preg_match($pattern, $value, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            $currency = $matches[4];
            $amount = str_replace(',', '.', $matches[5]); // remplacer virgule par point
            $date = '20' . $year . '-' . $month . '-' . $day; // format YYYY-MM-DD
            return [$date, $currency, (float)$amount];
        }
        return [null, null, null];
    }

    /**
     * Parse le tag 32B (Devise/Montant) – format plus simple
     * Format: DEV MONTANT (ex: EUR1250,50)
     */
    public static function parse32B($value)
    {
        $value = preg_replace('/\s+/', '', $value);
        $pattern = '/^([A-Z]{3})([\d,]+)$/';
        if (preg_match($pattern, $value, $matches)) {
            $currency = $matches[1];
            $amount = str_replace(',', '.', $matches[2]);
            return [$currency, (float)$amount];
        }
        return [null, null];
    }
}