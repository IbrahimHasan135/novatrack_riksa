<?php
$isLoginPage = false;
$pageTitle = 'Edit Role - NovaTrack';
require __DIR__ . '/../layout/header.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-shield-lock"></i> Edit Role</span></div></div></div>
<main class="main-content">
    <section class="admin-shell">
        <a class="back-link" href="<?= app_url('roles'); ?>"><i class="bi bi-arrow-left"></i> Back</a>
        <form class="admin-card wide" action="<?= app_url('roles/update/' . (int)$role['id']); ?>" method="POST">
            <h1>Edit <?= htmlspecialchars($role['name']); ?></h1>
            <label>Role Name</label>
            <input name="name" required value="<?= htmlspecialchars($role['name']); ?>">
            <label>Allowed Modules</label>
            <div class="check-grid">
                <?php foreach ($modules as $module): ?>
                    <label><input type="checkbox" name="modules[]" value="<?= htmlspecialchars($module->slug); ?>" <?= in_array($module->slug, $enabledModules, true) ? 'checked' : ''; ?>> <?= htmlspecialchars($module->label); ?></label>
                <?php endforeach; ?>
            </div>
            <label>Can Create Accounts In Roles</label>
            <div class="check-grid">
                <?php foreach ($roles as $availableRole): ?>
                    <label><input type="checkbox" name="creatable_roles[]" value="<?= (int)$availableRole['id']; ?>" <?= in_array((int)$availableRole['id'], $creatableRoleIds, true) ? 'checked' : ''; ?>> <?= htmlspecialchars($availableRole['name']); ?></label>
                <?php endforeach; ?>
            </div>
            <button type="submit">Save Role</button>
        </form>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span></footer>
<?php require __DIR__ . '/styles.php'; ?>
</body></html>
