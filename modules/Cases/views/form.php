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

<style>
.form-shell{padding:24px 28px 36px}.back-link{display:inline-flex;gap:6px;align-items:center;margin-bottom:14px;color:#255F8C;text-decoration:none;font-weight:800;font-size:13px}.form-card{background:rgba(255,255,255,.9);border:1px solid rgba(58,110,165,.14);border-radius:20px;padding:26px;box-shadow:0 18px 46px rgba(30,72,126,.10);max-width:980px}.form-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:22px}.case-kicker{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#1BA784;margin-bottom:6px;display:flex;gap:6px;align-items:center}.form-card h1{font-size:26px;font-weight:800;margin:0;color:#1C2B3A}.form-card p{font-size:13.5px;color:#416C92;margin:5px 0 0}.case-alert{margin-bottom:16px;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:700;display:flex;gap:8px;align-items:center}.case-alert.danger{background:#FFF1F0;color:#C0392B;border:1px solid #FFCDC9}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.field{display:flex;flex-direction:column;gap:7px}.field.span-2{grid-column:span 2}.field label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#416C92}.field label span{color:#EB5757}.field input,.field select,.field textarea{width:100%;border:1.5px solid #DDE8F4;border-radius:12px;background:#F7FAFD;color:#1C2B3A;font:14px Inter,sans-serif;padding:11px 13px;outline:none}.field textarea{resize:vertical}.field input:focus,.field select:focus,.field textarea:focus{border-color:#1BA784;background:#fff;box-shadow:0 0 0 4px rgba(27,167,132,.10)}.field input:disabled{color:#7A8FA8}

/* ─── USER MULTI-SELECT ───────────────────────── */
#user-multi{position:relative}
#user-multi input[type=hidden]{display:none}
.um-chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;min-height:0}
.um-chip{display:inline-flex;align-items:center;gap:5px;background:linear-gradient(135deg,#E8F0FB,#EAF7F2);border:1.5px solid rgba(58,110,165,.2);border-radius:999px;padding:4px 8px 4px 12px;font-size:12.5px;font-weight:700;color:#1C2B3A;white-space:nowrap;animation:chipIn .18s ease both}
.um-chip button{background:none;border:none;padding:0;margin-left:3px;cursor:pointer;color:#8CA0B3;font-size:14px;line-height:1;display:flex;align-items:center;transition:color .15s}
.um-chip button:hover{color:#C0392B}
@keyframes chipIn{from{opacity:0;transform:scale(.85)}to{opacity:1;transform:scale(1)}}
#umSearch{width:100%;border:1.5px solid #DDE8F4;border-radius:12px;background:#F7FAFD;color:#1C2B3A;font:14px Inter,sans-serif;padding:10px 13px;outline:none;transition:border-color .2s,box-shadow .2s}
#umSearch:focus{border-color:#1BA784;background:#fff;box-shadow:0 0 0 4px rgba(27,167,132,.10)}
.um-dropdown{display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1.5px solid #DDE8F4;border-radius:12px;margin-top:4px;max-height:200px;overflow-y:auto;z-index:99;box-shadow:0 12px 36px rgba(30,72,126,.14)}
.um-dropdown.show{display:block;animation:dropIn .18s ease both}
@keyframes dropIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
.um-dropdown::-webkit-scrollbar{width:5px}
.um-dropdown::-webkit-scrollbar-track{background:transparent}
.um-dropdown::-webkit-scrollbar-thumb{background:#DDE8F4;border-radius:99px}
.um-dropdown .um-empty{padding:13px 14px;font-size:12.5px;color:#8CA0B3;text-align:center}
.um-item{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;border-bottom:1px solid #F0F5FA;transition:background .12s}
.um-item:last-child{border-bottom:none}
.um-item:hover,.um-item.active{background:#EEF6FF}
.um-item .um-name{font-size:13.5px;font-weight:700;color:#1C2B3A}
.um-item .um-role{font-size:11.5px;color:#8CA0B3;font-weight:500}
.um-item.hidden{display:none}

/* ─── FORM ACTIONS ───────────────────────────── */
.form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px;padding-top:18px;border-top:1px solid #EEF4FA}
.form-actions a{display:inline-flex;align-items:center;gap:5px;padding:10px 16px;border:1.5px solid #DDE8F4;border-radius:12px;background:#F7FAFD;color:#255F8C;text-decoration:none;font-size:13px;font-weight:700;font-family:Inter,sans-serif;transition:background .15s,border-color .15s}
.form-actions a:hover{background:#EEF6FF;border-color:#8CBDE0}
.form-actions button{display:inline-flex;align-items:center;gap:7px;border:none;border-radius:12px;background:linear-gradient(135deg,#3A6EA5,#1BA784);color:#fff;font-weight:800;padding:11px 20px;font-size:14px;font-family:Inter,sans-serif;cursor:pointer;transition:transform .15s,box-shadow .2s}
.form-actions button:hover{transform:translateY(-1px);box-shadow:0 6px 22px rgba(27,167,132,.28)}

@media(max-width:700px){.form-grid{grid-template-columns:1fr}.form-head{flex-direction:column}}
</style>

</body>
</html>
