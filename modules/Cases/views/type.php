<?php
$isLoginPage = false;
$pageTitle = ($type['name'] ?? 'Cases') . ' - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';

use Core\Auth;

$auth = Auth::getInstance();
$user = $auth->check() ? $auth->user() : null;

function casePriorityBadge(string $priority): string {
    $map = [
        'normal' => ['Normal', '#E8F7EE', '#27AE60'],
        'medium' => ['Medium', '#FFF8E1', '#E09F3E'],
        'high' => ['High', '#FFF1F0', '#EB5757'],
        'critical' => ['Critical', '#FFF1F0', '#C0392B'],
    ];
    [$label, $bg, $fg] = $map[$priority] ?? $map['normal'];
    return "<span class=\"pill\" style=\"background:$bg;color:$fg;\">$label</span>";
}
function caseStatusBadge(string $status): string {
    $map = [
        'verification' => ['Verification', '#E8F0FB', '#3A6EA5'],
        'in_progress' => ['In Progress', '#FFF8E1', '#E09F3E'],
        'done' => ['Done', '#E8F7EE', '#27AE60'],
        'closed' => ['Close', '#F0F2F5', '#5A7089'],
    ];
    [$label, $bg, $fg] = $map[$status] ?? $map['verification'];
    return "<span class=\"pill\" style=\"background:$bg;color:$fg;\">$label</span>";
}
?>

<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-left">
            <span class="topbar-breadcrumb"><i class="bi bi-folder2-open"></i> Cases / <?= htmlspecialchars($type['name']); ?></span>
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting">Halo, <strong><?= htmlspecialchars($user['full_name'] ?? 'User'); ?></strong></span>
            <div class="topbar-avatar"><?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)); ?></div>
        </div>
    </div>
</div>

<main class="main-content">
    <section class="case-shell">
        <a class="back-link" href="<?= app_url('cases'); ?>"><i class="bi bi-arrow-left"></i> Back to types</a>
        <div class="case-hero">
            <div>
                <div class="case-kicker"><i class="bi bi-folder-fill"></i> Type Folder</div>
                <h1><?= htmlspecialchars($type['name']); ?></h1>
                <p>Tabel ini hanya menampilkan priority, status, tanggal dibuat, dan deadline. Klik detail untuk information dan personal note.</p>
            </div>
            <a href="<?= app_url('cases/create?type_id=' . (int)$type['id']); ?>" class="case-primary-btn"><i class="bi bi-plus-lg"></i> New Case</a>
        </div>

        <?php if (isset($_GET['created'])): ?><div class="case-alert success"><i class="bi bi-check-circle"></i> Case berhasil dibuat.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="case-alert success"><i class="bi bi-check-circle"></i> Case berhasil dihapus.</div><?php endif; ?>

        <div class="table-card">
            <table class="case-table">
                <thead>
                    <tr>
                        <th>Case</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Deadline</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cases)): ?>
                        <tr><td colspan="6" class="empty-row">Belum ada case di type ini.</td></tr>
                    <?php else: foreach ($cases as $case): ?>
                        <tr>
                            <td>
                                <strong>#<?= (int)$case['id']; ?> <?= htmlspecialchars($case['title']); ?></strong>
                                <small><?= htmlspecialchars($case['description'] ?: 'Tidak ada deskripsi'); ?></small>
                            </td>
                            <td><?= casePriorityBadge($case['priority'] ?? 'normal'); ?></td>
                            <td><?= caseStatusBadge($case['status'] ?? 'verification'); ?></td>
                            <td><?= date('d M Y', strtotime($case['created_at'] ?? 'now')); ?></td>
                            <td><?= !empty($case['deadline']) ? date('d M Y', strtotime($case['deadline'])) : '-'; ?></td>
                            <td style="text-align:right;">
                                <a class="mini-btn" href="<?= app_url('cases/detail/' . (int)$case['id']); ?>"><i class="bi bi-eye"></i> Detail</a>
                                <a class="mini-btn" href="<?= app_url('cases/edit/' . (int)$case['id']); ?>"><i class="bi bi-pencil"></i> Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Case Tracker</span></footer>

<style>
.case-shell{padding:24px 28px 36px}.back-link{display:inline-flex;gap:6px;align-items:center;margin-bottom:14px;color:#255F8C;text-decoration:none;font-weight:800;font-size:13px}.case-hero{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(231,247,242,.82));border:1px solid rgba(58,110,165,.14);border-radius:20px;padding:24px 26px;box-shadow:0 18px 46px rgba(30,72,126,.10);position:relative;overflow:hidden}.case-hero::before{content:'';position:absolute;left:0;right:0;top:0;height:3px;background:linear-gradient(90deg,#3A6EA5,#1BA784,#F2994A)}.case-kicker{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#1BA784;margin-bottom:6px;display:flex;gap:6px;align-items:center}.case-hero h1{margin:0;color:#1C2B3A;font-size:28px;font-weight:800}.case-hero p{margin:5px 0 0;color:#416C92;font-size:13.5px;max-width:720px}.case-primary-btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;background:linear-gradient(135deg,#3A6EA5,#1BA784);color:#fff;text-decoration:none;padding:11px 18px;font-weight:800;font-size:13.5px;box-shadow:0 14px 34px rgba(27,167,132,.22)}.case-alert{margin-top:16px;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:700;display:flex;gap:8px;align-items:center}.case-alert.success{background:#E8F7EE;color:#1E7E34;border:1px solid #BFE7CE}.table-card{margin-top:18px;background:rgba(255,255,255,.9);border:1px solid rgba(58,110,165,.14);border-radius:18px;overflow:auto;box-shadow:0 16px 42px rgba(30,72,126,.10)}.case-table{width:100%;border-collapse:collapse;min-width:820px}.case-table th{background:#F7FAFD;color:#1E487E;text-align:left;text-transform:uppercase;letter-spacing:.06em;font-size:11px;padding:13px 16px;border-bottom:1px solid #DDE8F4}.case-table td{padding:14px 16px;border-bottom:1px solid #EEF4FA;color:#1C2B3A;font-size:13.5px;vertical-align:middle}.case-table td strong{display:block;font-weight:800}.case-table td small{display:block;color:#7A8FA8;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:320px}.pill{display:inline-flex;border-radius:999px;padding:4px 10px;font-size:11px;font-weight:800}.mini-btn{display:inline-flex;align-items:center;gap:5px;background:#F7FAFD;border:1px solid #DDE8F4;border-radius:9px;color:#255F8C;text-decoration:none;font-size:12px;font-weight:800;padding:7px 10px;margin-left:4px}.empty-row{text-align:center!important;color:#7A8FA8!important;padding:38px!important}
</style>

</body>
</html>
