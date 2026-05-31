<?php
$isLoginPage = false;
$pageTitle = 'Detail Case #' . ($case['id'] ?? '') . ' - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';

use Core\Auth;

$auth = Auth::getInstance();
$user = $auth->check() ? $auth->user() : null;

function detailPill(string $value, string $kind): string {
    $maps = [
        'priority' => [
            'normal' => ['Normal', '#E8F7EE', '#27AE60'],
            'medium' => ['Medium', '#FFF8E1', '#E09F3E'],
            'high' => ['High', '#FFF1F0', '#EB5757'],
            'critical' => ['Critical', '#FFF1F0', '#C0392B'],
        ],
        'status' => [
            'verification' => ['Verification', '#E8F0FB', '#3A6EA5'],
            'in_progress' => ['In Progress', '#FFF8E1', '#E09F3E'],
            'done' => ['Done', '#E8F7EE', '#27AE60'],
            'closed' => ['Close', '#F0F2F5', '#5A7089'],
        ],
    ];
    [$label, $bg, $fg] = $maps[$kind][$value] ?? reset($maps[$kind]);
    return "<span class=\"pill\" style=\"background:$bg;color:$fg;\">$label</span>";
}
?>

<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-left">
            <span class="topbar-breadcrumb"><i class="bi bi-file-text"></i> Cases / Detail #<?= (int)$case['id']; ?></span>
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting">Halo, <strong><?= htmlspecialchars($user['full_name'] ?? 'User'); ?></strong></span>
            <div class="topbar-avatar"><?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)); ?></div>
        </div>
    </div>
</div>

<main class="main-content">
    <section class="detail-shell">
        <a class="back-link" href="<?= app_url('cases/type/' . (int)$case['type_id']); ?>"><i class="bi bi-arrow-left"></i> Back to <?= htmlspecialchars($case['type_name'] ?? 'type'); ?></a>

        <?php if (isset($_GET['updated'])): ?><div class="case-alert success"><i class="bi bi-check-circle"></i> Case berhasil diupdate.</div><?php endif; ?>

        <article class="detail-card">
            <div class="detail-head">
                <div>
                    <div class="case-kicker"><i class="bi bi-folder-fill"></i> <?= htmlspecialchars($case['type_name'] ?? 'General'); ?> / Case #<?= (int)$case['id']; ?></div>
                    <h1><?= htmlspecialchars($case['title']); ?></h1>
                    <p><?= htmlspecialchars($case['description'] ?: 'Tidak ada deskripsi singkat.'); ?></p>
                </div>
                <div class="detail-actions">
                    <a href="<?= app_url('cases/edit/' . (int)$case['id']); ?>"><i class="bi bi-pencil"></i> Edit</a>
                    <form action="<?= app_url('cases/delete/' . (int)$case['id']); ?>" method="POST" onsubmit="return confirm('Hapus case ini?');">
                        <button type="submit"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                </div>
            </div>

            <div class="meta-grid">
                <div><span>Prioritas</span><?= detailPill($case['priority'] ?? 'normal', 'priority'); ?></div>
                <div><span>Status</span><?= detailPill($case['status'] ?? 'verification', 'status'); ?></div>
                <div><span>Dibuat</span><strong><?= date('d M Y H:i', strtotime($case['created_at'])); ?></strong></div>
                <div><span>Deadline</span><strong><?= !empty($case['deadline']) ? date('d M Y', strtotime($case['deadline'])) : '-'; ?></strong></div>
            </div>

            <div class="text-panels">
                <section>
                    <h2><i class="bi bi-info-circle"></i> Information</h2>
                    <div class="textbox"><?= nl2br(htmlspecialchars($case['information'] ?: 'Belum ada information.')); ?></div>
                </section>
                <?php if ($canViewPersonalNote ?? false): ?>
                    <section>
                        <h2><i class="bi bi-journal-text"></i> Personal Note</h2>
                        <div class="textbox"><?= nl2br(htmlspecialchars($case['personal_note'] ?: 'Belum ada personal note.')); ?></div>
                    </section>
                <?php else: ?>
                    <section>
                        <h2><i class="bi bi-lock"></i> Personal Note</h2>
                        <div class="textbox locked">Personal note hanya bisa dilihat oleh Super Admin dan user yang di-assign ke case ini.</div>
                    </section>
                <?php endif; ?>
            </div>
        </article>
    </section>
</main>

<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Case Tracker</span></footer>

</body>
</html>
