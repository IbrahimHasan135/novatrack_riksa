<?php require_once __DIR__ . '/_helpers.php'; ?>
<div class="nt-metric-grid">
    <a class="nt-metric-card success" href="<?= app_url('accounting/income'); ?>">
        <div class="nt-metric-label">Pemasukan Bulan Ini</div>
        <div class="nt-metric-value"><?= nt_money($summary['income_month']); ?></div>
    </a>
    <a class="nt-metric-card danger" href="<?= app_url('accounting/expenses'); ?>">
        <div class="nt-metric-label">Pengeluaran Bulan Ini</div>
        <div class="nt-metric-value"><?= nt_money($summary['expense_month']); ?></div>
    </a>
    <a class="nt-metric-card" href="<?= app_url('accounting'); ?>">
        <div class="nt-metric-label">Net Bulan Ini</div>
        <div class="nt-metric-value"><?= nt_money($summary['net_month']); ?></div>
    </a>
    <a class="nt-metric-card warning" href="<?= app_url('accounting/receivables'); ?>">
        <div class="nt-metric-label">Utang Outstanding</div>
        <div class="nt-metric-value"><?= nt_money($summary['receivable_open']); ?></div>
    </a>
    <a class="nt-metric-card muted" href="<?= app_url('accounting/receivables'); ?>">
        <div class="nt-metric-label">Draft Accounting</div>
        <div class="nt-metric-value"><?= (int)$summary['draft_count']; ?></div>
    </a>
    <a class="nt-metric-card muted" href="<?= app_url('accounting/receivables'); ?>">
        <div class="nt-metric-label">Verification</div>
        <div class="nt-metric-value"><?= (int)$summary['verification_count']; ?></div>
    </a>
</div>
<div class="nt-panel-grid">
    <div class="nt-panel">
        <div class="nt-panel-title">Top Pemasukan</div>
        <?php foreach ($topSources as $source): ?>
            <div class="nt-split-row">
                <strong><?= htmlspecialchars($source['source_name']); ?></strong>
                <span class="nt-money-success"><?= nt_money($source['total']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="nt-panel">
        <div class="nt-panel-title">Recap Bulanan</div>
        <?php foreach ($monthly as $row): ?>
            <div class="nt-split-row">
                <strong><?= htmlspecialchars($row['period']); ?></strong>
                <span class="nt-money-primary"><?= nt_money($row['net']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
