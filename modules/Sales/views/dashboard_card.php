<?php require_once __DIR__ . '/_helpers.php'; ?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(185px,1fr));gap:12px;margin-bottom:16px;">
    <a href="<?= app_url('sales/leads'); ?>" style="text-decoration:none;background:#E8F0FB;border:1px solid #CFE0F3;border-radius:14px;padding:16px;"><div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#3A6EA5;">Leads Bulan Ini</div><div style="font-size:24px;font-weight:800;color:#1C2B3A;margin-top:8px;"><?= (int)$summary['leads_month']; ?></div></a>
    <a href="<?= app_url('sales/opportunities'); ?>" style="text-decoration:none;background:#E8F7EE;border:1px solid #BFE7CE;border-radius:14px;padding:16px;"><div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#1E7E34;">Pipeline Value</div><div style="font-size:24px;font-weight:800;color:#1C2B3A;margin-top:8px;"><?= sales_money($summary['pipeline_value']); ?></div></a>
    <a href="<?= app_url('sales/opportunities'); ?>" style="text-decoration:none;background:#FFF8E1;border:1px solid #F5E2A8;border-radius:14px;padding:16px;"><div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#B9770E;">Won Bulan Ini</div><div style="font-size:24px;font-weight:800;color:#1C2B3A;margin-top:8px;"><?= sales_money($summary['won_month']); ?></div></a>
    <a href="<?= app_url('sales/followups'); ?>" style="text-decoration:none;background:#FFF1F0;border:1px solid #FFCDC9;border-radius:14px;padding:16px;"><div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#C0392B;">Follow-up Due</div><div style="font-size:24px;font-weight:800;color:#1C2B3A;margin-top:8px;"><?= (int)$summary['followups_due']; ?></div></a>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">
    <div style="background:#fff;border:1px solid #EEF4FA;border-radius:14px;padding:14px;">
        <div style="font-size:13px;font-weight:800;color:#1C2B3A;margin-bottom:10px;">Pipeline</div>
        <?php foreach ($pipeline as $row): ?><div style="display:flex;justify-content:space-between;gap:10px;border-top:1px solid #EEF4FA;padding:9px 0;font-size:12.5px;"><strong><?= htmlspecialchars(sales_label($row['stage'])); ?></strong><span style="color:#3A6EA5;font-weight:800;"><?= sales_money($row['value']); ?></span></div><?php endforeach; ?>
    </div>
    <div style="background:#fff;border:1px solid #EEF4FA;border-radius:14px;padding:14px;">
        <div style="font-size:13px;font-weight:800;color:#1C2B3A;margin-bottom:10px;">Upcoming Follow-ups</div>
        <?php foreach ($upcoming as $row): ?><div style="display:flex;justify-content:space-between;gap:10px;border-top:1px solid #EEF4FA;padding:9px 0;font-size:12.5px;"><strong><?= htmlspecialchars($row['company_name'] ?: $row['opportunity_title'] ?: 'Follow-up'); ?></strong><span style="color:#B9770E;font-weight:800;"><?= sales_date($row['next_followup_date']); ?></span></div><?php endforeach; ?>
    </div>
</div>
