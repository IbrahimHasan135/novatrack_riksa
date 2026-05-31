<?php
$isLoginPage = false;
$pageTitle = 'Profile - NovaTrack';
require __DIR__ . '/layout/header.php';
$activePassword = ($_GET['tab'] ?? '') === 'password';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-person-circle"></i> Profile</span></div></div></div>
<main class="main-content">
    <section class="admin-shell">
        <div class="admin-hero">
            <div class="admin-kicker">Account Profile</div>
            <h1><?= htmlspecialchars($user['full_name'] ?: $user['username']); ?></h1>
            <p>Detail akun dan pengaturan password untuk akses NovaTrack.</p>
        </div>

        <?php if (isset($_GET['updated'])): ?><div class="admin-alert success">Password berhasil diupdate.</div><?php endif; ?>
        <?php if (($_GET['error'] ?? '') === 'password'): ?><div class="admin-alert danger">Password gagal diupdate. Cek password lama dan password baru.</div><?php endif; ?>

        <div class="admin-grid">
            <article class="admin-card">
                <h2>Show Profile</h2>
                <div class="profile-lines">
                    <div><span>Full Name</span><strong><?= htmlspecialchars($user['full_name'] ?: '-'); ?></strong></div>
                    <div><span>Username</span><strong><?= htmlspecialchars($user['username']); ?></strong></div>
                    <div><span>Role</span><strong><?= htmlspecialchars($user['role']); ?></strong></div>
                    <div><span>Created</span><strong><?= date('d M Y', strtotime($user['created_at'] ?? 'now')); ?></strong></div>
                </div>
            </article>

            <form class="admin-card" action="<?= app_url('profile/password'); ?>" method="POST">
                <h2>Edit Password</h2>
                <label for="current_password">Current Password</label>
                <div class="pw-wrap">
                    <input id="current_password" type="password" name="current_password" required>
                    <button type="button" class="pw-toggle js-pw-toggle" aria-label="Toggle password visibility"><i class="bi bi-eye-slash"></i></button>
                </div>
                <label for="new_password">New Password</label>
                <div class="pw-wrap">
                    <input id="new_password" type="password" name="new_password" required minlength="6">
                    <button type="button" class="pw-toggle js-pw-toggle" aria-label="Toggle password visibility"><i class="bi bi-eye-slash"></i></button>
                </div>
                <button type="submit">Update Password</button>
            </form>
        </div>
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
