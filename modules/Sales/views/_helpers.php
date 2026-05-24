<?php

if (!function_exists('sales_money')) {
    function sales_money(float|int|string|null $value): string
    {
        return 'Rp ' . number_format((float)$value, 0, ',', '.');
    }
}

if (!function_exists('sales_date')) {
    function sales_date(?string $value): string
    {
        return $value ? date('d M Y', strtotime($value)) : '-';
    }
}

if (!function_exists('sales_label')) {
    function sales_label(?string $value): string
    {
        return ucwords(str_replace('_', ' ', (string)$value));
    }
}
