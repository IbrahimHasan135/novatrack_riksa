<?php
$isLoginPage = false;
$pageTitle = 'Pengeluaran - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';
require_once __DIR__ . '/_helpers.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-arrow-up-circle"></i> Accounting / Pengeluaran</span></div></div></div>
<main class="main-content">
    <section class="acct-shell">
        <div class="acct-hero"><div><div class="acct-kicker"><i class="bi bi-wallet2"></i> Cost Control</div><h1>Pengeluaran</h1><p>Catat biaya operasional, fee profesional, filing court, dan kebutuhan kantor untuk membaca pola biaya perusahaan.</p></div><div class="acct-actions"><a class="acct-btn secondary" href="<?= app_url('accounting'); ?>"><i class="bi bi-speedometer2"></i> Overview</a></div></div>
        <?php if (isset($_GET['created']) || isset($_GET['deleted']) || isset($_GET['updated'])): ?><div class="acct-alert"><i class="bi bi-check-circle"></i> Data pengeluaran berhasil diperbarui.</div><?php endif; ?>
        <div class="acct-grid">
            <article class="acct-card span-5"><h2>Tambah Pengeluaran</h2><form class="acct-form" action="<?= app_url('accounting/expenses'); ?>" method="POST">
                <div class="acct-field full"><label>Judul</label><input name="title" required placeholder="Contoh: Court filing fee"></div>
                <div class="acct-field"><label>Kategori</label><select name="category_id"><option value="">Pilih kategori</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id']; ?>"><?= htmlspecialchars($category['name']); ?></option><?php endforeach; ?></select></div>
                <div class="acct-field"><label>Kategori Baru</label><input name="new_category_name" placeholder="Opsional"></div>
                <div class="acct-field"><label>Vendor/Penerima</label><input name="vendor_name" placeholder="Nama vendor/penerima"></div>
                <div class="acct-field"><label>Nominal</label><input name="amount" type="number" min="0" step="1000" required></div>
                <div class="acct-field"><label>Tanggal</label><input name="expense_date" type="date" value="<?= date('Y-m-d'); ?>" required></div>
                <div class="acct-field"><label>Metode</label><input name="payment_method" placeholder="Transfer, cash, corporate card"></div>
                <div class="acct-field full"><label>Reference No</label><input name="reference_no" placeholder="Receipt / invoice / memo"></div>
                <div class="acct-field full"><label>Notes</label><textarea name="notes" placeholder="Catatan detail pengeluaran"></textarea></div>
                <div class="acct-field full acct-submit-row"><button class="acct-btn secondary" type="submit" name="record_state" value="draft"><i class="bi bi-file-earmark"></i> Simpan Draft</button><button class="acct-btn secondary" type="submit" name="record_state" value="verification"><i class="bi bi-hourglass-split"></i> Verification</button><button class="acct-btn" type="submit" name="record_state" value="published"><i class="bi bi-save"></i> Publish Pengeluaran</button></div>
            </form></article>
            <article class="acct-card span-7"><h2>Pengeluaran Terbesar Bulan Ini</h2><div class="acct-list"><?php $max = max(1, ...array_map(fn($r) => (float)$r['total'], $categoryStats ?: [['total' => 1]])); ?><?php foreach ($categoryStats as $cat): ?><div class="acct-list-row"><div class="nt-flex-1"><b><?= htmlspecialchars($cat['category_name']); ?></b><br><span><?= (int)$cat['transactions']; ?> transaksi</span><div class="acct-progress"><progress max="100" value="<?= min(100, ((float)$cat['total'] / $max) * 100); ?>"></progress></div></div><strong><?= nt_money($cat['total']); ?></strong></div><?php endforeach; ?></div></article>
            <article class="acct-card"><h2>Riwayat Pengeluaran</h2><div class="acct-table-wrap"><table class="acct-table"><thead><tr><th>Tanggal</th><th>Judul</th><th>Kategori</th><th>Vendor</th><th>Nominal</th><th>Ref</th><th>State</th><th></th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= nt_date($row['expense_date']); ?></td><td><strong><?= htmlspecialchars($row['title']); ?></strong><br><span><?= htmlspecialchars($row['payment_method']); ?></span></td><td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></td><td><?= htmlspecialchars($row['vendor_name']); ?></td><td><strong><?= nt_money($row['amount']); ?></strong></td><td><?= htmlspecialchars($row['reference_no']); ?></td><td><span class="acct-pill <?= nt_record_state_class($row['record_state'] ?? 'published'); ?>"><?= htmlspecialchars(nt_record_state_label($row['record_state'] ?? 'published')); ?></span></td><td class="acct-row-actions"><a class="acct-edit" href="<?= app_url('accounting/expenses/edit/' . (int)$row['id']); ?>"><i class="bi bi-pencil-square"></i></a><form action="<?= app_url('accounting/expenses/delete/' . (int)$row['id']); ?>" method="POST" onsubmit="return confirm('Hapus pengeluaran ini?');"><button class="acct-delete" type="submit"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?></tbody></table></div></article>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Accounting</span></footer>

</body></html>
