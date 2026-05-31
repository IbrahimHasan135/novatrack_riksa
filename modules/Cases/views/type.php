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
                        <th class="nt-text-end">Aksi</th>
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
                            <td class="nt-text-end">
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

</body>
</html>
