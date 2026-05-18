<?php
$pageTitle  = 'Login — NovaTrack Riksa';
$isLoginPage = true;
$auth       = \Core\Auth::getInstance();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    /* ─── LOGIN PAGE ─────────────────────────────── */
    *,*::before,*::after{box-sizing:border-box;}
    html,body{height:100%;}

    .login-bg {
        min-height: 100vh;
        display: flex; align-items: center; justify-content: center;
        font-family: 'Inter', sans-serif;
        background:
            linear-gradient(90deg, rgba(255,255,255,.055) 1px, transparent 1px),
            linear-gradient(180deg, rgba(255,255,255,.045) 1px, transparent 1px),
            radial-gradient(circle at 18% 20%, rgba(27,167,132,.34), transparent 30%),
            radial-gradient(circle at 82% 12%, rgba(91,155,213,.34), transparent 34%),
            linear-gradient(135deg, #102947 0%, #173E6D 48%, #0F5B67 100%);
        background-size: 34px 34px, 34px 34px, auto, auto, auto;
        position: relative; overflow: hidden;
    }
    .login-bg::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(145deg, rgba(255,255,255,.08), transparent 28%);
        pointer-events: none;
    }
    /* Animated bubbles */
    .bubbles {
        position: absolute; inset: 0; pointer-events: none; overflow: hidden;
    }
    .bubble {
        position: absolute; border-radius: 50%;
        background: rgba(102,224,190,.08);
        border: 1px solid rgba(210,238,255,.10);
        animation: bubbleFloat linear infinite;
    }
    .bubble:nth-child(1){width:70px;height:70px;bottom:-70px;left:8%;animation-duration:7s;}
    .bubble:nth-child(2){width:110px;height:110px;bottom:-110px;left:25%;animation-duration:9s;animation-delay:1s;}
    .bubble:nth-child(3){width:55px;height:55px;bottom:-55px;left:50%;animation-duration:6s;animation-delay:2s;}
    .bubble:nth-child(4){width:90px;height:90px;bottom:-90px;left:65%;animation-duration:8s;animation-delay:0.3s;}
    .bubble:nth-child(5){width:130px;height:130px;bottom:-130px;left:82%;animation-duration:10s;animation-delay:3s;}
    @keyframes bubbleFloat {
        0%  { transform: translateY(0)   rotate(0deg); opacity:.7; }
        50%  { opacity:.45; }
        100% { transform: translateY(-115vh) rotate(360deg); opacity:.0; }
    }

    /* Card */
    .login-wrap {
        position:relative; z-index:10;
        width:100%; max-width:440px;
        padding: 0 16px;
    }
    .login-card {
        background:
            linear-gradient(135deg, rgba(255,255,255,.96), rgba(255,255,255,.82)),
            linear-gradient(90deg, rgba(58,110,165,.06) 1px, transparent 1px),
            linear-gradient(180deg, rgba(27,167,132,.05) 1px, transparent 1px);
        background-size: auto, 28px 28px, 28px 28px;
        backdrop-filter: blur(24px);
        border-radius: 22px;
        border: 1px solid rgba(210,238,255,.44);
        box-shadow: 0 0 0 1px rgba(255,255,255,.20),
                    0 30px 90px rgba(4,18,36,.42);
        padding: 44px 38px 36px;
        animation: cardAppear .55s cubic-bezier(.16,1,.3,1) both;
        position: relative;
        overflow: hidden;
    }
    .login-card::before {
        content: '';
        position: absolute;
        left: 0; right: 0; top: 0;
        height: 3px;
        background: linear-gradient(90deg, #3A6EA5, #1BA784, #F2994A);
    }
    @keyframes cardAppear {
        from { opacity:0; transform: translateY(28px) scale(.97); }
        to   { opacity:1; transform: translateY(0)   scale(1);    }
    }
    /* Logo */
    .login-logo { text-align: center; margin-bottom: 30px; }
    .login-logo-icon {
        width:68px; height:68px; border-radius:20px;
        background: linear-gradient(135deg, #3A6EA5, #1BA784);
        display:inline-flex; align-items:center; justify-content:center;
        font-size:30px; color:#fff;
        box-shadow: 0 14px 38px rgba(27,167,132,.32);
        margin-bottom:14px;
    }
    .login-logo h1 { font-size:24px; font-weight:800; letter-spacing:-.02em; color:#1E487E; margin:0; }
    .login-logo p  { font-size:13px; color:#416C92; margin:5px 0 0; }

    /* Alert */
    .login-alert {
        display:none; padding:10px 14px; border-radius:10px;
        font-size:13px; font-weight:500; margin-bottom:16px;
        align-items:center; gap:8px;
    }
    .login-alert.show{display:flex; animation:alertIn .3s ease;}
    .login-alert.error{background:#FFF1F0;border:1px solid #FFCDC9;color:#C0392B;}
    .login-alert.success{background:#E8F7EE;border:1px solid #9AE6B4;color:#1E7E34;}
    @keyframes alertIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}

    /* Form */
    .form-group { margin-bottom:16px; }
    .form-label {
        display:block; font-size:12px; font-weight:700;
        color:#4A5E75; text-transform:uppercase; letter-spacing:.07em; margin-bottom:5px;
    }
    .input-wrap { position:relative; }
    .input-wrap .bi {
        position:absolute; left:13px; top:50%; transform:translateY(-50%);
        color:#8CA0B3; font-size:16px; pointer-events:none;
        transition:color .2s;
    }
    .input-field {
        width:100%; padding:11px 14px 11px 40px;
        border:2px solid #DDE8F4; border-radius:12px;
        font-size:14.5px; font-family:Inter,sans-serif;
        color:var(--nt-text,#1C2B3A); background:rgba(255,255,255,.74);
        outline:none; transition:border-color .2s, box-shadow .2s, background .2s;
        box-sizing:border-box;
    }
    .input-field:focus {
        border-color:#1BA784; background:#fff;
        box-shadow: 0 0 0 4px rgba(27,167,132,.12);
    }
    .input-field:focus ~ .bi,
    .input-wrap:focus-within .bi { color:#1BA784; }

    /* Input wrapper */
    .input-wrap {
        position: relative;
    }

    /* Password toggle — same look everywhere */
    .pw-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,.6);
        border: 1px solid rgba(221,232,244,.8);
        border-radius: 8px;
        color: #8CA0B3;
        font-size: 16px;
        cursor: pointer;
        padding: 5px 7px;
        line-height: 1;
        transition: color .2s, background .2s, border-color .2s;
        display: flex;
        align-items: center;
        z-index: 2;
    }

    .pw-toggle:hover {
        background: rgba(255,255,255,.95);
        border-color: #3A6EA5;
        color: #3A6EA5;
    }

    /* Password input gets extra right-padding for the toggle button */
    .pw-wrap .input-field {
        padding-right: 50px !important;
    }
    .err-msg { display:none; font-size:12px; color:#EB5757; margin-top:4px; }
    .input-wrap.has-error .input-field { border-color:#EB5757 !important; box-shadow: 0 0 0 4px rgba(235,87,87,.1) !important; }
    .input-wrap.has-error .err-msg   { display:block; }

    /* Login meta */
    .login-meta { display:flex; align-items:center; justify-content:space-between; margin:16px 0 20px; }
    .chk-wrap { display:flex; align-items:center; gap:6px; cursor:pointer; font-size:13px; color:#5A7089; }
    .forgot-link { font-size:13px; font-weight:600; color:#1D6A77; text-decoration:none; }
    .forgot-link:hover{color:#1BA784; text-decoration:underline;}

    /* Button */
    .btn-login {
        width:100%; padding:13px;
        background:linear-gradient(135deg,#3A6EA5,#1BA784);
        color:#fff; font-weight:700; font-size:15px; font-family:Inter,sans-serif;
        border:none; border-radius:12px; cursor:pointer;
        transition:transform .2s, box-shadow .2s;
        position:relative; overflow:hidden;
    }
    .btn-login::before {
        content:''; position:absolute; inset:0;
        background:linear-gradient(135deg,transparent,rgba(255,255,255,.12));
        opacity:0; transition:opacity .3s;
    }
    .btn-login:hover{transform:translateY(-2px);box-shadow:0 14px 34px rgba(27,167,132,.32);}
    .btn-login:hover::before{opacity:1;}
    .btn-login:active{transform:translateY(0);box-shadow:0 6px 20px rgba(58,110,165,.35);}
    .btn-login.loading .btn-label{display:none;}
    .btn-login .spn{display:none;}
    .btn-login.loading .spn{display:inline-block;}

    /* Divider */
    .login-divider { text-align:center; margin:20px 0; position:relative; }
    .login-divider::before{content:'';position:absolute;top:50%;left:0;right:0;height:1px;background:rgba(221,232,244,.6);}
    .login-divider span{position:relative;z-index:1;background:#fff;padding:0 14px;font-size:12px;color:#8CA0B3;font-weight:600;}

    /* Blurb */
    .blurb { text-align:center;font-size:12px;color:#7A8FA8; }
    .blurb i { color:#1BA784; }

    /* Shake */
    @keyframes shake {
        0%,100%{transform:translateX(0)}
        15%,45%,75%{transform:translateX(-6px)}
        30%,60%,90%{transform:translateX(6px)}
    }

    /* ─── RESPONSIVE ─────────────────────────────── */
    @media(max-width:575px){
        .login-card{padding:30px 22px 26px;}
    }
    </style>
</head>
<body class="login-bg">

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
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="err-msg">Username wajib diisi (min. 3 karakter)</div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="u_pass">Password</label>
                    <div class="input-wrap pw-wrap" id="wrap-pw">
                        <input class="input-field" type="password" id="u_pass" name="password"
                               placeholder="Masukkan password" autocomplete="current-password" required>
                        <i class="bi bi-lock"></i>
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
        <div style="text-align:center;margin-top:18px;color:rgba(255,255,255,.55);font-size:11px;font-family:Inter,sans-serif;">
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
