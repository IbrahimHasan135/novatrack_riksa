<!DOCTYPE html>
<?php
require_once __DIR__ . '/../../core/Design.php';
?>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NovaTrack Riksa — Case Tracker System">
    <title><?= $pageTitle ?? 'NovaTrack Riksa'; ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="<?= htmlspecialchars(\Core\Design::fontUrl()); ?>" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- App Styles -->
    <?php foreach (\Core\Design::stylesheets() as $stylesheet): ?>
        <link rel="stylesheet" href="<?= app_url($stylesheet); ?>">
    <?php endforeach; ?>

    <!-- Icons loaded last so app component styles cannot override icon glyphs -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="<?= $isLoginPage ?? false ? 'login-page' : 'app-page'; ?>"
    style="<?= htmlspecialchars(\Core\Design::bodyStyle()); ?>"
>
<?php
// navbar.php: render sidebar untuk halaman authenticated
// (login.php sendiri menutup </html>, tidak memanggil header/footer)
require __DIR__ . '/navbar.php';
?>
