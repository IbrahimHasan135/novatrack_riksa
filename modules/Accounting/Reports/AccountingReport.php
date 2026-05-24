<?php

use Core\Database;
use Core\Report\ReportDefinition;

class AccountingReport
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: Database::connection();
    }

    public function build(string $mode, ?string $month, ?int $year): ReportDefinition
    {
        $mode = $mode === 'year' ? 'year' : 'month';
        $month = preg_match('/^\d{4}-\d{2}$/', (string)$month) ? $month : date('Y-m');
        $year = $year ?: (int)date('Y');

        if ($mode === 'year') {
            $start = $year . '-01-01';
            $end = $year . '-12-31';
            $periodLabel = 'Tahun ' . $year;
            $chartRows = $this->yearChart($year);
            $detailSections = [$this->yearRecapSection($year)];
        } else {
            $start = $month . '-01';
            $end = date('Y-m-t', strtotime($start));
            $periodLabel = date('F Y', strtotime($start));
            $chartRows = $this->monthChart($month);
            $detailSections = [
                $this->incomeDetailSection($start, $end),
                $this->expenseDetailSection($start, $end),
                $this->receivableDetailSection($start, $end),
            ];
        }

        $metrics = [
            ['label' => 'Pemasukan', 'value' => $this->sum('accounting_incomes', 'amount', 'received_date BETWEEN :start AND :end', compact('start', 'end')), 'type' => 'money'],
            ['label' => 'Pengeluaran', 'value' => $this->sum('accounting_expenses', 'amount', 'expense_date BETWEEN :start AND :end', compact('start', 'end')), 'type' => 'money'],
            ['label' => 'Net', 'value' => 0, 'type' => 'money'],
            ['label' => 'Piutang', 'value' => $this->sum('accounting_receivables', 'GREATEST(amount - paid_amount, 0)', 'issued_date BETWEEN :start AND :end', compact('start', 'end')), 'type' => 'money'],
        ];
        $metrics[2]['value'] = $metrics[0]['value'] - $metrics[1]['value'];

        $topIncome = $this->topIncomeSection($start, $end);
        $topExpense = $this->topExpenseSection($start, $end);
        $sections = array_merge([$topIncome, $topExpense], $detailSections);

        return new ReportDefinition([
            'title' => 'Accounting Report',
            'subtitle' => 'NovaTrack Riksa',
            'periodLabel' => $periodLabel,
            'mode' => $mode,
            'metrics' => $metrics,
            'chartRows' => $chartRows,
            'sections' => $sections,
            'sheets' => array_merge([
                $this->summarySheet($metrics, $periodLabel),
                $this->chartSheet($chartRows),
                $topIncome,
                $topExpense,
            ], $detailSections),
        ]);
    }

    private function summarySheet(array $metrics, string $periodLabel): array
    {
        $rows = [];
        foreach ($metrics as $metric) {
            $rows[] = ['metric' => $metric['label'], 'value' => $metric['value'], 'period' => $periodLabel];
        }
        return [
            'title' => 'Summary',
            'columns' => [
                ['key' => 'period', 'label' => 'Periode'],
                ['key' => 'metric', 'label' => 'Metric'],
                ['key' => 'value', 'label' => 'Value', 'type' => 'money'],
            ],
            'rows' => $rows,
        ];
    }

    private function topIncomeSection(string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(s.name, "Uncategorized") AS source_name, SUM(i.amount) AS total, COUNT(i.id) AS transactions
             FROM accounting_incomes i
             LEFT JOIN accounting_income_sources s ON s.id = i.source_id
             WHERE i.received_date BETWEEN :start AND :end
             GROUP BY COALESCE(s.name, "Uncategorized")
             ORDER BY total DESC'
        );
        $stmt->execute(compact('start', 'end'));
        return [
            'title' => 'Sumber Pemasukan Terbesar',
            'columns' => [
                ['key' => 'source_name', 'label' => 'Sumber'],
                ['key' => 'transactions', 'label' => 'Transaksi', 'type' => 'number'],
                ['key' => 'total', 'label' => 'Total', 'type' => 'money'],
            ],
            'rows' => $stmt->fetchAll(),
        ];
    }

    private function topExpenseSection(string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(c.name, "Uncategorized") AS category_name, SUM(e.amount) AS total, COUNT(e.id) AS transactions
             FROM accounting_expenses e
             LEFT JOIN accounting_expense_categories c ON c.id = e.category_id
             WHERE e.expense_date BETWEEN :start AND :end
             GROUP BY COALESCE(c.name, "Uncategorized")
             ORDER BY total DESC'
        );
        $stmt->execute(compact('start', 'end'));
        return [
            'title' => 'Sumber Pengeluaran Terbesar',
            'columns' => [
                ['key' => 'category_name', 'label' => 'Sumber'],
                ['key' => 'transactions', 'label' => 'Transaksi', 'type' => 'number'],
                ['key' => 'total', 'label' => 'Total', 'type' => 'money'],
            ],
            'rows' => $stmt->fetchAll(),
        ];
    }

    private function incomeDetailSection(string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            'SELECT i.received_date AS date, i.title, COALESCE(s.name, "Uncategorized") AS source, i.client_name AS client, i.amount, i.reference_no AS ref
             FROM accounting_incomes i
             LEFT JOIN accounting_income_sources s ON s.id = i.source_id
             WHERE i.received_date BETWEEN :start AND :end
             ORDER BY i.received_date ASC, i.id ASC'
        );
        $stmt->execute(compact('start', 'end'));
        return [
            'title' => 'Detail Pemasukan',
            'columns' => [
                ['key' => 'date', 'label' => 'Tanggal', 'type' => 'date'],
                ['key' => 'title', 'label' => 'Judul'],
                ['key' => 'source', 'label' => 'Sumber'],
                ['key' => 'client', 'label' => 'Client'],
                ['key' => 'amount', 'label' => 'Nominal', 'type' => 'money'],
                ['key' => 'ref', 'label' => 'Ref'],
            ],
            'rows' => $stmt->fetchAll(),
        ];
    }

    private function expenseDetailSection(string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.expense_date AS date, e.title, COALESCE(c.name, "Uncategorized") AS source, e.vendor_name AS client, e.amount, e.reference_no AS ref
             FROM accounting_expenses e
             LEFT JOIN accounting_expense_categories c ON c.id = e.category_id
             WHERE e.expense_date BETWEEN :start AND :end
             ORDER BY e.expense_date ASC, e.id ASC'
        );
        $stmt->execute(compact('start', 'end'));
        return [
            'title' => 'Detail Pengeluaran',
            'columns' => [
                ['key' => 'date', 'label' => 'Tanggal', 'type' => 'date'],
                ['key' => 'title', 'label' => 'Judul'],
                ['key' => 'source', 'label' => 'Sumber'],
                ['key' => 'client', 'label' => 'Client/Vendor'],
                ['key' => 'amount', 'label' => 'Nominal', 'type' => 'money'],
                ['key' => 'ref', 'label' => 'Ref'],
            ],
            'rows' => $stmt->fetchAll(),
        ];
    }

    private function receivableDetailSection(string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            'SELECT issued_date AS date, title, debtor_name, amount, paid_amount, GREATEST(amount - paid_amount, 0) AS outstanding, due_date, status, reference_no
             FROM accounting_receivables
             WHERE issued_date BETWEEN :start AND :end
             ORDER BY issued_date ASC, id ASC'
        );
        $stmt->execute(compact('start', 'end'));
        return [
            'title' => 'Detail Piutang',
            'columns' => [
                ['key' => 'date', 'label' => 'Tanggal', 'type' => 'date'],
                ['key' => 'title', 'label' => 'Judul'],
                ['key' => 'debtor_name', 'label' => 'Debtor'],
                ['key' => 'amount', 'label' => 'Nominal', 'type' => 'money'],
                ['key' => 'paid_amount', 'label' => 'Terbayar', 'type' => 'money'],
                ['key' => 'outstanding', 'label' => 'Outstanding', 'type' => 'money'],
                ['key' => 'due_date', 'label' => 'Deadline', 'type' => 'date'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'reference_no', 'label' => 'Ref'],
            ],
            'rows' => $stmt->fetchAll(),
        ];
    }

    private function yearRecapSection(int $year): array
    {
        return [
            'title' => 'Detail Transaksi Tahunan',
            'columns' => [
                ['key' => 'period', 'label' => 'Periode'],
                ['key' => 'income', 'label' => 'Pemasukan', 'type' => 'money'],
                ['key' => 'expense', 'label' => 'Pengeluaran', 'type' => 'money'],
                ['key' => 'net', 'label' => 'Net', 'type' => 'money'],
            ],
            'rows' => $this->yearChart($year),
        ];
    }

    private function chartSheet(array $rows): array
    {
        return [
            'title' => 'Chart Data',
            'columns' => [
                ['key' => 'period', 'label' => 'Periode'],
                ['key' => 'income', 'label' => 'Pemasukan', 'type' => 'money'],
                ['key' => 'expense', 'label' => 'Pengeluaran', 'type' => 'money'],
                ['key' => 'net', 'label' => 'Net', 'type' => 'money'],
                ['key' => 'debt', 'label' => 'Piutang', 'type' => 'money'],
            ],
            'rows' => $rows,
        ];
    }

    private function monthChart(string $month): array
    {
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $days = (int)date('t', strtotime($start));
        $rows = [];
        for ($day = 1; $day <= $days; $day++) {
            $date = sprintf('%s-%02d', $month, $day);
            $rows[$date] = ['period' => $date, 'income' => 0, 'expense' => 0, 'net' => 0, 'debt' => 0];
        }
        $this->fillChart($rows, 'accounting_incomes', 'received_date', 'amount', 'income', $start, $end);
        $this->fillChart($rows, 'accounting_expenses', 'expense_date', 'amount', 'expense', $start, $end);
        $this->fillChart($rows, 'accounting_receivables', 'issued_date', 'GREATEST(amount - paid_amount, 0)', 'debt', $start, $end);
        foreach ($rows as &$row) {
            $row['net'] = $row['income'] - $row['expense'];
        }
        return array_values($rows);
    }

    private function yearChart(int $year): array
    {
        $rows = [];
        for ($month = 1; $month <= 12; $month++) {
            $period = sprintf('%d-%02d', $year, $month);
            $rows[$period] = ['period' => $period, 'income' => 0, 'expense' => 0, 'net' => 0, 'debt' => 0];
        }
        $this->fillYearChart($rows, 'accounting_incomes', 'received_date', 'amount', 'income', $year);
        $this->fillYearChart($rows, 'accounting_expenses', 'expense_date', 'amount', 'expense', $year);
        $this->fillYearChart($rows, 'accounting_receivables', 'issued_date', 'GREATEST(amount - paid_amount, 0)', 'debt', $year);
        foreach ($rows as &$row) {
            $row['net'] = $row['income'] - $row['expense'];
        }
        return array_values($rows);
    }

    private function fillChart(array &$rows, string $table, string $dateColumn, string $amountColumn, string $target, string $start, string $end): void
    {
        $stmt = $this->db->prepare("SELECT `$dateColumn` AS period, SUM($amountColumn) AS total FROM `$table` WHERE `$dateColumn` BETWEEN :start AND :end GROUP BY `$dateColumn`");
        $stmt->execute(compact('start', 'end'));
        foreach ($stmt->fetchAll() as $row) {
            $rows[$row['period']][$target] = (float)$row['total'];
        }
    }

    private function fillYearChart(array &$rows, string $table, string $dateColumn, string $amountColumn, string $target, int $year): void
    {
        $stmt = $this->db->prepare("SELECT DATE_FORMAT(`$dateColumn`, '%Y-%m') AS period, SUM($amountColumn) AS total FROM `$table` WHERE YEAR(`$dateColumn`) = :year GROUP BY DATE_FORMAT(`$dateColumn`, '%Y-%m')");
        $stmt->execute(['year' => $year]);
        foreach ($stmt->fetchAll() as $row) {
            $rows[$row['period']][$target] = (float)$row['total'];
        }
    }

    private function sum(string $table, string $column, string $where, array $params): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM($column), 0) FROM `$table` WHERE $where");
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }
}
