<?php

namespace Core\Report;

class HtmlReportRenderer
{
    public function render(ReportDefinition $report): string
    {
        $chartSvg = $this->chartSvg($report->chartRows);
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $this->e($report->title); ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin:0;background:#EAF1FB;color:#1C2B3A;font-family:Inter,Arial,sans-serif; }
        .report-page { max-width:1120px;margin:0 auto;padding:28px; }
        .report-hero { background:linear-gradient(135deg,#fff,rgba(221,234,245,.88) 48%,rgba(231,247,242,.92));border:1px solid rgba(58,110,165,.16);border-radius:18px;padding:24px;box-shadow:0 18px 46px rgba(30,72,126,.10);position:relative;overflow:hidden; }
        .report-hero::before { content:'';position:absolute;left:0;right:0;top:0;height:4px;background:linear-gradient(90deg,#3A6EA5,#1BA784,#F2994A); }
        .kicker { color:#1BA784;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px; }
        h1 { margin:0;color:#1C2B3A;font-size:28px; }
        .subtitle { color:#416C92;margin-top:6px;font-size:13px;font-weight:650; }
        .toolbar { display:flex;justify-content:flex-end;margin:16px 0;gap:8px; }
        .toolbar button { border:none;border-radius:11px;background:linear-gradient(135deg,#3A6EA5,#1BA784);color:#fff;padding:10px 14px;font-weight:800;cursor:pointer; }
        .metrics { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:18px; }
        .metric { background:#fff;border:1px solid rgba(58,110,165,.14);border-radius:14px;padding:15px; }
        .metric span { display:block;color:#68839E;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em; }
        .metric strong { display:block;color:#1C2B3A;font-size:20px;margin-top:7px; }
        .card { background:#fff;border:1px solid rgba(58,110,165,.14);border-radius:16px;padding:18px;margin-top:16px;box-shadow:0 12px 30px rgba(30,72,126,.08);page-break-inside:avoid; }
        h2 { margin:0 0 12px;color:#1C2B3A;font-size:17px; }
        .chart { width:100%;height:auto;border:1px solid #EEF4FA;border-radius:14px;background:#F7FAFD; }
        table { width:100%;border-collapse:collapse;font-size:12.5px; }
        th { background:#F4F8FC;color:#416C92;font-size:11px;text-transform:uppercase;letter-spacing:.05em;text-align:left;padding:10px;border-bottom:1px solid #DDE8F4; }
        td { padding:10px;border-bottom:1px solid #EEF4FA;vertical-align:top; }
        .num { text-align:right;font-weight:800; }
        @media print { body { background:#fff; } .report-page { max-width:none;padding:0; } .toolbar { display:none; } .card,.report-hero { box-shadow:none; } }
    </style>
</head>
<body>
<main class="report-page">
    <section class="report-hero">
        <div class="kicker">NovaTrack Riksa Report</div>
        <h1><?= $this->e($report->title); ?></h1>
        <div class="subtitle"><?= $this->e($report->subtitle); ?> · <?= $this->e($report->periodLabel); ?></div>
        <div class="metrics">
            <?php foreach ($report->metrics as $metric): ?>
                <div class="metric"><span><?= $this->e($metric['label']); ?></span><strong><?= $this->format($metric['value'], $metric['type'] ?? 'text'); ?></strong></div>
            <?php endforeach; ?>
        </div>
    </section>
    <div class="toolbar"><button onclick="window.print()">Print / Save as PDF</button></div>
    <section class="card"><h2>Grafik Periode</h2><?= $chartSvg; ?></section>
    <?php foreach ($report->sections as $section): ?>
        <section class="card">
            <h2><?= $this->e($section['title']); ?></h2>
            <?= $this->table($section['columns'], $section['rows']); ?>
        </section>
    <?php endforeach; ?>
</main>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    private function table(array $columns, array $rows): string
    {
        ob_start();
        ?>
        <table>
            <thead><tr><?php foreach ($columns as $column): ?><th><?= $this->e($column['label']); ?></th><?php endforeach; ?></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <?php $type = $column['type'] ?? 'text'; ?>
                        <td class="<?= in_array($type, ['money', 'number'], true) ? 'num' : ''; ?>"><?= $this->format($row[$column['key']] ?? '', $type); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    private function chartSvg(array $rows): string
    {
        $width = 1040;
        $height = 300;
        $pad = 42;
        $keys = ['income' => '#27AE60', 'expense' => '#EB5757', 'net' => '#3A6EA5', 'debt' => '#F2994A'];
        $max = 1;
        foreach ($rows as $row) {
            foreach (array_keys($keys) as $key) {
                $max = max($max, abs((float)($row[$key] ?? 0)));
            }
        }
        $plotW = $width - ($pad * 2);
        $plotH = $height - ($pad * 2);
        $count = max(1, count($rows) - 1);
        $svg = '<svg class="chart" viewBox="0 0 ' . $width . ' ' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<line x1="' . $pad . '" y1="' . ($height - $pad) . '" x2="' . ($width - $pad) . '" y2="' . ($height - $pad) . '" stroke="#DDE8F4"/>';
        $svg .= '<line x1="' . $pad . '" y1="' . $pad . '" x2="' . $pad . '" y2="' . ($height - $pad) . '" stroke="#DDE8F4"/>';
        foreach ($keys as $key => $color) {
            $points = [];
            foreach ($rows as $i => $row) {
                $x = $pad + (($plotW / $count) * $i);
                $value = (float)($row[$key] ?? 0);
                $y = ($height - $pad) - (($value / $max) * $plotH);
                $points[] = round($x, 2) . ',' . round($y, 2);
            }
            $svg .= '<polyline fill="none" stroke="' . $color . '" stroke-width="3" points="' . implode(' ', $points) . '"/>';
        }
        $legendX = $pad;
        foreach (['income' => 'Pemasukan', 'expense' => 'Pengeluaran', 'net' => 'Net', 'debt' => 'Utang'] as $key => $label) {
            $svg .= '<rect x="' . $legendX . '" y="12" width="10" height="10" rx="2" fill="' . $keys[$key] . '"/>';
            $svg .= '<text x="' . ($legendX + 15) . '" y="21" font-size="11" fill="#416C92" font-weight="700">' . $this->e($label) . '</text>';
            $legendX += 118;
        }
        $svg .= '</svg>';
        return $svg;
    }

    private function format(mixed $value, string $type): string
    {
        if ($type === 'money') {
            return 'Rp ' . number_format((float)$value, 0, ',', '.');
        }
        if ($type === 'date') {
            return $value ? date('d M Y', strtotime((string)$value)) : '-';
        }
        if ($type === 'number') {
            return number_format((float)$value, 0, ',', '.');
        }
        return $this->e((string)$value);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
