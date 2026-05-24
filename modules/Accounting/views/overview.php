<?php
$isLoginPage = false;
$pageTitle = 'Accounting - NovaTrack';
require_once __DIR__ . '/../../../views/layout/header.php';
require_once __DIR__ . '/_helpers.php';
$user = \Core\Auth::getInstance()->user();
?>
<div class="topbar"><div class="topbar-inner"><div class="topbar-left"><span class="topbar-breadcrumb"><i class="bi bi-calculator"></i> Accounting</span></div><div class="topbar-right"><span class="topbar-greeting">Halo, <strong><?= htmlspecialchars($user['full_name'] ?? 'User'); ?></strong></span><div class="topbar-avatar"><?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)); ?></div></div></div></div>
<main class="main-content">
    <section class="acct-shell">
        <div class="acct-hero">
            <div><div class="acct-kicker"><i class="bi bi-activity"></i> Financial Intelligence</div><h1>Accounting Overview</h1><p>Monitor pemasukan, pengeluaran, piutang, recap bulanan, recap tahunan, dan sumber pemasukan paling menguntungkan.</p></div>
            <div class="acct-actions"><a class="acct-btn" href="<?= app_url('accounting/income'); ?>"><i class="bi bi-plus-circle"></i> Pemasukan</a><a class="acct-btn secondary" href="<?= app_url('accounting/expenses'); ?>"><i class="bi bi-receipt"></i> Pengeluaran</a></div>
        </div>
        <div class="acct-grid">
            <article class="acct-card acct-metric income span-3"><div class="label">Pemasukan Bulan Ini</div><div class="value"><?= nt_money($summary['income_month']); ?></div><div class="hint">Tahun ini <?= nt_money($summary['income_year']); ?></div></article>
            <article class="acct-card acct-metric expense span-3"><div class="label">Pengeluaran Bulan Ini</div><div class="value"><?= nt_money($summary['expense_month']); ?></div><div class="hint">Tahun ini <?= nt_money($summary['expense_year']); ?></div></article>
            <article class="acct-card acct-metric net span-3"><div class="label">Net Bulan Ini</div><div class="value"><?= nt_money($summary['net_month']); ?></div><div class="hint">Net tahun ini <?= nt_money($summary['net_year']); ?></div></article>
            <article class="acct-card acct-metric debt span-3"><div class="label">Piutang Outstanding</div><div class="value"><?= nt_money($summary['receivable_open']); ?></div><div class="hint"><?= (int)$receivableStats['overdue_count']; ?> overdue</div></article>

            <article class="acct-card span-12">
                <div class="acct-card-head">
                    <div>
                        <h2>Analitik Accounting</h2>
                        <p><?= htmlspecialchars($periodMode === 'year' ? 'Recap bulanan ' . $periodLabel : 'Recap harian ' . $periodLabel); ?> untuk pemasukan, pengeluaran, net, piutang, dan top transaksi.</p>
                    </div>
                    <form class="acct-filter" action="<?= app_url('accounting'); ?>" method="GET">
                        <select name="view" id="acctPeriodMode">
                            <option value="month" <?= $periodMode === 'month' ? 'selected' : ''; ?>>Bulan</option>
                            <option value="year" <?= $periodMode === 'year' ? 'selected' : ''; ?>>Tahun</option>
                        </select>
                        <input type="month" name="month" id="acctMonthInput" value="<?= htmlspecialchars($selectedMonth); ?>">
                        <select name="year" id="acctYearInput">
                            <?php foreach ($availableYears as $year): ?>
                                <option value="<?= (int)$year; ?>" <?= (int)$selectedYear === (int)$year ? 'selected' : ''; ?>><?= (int)$year; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit"><i class="bi bi-funnel"></i> Apply</button>
                    </form>
                </div>
                <?php $reportQuery = 'view=' . urlencode($periodMode) . '&month=' . urlencode($selectedMonth) . '&year=' . urlencode((string)$selectedYear); ?>
                <div class="acct-report-actions">
                    <a href="<?= app_url('accounting/report?' . $reportQuery . '&format=pdf'); ?>" target="_blank"><i class="bi bi-filetype-pdf"></i> PDF</a>
                    <a href="<?= app_url('accounting/report?' . $reportQuery . '&format=excel'); ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
                </div>
                <div class="acct-analytics-grid">
                    <div class="acct-chart-box"><canvas id="acctMainChart"></canvas></div>
                    <aside class="acct-top-panel">
                        <div>
                            <div class="acct-mini-title">Top Pemasukan <?= htmlspecialchars($periodLabel); ?></div>
                            <div class="acct-list compact">
                                <?php foreach ($topSources as $source): ?>
                                    <div class="acct-list-row"><div><b><?= htmlspecialchars($source['source_name']); ?></b><br><span><?= (int)$source['transactions']; ?> transaksi</span></div><strong><?= nt_money($source['total']); ?></strong></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div>
                            <div class="acct-mini-title">Top Pengeluaran <?= htmlspecialchars($periodLabel); ?></div>
                            <div class="acct-list compact">
                                <?php foreach ($topExpenses as $expense): ?>
                                    <div class="acct-list-row"><div><b><?= htmlspecialchars($expense['category_name']); ?></b><br><span><?= (int)$expense['transactions']; ?> transaksi</span></div><strong><?= nt_money($expense['total']); ?></strong></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </aside>
                </div>
            </article>

            <article class="acct-card span-7"><h2>Recap Bulanan</h2><div class="acct-table-wrap"><table class="acct-table"><thead><tr><th>Periode</th><th>Pemasukan</th><th>Pengeluaran</th><th>Net</th></tr></thead><tbody><?php foreach ($monthly as $row): ?><tr><td><strong><?= htmlspecialchars($row['period']); ?></strong></td><td><?= nt_money($row['income']); ?></td><td><?= nt_money($row['expense']); ?></td><td><strong><?= nt_money($row['net']); ?></strong></td></tr><?php endforeach; ?></tbody></table></div></article>
            <article class="acct-card span-5"><h2>Piutang</h2><div class="acct-list"><div class="acct-list-row"><div><b>Total Piutang</b><br><span><?= (int)$receivableStats['total_items']; ?> item</span></div><strong><?= nt_money($receivableStats['total_amount']); ?></strong></div><div class="acct-list-row"><div><b>Outstanding</b><br><span>Belum dibayar</span></div><strong><?= nt_money($receivableStats['outstanding']); ?></strong></div><div class="acct-list-row"><div><b>Overdue</b><br><span>Butuh follow-up</span></div><strong><?= (int)$receivableStats['overdue_count']; ?></strong></div></div></article>

            <article class="acct-card span-6"><h2>Recap Tahunan</h2><div class="acct-table-wrap"><table class="acct-table"><thead><tr><th>Tahun</th><th>Pemasukan</th><th>Pengeluaran</th><th>Net</th></tr></thead><tbody><?php foreach ($yearly as $row): ?><tr><td><strong><?= htmlspecialchars($row['period']); ?></strong></td><td><?= nt_money($row['income']); ?></td><td><?= nt_money($row['expense']); ?></td><td><strong><?= nt_money($row['net']); ?></strong></td></tr><?php endforeach; ?></tbody></table></div></article>
            <article class="acct-card span-6"><h2>Aktivitas Terbaru</h2><div class="acct-list"><?php foreach ($recentIncomes as $row): ?><div class="acct-list-row"><div><b><?= htmlspecialchars($row['title']); ?></b><br><span><?= htmlspecialchars($row['source_name'] ?? 'Uncategorized'); ?> · <?= nt_date($row['received_date']); ?></span></div><span class="acct-pill green"><?= nt_money($row['amount']); ?></span></div><?php endforeach; ?><?php foreach ($recentExpenses as $row): ?><div class="acct-list-row"><div><b><?= htmlspecialchars($row['title']); ?></b><br><span><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?> · <?= nt_date($row['expense_date']); ?></span></div><span class="acct-pill red"><?= nt_money($row['amount']); ?></span></div><?php endforeach; ?></div></article>
        </div>
    </section>
