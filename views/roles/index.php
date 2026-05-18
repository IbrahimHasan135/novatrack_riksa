<?php
$isLoginPage = false;
$pageTitle = 'Role Management - NovaTrack';
require __DIR__ . '/../layout/header.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-shield-lock"></i> Role Management</span></div></div></div>
<main class="main-content">
    <section class="admin-shell">
        <div class="admin-hero">
            <div><div class="admin-kicker">Access Control</div><h1>Role Management</h1><p>Buat role, pilih module yang boleh diload, dan tentukan role apa saja yang boleh dibuat oleh role tersebut.</p></div>
        </div>
        <?php if (($_GET['error'] ?? '') === 'delete'): ?><div class="admin-alert danger">Role system atau role yang masih dipakai user tidak bisa dihapus.</div><?php endif; ?>
        <div class="admin-grid">
            <form class="admin-card" action="<?= app_url('roles'); ?>" method="POST">
                <h2>Create Role</h2>
                <label>Role Name</label>
                <input name="name" required placeholder="Contoh: Admin Sales">
                <label>Allowed Modules</label>
                <div class="check-grid">
                    <?php foreach ($modules as $module): ?>
                        <label><input type="checkbox" name="modules[]" value="<?= htmlspecialchars($module->slug); ?>"> <?= htmlspecialchars($module->label); ?></label>
                    <?php endforeach; ?>
                </div>
                <label>Can Create Accounts In Roles</label>
                <div class="check-grid">
                    <?php foreach ($roles as $role): ?>
                        <label><input type="checkbox" name="creatable_roles[]" value="<?= (int)$role['id']; ?>"> <?= htmlspecialchars($role['name']); ?></label>
                    <?php endforeach; ?>
                </div>
                <button type="submit">Create Role</button>
            </form>
            <div class="admin-card">
                <h2>Roles</h2>
                <table class="admin-table">
                    <thead><tr><th>Role</th><th>Slug</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?= htmlspecialchars($role['name']); ?> <?= (int)$role['is_system'] ? '<span class="tag">system</span>' : ''; ?></td>
                            <td><?= htmlspecialchars($role['slug']); ?></td>
                            <td>
                                <a href="<?= app_url('roles/edit/' . (int)$role['id']); ?>">Edit</a>
                                <form action="<?= app_url('roles/delete/' . (int)$role['id']); ?>" method="POST" onsubmit="return confirm('Hapus role ini?');">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span></footer>
<?php require __DIR__ . '/styles.php'; ?>
</body></html>
