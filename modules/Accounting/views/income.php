<?php
$isLoginPage = false;
$pageTitle = 'Pemasukan - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';
require_once __DIR__ . '/_helpers.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-arrow-down-circle"></i> Accounting / Pemasukan</span></div></div></div>
<main class="main-content">
    <section class="acct-shell">
        <div class="acct-hero">
            <div><div class="acct-kicker"><i class="bi bi-graph-up-arrow"></i> Revenue Tracking</div><h1>Pemasukan</h1><p>Catat pemasukan berdasarkan jenis sumber, client, metode pembayaran, dan referensi agar sumber revenue terbesar mudah dianalisis.</p></div>
            <div class="acct-actions"><a class="acct-btn secondary" href="<?= app_url('accounting'); ?>"><i class="bi bi-speedometer2"></i> Overview</a></div>
        </div>
        <?php if (isset($_GET['created']) || isset($_GET['deleted']) || isset($_GET['updated'])): ?><div class="acct-alert"><i class="bi bi-check-circle"></i> Data pemasukan berhasil diperbarui.</div><?php endif; ?>
        <div class="acct-grid">
            <article class="acct-card span-5">
                <h2>Tambah Pemasukan</h2>
                <form class="acct-form" action="<?= app_url('accounting/income'); ?>" method="POST">
                    <div class="acct-field full"><label>Judul</label><input name="title" required placeholder="Contoh: Retainer PT Aman"></div>
                    <div class="acct-field"><label>Jenis Pemasukan</label><select name="source_id"><option value="">Pilih sumber</option><?php foreach ($sources as $source): ?><option value="<?= (int)$source['id']; ?>"><?= htmlspecialchars($source['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="acct-field"><label>Sumber Baru</label><input name="new_source_name" placeholder="Opsional"></div>
                    <div class="acct-field"><label>Client</label><input name="client_name" placeholder="Nama perusahaan/client"></div>
                    <div class="acct-field"><label>Nominal</label><input name="amount" type="number" min="0" step="1000" required></div>
                    <div class="acct-field"><label>Tanggal Terima</label><input name="received_date" type="date" value="<?= date('Y-m-d'); ?>" required></div>
                    <div class="acct-field"><label>Metode</label><input name="payment_method" placeholder="Transfer, cash, giro"></div>
                    <div class="acct-field full"><label>Reference No</label><input name="reference_no" placeholder="Invoice / receipt / kontrak"></div>
                    <div class="acct-field full"><label>Notes</label><textarea name="notes" placeholder="Catatan detail pemasukan"></textarea></div>
                    <div class="acct-field full"><button class="acct-btn" type="submit"><i class="bi bi-save"></i> Simpan Pemasukan</button></div>
                </form>
            </article>
            <article class="acct-card span-7">
                <h2>Sumber Pemasukan Terbesar Bulan Ini</h2>
                <div class="acct-list">
                    <?php $max = max(1, ...array_map(fn($r) => (float)$r['total'], $topSources ?: [['total' => 1]])); ?>
                    <?php foreach ($topSources as $source): ?>
                        <div class="acct-list-row"><div style="flex:1;"><b><?= htmlspecialchars($source['source_name']); ?></b><br><span><?= (int)$source['transactions']; ?> transaksi</span><div class="acct-progress"><span style="width:<?= min(100, ((float)$source['total'] / $max) * 100); ?>%"></span></div></div><strong><?= nt_money($source['total']); ?></strong></div>
                    <?php endforeach; ?>
                </div>
            </article>
            <article class="acct-card"><h2>Riwayat Pemasukan</h2><div class="acct-table-wrap"><table class="acct-table"><thead><tr><th>Tanggal</th><th>Judul</th><th>Sumber</th><th>Client</th><th>Nominal</th><th>Ref</th><th></th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= nt_date($row['received_date']); ?></td><td><strong><?= htmlspecialchars($row['title']); ?></strong><br><span><?= htmlspecialchars($row['payment_method']); ?></span></td><td><?= htmlspecialchars($row['source_name'] ?? 'Uncategorized'); ?></td><td><?= htmlspecialchars($row['client_name']); ?></td><td><strong><?= nt_money($row['amount']); ?></strong></td><td><?= htmlspecialchars($row['reference_no']); ?></td><td class="acct-row-actions"><a class="acct-edit" href="<?= app_url('accounting/income/edit/' . (int)$row['id']); ?>"><i class="bi bi-pencil-square"></i></a><form action="<?= app_url('accounting/income/delete/' . (int)$row['id']); ?>" method="POST" onsubmit="return confirm('Hapus pemasukan ini?');"><button class="acct-delete" type="submit"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?></tbody></table></div></article>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Accounting</span></footer>
<?php require __DIR__ . '/styles.php'; ?>
</body></html>
