<?php
$isLoginPage = false;
$pageTitle = 'Utang - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';
require_once __DIR__ . '/_helpers.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-receipt"></i> Accounting / Utang</span></div></div></div>
<main class="main-content">
    <section class="acct-shell">
        <div class="acct-hero">
            <div><div class="acct-kicker"><i class="bi bi-hourglass-split"></i> Debt Control</div><h1>Utang Perusahaan</h1><p>Track kewajiban perusahaan ke vendor, partner, atau pihak lain, termasuk pembayaran parsial, jatuh tempo, dan status outstanding.</p></div>
            <div class="acct-actions"><a class="acct-btn secondary" href="<?= app_url('accounting'); ?>"><i class="bi bi-speedometer2"></i> Overview</a></div>
        </div>
        <?php if (isset($_GET['created']) || isset($_GET['deleted']) || isset($_GET['updated'])): ?><div class="acct-alert"><i class="bi bi-check-circle"></i> Data utang berhasil diperbarui.</div><?php endif; ?>
        <div class="acct-grid">
            <article class="acct-card acct-metric debt span-3"><div class="label">Outstanding</div><div class="value"><?= nt_money($stats['outstanding'] ?? 0); ?></div><div class="hint"><?= (int)($stats['total_items'] ?? 0); ?> invoice/utang</div></article>
            <article class="acct-card acct-metric income span-3"><div class="label">Sudah Dibayar</div><div class="value"><?= nt_money($stats['paid_amount'] ?? 0); ?></div><div class="hint">Dari total <?= nt_money($stats['total_amount'] ?? 0); ?></div></article>
            <article class="acct-card acct-metric expense span-3"><div class="label">Overdue</div><div class="value"><?= (int)($stats['overdue_count'] ?? 0); ?></div><div class="hint">Butuh follow-up</div></article>
            <article class="acct-card acct-metric net span-3"><div class="label">Payment Rate</div><div class="value"><?= ((float)($stats['total_amount'] ?? 0) > 0) ? number_format(((float)$stats['paid_amount'] / (float)$stats['total_amount']) * 100, 1) : '0.0'; ?>%</div><div class="hint">Rasio pembayaran</div></article>

            <article class="acct-card span-5">
                <h2>Tambah Utang</h2>
                <form class="acct-form" action="<?= app_url('accounting/receivables'); ?>" method="POST">
                    <div class="acct-field full"><label>Creditor / Pihak Tujuan Pembayaran</label><input name="debtor_name" required placeholder="Contoh: PT Aman, vendor, partner"></div>
                    <div class="acct-field full"><label>Judul</label><input name="title" required placeholder="Contoh: Tagihan vendor legal document"></div>
                    <div class="acct-field"><label>Nominal</label><input name="amount" type="number" min="0" step="1000" required></div>
                    <div class="acct-field"><label>Sudah Dibayar</label><input name="paid_amount" type="number" min="0" step="1000" value="0"></div>
                    <div class="acct-field"><label>Tanggal Terbit</label><input name="issued_date" type="date" value="<?= date('Y-m-d'); ?>" required></div>
                    <div class="acct-field"><label>Deadline</label><input name="due_date" type="date"></div>
                    <div class="acct-field"><label>Status</label><select name="status"><option value="open">Open</option><option value="partial">Partial</option><option value="paid">Paid</option><option value="overdue">Overdue</option><option value="written_off">Written Off</option></select></div>
                    <div class="acct-field"><label>Reference No</label><input name="reference_no" placeholder="Invoice / kontrak"></div>
                    <div class="acct-field full"><label>Notes</label><textarea name="notes" placeholder="Catatan kewajiban, PIC, timeline pembayaran"></textarea></div>
                    <div class="acct-field full acct-submit-row"><button class="acct-btn secondary" type="submit" name="record_state" value="draft"><i class="bi bi-file-earmark"></i> Simpan Draft</button><button class="acct-btn secondary" type="submit" name="record_state" value="verification"><i class="bi bi-hourglass-split"></i> Verification</button><button class="acct-btn" type="submit" name="record_state" value="published"><i class="bi bi-save"></i> Publish Utang</button></div>
                </form>
            </article>
            <article class="acct-card span-7">
                <h2>Daftar Utang</h2>
                <div class="acct-table-wrap">
                    <table class="acct-table">
                        <thead><tr><th>Creditor</th><th>Judul</th><th>Due</th><th>Total</th><th>Outstanding</th><th>Status</th><th>State</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php $statusClass = ['paid' => 'green', 'overdue' => 'red', 'partial' => 'orange', 'written_off' => 'red'][$row['status']] ?? ''; ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['debtor_name']); ?></strong><br><span><?= htmlspecialchars($row['reference_no']); ?></span></td>
                                <td><?= htmlspecialchars($row['title']); ?><br><span><?= htmlspecialchars($row['notes']); ?></span></td>
                                <td><?= nt_date($row['due_date']); ?></td>
                                <td><?= nt_money($row['amount']); ?></td>
                                <td><strong><?= nt_money($row['outstanding']); ?></strong></td>
                                <td><span class="acct-pill <?= $statusClass; ?>"><?= htmlspecialchars(str_replace('_', ' ', $row['status'])); ?></span></td>
                                <td><span class="acct-pill <?= nt_record_state_class($row['record_state'] ?? 'published'); ?>"><?= htmlspecialchars(nt_record_state_label($row['record_state'] ?? 'published')); ?></span></td>
                                <td class="acct-row-actions"><a class="acct-edit" href="<?= app_url('accounting/receivables/edit/' . (int)$row['id']); ?>"><i class="bi bi-pencil-square"></i></a><form action="<?= app_url('accounting/receivables/delete/' . (int)$row['id']); ?>" method="POST" onsubmit="return confirm('Hapus utang ini?');"><button class="acct-delete" type="submit"><i class="bi bi-trash"></i></button></form></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Accounting</span></footer>

</body></html>
