<?php
$isLoginPage = false;
$pageTitle = 'Edit Account - NovaTrack';
require __DIR__ . '/../layout/header.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-person-gear"></i> Edit Account</span></div></div></div>
<main class="main-content">
    <section class="admin-shell">
        <a class="back-link" href="<?= app_url('users'); ?>"><i class="bi bi-arrow-left"></i> Back</a>
        <form class="admin-card wide" action="<?= app_url('users/update/' . (int)$editUser['id']); ?>" method="POST">
            <h1>Edit <?= htmlspecialchars($editUser['full_name'] ?: $editUser['username']); ?></h1>
            <p class="admin-muted">Role tetap: <strong><?= htmlspecialchars($editUser['role']); ?></strong></p>

            <label for="edit_username">Username</label>
            <input id="edit_username" name="username" required value="<?= htmlspecialchars($editUser['username']); ?>">

            <label for="edit_full_name">Full Name</label>
            <input id="edit_full_name" name="full_name" required value="<?= htmlspecialchars($editUser['full_name']); ?>">

            <label for="edit_password">New Password</label>
            <div class="pw-wrap">
                <input id="edit_password" type="password" name="password" placeholder="Kosongkan kalau tidak ganti password">
                <button type="button" class="pw-toggle js-pw-toggle" aria-label="Toggle password visibility"><i class="bi bi-eye-slash"></i></button>
            </div>

            <button type="submit">Save Account</button>
        </form>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span></footer>
<script>
(function () {
    document.querySelectorAll('.js-pw-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = button.parentElement.querySelector('input');
            var icon = button.querySelector('i');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = 'bi ' + (show ? 'bi-eye' : 'bi-eye-slash');
        });
    });
})();
</script>

</body></html>
