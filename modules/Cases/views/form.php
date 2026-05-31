<?php
$isLoginPage = false;
$editing = ($mode ?? 'create') === 'edit';
$pageTitle = ($editing ? 'Edit Case' : 'Tambah Case') . ' - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';

use Core\Auth;

$auth = Auth::getInstance();
$user = $auth->check() ? $auth->user() : null;
$action = $editing ? app_url('cases/update/' . (int)$case['id']) : app_url('cases');
$assignedIds = json_decode($case['assigned_user_ids'] ?? '[]', true);
if (!is_array($assignedIds)) { $assignedIds = []; }
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
                        <label>Assign To Users</label>
                        <div id="user-multi" data-users='<?= json_encode(array_values($users ?? [])); ?>' data-assigned='<?= json_encode(array_values($assignedIds)); ?>'>
                            <div class="um-chips" id="umChips"></div>
                            <input type="text" id="umSearch" placeholder="Ketik nama user untuk mencari..." autocomplete="off">
                            <div class="um-dropdown" id="umDropdown">
                                <?php foreach (($users ?? []) as $assignUser): ?>
                                    <div class="um-item"
                                         data-id="<?= (int)$assignUser['id']; ?>"
                                         data-name="<?= htmlspecialchars($assignUser['full_name'] ?: $assignUser['username']); ?>"
                                         data-role="<?= htmlspecialchars($assignUser['role']); ?>">
                                        <span class="um-name"><?= htmlspecialchars($assignUser['full_name'] ?: $assignUser['username']); ?></span>
                                        <small class="um-role"><?= htmlspecialchars($assignUser['role']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($users ?? [])): ?>
                                    <div class="um-empty">Tidak ada user yang tersedia</div>
                                <?php endif; ?>
                            </div>
                        </div>
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

<script>
(function () {
    var wrapper  = document.getElementById('user-multi');
    var chipsBox = document.getElementById('umChips');
    var search   = document.getElementById('umSearch');
    var dropdown = document.getElementById('umDropdown');
    if (!wrapper || !chipsBox || !search || !dropdown) return;

    var assignedIds = JSON.parse(wrapper.getAttribute('data-assigned') || '[]');
    var usersData   = JSON.parse(wrapper.getAttribute('data-users') || '[]');
    var selected    = new Map();

    // Sync hide from pre-selected checkbox state
    assignedIds.forEach(function (id) {
        id = parseInt(id, 10);
        var user = usersData.find(function (u) { return parseInt(u.id, 10) === id; });
        if (user) selected.set(id, user);
    });

    // ── Hidden inputs ──
    function syncHidden() {
        // Remove all existing hidden inputs
        wrapper.querySelectorAll('input[name="assigned_user_ids[]"]').forEach(function (n) { n.remove(); });
        selected.forEach(function (user) {
            var inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'assigned_user_ids[]';
            inp.value = user.id;
            wrapper.appendChild(inp);
        });
    }

    syncHidden();

    // ── Render chips ──
    function renderChips() {
        chipsBox.innerHTML = '';
        selected.forEach(function (user, id) {
            var chip = document.createElement('span');
            chip.className = 'um-chip';
            chip.setAttribute('data-chip-id', id);
            chip.innerHTML =
                '<span>' + escHtml(user.name || user.username) + '</span>' +
                '<button type="button" aria-label="Remove"><i class="bi bi-x"></i></button>';
            chip.querySelector('button').addEventListener('click', function () {
                removeUser(id);
            });
            chipsBox.appendChild(chip);
        });
    }

    renderChips();

    // ── Add / Remove ──
    function addUser(id) {
        id = parseInt(id, 10);
        if (selected.has(id)) return;
        var user = usersData.find(function (u) { return parseInt(u.id, 10) === id; });
        if (!user) return;
        selected.set(id, user);
        syncHidden();
        renderChips();
        filterItems(search.value);
        search.value = '';
        search.focus();
    }

    function removeUser(id) {
        id = parseInt(id, 10);
        selected.delete(id);
        syncHidden();
        renderChips();
        filterItems(search.value);
    }

    // ── Filter ──
    function filterItems(q) {
        q = q.toLowerCase().trim();
        var items = dropdown.querySelectorAll('.um-item');
        items.forEach(function (item) {
            var id  = parseInt(item.getAttribute('data-id'), 10);
            var name  = (item.getAttribute('data-name') || '').toLowerCase();
            var role  = (item.getAttribute('data-role') || '').toLowerCase();
            var match = !q || name.indexOf(q) !== -1 || role.indexOf(q) !== -1;
            var selectedHidden = selected.has(id);
            item.classList.toggle('hidden', !match || selectedHidden);
        });
        dropdown.classList.toggle('show', q.length > 0);
    }

    search.addEventListener('input', function () {
        filterItems(search.value);
    });

    // ── Item click ──
    dropdown.addEventListener('click', function (e) {
        var item = e.target.closest('.um-item');
        if (!item || item.classList.contains('hidden')) return;
        addUser(item.getAttribute('data-id'));
    });

    // ── Outside click ──
    document.addEventListener('click', function (e) {
        if (!wrapper.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    search.addEventListener('focus', function () {
        filterItems(search.value);
    });

    // ── Escape key ──
    search.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            dropdown.classList.remove('show');
            search.blur();
        }
        if (e.key === 'Backspace' && search.value === '' && selected.size > 0) {
            var lastId;
            selected.forEach(function (v, k) { lastId = k; });
            removeUser(lastId);
        }
    });

    function escHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
})();
</script>

</body>
</html>
