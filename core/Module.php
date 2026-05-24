<?php

namespace Core;

/**
 * Module  —  Modular system loader
 *
 * Urutan eksekusi (dipanggil dari index.php):
 *   1. Module::bootAll($registry)   → scan config/modules.php, require module.php, call boot()
 *   2. Module::loadRoutes($router)  → scan route files module, require tiap file
 *   3. Module::renderView(...)      → require file view dari folder module
 *
 * Setiap module (modules/ModuleName/module.php) MENGEMBALIKAN ModuleMeta:
 *   ->menuItems()        -> array<array{slug,label,icon,href,badge?}>
 *   ->tables()           -> array<string> nama tabel yang dibutuhkan
 *   ->dashboardCards()   -> array<DashboardCard>
 *   ->boot(ModuleRegistry $registry)  -> dipanggil setelah module di-load
 */
class Module
{
    /**
     * Scan config/modules.php, require tiap module.php, panggil boot()
     */
    public static function bootAll(ModuleRegistry $registry): void
    {
        $modules = require __DIR__ . '/../config/modules.php';

        foreach ($modules as $slug => $path) {
            $moduleFile = rtrim($path, '/') . '/module.php';
            if (!file_exists($moduleFile)) {
                continue;
            }
            /** @var ModuleMeta $meta */
            $meta = require $moduleFile;
            if (!$meta instanceof ModuleMeta) {
                $meta = new ModuleMeta('', '', '', $path);
            }

            if (empty($meta->slug)) {
                $meta->slug = $slug;
            }

            // Register in global registry
            $registry->registerModule($meta->slug, $meta->label, $meta->icon, $meta->path);

            // Register menu items
            foreach ((array)$meta->menuItems() as $item) {
                if (is_array($item)) {
                    $registry->registerMenuItem($meta->slug, $item);
                }
            }

            // Register tables for auto-migration
            foreach ((array)$meta->tables() as $tableName => $schema) {
                $registry->registerTable($meta->slug, (string)$tableName, $schema);
            }

            // Register dashboard cards
            foreach ((array)$meta->dashboardCards() as $card) {
                if ($card instanceof DashboardCard) {
                    $registry->registerCard($meta->slug, $card);
                }
            }

            foreach ((array)$meta->listeners() as $event => $listeners) {
                foreach ((array)$listeners as $listener) {
                    if (is_callable($listener)) {
                        EventBus::listen((string)$event, $listener);
                    }
                }
            }

            // Call module boot() hook
            if (method_exists($meta, 'boot') && !$registry->isBooted($meta->slug)) {
                $meta->boot($registry);
                $registry->markBooted($meta->slug);
            }
        }
    }

    /**
     * Load semua routes dari setiap module aktif
     */
    public static function loadRoutes(Router $router): void
    {
        $modules = require __DIR__ . '/../config/modules.php';
        $registry = ModuleRegistry::getInstance();

        foreach ($modules as $slug => $path) {
            if (!$registry->isEnabled($slug)) {
                continue;
            }
            $routesFile = rtrim($path, '/') . '/routes.php';
            if (file_exists($routesFile)) {
                require $routesFile;
            }
        }
    }

    /**
     * Render view dari module yang dipilih.
     * Cari file di: modules/ModuleName/views/{path}.php
     */
    public static function renderView(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = sprintf('%s/modules/%s', dirname(__DIR__, 1), $path);
        $viewFile .= '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo '<div class="p-5 text-center text-danger">View tidak ditemukan: <code>' .
                 htmlspecialchars($path) . '</code></div>';
        }
    }

    /**
     * View alias — sama dengan renderView tapi bisa dipanggil dari controller
     */
    public static function view(string $path, array $data = []): void
    {
        self::renderView($path, $data);
    }
}

/**
 * ModuleMeta  —  Interface yang DIKEMBALIKAN oleh setiap module.php
 * Setiap module Define sendiri: menu, card dashboard, tabel yang dibutuhkan.
 */
class ModuleMeta
{
    public string $slug;
    public string $label;
    public string $icon;      // Bootstrap Icon name (tanpa prefix "bi-")
    public string $path;      // Absolute path ke folder module

    public function __construct(string $slug, string $label, string $icon, string $path)
    {
        $this->slug  = $slug;
        $this->label = $label;
        $this->icon  = $icon;
        $this->path  = rtrim($path, '/');
    }

    /** @return array<array{slug:string,label:string,icon:string,href:string,badge?:string}> */
    public function menuItems(): array { return []; }

    /** @return array<string> Daftar tabel yang dibutuhkan module */
    public function tables(): array { return []; }

    /** @return array<DashboardCard|array> */
    public function dashboardCards(): array { return []; }

    /** @return array<string, callable[]> */
    public function listeners(): array { return []; }

    /** Hook setelah module di-register */
    public function boot(ModuleRegistry $registry): void {}
}
