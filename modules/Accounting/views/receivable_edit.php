<?php
$isLoginPage = false;
$pageTitle = 'Edit Piutang - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';
require_once __DIR__ . '/_helpers.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-pencil-square"></i> Accounting / Edit Piutang</span></div></div></div>
<main class="main-content">
    <section class="acct-shell">
        <div class="acct-hero"><div><div class="acct-kicker"><i class="bi bi-receipt"></i> Receivable Control</div><h1>Edit Piutang</h1><p>Perbarui debtor, pembayaran, jatuh tempo, dan status penagihan.</p></div><div class="acct-actions"><a class="acct-btn secondary" href="<?= app_url('accounting/receivables'); ?>"><i class="bi bi-arrow-left"></i> Back</a></div></div>
        <div class="acct-grid">
            <article class="acct-card span-7">
                <h2>Data Piutang</h2>
                <form class="acct-form" action="<?= app_url('accounting/receivables/update/' . (int)$receivable['id']); ?>" method="POST">
                    <div class="acct-field full"><label>Debtor / Pihak Berutang</label><input name="debtor_name" required value="<?= htmlspecialchars($receivable['debtor_name']); ?>"></div>
                    <div class="acct-field full"><label>Judul</label><input name="title" required value="<?= htmlspecialchars($receivable['title']); ?>"></div>
                    <div class="acct-field"><label>Nominal</label><input name="amount" type="number" min="0" step="1000" required value="<?= htmlspecialchars($receivable['amount']); ?>"></div>
                    <div class="acct-field"><label>Sudah Dibayar</label><input name="paid_amount" type="number" min="0" step="1000" value="<?= htmlspecialchars($receivable['paid_amount']); ?>"></div>
                    <div class="acct-field"><label>Tanggal Terbit</label><input name="issued_date" type="date" required value="<?= htmlspecialchars($receivable['issued_date']); ?>"></div>
                    <div class="acct-field"><label>Deadline</label><input name="due_date" type="date" value="<?= htmlspecialchars($receivable['due_date'] ?? ''); ?>"></div>
                    <div class="acct-field"><label>Status</label><select name="status"><?php foreach (['open' => 'Open', 'partial' => 'Partial', 'paid' => 'Paid', 'overdue' => 'Overdue', 'written_off' => 'Written Off'] as $value => $label): ?><option value="<?= $value; ?>" <?= $receivable['status'] === $value ? 'selected' : ''; ?>><?= $label; ?></option><?php endforeach; ?></select></div>
                    <div class="acct-field"><label>Reference No</label><input name="reference_no" value="<?= htmlspecialchars($receivable['reference_no']); ?>"></div>
                    <div class="acct-field full"><label>Notes</label><textarea name="notes"><?= htmlspecialchars($receivable['notes']); ?></textarea></div>
                    <div class="acct-field full"><button class="acct-btn" type="submit"><i class="bi bi-save"></i> Update Piutang</button></div>
                </form>
            </article>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Accounting</span></footer>
<?php require __DIR__ . '/styles.php'; ?>
</body></html>
