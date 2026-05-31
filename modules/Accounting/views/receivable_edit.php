<?php
$isLoginPage = false;
$pageTitle = 'Edit Utang - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';
require_once __DIR__ . '/_helpers.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-pencil-square"></i> Accounting / Edit Utang</span></div></div></div>
<main class="main-content">
    <section class="acct-shell">
        <div class="acct-hero"><div><div class="acct-kicker"><i class="bi bi-receipt"></i> Debt Control</div><h1>Edit Utang</h1><p>Perbarui creditor, pembayaran, jatuh tempo, dan status kewajiban perusahaan.</p></div><div class="acct-actions"><a class="acct-btn secondary" href="<?= app_url('accounting/receivables'); ?>"><i class="bi bi-arrow-left"></i> Back</a></div></div>
        <div class="acct-grid">
            <article class="acct-card span-7">
                <h2>Data Utang</h2>
                <form class="acct-form" action="<?= app_url('accounting/receivables/update/' . (int)$receivable['id']); ?>" method="POST">
                    <div class="acct-field full"><label>Creditor / Pihak Tujuan Pembayaran</label><input name="debtor_name" required value="<?= htmlspecialchars($receivable['debtor_name']); ?>"></div>
                    <div class="acct-field full"><label>Judul</label><input name="title" required value="<?= htmlspecialchars($receivable['title']); ?>"></div>
                    <div class="acct-field"><label>Nominal</label><input name="amount" type="number" min="0" step="1000" required value="<?= htmlspecialchars($receivable['amount']); ?>"></div>
                    <div class="acct-field"><label>Sudah Dibayar</label><input name="paid_amount" type="number" min="0" step="1000" value="<?= htmlspecialchars($receivable['paid_amount']); ?>"></div>
                    <div class="acct-field"><label>Tanggal Terbit</label><input name="issued_date" type="date" required value="<?= htmlspecialchars($receivable['issued_date']); ?>"></div>
                    <div class="acct-field"><label>Deadline</label><input name="due_date" type="date" value="<?= htmlspecialchars($receivable['due_date'] ?? ''); ?>"></div>
                    <div class="acct-field"><label>Status</label><select name="status"><?php foreach (['open' => 'Open', 'partial' => 'Partial', 'paid' => 'Paid', 'overdue' => 'Overdue', 'written_off' => 'Written Off'] as $value => $label): ?><option value="<?= $value; ?>" <?= $receivable['status'] === $value ? 'selected' : ''; ?>><?= $label; ?></option><?php endforeach; ?></select></div>
                    <div class="acct-field"><label>Reference No</label><input name="reference_no" value="<?= htmlspecialchars($receivable['reference_no']); ?>"></div>
                    <div class="acct-field full"><label>Notes</label><textarea name="notes"><?= htmlspecialchars($receivable['notes']); ?></textarea></div>
                    <div class="acct-field full acct-submit-row"><button class="acct-btn secondary" type="submit" name="record_state" value="draft"><i class="bi bi-file-earmark"></i> Simpan Draft</button><button class="acct-btn secondary" type="submit" name="record_state" value="verification"><i class="bi bi-hourglass-split"></i> Verification</button><button class="acct-btn" type="submit" name="record_state" value="published"><i class="bi bi-save"></i> Publish Utang</button></div>
                </form>
            </article>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Accounting</span></footer>

</body></html>
