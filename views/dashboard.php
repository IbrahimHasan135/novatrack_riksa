<?php
$isLoginPage = false;
$pageTitle   = 'Dashboard — NovaTrack';
require_once __DIR__ . '/layout/header.php';

use Core\Auth;
use Core\ModuleRegistry;

$auth     = Auth::getInstance();
$user     = $auth->check() ? $auth->user() : null;
$registry = ModuleRegistry::getInstance();
?>

<!-- TOPBAR (di bawah sidebar) -->
<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-left">
            <span class="topbar-breadcrumb">
                <i class="bi bi-speedometer2"></i>  Dashboard
            </span>
        </div>
        <div class="topbar-search">
            <i class="bi bi-search"></i>
            <input class="topbar-search-input" type="text" placeholder="Cari case, menu, modul...">
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting">Halo, <strong><?= htmlspecialchars($user['full_name'] ?? 'User'); ?></strong></span>
            <div class="topbar-avatar" title="<?= htmlspecialchars($user['role'] ?? 'user'); ?>">
                <?= strtoupper(mb_substr($user['full_name'] ?? $user['username'] ?? 'U', 0, 1)); ?>
            </div>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<main class="main-content" id="mainContent">

    <!-- Greeting bar -->
    <div class="greeting-bar">
        <div class="greeting-tag"><i class="bi bi-sun me-1"></i><?= date('l, d F Y'); ?></div>
        <h1 class="greeting-h">Selamat Datang, <?= htmlspecialchars($user['full_name'] ?? 'User'); ?></h1>
        <p class="greeting-sub">Ringkasan semua modul yang aktif hari ini.</p>
    </div>

    <?php $cards = $registry->allCards(); ?>

    <!-- MODULE SECTIONS -->
    <?php if (empty($cards)): ?>

        <div class="empty-state">
            <i class="bi bi-inbox empty-state-icon"></i>
            <h3>Belum ada modul aktif</h3>
            <p>Tambah module di <code>config/modules.php</code> untuk mulai melihat statistik dan card.</p>
        </div>

    <?php else: ?>

        <?php
        // Group cards by module to show one section per module
        $displayedSlugs = [];
        foreach ($cards as $card):
            if (isset($displayedSlugs[$card->module])) continue;
            $displayedSlugs[$card->module] = true;
            $modInfo  = $registry->getModule($card->module);
            $modLabel = $modInfo->label ?? ucfirst($card->module);
            $modIcon  = $modInfo->icon  ?? 'bi-box';
            $modSlug  = $card->module;

            // Collect all cards for this module
            $modCards = array_values(array_filter($cards, fn($c) => $c->module === $modSlug));

            // Module menu links
            $menuItems   = $registry->allMenuItems();
            $modMenuLinks = array_values(array_filter($menuItems, fn($m) => $m['module'] === $modSlug));
        ?>
        <!-- Section: <?= htmlspecialchars($modLabel); ?> -->
        <div class="module-section" data-module="<?= htmlspecialchars($modSlug); ?>">

            <!-- Module header -->
            <div class="module-section-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi <?= htmlspecialchars($modIcon); ?> module-section-icon"></i>
                    <span class="module-section-title"><?= htmlspecialchars($modLabel); ?></span>
                </div>
                <div class="module-section-actions">
                    <?php foreach ($modMenuLinks as $ml): ?>
                        <a href="<?= htmlspecialchars($ml['href']); ?>"
                           class="dash-link ms-3"
                           title="<?= htmlspecialchars($ml['label']); ?>">
                            <i class="bi <?= htmlspecialchars($ml['icon']); ?>"></i>
                            <?= htmlspecialchars($ml['label']); ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= app_url('cases/create'); ?>" class="dash-link ms-3" style="background:var(--nt-primary);color:#fff;border-radius:8px;padding:5px 14px;text-decoration:none;font-size:12px;font-weight:700;">
                        <i class="bi bi-plus-lg"></i>  Tambah
                    </a>
                </div>
            </div>

            <!-- Cards grid -->
            <div class="dash-cards-grid">
                <?php foreach ($modCards as $card): ?>
                    <?php $card->content = call_user_func($card->callback); ?>
                    <div class="nt-card dash-card" data-card-id="<?= htmlspecialchars($card->id); ?>">
                        <div class="dash-card-hdr">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi <?= htmlspecialchars($card->icon); ?> dash-card-hdr-icon"></i>
                                <span class="dash-card-hdr-title"><?= htmlspecialchars($card->title); ?></span>
                            </div>
                            <span class="badge badge-module"><?= htmlspecialchars(ucfirst($modSlug)); ?></span>
                        </div>
                        <div class="dash-card-body">
                            <?= $card->content ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div><!-- /module-section -->

        <?php endforeach; ?>

    <?php endif; ?>

</main><!-- /main-content -->

<!-- Footer -->
<footer class="app-footer">
    <span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span>
    <span class="app-footer-sep">|</span>
    <span>Case Tracker System</span>
    <span class="app-footer-sep">|</span>
    <span>v1.0</span>
</footer>

</body>
</html>
