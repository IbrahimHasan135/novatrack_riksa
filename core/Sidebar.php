<?php

namespace Core;

class Sidebar
{
    private ModuleRegistry $registry;

    public function __construct(ModuleRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Render HTML full sidebar (kotak kiri)
     * @return string
     */
    public function render(): string
    {
        ob_start();

        $auth        = Auth::getInstance();
        $rbac        = new Rbac();
        $user        = $auth->check() ? $auth->user() : null;
        $menuItems   = $this->registry->allMenuItems();
        $modulesList = $this->registry->allModules();
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $currentPath = rtrim($currentPath, '/');
        if ($currentPath === '') $currentPath = '/';

        // Group nav items by module
        $byModule = [];
        foreach ($menuItems as $item) {
            $byModule[$item['module']][] = $item;
        }
        ?>

        <!-- Toggle button (mobile / collapsed) -->
        <button class="sidebar-toggle" id="sidebar_toggle" type="button" aria-label="Toggle menu">
            <i class="bi bi-list"></i>
        </button>

        <!-- Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebar_overlay"></div>

        <!-- ===================== SIDEBAR ===================== -->
        <aside class="sidebar" id="sidebar">

            <!-- Brand -->
            <div class="sidebar-brand">
                <span class="brand-icon-sm"><i class="bi bi-collection-play-fill"></i></span>
                <div>
                    <span class="brand-name">NovaTrack</span>
                    <small class="brand-tag">Riksa v1.0</small>
                </div>
            </div>

            <!-- Search mini -->
            <div class="sidebar-search">
                <i class="bi bi-search"></i>
                <input type="text" class="sidebar-search-input" placeholder="Cari menu...">
            </div>

            <!-- Navigation — satu grup per module yang aktif -->
            <nav class="sidebar-nav" id="sidebarNav">
                <div class="nav-module-group" data-module="core">
                    <p class="nav-section-label">
                        <i class="bi bi-grid-1x2-fill me-1"></i>
                        Workspace
                    </p>
                    <?php $dashboardHref = \app_url('dashboard'); ?>
                    <a href="<?= htmlspecialchars($dashboardHref); ?>"
                       class="nav-link <?= rtrim($dashboardHref, '/') === $currentPath ? 'active' : ''; ?>"
                       title="Dashboard">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                    <?php if ($rbac->canManageRoles($user)): ?>
                    <a href="<?= htmlspecialchars(\app_url('roles')); ?>"
                       class="nav-link <?= rtrim(\app_url('roles'), '/') === $currentPath ? 'active' : ''; ?>"
                       title="Role Management">
                        <i class="bi bi-shield-lock"></i>
                        <span>Role Management</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($rbac->canManageUsers($user)): ?>
                    <a href="<?= htmlspecialchars(\app_url('users')); ?>"
                       class="nav-link <?= rtrim(\app_url('users'), '/') === $currentPath ? 'active' : ''; ?>"
                       title="Users">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                    <?php endif; ?>
                </div>

                <?php foreach ($modulesList as $mod): ?>
                <?php if (!$this->registry->isEnabled($mod->slug) || !$rbac->canAccessModule($mod->slug, $user)) continue;
                      $items = $byModule[$mod->slug] ?? []; ?>

                <div class="nav-module-group" data-module="<?= htmlspecialchars($mod->slug); ?>">
                    <p class="nav-section-label">
                        <i class="bi <?= htmlspecialchars($mod->icon); ?> me-1"></i>
                        <?= htmlspecialchars($mod->label); ?>
                    </p>
                    <?php foreach ($items as $item): ?>
                    <?php
                        $href  = rtrim($item['href'], '/') ?: '/';
                        $isAct = ($href === $currentPath || $href === rtrim($currentPath, '/'));
                    ?>
                    <a href="<?= htmlspecialchars($item['href']); ?>"
                       class="nav-link <?= $isAct ? 'active' : ''; ?>"
                       title="<?= htmlspecialchars($item['label']); ?>">
                        <i class="bi <?= htmlspecialchars($item['icon']); ?>"></i>
                        <span><?= htmlspecialchars($item['label']); ?></span>
                        <?php if (!empty($item['badge'])): ?>
                            <span class="badge-count"><?= htmlspecialchars($item['badge']); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <?php endforeach; ?>

            </nav>

            <!-- Footer — user info + logout -->
            <div class="sidebar-footer">
                <a href="<?= htmlspecialchars(\app_url('logout')); ?>" class="logout-btn" onclick="return confirm('Logout dari sistem?')">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>
        <?php

        return ob_get_clean();
    }
}
