<?php
$isLoginPage = false;
$pageTitle = 'Edit Pemasukan - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';
require_once __DIR__ . '/_helpers.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-pencil-square"></i> Accounting / Edit Pemasukan</span></div></div></div>
<main class="main-content">
    <section class="acct-shell">
        <div class="acct-hero"><div><div class="acct-kicker"><i class="bi bi-arrow-down-circle"></i> Revenue Tracking</div><h1>Edit Pemasukan</h1><p>Perbarui detail pemasukan, sumber revenue, nominal, dan referensi transaksi.</p></div><div class="acct-actions"><a class="acct-btn secondary" href="<?= app_url('accounting/income'); ?>"><i class="bi bi-arrow-left"></i> Back</a></div></div>
        <div class="acct-grid">
            <article class="acct-card span-7">
                <h2>Data Pemasukan</h2>
                <form class="acct-form" action="<?= app_url('accounting/income/update/' . (int)$income['id']); ?>" method="POST">
                    <div class="acct-field full"><label>Judul</label><input name="title" required value="<?= htmlspecialchars($income['title']); ?>"></div>
                    <div class="acct-field"><label>Jenis Pemasukan</label><select name="source_id"><option value="">Pilih sumber</option><?php foreach ($sources as $source): ?><option value="<?= (int)$source['id']; ?>" <?= (int)$income['source_id'] === (int)$source['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($source['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="acct-field"><label>Sumber Baru</label><input name="new_source_name" placeholder="Opsional"></div>
                    <div class="acct-field"><label>Client</label><input name="client_name" value="<?= htmlspecialchars($income['client_name']); ?>"></div>
                    <div class="acct-field"><label>Nominal</label><input name="amount" type="number" min="0" step="1000" required value="<?= htmlspecialchars($income['amount']); ?>"></div>
                    <div class="acct-field"><label>Tanggal Terima</label><input name="received_date" type="date" required value="<?= htmlspecialchars($income['received_date']); ?>"></div>
                    <div class="acct-field"><label>Metode</label><input name="payment_method" value="<?= htmlspecialchars($income['payment_method']); ?>"></div>
                    <div class="acct-field full"><label>Reference No</label><input name="reference_no" value="<?= htmlspecialchars($income['reference_no']); ?>"></div>
                    <div class="acct-field full"><label>Notes</label><textarea name="notes"><?= htmlspecialchars($income['notes']); ?></textarea></div>
                    <div class="acct-field full"><button class="acct-btn" type="submit"><i class="bi bi-save"></i> Update Pemasukan</button></div>
                </form>
            </article>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Accounting</span></footer>
<?php require __DIR__ . '/styles.php'; ?>
</body></html>
