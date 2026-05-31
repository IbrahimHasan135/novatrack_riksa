<?php
$isLoginPage = false;
$pageTitle = 'Sales - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';
require_once __DIR__ . '/_helpers.php';
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-briefcase"></i> Sales</span></div></div></div>
<main class="main-content">
    <section class="sales-shell">
        <div class="sales-hero">
            <div><div class="sales-kicker"><i class="bi bi-activity"></i> Legal Business Pipeline</div><h1>Sales Overview</h1><p>Kelola inquiry, konsultasi, proposal, negosiasi, sampai engagement untuk layanan legal consulting, perizinan, audit, dan compliance.</p></div>
            <div class="sales-actions"><a class="sales-btn" href="<?= app_url('sales/leads'); ?>"><i class="bi bi-person-plus"></i> New Lead</a><a class="sales-btn secondary" href="<?= app_url('sales/opportunities'); ?>"><i class="bi bi-kanban"></i> Pipeline</a></div>
        </div>
        <div class="sales-grid">
            <article class="sales-card sales-metric span-3"><div class="label">Leads Bulan Ini</div><div class="value"><?= (int)$summary['leads_month']; ?></div><div class="hint">Inquiry baru</div></article>
            <article class="sales-card sales-metric green span-3"><div class="label">Pipeline Value</div><div class="value"><?= sales_money($summary['pipeline_value']); ?></div><div class="hint">Active deal value</div></article>
            <article class="sales-card sales-metric orange span-3"><div class="label">Won Bulan Ini</div><div class="value"><?= sales_money($summary['won_month']); ?></div><div class="hint">Engagement closed</div></article>
            <article class="sales-card sales-metric red span-3"><div class="label">Follow-up Due</div><div class="value"><?= (int)$summary['followups_due']; ?></div><div class="hint"><?= (float)$summary['conversion_rate']; ?>% conversion</div></article>

            <article class="sales-card span-7"><h2>Pipeline by Stage</h2><div class="sales-list"><?php $max = max(1, ...array_map(fn($r) => (float)$r['value'], $pipeline ?: [['value' => 1]])); ?><?php foreach ($pipeline as $row): ?><div class="sales-list-row"><div class="nt-flex-1"><b><?= htmlspecialchars(sales_label($row['stage'])); ?></b><br><span><?= (int)$row['total']; ?> opportunity · <?= sales_money($row['value']); ?></span><div class="sales-progress"><progress max="100" value="<?= min(100, ((float)$row['value'] / $max) * 100); ?>"></progress></div></div></div><?php endforeach; ?></div></article>
            <article class="sales-card span-5"><h2>Top Lead Sources</h2><div class="sales-list"><?php foreach ($topSources as $row): ?><div class="sales-list-row"><div><b><?= htmlspecialchars($row['source']); ?></b><br><span><?= (int)$row['total']; ?> leads</span></div><strong><?= sales_money($row['value']); ?></strong></div><?php endforeach; ?></div></article>
            <article class="sales-card span-6"><h2>Upcoming Follow-ups</h2><div class="sales-list"><?php foreach ($upcoming as $row): ?><div class="sales-list-row"><div><b><?= htmlspecialchars($row['company_name'] ?: $row['opportunity_title'] ?: 'Follow-up'); ?></b><br><span><?= sales_label($row['activity_type']); ?> · <?= sales_date($row['next_followup_date']); ?></span></div><span class="sales-pill orange"><?= htmlspecialchars($row['next_action'] ?: 'Next action'); ?></span></div><?php endforeach; ?></div></article>
            <article class="sales-card span-6"><h2>Recent Leads</h2><div class="sales-list"><?php foreach ($recentLeads as $row): ?><div class="sales-list-row"><div><b><?= htmlspecialchars($row['company_name']); ?></b><br><span><?= htmlspecialchars($row['need_category']); ?> · <?= sales_date($row['created_at']); ?></span></div><span class="sales-pill"><?= sales_label($row['status']); ?></span></div><?php endforeach; ?></div></article>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Sales</span></footer>

</body></html>
