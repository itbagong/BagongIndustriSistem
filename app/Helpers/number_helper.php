<?php

if (!function_exists('normalize_number')) {
    function normalize_number($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $value = trim((string)$value);
        $value = str_replace([' ', "\u{00A0}"], '', $value);

        // format Indonesia: 1.234.567,89
        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } 
        // format 1,234,567 atau 1234,56
        else {
            $value = str_replace(',', '', $value);
        }

        return (float) $value;
    }
}
