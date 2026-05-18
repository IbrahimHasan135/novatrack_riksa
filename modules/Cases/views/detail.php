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

<style>
.detail-shell{padding:24px 28px 36px}.back-link{display:inline-flex;gap:6px;align-items:center;margin-bottom:14px;color:#255F8C;text-decoration:none;font-weight:800;font-size:13px}.case-alert{margin-bottom:16px;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:700;display:flex;gap:8px;align-items:center}.case-alert.success{background:#E8F7EE;color:#1E7E34;border:1px solid #BFE7CE}.detail-card{background:rgba(255,255,255,.9);border:1px solid rgba(58,110,165,.14);border-radius:20px;padding:26px;box-shadow:0 18px 46px rgba(30,72,126,.10)}.detail-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap;border-bottom:1px solid #EEF4FA;padding-bottom:20px}.case-kicker{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#1BA784;margin-bottom:6px;display:flex;gap:6px;align-items:center}.detail-card h1{font-size:28px;font-weight:800;margin:0;color:#1C2B3A}.detail-card p{margin:7px 0 0;color:#416C92;font-size:14px;max-width:780px}.detail-actions{display:flex;gap:8px;flex-wrap:wrap}.detail-actions a,.detail-actions button{display:inline-flex;align-items:center;gap:7px;border-radius:11px;padding:9px 13px;font-weight:800;font-size:12.5px;text-decoration:none;border:1px solid #DDE8F4;background:#F7FAFD;color:#255F8C}.detail-actions button{color:#C0392B;cursor:pointer}.meta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin:20px 0}.meta-grid>div{background:#F7FAFD;border:1px solid #DDE8F4;border-radius:14px;padding:14px}.meta-grid span{display:block;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#416C92;margin-bottom:7px}.meta-grid strong{font-size:14px;color:#1C2B3A}.pill{display:inline-flex;border-radius:999px;padding:4px 10px;font-size:11px;font-weight:800}.text-panels{display:grid;grid-template-columns:1fr 1fr;gap:14px}.text-panels section{background:linear-gradient(135deg,#fff,#F7FAFD);border:1px solid #DDE8F4;border-radius:16px;padding:18px}.text-panels h2{font-size:14px;font-weight:800;margin:0 0 10px;color:#1C2B3A;display:flex;gap:7px;align-items:center}.text-panels h2 i{color:#1BA784}.textbox{min-height:170px;border:1.5px solid #DDE8F4;border-radius:13px;background:#fff;padding:14px;color:#1C2B3A;font-size:14px;line-height:1.7;white-space:normal}.textbox.locked{color:#7A8FA8;background:#F7FAFD;display:flex;align-items:center;justify-content:center;text-align:center}@media(max-width:800px){.text-panels{grid-template-columns:1fr}}
</style>

</body>
</html>
