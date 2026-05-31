<?php
$isLoginPage = false;
$pageTitle = 'Case Types - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';

use Core\Auth;

$auth = Auth::getInstance();
$user = $auth->check() ? $auth->user() : null;
?>

<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-left">
            <span class="topbar-breadcrumb"><i class="bi bi-folder-fill"></i> Cases / Types</span>
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting">Halo, <strong><?= htmlspecialchars($user['full_name'] ?? 'User'); ?></strong></span>
            <div class="topbar-avatar"><?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)); ?></div>
        </div>
    </div>
</div>

<main class="main-content">
    <section class="case-shell">
        <div class="case-hero">
            <div>
                <div class="case-kicker"><i class="bi bi-grid-1x2-fill"></i> Case Workspace</div>
                <h1>Case Types</h1>
                <p>Pilih folder type untuk melihat case tracker di dalamnya, atau buat type baru untuk pengelompokan kerja.</p>
            </div>
            <a href="<?= app_url('cases/create'); ?>" class="case-primary-btn"><i class="bi bi-plus-lg"></i> New Case</a>
        </div>

        <?php if (($_GET['type_error'] ?? '') === 'not_empty'): ?>
            <div class="case-alert danger"><i class="bi bi-exclamation-triangle"></i> Type tidak bisa dihapus karena masih berisi case.</div>
        <?php elseif (isset($_GET['type_created'])): ?>
            <div class="case-alert success"><i class="bi bi-check-circle"></i> Type berhasil dibuat.</div>
        <?php elseif (isset($_GET['type_deleted'])): ?>
            <div class="case-alert success"><i class="bi bi-check-circle"></i> Type berhasil dihapus.</div>
        <?php endif; ?>

        <div class="type-create-panel">
            <form action="<?= app_url('cases/types'); ?>" method="POST">
                <label for="type_name">Create Type</label>
                <div>
                    <input id="type_name" name="name" type="text" placeholder="Contoh: Infrastructure, Security, HR Issue" required>
                    <button type="submit"><i class="bi bi-folder-plus"></i> Add Type</button>
                </div>
            </form>
        </div>

        <?php if (empty($types ?? [])): ?>
            <div class="case-empty">
                <i class="bi bi-folder2-open"></i>
                <h2>Belum ada type</h2>
                <p>Buat type pertama untuk mulai mengelompokkan case tracker.</p>
            </div>
        <?php else: ?>
            <div class="type-grid">
                <?php foreach ($types as $type): ?>
                    <article class="type-card">
                        <a href="<?= app_url('cases/type/' . (int)$type['id']); ?>" class="type-card-main">
                            <div class="type-folder-icon"><i class="bi bi-folder-fill"></i></div>
                            <div>
                                <h2><?= htmlspecialchars($type['name']); ?></h2>
                                <p><?= (int)$type['case_count']; ?> case tracker</p>
                            </div>
                        </a>
                        <div class="type-stats">
                            <span class="badge-status verification">Verification <?= (int)$type['verification_count']; ?></span>
                            <span class="badge-status progress">In Progress <?= (int)$type['progress_count']; ?></span>
                            <span class="badge-status done">Done <?= (int)$type['done_count']; ?></span>
                            <span class="badge-status closed">Close <?= (int)$type['closed_count']; ?></span>
                        </div>
                        <div class="type-actions">
                            <a href="<?= app_url('cases/create?type_id=' . (int)$type['id']); ?>"><i class="bi bi-plus-circle"></i> Case</a>
                            <form action="<?= app_url('cases/types/delete/' . (int)$type['id']); ?>" method="POST" onsubmit="return confirm('Hapus type ini? Type yang masih berisi case tidak akan bisa dihapus.');">
                                <button type="submit"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer class="app-footer">
    <span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span>
    <span class="app-footer-sep">|</span>
    <span>Case Tracker</span>
</footer>

</body>
</html>
