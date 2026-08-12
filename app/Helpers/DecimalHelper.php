<?php

namespace App\Helpers;

class DecimalHelper
{
    /**
     * Add two decimal numbers with precision
     */
    public static function add($a, $b, $precision = 2)
    {
        if (function_exists('bcadd')) {
            return (float) bcadd((string) $a, (string) $b, $precision);
        }
        return round($a + $b, $precision);
    }

    /**
     * Subtract two decimal numbers with precision
     */
    public static function subtract($a, $b, $precision = 2)
    {
        if (function_exists('bcsub')) {
            return (float) bcsub((string) $a, (string) $b, $precision);
        }
        return round($a - $b, $precision);
    }

    /**
     * Multiply two decimal numbers with precision
     */
    public static function multiply($a, $b, $precision = 2)
    {
        if (function_exists('bcmul')) {
            return (float) bcmul((string) $a, (string) $b, $precision);
        }
        return round($a * $b, $precision);
    }

    /**
     * Divide two decimal numbers with precision
     */
    public static function divide($a, $b, $precision = 2)
    {
        if ($b == 0) {
            return 0;
        }
        if (function_exists('bcdiv')) {
            return (float) bcdiv((string) $a, (string) $b, $precision);
        }
        return round($a / $b, $precision);
    }
}