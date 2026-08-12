<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Format a number as a compact K/M/B string (e.g. 125000 -> "125K",
     * 4200000 -> "4.2M") so large figures don't overflow fixed-width
     * card layouts. Numbers under 1000 are returned as plain formatted
     * numbers with no suffix.
     */
    public static function compact($number, $decimals = 1)
    {
        $number = (float) $number;
        $sign = $number < 0 ? '-' : '';
        $number = abs($number);

        if ($number >= 1_000_000_000) {
            return $sign . self::trimZeros($number / 1_000_000_000, $decimals) . 'B';
        }

        if ($number >= 1_000_000) {
            return $sign . self::trimZeros($number / 1_000_000, $decimals) . 'M';
        }

        if ($number >= 1_000) {
            return $sign . self::trimZeros($number / 1_000, $decimals) . 'K';
        }

        return $sign . number_format($number, $number == (int) $number ? 0 : $decimals);
    }

    // Drops a pointless trailing ".0" (1.0K -> 1K) while keeping real
    // fractional precision (1.5K stays 1.5K).
    private static function trimZeros($value, $decimals)
    {
        $formatted = number_format($value, $decimals);
        return rtrim(rtrim($formatted, '0'), '.');
    }
}