</main>
<footer class="app-footer"><span>&copy; <?= date('Y'); ?> NovaTrack Riksa</span><span class="app-footer-sep">|</span><span>Accounting</span></footer>
<?php require __DIR__ . '/styles.php'; ?>
<script>
(function () {
    var rows = <?= json_encode($chartRows, JSON_NUMERIC_CHECK); ?>;
    var canvas = document.getElementById('acctMainChart');
    if (!canvas || typeof Chart === 'undefined') return;
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: rows.map(function (row) {
                return '<?= $periodMode; ?>' === 'year' ? row.period.slice(5, 7) : row.period.slice(8, 10);
            }),
            datasets: [
                { label: 'Pemasukan', data: rows.map(function (row) { return row.income; }), borderColor: '#27AE60', backgroundColor: 'rgba(39,174,96,.12)', tension: .32, fill: false },
                { label: 'Pengeluaran', data: rows.map(function (row) { return row.expense; }), borderColor: '#EB5757', backgroundColor: 'rgba(235,87,87,.10)', tension: .32, fill: false },
                { label: 'Net', data: rows.map(function (row) { return row.net; }), borderColor: '#3A6EA5', backgroundColor: 'rgba(58,110,165,.12)', tension: .32, fill: true },
                { label: 'Piutang', data: rows.map(function (row) { return row.debt; }), borderColor: '#F2994A', backgroundColor: 'rgba(242,153,74,.10)', tension: .32, fill: false }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { callback: function (value) { return 'Rp ' + Number(value).toLocaleString('id-ID'); } } } }
        }
    });
    var mode = document.getElementById('acctPeriodMode');
    var month = document.getElementById('acctMonthInput');
    var year = document.getElementById('acctYearInput');
    function syncPeriodFields() {
        if (!mode || !month || !year) return;
        var yearly = mode.value === 'year';
        month.style.display = yearly ? 'none' : '';
        year.style.display = yearly ? '' : 'none';
    }
    mode && mode.addEventListener('change', syncPeriodFields);
    syncPeriodFields();
})();
</script>
</body></html>
