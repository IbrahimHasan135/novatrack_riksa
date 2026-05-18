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
                <label>Username</label><input name="username" required>
                <label>Full Name</label><input name="full_name" required>
                <label>Password</label><input type="password" name="password" required>
                <label>Role</label>
                <select name="role" required>
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
<?php require __DIR__ . '/../roles/styles.php'; ?>
</body></html>
