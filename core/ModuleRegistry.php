<?php

namespace Core;

/**
 * ModuleRegistry  —  Global singletone registry
 * Menampung semua metadata dari setiap module yang di-load.
 *
 * Semua entry adalah map:  slug => array(meta)
 */
class ModuleRegistry
{
    private static ?ModuleRegistry $instance = null;
    private array $menu       = [];
    private array $cards      = [];
    private array $tables     = [];
    private array $modules    = [];
    private array $booted     = [];

    public static function getInstance(): ModuleRegistry
    {
        if (self::$instance === null) {
            self::$instance = new ModuleRegistry();
        }
        return self::$instance;
    }

    /* ─── menu ─── */
    /** @param array{slug:string,label:string,icon:string,href:string,badge?:string} $item */
    public function registerMenuItem(string $moduleSlug, array $item): void
    {
        $this->menu[$moduleSlug][] = $item;
    }

    /** @return array<int, array{slug:string,label:string,icon:string,href:string,badge?:string,module:string}> */
    public function allMenuItems(): array
    {
        $out = [];
        foreach (array_keys($this->modules) as $slug) {
            foreach (($this->menu[$slug] ?? []) as $item) {
                $out[] = array_merge($item, ['module' => $slug]);
            }
        }
        return $out;
    }

    /* ─── dashboard cards ─── */
    /** @param DashboardCard|array{id:string,title:string,icon:string,colspan:int,order:int,callback:callable} $card */
    public function registerCard(string $moduleSlug, DashboardCard|array $card): void
    {
        $this->cards[$moduleSlug][] = $card;
    }

    /** @return DashboardCard[] sorted by order */
    public function allCards(): array
    {
        $flat = [];
        foreach (array_keys($this->modules) as $slug) {
            foreach (($this->cards[$slug] ?? []) as $c) {
                if ($c instanceof DashboardCard) {
                    $c->module = $slug;
                    $flat[] = $c;
                    continue;
                }
                $flat[] = new DashboardCard(array_merge($c, ['module' => $slug]));
            }
        }
        usort($flat, fn($a, $b) => ($a->order ?? 0) <=> ($b->order ?? 0));
        return $flat;
    }

    /* ─── required tables ─── */
    public function registerTable(string $moduleSlug, string $tableName, string|array $schema): void
    {
        $this->tables[$tableName] = $schema;
    }

    /** @return array<string,string|array> table => schema */
    public function allTables(): array
    {
        return $this->tables;
    }

    /* ─── module registration ─── */
    public function registerModule(string $slug, string $label, string $icon, string $path): void
    {
        $this->modules[$slug] = (object)[
            'slug'  => $slug,
            'label' => $label,
            'icon'  => $icon,
            'path'  => rtrim($path, '/'),
            'enabled' => true,
        ];
    }

    /** @return object[] */
    public function allModules(): array
    {
        return $this->modules;
    }

    public function getModule(string $slug): ?object
    {
        return $this->modules[$slug] ?? null;
    }

    public function isEnabled(string $slug): bool
    {
        return ($this->modules[$slug]->enabled ?? false) === true;
    }

    public function setEnabled(string $slug, bool $enabled): void
    {
        if (isset($this->modules[$slug])) {
            $this->modules[$slug]->enabled = $enabled;
        }
    }

    /* ─── bootstrap ─── */
    /** Mark a module as booted so boot() is not called twice */
    public function markBooted(string $slug): void
    {
        $this->booted[] = $slug;
    }

    public function isBooted(string $slug): bool
    {
        return in_array($slug, $this->booted, true);
    }
}

/**
 * DashboardCard  —  value object, satu kartu di dashboard
 */
class DashboardCard
{
    public string $id;
    public string $title;
    public string $icon;
    public int $colspan;
    public int $order;
    /** @var callable|null */
    public $callback;
    public string $module;
    public string $content; // rendered HTML

    public function __construct(array $data)
    {
        $this->id       = $data['id']       ?? '';
        $this->title    = $data['title']    ?? '';
        $this->icon     = $data['icon']     ?? 'bi-circle';
        $this->colspan  = $data['colspan']  ?? 12;
        $this->order    = $data['order']    ?? 999;
        $this->callback = $data['callback'] ?? null;
        $this->module   = $data['module']   ?? '';
        $this->content  = '';
    }

    public static function fromModule(string $moduleSlug, array $data): self
    {
        return new self(array_merge($data, ['module' => $moduleSlug]));
    }
}
