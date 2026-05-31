<?php require_once __DIR__ . '/_helpers.php'; ?>
<div class="nt-metric-grid">
    <a class="nt-metric-card" href="<?= app_url('sales/leads'); ?>">
        <div class="nt-metric-label">Leads Bulan Ini</div>
        <div class="nt-metric-value"><?= (int)$summary['leads_month']; ?></div>
    </a>
    <a class="nt-metric-card success" href="<?= app_url('sales/opportunities'); ?>">
        <div class="nt-metric-label">Pipeline Value</div>
        <div class="nt-metric-value"><?= sales_money($summary['pipeline_value']); ?></div>
    </a>
    <a class="nt-metric-card warning" href="<?= app_url('sales/opportunities'); ?>">
        <div class="nt-metric-label">Won Bulan Ini</div>
        <div class="nt-metric-value"><?= sales_money($summary['won_month']); ?></div>
    </a>
    <a class="nt-metric-card danger" href="<?= app_url('sales/followups'); ?>">
        <div class="nt-metric-label">Follow-up Due</div>
        <div class="nt-metric-value"><?= (int)$summary['followups_due']; ?></div>
    </a>
</div>
<div class="nt-panel-grid">
    <div class="nt-panel">
        <div class="nt-panel-title">Pipeline</div>
        <?php foreach ($pipeline as $row): ?>
            <div class="nt-split-row">
                <strong><?= htmlspecialchars(sales_label($row['stage'])); ?></strong>
                <span class="nt-money-primary"><?= sales_money($row['value']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="nt-panel">
        <div class="nt-panel-title">Upcoming Follow-ups</div>
        <?php foreach ($upcoming as $row): ?>
            <div class="nt-split-row">
                <strong><?= htmlspecialchars($row['company_name'] ?: $row['opportunity_title'] ?: 'Follow-up'); ?></strong>
                <span class="nt-money-warning"><?= sales_date($row['next_followup_date']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
