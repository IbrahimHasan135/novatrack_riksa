<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NovaTrack Riksa — Case Tracker System">
    <title><?= $pageTitle ?? 'NovaTrack Riksa'; ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- App Styles -->
    <link rel="stylesheet" href="<?= app_url('assets/style.css'); ?>">
</head>
<body class="<?= $isLoginPage ?? false ? 'login-page' : 'app-page'; ?>"
    style="
        --nt-primary:#3A6EA5;
        --nt-primary-l:#DDEAF5;
        --nt-primary-h:#2563A8;
        --nt-secondary:#1E487E;
        --nt-accent:#5B9BD5;
        --nt-success:#27AE60;
        --nt-warning:#F2994A;
        --nt-danger:#EB5757;
        --nt-body:#EAF1FB;
        --nt-card:#FFFFFF;
        --nt-text:#1C2B3A;
        --nt-muted:#7A8FA8;
    "
>
<?php
// navbar.php: render sidebar untuk halaman authenticated
// (login.php sendiri menutup </html>, tidak memanggil header/footer)
require __DIR__ . '/navbar.php';
?>
