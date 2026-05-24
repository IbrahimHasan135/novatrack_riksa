<?php require_once __DIR__ . '/_helpers.php'; ?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;margin-bottom:16px;">
    <a href="<?= app_url('accounting/income'); ?>" style="text-decoration:none;background:#E8F7EE;border:1px solid #BFE7CE;border-radius:14px;padding:16px;">
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#1E7E34;">Pemasukan Bulan Ini</div>
        <div style="font-size:24px;font-weight:800;color:#1C2B3A;margin-top:8px;"><?= nt_money($summary['income_month']); ?></div>
    </a>
    <a href="<?= app_url('accounting/expenses'); ?>" style="text-decoration:none;background:#FFF1F0;border:1px solid #FFCDC9;border-radius:14px;padding:16px;">
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#C0392B;">Pengeluaran Bulan Ini</div>
        <div style="font-size:24px;font-weight:800;color:#1C2B3A;margin-top:8px;"><?= nt_money($summary['expense_month']); ?></div>
    </a>
    <a href="<?= app_url('accounting'); ?>" style="text-decoration:none;background:#E8F0FB;border:1px solid #CFE0F3;border-radius:14px;padding:16px;">
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#3A6EA5;">Net Bulan Ini</div>
        <div style="font-size:24px;font-weight:800;color:#1C2B3A;margin-top:8px;"><?= nt_money($summary['net_month']); ?></div>
    </a>
    <a href="<?= app_url('accounting/receivables'); ?>" style="text-decoration:none;background:#FFF8E1;border:1px solid #F5E2A8;border-radius:14px;padding:16px;">
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#B9770E;">Piutang Outstanding</div>
        <div style="font-size:24px;font-weight:800;color:#1C2B3A;margin-top:8px;"><?= nt_money($summary['receivable_open']); ?></div>
    </a>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">
    <div style="background:#fff;border:1px solid #EEF4FA;border-radius:14px;padding:14px;">
        <div style="font-size:13px;font-weight:800;color:#1C2B3A;margin-bottom:10px;">Top Pemasukan</div>
        <?php foreach ($topSources as $source): ?>
            <div style="display:flex;justify-content:space-between;gap:10px;border-top:1px solid #EEF4FA;padding:9px 0;font-size:12.5px;">
                <strong><?= htmlspecialchars($source['source_name']); ?></strong>
                <span style="color:#1E7E34;font-weight:800;"><?= nt_money($source['total']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div style="background:#fff;border:1px solid #EEF4FA;border-radius:14px;padding:14px;">
        <div style="font-size:13px;font-weight:800;color:#1C2B3A;margin-bottom:10px;">Recap Bulanan</div>
        <?php foreach ($monthly as $row): ?>
            <div style="display:flex;justify-content:space-between;gap:10px;border-top:1px solid #EEF4FA;padding:9px 0;font-size:12.5px;">
                <strong><?= htmlspecialchars($row['period']); ?></strong>
                <span style="color:#3A6EA5;font-weight:800;"><?= nt_money($row['net']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
