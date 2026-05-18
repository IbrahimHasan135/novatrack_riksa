<?php
$isLoginPage = false;
$pageTitle = 'Users - NovaTrack';
require __DIR__ . '/../layout/header.php';
$currentUser = \Core\Auth::getInstance()->user();
$rbac = new \Core\Rbac();
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-people"></i> Users</span></div></div></div>
<main class="main-content">
    <section class="admin-shell">
        <div class="admin-hero"><div class="admin-kicker">Account Control</div><h1>Users</h1><p>Admin dapat membuat account hanya untuk role yang diizinkan oleh Super Admin.</p></div>
        <?php if (($_GET['error'] ?? '') === 'role'): ?><div class="admin-alert danger">Role tidak boleh dibuat oleh user ini, atau data tidak lengkap.</div><?php endif; ?>
        <div class="admin-grid">
            <form class="admin-card" action="<?= app_url('users'); ?>" method="POST">
                <h2>Create Account</h2>
                <label for="u_username">Username</label>
                <input id="u_username" name="username" required placeholder="Contoh: john.doe">
                <label for="u_fullname">Full Name</label>
                <input id="u_fullname" name="full_name" required placeholder="Contoh: John Doe">
                <label for="u_password">Password</label>
                <div class="pw-wrap">
                    <input id="u_password" type="password" name="password" required placeholder="Min. 6 karakter">
                    <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password visibility">
                        <i class="bi bi-eye-slash" id="pwIcon"></i>
                    </button>
                </div>
                <label for="u_role">Role</label>
                <select id="u_role" name="role" required>
                    <option value="">Pilih role...</option>
                    <?php foreach ($roles as $role): ?>
                        <?php if ($rbac->canCreateRoleSlug($role['slug'], $currentUser)): ?>
                            <option value="<?= htmlspecialchars($role['slug']); ?>"><?= htmlspecialchars($role['name']); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Create User</button>
            </form>
            <div class="admin-card">
                <h2>Accounts</h2>
                <table class="admin-table">
                    <thead><tr><th>User</th><th>Role</th><th>Created</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($user['full_name'] ?: $user['username']); ?></strong><br><small><?= htmlspecialchars($user['username']); ?></small></td>
                            <td><?= htmlspecialchars($user['role']); ?></td>
                            <td><?= date('d M Y', strtotime($user['created_at'] ?? 'now')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span></footer>
<script>
(function () {
    var toggle = document.getElementById('pwToggle');
    var input  = document.getElementById('u_password');
    var icon   = document.getElementById('pwIcon');
    if (!toggle || !input || !icon) return;
    toggle.addEventListener('click', function () {
        var isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        icon.className = 'bi ' + (isHidden ? 'bi-eye' : 'bi-eye-slash');
    });
})();
</script>
<?php require __DIR__ . '/../roles/styles.php'; ?>
</body></html>
