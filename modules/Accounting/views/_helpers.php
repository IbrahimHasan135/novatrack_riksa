<?php

if (!function_exists('nt_money')) {
    function nt_money(float|int|string|null $value): string
    {
        return 'Rp ' . number_format((float)$value, 0, ',', '.');
    }
}

if (!function_exists('nt_date')) {
    function nt_date(?string $value): string
    {
        return $value ? date('d M Y', strtotime($value)) : '-';
    }
}
