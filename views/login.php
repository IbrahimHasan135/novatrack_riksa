<?php
require_once __DIR__ . '/../core/Design.php';

$pageTitle  = 'Login — NovaTrack Riksa';
$isLoginPage = true;
$auth       = \Core\Auth::getInstance();
$designFontUrl = \Core\Design::fontUrl();
$designStylesheets = \Core\Design::stylesheets();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>

    <?php if ($designFontUrl): ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="<?= htmlspecialchars($designFontUrl); ?>" rel="stylesheet">
    <?php endif; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php foreach ($designStylesheets as $stylesheet): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars(app_url($stylesheet)); ?>">
    <?php endforeach; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="login-bg" style="<?= htmlspecialchars(\Core\Design::bodyStyle()); ?>">

    <!-- Bubbles (animated) -->
    <div class="bubbles">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>

    <!-- Card -->
    <div class="login-wrap">
        <div class="login-card">

            <!-- Logo -->
            <div class="login-logo">
                <div class="login-logo-icon"><i class="bi bi-collection-play-fill"></i></div>
                <h1>NovaTrack</h1>
                <p>Riksa &mdash; Case Tracker System</p>
            </div>

            <!-- Alert -->
            <div id="loginAlert" class="login-alert">
                <i id="alertIcon" class="bi"></i>
                <span id="alertMsg"></span>
            </div>

            <!-- Form -->
            <form id="loginForm" method="POST" action="<?= app_url('login'); ?>" novalidate>

                <!-- Username -->
                <div class="form-group">
                    <label class="form-label" for="u_user">Username</label>
                    <div class="input-wrap" id="wrap-user">
                        <input class="input-field" type="text" id="u_user" name="username"
                               placeholder="Masukkan username" autocomplete="username" required minlength="3">
                        <i class="bi bi-person field-icon"></i>
                    </div>
                    <div class="err-msg">Username wajib diisi (min. 3 karakter)</div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="u_pass">Password</label>
                    <div class="input-wrap pw-wrap" id="wrap-pw">
                        <input class="input-field" type="password" id="u_pass" name="password"
                               placeholder="Masukkan password" autocomplete="current-password" required>
                        <i class="bi bi-lock field-icon"></i>
                        <button type="button" class="pw-toggle" id="pwLoginToggle" aria-label="Toggle password visibility">
                            <i class="bi bi-eye-slash" id="pwLoginIcon"></i>
                        </button>
                    </div>
                    <div class="err-msg">Password wajib diisi</div>
                </div>

                <!-- Remember me -->
                <div class="login-meta">
                    <label class="chk-wrap">
                        <input type="checkbox" id="chk_remember" name="remember" value="1">
                        Ingat saya
                    </label>
                    <a href="#" class="forgot-link">Lupa password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-login" id="btnLogin">
                    <span class="btn-label">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Sistem
                    </span>
                    <span class="spn">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </span>
                </button>
            </form>

            <div class="login-divider"><span>atau</span></div>

            <div class="blurb">
                <i class="bi bi-info-circle me-1"></i>Tidak punya akun? Hubungi administrator
            </div>
        </div>

        <!-- Copyright kecil -->
        <div class="login-copy">
            &copy; <?= date('Y'); ?> NovaTrack Riksa — v1.0
        </div>
    </div>

<script>
(function () {
    const form   = document.getElementById('loginForm');
    const btn    = document.getElementById('btnLogin');
    const alertB = document.getElementById('loginAlert');
    const alertI = document.getElementById('alertIcon');
    const alertT = document.getElementById('alertMsg');

    function alertShow(type, msg) {
        alertB.className = 'login-alert show ' + type;
        alertI.className = 'bi ' + (type === 'error' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill');
        alertT.textContent = msg;
    }
    function alertHide() { alertB.classList.remove('show'); }

    document.getElementById('u_user')?.addEventListener('input', function(){
        this.closest('.input-wrap').classList.remove('has-error');
        alertHide();
    });
    document.getElementById('u_pass')?.addEventListener('input', function(){
        this.closest('.input-wrap').classList.remove('has-error');
        alertHide();
    });

    var loginToggle = document.getElementById('pwLoginToggle');
    var loginInput  = document.getElementById('u_pass');
    var loginIcon   = document.getElementById('pwLoginIcon');
    if (loginToggle && loginInput && loginIcon) {
        loginToggle.addEventListener('click', function () {
            var visible = loginInput.type === 'text';
            loginInput.type = visible ? 'password' : 'text';
            loginIcon.className = 'bi ' + (visible ? 'bi-eye-slash' : 'bi-eye');
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        alertHide();

        const u  = document.getElementById('u_user').value.trim();
        const p  = document.getElementById('u_pass').value;
        const gU = document.getElementById('wrap-user');
        const gP = document.getElementById('wrap-pw');
        let err = false;

        if (u.length < 3) { gU.classList.add('has-error'); err = true; }
        if (p.length < 1) { gP.classList.add('has-error'); err = true; }

        if (err) {
            form.style.animation = 'none';
            form.offsetHeight; // trigger reflow
            form.style.animation = 'shake .4s ease';
            alertShow('error', 'Mohon lengkapi semua field terlebih dahulu.');
            return;
        }

        btn.classList.add('loading');
        const data = new FormData(form);

        fetch('<?= app_url('login'); ?>', {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            btn.classList.remove('loading');
            if (res.success) {
                alertShow('success', res.message || 'Login berhasil! Mengalihkan...');
                setTimeout(() => { window.location.href = res.redirect || '<?= app_url('dashboard'); ?>'; }, 700);
            } else {
                alertShow('error', res.message || 'Username atau password salah');
                form.style.animation = 'none';
                form.offsetHeight;
                form.style.animation = 'shake .4s ease';
            }
        })
        .catch(() => {
            btn.classList.remove('loading');
            alertShow('error', 'Terjadi kesalahan pada server. Coba lagi.');
        });
    });
})();
</script>

</body>
</html>
