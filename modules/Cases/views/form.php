<?php
$isLoginPage = false;
$editing = ($mode ?? 'create') === 'edit';
$pageTitle = ($editing ? 'Edit Case' : 'Tambah Case') . ' - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';

use Core\Auth;

$auth = Auth::getInstance();
$user = $auth->check() ? $auth->user() : null;
$action = $editing ? app_url('cases/update/' . (int)$case['id']) : app_url('cases');
?>

<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-left">
            <span class="topbar-breadcrumb"><i class="bi bi-pencil-square"></i> Cases / <?= $editing ? 'Edit' : 'Create'; ?></span>
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting">Halo, <strong><?= htmlspecialchars($user['full_name'] ?? 'User'); ?></strong></span>
            <div class="topbar-avatar"><?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)); ?></div>
        </div>
    </div>
</div>

<main class="main-content">
    <section class="form-shell">
        <a class="back-link" href="<?= $editing ? app_url('cases/detail/' . (int)$case['id']) : app_url('cases'); ?>"><i class="bi bi-arrow-left"></i> Back</a>
        <div class="form-card">
            <div class="form-head">
                <div>
                    <div class="case-kicker"><i class="bi bi-kanban"></i> Case Tracker</div>
                    <h1><?= $editing ? 'Edit Case' : 'Create New Case'; ?></h1>
                    <p>Pilih type folder, isi ringkasan case, lalu tambahkan information dan personal note.</p>
                </div>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="case-alert danger"><i class="bi bi-exclamation-triangle"></i> Lengkapi title dan type case.</div>
            <?php endif; ?>

            <form action="<?= $action; ?>" method="POST">
                <div class="form-grid">
                    <div class="field span-2">
                        <label>Title <span>*</span></label>
                        <input name="title" required value="<?= htmlspecialchars($case['title'] ?? ''); ?>" placeholder="Contoh: Akses VPN tidak bisa connect">
                    </div>
                    <div class="field">
                        <label>Type Folder <span>*</span></label>
                        <select name="type_id">
                            <option value="">Pilih type</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= (int)$type['id']; ?>" <?= (int)$selectedTypeId === (int)$type['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Type Baru</label>
                        <input name="new_type_name" placeholder="Opsional, otomatis dibuat">
                    </div>
                    <div class="field">
                        <label>Prioritas</label>
                        <select name="priority">
                            <?php foreach (['normal' => 'Normal', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $value => $label): ?>
                                <option value="<?= $value; ?>" <?= ($case['priority'] ?? 'normal') === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Status</label>
                        <select name="status">
                            <?php foreach (['verification' => 'Verification', 'in_progress' => 'In Progress', 'done' => 'Done', 'closed' => 'Close'] as $value => $label): ?>
                                <option value="<?= $value; ?>" <?= ($case['status'] ?? 'verification') === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Deadline</label>
                        <input type="date" name="deadline" value="<?= htmlspecialchars($case['deadline'] ?? ''); ?>">
                    </div>
                    <div class="field">
                        <label>Created</label>
                        <input disabled value="<?= $editing ? date('d M Y H:i', strtotime($case['created_at'])) : 'Otomatis saat disimpan'; ?>">
                    </div>
                    <div class="field span-2">
                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Ringkasan singkat case"><?= htmlspecialchars($case['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="field span-2">
                        <label>Information</label>
                        <textarea name="information" rows="5" placeholder="Informasi lengkap yang perlu diketahui tim"><?= htmlspecialchars($case['information'] ?? ''); ?></textarea>
                    </div>
                    <div class="field span-2">
                        <label>Personal Note</label>
                        <textarea name="personal_note" rows="5" placeholder="Catatan personal/internal"><?= htmlspecialchars($case['personal_note'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="<?= $editing ? app_url('cases/detail/' . (int)$case['id']) : app_url('cases'); ?>">Cancel</a>
                    <button type="submit"><i class="bi bi-check-lg"></i> <?= $editing ? 'Update Case' : 'Save Case'; ?></button>
                </div>
            </form>
        </div>
    </section>
</main>

<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Case Tracker</span></footer>

<style>
.form-shell{padding:24px 28px 36px}.back-link{display:inline-flex;gap:6px;align-items:center;margin-bottom:14px;color:#255F8C;text-decoration:none;font-weight:800;font-size:13px}.form-card{background:rgba(255,255,255,.9);border:1px solid rgba(58,110,165,.14);border-radius:20px;padding:26px;box-shadow:0 18px 46px rgba(30,72,126,.10);max-width:980px}.form-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:22px}.case-kicker{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#1BA784;margin-bottom:6px;display:flex;gap:6px;align-items:center}.form-card h1{font-size:26px;font-weight:800;margin:0;color:#1C2B3A}.form-card p{font-size:13.5px;color:#416C92;margin:5px 0 0}.case-alert{margin-bottom:16px;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:700;display:flex;gap:8px;align-items:center}.case-alert.danger{background:#FFF1F0;color:#C0392B;border:1px solid #FFCDC9}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.field{display:flex;flex-direction:column;gap:7px}.field.span-2{grid-column:span 2}.field label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#416C92}.field label span{color:#EB5757}.field input,.field select,.field textarea{width:100%;border:1.5px solid #DDE8F4;border-radius:12px;background:#F7FAFD;color:#1C2B3A;font:14px Inter,sans-serif;padding:11px 13px;outline:none}.field textarea{resize:vertical}.field input:focus,.field select:focus,.field textarea:focus{border-color:#1BA784;background:#fff;box-shadow:0 0 0 4px rgba(27,167,132,.10)}.field input:disabled{color:#7A8FA8}.form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px}.form-actions a,.form-actions button{border-radius:12px;padding:10px 18px;font-weight:800;font-size:13px;text-decoration:none}.form-actions a{border:1px solid #DDE8F4;color:#416C92;background:#fff}.form-actions button{border:none;color:#fff;background:linear-gradient(135deg,#3A6EA5,#1BA784);box-shadow:0 12px 30px rgba(27,167,132,.22)}@media(max-width:720px){.form-grid{grid-template-columns:1fr}.field.span-2{grid-column:auto}}
</style>

</body>
</html>
