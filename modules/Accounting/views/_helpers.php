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

if (!function_exists('nt_record_state_class')) {
    function nt_record_state_class(?string $state): string
    {
        return match ($state ?: 'published') {
            'draft' => 'orange',
            'verification' => '',
            default => 'green',
        };
    }
}

if (!function_exists('nt_record_state_label')) {
    function nt_record_state_label(?string $state): string
    {
        return match ($state ?: 'published') {
            'draft' => 'Draft',
            'verification' => 'Verification',
            default => 'Published',
        };
    }
}
