<?php

namespace Core;

class Design
{
    private static ?array $config = null;

    public static function config(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../config/design.php';
        }
        return self::$config;
    }

    public static function fontUrl(): string
    {
        return self::config()['font']['provider'] ?? '';
    }

    public static function stylesheets(): array
    {
        return self::config()['stylesheets'] ?? [];
    }

    public static function bodyStyle(): string
    {
        $config = self::config();
        $colors = $config['colors'] ?? [];
        $layout = $config['layout'] ?? [];
        $background = $config['background'] ?? [];

        $tokens = [
            'nt-font' => $config['font']['family'] ?? 'Inter, sans-serif',
            'nt-primary' => $colors['primary'] ?? '#3A6EA5',
            'nt-primary-l' => $colors['primary_light'] ?? '#DDEAF5',
            'nt-primary-h' => $colors['primary_hover'] ?? '#2563A8',
            'nt-secondary' => $colors['secondary'] ?? '#1E487E',
            'nt-accent' => $colors['accent'] ?? '#1BA784',
            'nt-accent-soft' => $colors['accent_soft'] ?? '#E7F7F2',
            'nt-success' => $colors['success'] ?? '#27AE60',
            'nt-warning' => $colors['warning'] ?? '#F2994A',
            'nt-danger' => $colors['danger'] ?? '#EB5757',
            'nt-body' => $colors['body'] ?? '#EAF1FB',
            'nt-card' => $colors['surface'] ?? '#FFFFFF',
            'nt-text' => $colors['text'] ?? '#1C2B3A',
            'nt-muted' => $colors['muted'] ?? '#7A8FA8',
            'nt-line' => $colors['line'] ?? '#DDE8F4',
            'nt-radius' => $layout['radius'] ?? '14px',
            'nt-card-radius' => $layout['card_radius'] ?? '18px',
            'sb-w' => $layout['sidebar_width'] ?? '286px',
            'sb-w-collapsed' => $layout['sidebar_collapsed_width'] ?? '68px',
            'nt-grid-size' => $background['grid_size'] ?? '34px',
            'nt-pattern-opacity' => $background['pattern_opacity'] ?? '.055',
            'nt-page-bg' => $background['page_gradient'] ?? 'linear-gradient(135deg, #F7FAFD, #EAF1FB)',
            'nt-sidebar-bg' => $background['sidebar_gradient'] ?? 'linear-gradient(180deg, #102947, #0F5B67)',
            'nt-brand-bg' => $background['brand_gradient'] ?? 'linear-gradient(135deg, #3A6EA5, #1BA784)',
        ];

        return implode(';', array_map(
            fn(string $key, string $value) => '--' . $key . ':' . $value,
            array_keys($tokens),
            $tokens
        ));
    }
}
