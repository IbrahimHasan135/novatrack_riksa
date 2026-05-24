<?php require_once __DIR__ . '/_helpers.php'; ?>
<div style="height:310px;position:relative;">
    <canvas id="acctDashMonthChart"></canvas>
</div>
<script>
(function () {
    var rows = <?= json_encode($chartRows, JSON_NUMERIC_CHECK); ?>;
    var canvas = document.getElementById('acctDashMonthChart');
    if (!canvas || typeof Chart === 'undefined') return;
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: rows.map(function (row) { return row.period.slice(-2); }),
            datasets: [
                { type: 'bar', label: 'Pemasukan', data: rows.map(function (row) { return row.income; }), backgroundColor: 'rgba(39,174,96,.62)', borderRadius: 6 },
                { type: 'bar', label: 'Pengeluaran', data: rows.map(function (row) { return row.expense; }), backgroundColor: 'rgba(235,87,87,.55)', borderRadius: 6 },
                { type: 'line', label: 'Net', data: rows.map(function (row) { return row.net; }), borderColor: '#3A6EA5', backgroundColor: 'rgba(58,110,165,.12)', tension: .32 },
                { type: 'line', label: 'Piutang', data: rows.map(function (row) { return row.debt; }), borderColor: '#F2994A', backgroundColor: 'rgba(242,153,74,.12)', tension: .32 }
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
})();
</script>
