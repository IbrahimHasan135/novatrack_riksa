<?php
$isLoginPage = false;
$pageTitle = 'Edit Pengeluaran - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';
require_once __DIR__ . '/_helpers.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-pencil-square"></i> Accounting / Edit Pengeluaran</span></div></div></div>
<main class="main-content">
    <section class="acct-shell">
        <div class="acct-hero"><div><div class="acct-kicker"><i class="bi bi-arrow-up-circle"></i> Cost Control</div><h1>Edit Pengeluaran</h1><p>Perbarui kategori, vendor, nominal, tanggal, dan catatan pengeluaran.</p></div><div class="acct-actions"><a class="acct-btn secondary" href="<?= app_url('accounting/expenses'); ?>"><i class="bi bi-arrow-left"></i> Back</a></div></div>
        <div class="acct-grid">
            <article class="acct-card span-7">
                <h2>Data Pengeluaran</h2>
                <form class="acct-form" action="<?= app_url('accounting/expenses/update/' . (int)$expense['id']); ?>" method="POST">
                    <div class="acct-field full"><label>Judul</label><input name="title" required value="<?= htmlspecialchars($expense['title']); ?>"></div>
                    <div class="acct-field"><label>Kategori</label><select name="category_id"><option value="">Pilih kategori</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id']; ?>" <?= (int)$expense['category_id'] === (int)$category['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($category['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="acct-field"><label>Kategori Baru</label><input name="new_category_name" placeholder="Opsional"></div>
                    <div class="acct-field"><label>Vendor/Penerima</label><input name="vendor_name" value="<?= htmlspecialchars($expense['vendor_name']); ?>"></div>
                    <div class="acct-field"><label>Nominal</label><input name="amount" type="number" min="0" step="1000" required value="<?= htmlspecialchars($expense['amount']); ?>"></div>
                    <div class="acct-field"><label>Tanggal</label><input name="expense_date" type="date" required value="<?= htmlspecialchars($expense['expense_date']); ?>"></div>
                    <div class="acct-field"><label>Metode</label><input name="payment_method" value="<?= htmlspecialchars($expense['payment_method']); ?>"></div>
                    <div class="acct-field full"><label>Reference No</label><input name="reference_no" value="<?= htmlspecialchars($expense['reference_no']); ?>"></div>
                    <div class="acct-field full"><label>Notes</label><textarea name="notes"><?= htmlspecialchars($expense['notes']); ?></textarea></div>
                    <div class="acct-field full acct-submit-row"><button class="acct-btn secondary" type="submit" name="record_state" value="draft"><i class="bi bi-file-earmark"></i> Simpan Draft</button><button class="acct-btn secondary" type="submit" name="record_state" value="verification"><i class="bi bi-hourglass-split"></i> Verification</button><button class="acct-btn" type="submit" name="record_state" value="published"><i class="bi bi-save"></i> Publish Pengeluaran</button></div>
                </form>
            </article>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Accounting</span></footer>
<?php require __DIR__ . '/styles.php'; ?>
</body></html>
