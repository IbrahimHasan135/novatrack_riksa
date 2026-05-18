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

<style>
.case-shell { padding: 24px 28px 36px; }
.case-hero {
    display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;
    background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(231,247,242,.82));
    border:1px solid rgba(58,110,165,.14);border-radius:20px;padding:24px 26px;
    box-shadow:0 18px 46px rgba(30,72,126,.10);position:relative;overflow:hidden;
}
.case-hero::before { content:'';position:absolute;left:0;right:0;top:0;height:3px;background:linear-gradient(90deg,#3A6EA5,#1BA784,#F2994A); }
.case-kicker { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#1BA784;margin-bottom:6px;display:flex;gap:6px;align-items:center; }
.case-hero h1 { margin:0;color:#1C2B3A;font-size:28px;font-weight:800; }
.case-hero p { margin:5px 0 0;color:#416C92;font-size:13.5px;max-width:660px; }
.case-primary-btn, .type-create-panel button {
    display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;
    background:linear-gradient(135deg,#3A6EA5,#1BA784);color:#fff;text-decoration:none;
    padding:11px 18px;font-weight:800;font-size:13.5px;box-shadow:0 14px 34px rgba(27,167,132,.22);
}
.case-alert { margin-top:16px;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:700;display:flex;gap:8px;align-items:center; }
.case-alert.success { background:#E8F7EE;color:#1E7E34;border:1px solid #BFE7CE; }
.case-alert.danger { background:#FFF1F0;color:#C0392B;border:1px solid #FFCDC9; }
.type-create-panel {
    margin-top:18px;background:rgba(255,255,255,.82);border:1px solid rgba(58,110,165,.14);
    border-radius:18px;padding:18px;box-shadow:0 12px 34px rgba(30,72,126,.08);
}
.type-create-panel label { display:block;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#416C92;margin-bottom:8px; }
.type-create-panel form > div { display:flex;gap:10px; }
.type-create-panel input {
    flex:1;min-width:0;border:1.5px solid #DDE8F4;border-radius:12px;padding:11px 14px;
    font:14px Inter,sans-serif;outline:none;background:#fff;color:#1C2B3A;
}
.type-create-panel input:focus { border-color:#1BA784;box-shadow:0 0 0 4px rgba(27,167,132,.10); }
.type-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:18px; }
.type-card {
    background:rgba(255,255,255,.88);border:1px solid rgba(58,110,165,.14);border-radius:18px;padding:18px;
    box-shadow:0 16px 42px rgba(30,72,126,.10);transition:transform .2s,box-shadow .2s,border-color .2s;
}
.type-card:hover { transform:translateY(-3px);box-shadow:0 22px 56px rgba(30,72,126,.15);border-color:rgba(27,167,132,.28); }
.type-card-main { display:flex;gap:13px;align-items:center;text-decoration:none;color:#1C2B3A; }
.type-folder-icon { width:50px;height:50px;border-radius:15px;background:linear-gradient(135deg,#DDEAF5,#E7F7F2);display:flex;align-items:center;justify-content:center;color:#1BA784;font-size:26px; }
.type-card h2 { margin:0;font-size:17px;font-weight:800;color:#1C2B3A; }
.type-card p { margin:3px 0 0;font-size:12.5px;color:#416C92;font-weight:600; }
.type-stats { display:flex;gap:6px;flex-wrap:wrap;margin-top:15px; }
.badge-status { border-radius:999px;padding:4px 9px;font-size:11px;font-weight:800; }
.badge-status.verification { background:#E8F0FB;color:#3A6EA5; }
.badge-status.progress { background:#FFF8E1;color:#E09F3E; }
.badge-status.done { background:#E8F7EE;color:#27AE60; }
.badge-status.closed { background:#F0F2F5;color:#5A7089; }
.type-actions { display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:16px;border-top:1px solid #EEF4FA;padding-top:12px; }
.type-actions a, .type-actions button { background:#F7FAFD;border:1px solid #DDE8F4;border-radius:10px;color:#255F8C;padding:8px 11px;font-size:12px;font-weight:800;text-decoration:none;display:inline-flex;gap:6px;align-items:center; }
.type-actions button { color:#C0392B;cursor:pointer; }
.case-empty { margin-top:18px;text-align:center;background:#fff;border:1px dashed #BFD8F5;border-radius:18px;padding:44px;color:#416C92; }
.case-empty i { font-size:44px;color:#1BA784; }
@media(max-width: 575px){ .type-create-panel form > div { flex-direction:column; } }
</style>

</body>
</html>
