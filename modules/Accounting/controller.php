<?php

use Core\Database;
use Core\Module;
use Core\Report\ReportManager;

class AccountingController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->ensureSchema();
    }

    public function overview(): void
    {
        $summary = $this->summary();
        $monthly = $this->monthlyRecap(12);
        $yearly = $this->yearlyRecap();
        $periodMode = ($_GET['view'] ?? 'month') === 'year' ? 'year' : 'month';
        $selectedMonth = preg_match('/^\d{4}-\d{2}$/', $_GET['month'] ?? '') ? $_GET['month'] : date('Y-m');
        $selectedYear = preg_match('/^\d{4}$/', $_GET['year'] ?? '') ? (int)$_GET['year'] : (int)date('Y');
        if ($periodMode === 'year') {
            $chartRows = $this->yearChart($selectedYear);
            $periodStart = $selectedYear . '-01-01';
            $periodEnd = $selectedYear . '-12-31';
            $periodLabel = 'Tahun ' . $selectedYear;
        } else {
            $chartRows = $this->monthChart($selectedMonth);
            $periodStart = $selectedMonth . '-01';
            $periodEnd = date('Y-m-t', strtotime($periodStart));
            $periodLabel = date('F Y', strtotime($periodStart));
        }
        $topSources = $this->topIncomeSources(3, $periodStart, $periodEnd);
        $topExpenses = $this->expenseByCategory(3, $periodStart, $periodEnd);
        $recentIncomes = $this->recentIncomes(6);
        $recentExpenses = $this->recentExpenses(6);
        $receivableStats = $this->receivableStats();
        $availableYears = $this->availableAccountingYears();

        Module::renderView('Accounting/views/overview', compact(
            'summary',
            'monthly',
            'yearly',
            'periodMode',
            'selectedMonth',
            'selectedYear',
            'periodLabel',
            'availableYears',
            'chartRows',
            'topSources',
            'topExpenses',
            'recentIncomes',
            'recentExpenses',
            'receivableStats'
        ));
    }

    public function report(): void
    {
        require_once __DIR__ . '/../../core/Report/ReportDefinition.php';
        require_once __DIR__ . '/../../core/Report/HtmlReportRenderer.php';
        require_once __DIR__ . '/../../core/Report/ExcelExporter.php';
        require_once __DIR__ . '/../../core/Report/ReportManager.php';
        require_once __DIR__ . '/Reports/AccountingReport.php';

        $mode = ($_GET['view'] ?? 'month') === 'year' ? 'year' : 'month';
        $month = $_GET['month'] ?? date('Y-m');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $format = ($_GET['format'] ?? 'pdf') === 'excel' ? 'excel' : 'pdf';
        $report = (new AccountingReport($this->db))->build($mode, $month, $year);
        $filename = 'accounting-report-' . ($mode === 'year' ? $year : $month);
        (new ReportManager())->output($report, $format, $filename);
    }

    public function income(): void
    {
        $sources = $this->incomeSources();
        $rows = $this->db->query(
            'SELECT i.*, s.name AS source_name
             FROM accounting_incomes i
             LEFT JOIN accounting_income_sources s ON s.id = i.source_id
             ORDER BY i.received_date DESC, i.id DESC
             LIMIT 80'
        )->fetchAll();
        $topSources = $this->topIncomeSources(10, date('Y-m-01'), date('Y-m-t'));
        Module::renderView('Accounting/views/income', compact('sources', 'rows', 'topSources'));
    }

    public function editIncome(int $id): void
    {
        $income = $this->getIncome($id);
        if (!$income) {
            $this->notFound('Pemasukan tidak ditemukan');
            return;
        }
        $sources = $this->incomeSources();
        Module::renderView('Accounting/views/income_edit', compact('income', 'sources'));
    }

    public function expenses(): void
    {
        $categories = $this->expenseCategories();
        $rows = $this->db->query(
            'SELECT e.*, c.name AS category_name
             FROM accounting_expenses e
             LEFT JOIN accounting_expense_categories c ON c.id = e.category_id
             ORDER BY e.expense_date DESC, e.id DESC
             LIMIT 80'
        )->fetchAll();
        $categoryStats = $this->expenseByCategory(10, date('Y-m-01'), date('Y-m-t'));
        Module::renderView('Accounting/views/expenses', compact('categories', 'rows', 'categoryStats'));
    }

    public function editExpense(int $id): void
    {
        $expense = $this->getExpense($id);
        if (!$expense) {
            $this->notFound('Pengeluaran tidak ditemukan');
            return;
        }
        $categories = $this->expenseCategories();
        Module::renderView('Accounting/views/expense_edit', compact('expense', 'categories'));
    }

    public function receivables(): void
    {
        $rows = $this->db->query(
            'SELECT *,
                    GREATEST(amount - paid_amount, 0) AS outstanding
             FROM accounting_receivables
             ORDER BY FIELD(status, "overdue", "open", "partial", "paid", "written_off"), COALESCE(due_date, "9999-12-31") ASC, id DESC'
        )->fetchAll();
        $stats = $this->receivableStats();
        Module::renderView('Accounting/views/receivables', compact('rows', 'stats'));
    }

    public function editReceivable(int $id): void
    {
        $receivable = $this->getReceivable($id);
        if (!$receivable) {
            $this->notFound('Utang tidak ditemukan');
            return;
        }
        Module::renderView('Accounting/views/receivable_edit', compact('receivable'));
    }

    public function storeIncome(): void
    {
        $sourceId = $this->resolveIncomeSource();
        $stmt = $this->db->prepare(
            'INSERT INTO accounting_incomes
                (source_id, client_name, title, amount, received_date, payment_method, reference_no, record_state, notes, created_at)
             VALUES
                (:source_id, :client_name, :title, :amount, :received_date, :payment_method, :reference_no, :record_state, :notes, NOW())'
        );
        $stmt->execute([
            'source_id' => $sourceId ?: null,
            'client_name' => trim($_POST['client_name'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'amount' => $this->money($_POST['amount'] ?? 0),
            'received_date' => $_POST['received_date'] ?: date('Y-m-d'),
            'payment_method' => trim($_POST['payment_method'] ?? ''),
            'reference_no' => trim($_POST['reference_no'] ?? ''),
            'record_state' => $this->recordState(),
            'notes' => trim($_POST['notes'] ?? ''),
        ]);
        header('Location: ' . app_url('accounting/income?created=1'));
    }

    public function updateIncome(int $id): void
    {
        $sourceId = $this->resolveIncomeSource();
        $stmt = $this->db->prepare(
            'UPDATE accounting_incomes
             SET source_id = :source_id,
                 client_name = :client_name,
                 title = :title,
                 amount = :amount,
                 received_date = :received_date,
                 payment_method = :payment_method,
                 reference_no = :reference_no,
                 record_state = :record_state,
                 notes = :notes,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($this->incomePayload($sourceId) + ['id' => $id]);
        header('Location: ' . app_url('accounting/income?updated=1'));
    }

    public function deleteIncome(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM accounting_incomes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        header('Location: ' . app_url('accounting/income?deleted=1'));
    }

    public function storeExpense(): void
    {
        $categoryId = $this->resolveExpenseCategory();
        $stmt = $this->db->prepare(
            'INSERT INTO accounting_expenses
                (category_id, vendor_name, title, amount, expense_date, payment_method, reference_no, record_state, notes, created_at)
             VALUES
                (:category_id, :vendor_name, :title, :amount, :expense_date, :payment_method, :reference_no, :record_state, :notes, NOW())'
        );
        $stmt->execute([
            'category_id' => $categoryId ?: null,
            'vendor_name' => trim($_POST['vendor_name'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'amount' => $this->money($_POST['amount'] ?? 0),
            'expense_date' => $_POST['expense_date'] ?: date('Y-m-d'),
            'payment_method' => trim($_POST['payment_method'] ?? ''),
            'reference_no' => trim($_POST['reference_no'] ?? ''),
            'record_state' => $this->recordState(),
            'notes' => trim($_POST['notes'] ?? ''),
        ]);
        header('Location: ' . app_url('accounting/expenses?created=1'));
    }

    public function updateExpense(int $id): void
    {
        $categoryId = $this->resolveExpenseCategory();
        $stmt = $this->db->prepare(
            'UPDATE accounting_expenses
             SET category_id = :category_id,
                 vendor_name = :vendor_name,
                 title = :title,
                 amount = :amount,
                 expense_date = :expense_date,
                 payment_method = :payment_method,
                 reference_no = :reference_no,
                 record_state = :record_state,
                 notes = :notes,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($this->expensePayload($categoryId) + ['id' => $id]);
        header('Location: ' . app_url('accounting/expenses?updated=1'));
    }

    public function deleteExpense(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM accounting_expenses WHERE id = :id');
        $stmt->execute(['id' => $id]);
        header('Location: ' . app_url('accounting/expenses?deleted=1'));
    }

    public function storeReceivable(): void
    {
        $amount = $this->money($_POST['amount'] ?? 0);
        $paid = min($amount, $this->money($_POST['paid_amount'] ?? 0));
        $status = $_POST['status'] ?? 'open';
        if ($paid >= $amount && $amount > 0) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        }
        if ($status === 'open' && !empty($_POST['due_date']) && $_POST['due_date'] < date('Y-m-d')) {
            $status = 'overdue';
        }

        $stmt = $this->db->prepare(
            'INSERT INTO accounting_receivables
                (debtor_name, title, amount, paid_amount, issued_date, due_date, status, record_state, reference_no, notes, created_at)
             VALUES
                (:debtor_name, :title, :amount, :paid_amount, :issued_date, :due_date, :status, :record_state, :reference_no, :notes, NOW())'
        );
        $stmt->execute([
            'debtor_name' => trim($_POST['debtor_name'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'amount' => $amount,
            'paid_amount' => $paid,
            'issued_date' => $_POST['issued_date'] ?: date('Y-m-d'),
            'due_date' => ($_POST['due_date'] ?? '') !== '' ? $_POST['due_date'] : null,
            'status' => in_array($status, ['open', 'partial', 'paid', 'overdue', 'written_off'], true) ? $status : 'open',
            'record_state' => $this->recordState(),
            'reference_no' => trim($_POST['reference_no'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ]);
        header('Location: ' . app_url('accounting/receivables?created=1'));
    }

    public function updateReceivable(int $id): void
    {
        $payload = $this->receivablePayload();
        $stmt = $this->db->prepare(
            'UPDATE accounting_receivables
             SET debtor_name = :debtor_name,
                 title = :title,
                 amount = :amount,
                 paid_amount = :paid_amount,
                 issued_date = :issued_date,
                 due_date = :due_date,
                 status = :status,
                 record_state = :record_state,
                 reference_no = :reference_no,
                 notes = :notes,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($payload + ['id' => $id]);
        header('Location: ' . app_url('accounting/receivables?updated=1'));
    }

    public function deleteReceivable(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM accounting_receivables WHERE id = :id');
        $stmt->execute(['id' => $id]);
        header('Location: ' . app_url('accounting/receivables?deleted=1'));
    }

    public function dashboardCard(): string
    {
        $summary = $this->summary();
        $topSources = $this->topIncomeSources(4, date('Y-m-01'), date('Y-m-t'));
        $monthly = $this->monthlyRecap(6);

        ob_start();
        require __DIR__ . '/views/dashboard_card.php';
        return ob_get_clean();
    }

    public function dashboardChartCard(): string
    {
        $chartRows = $this->monthChart(date('Y-m'));
        ob_start();
        require __DIR__ . '/views/dashboard_chart_card.php';
        return ob_get_clean();
    }

    public function summary(): array
    {
        $monthStart = date('Y-m-01');
        $yearStart = date('Y-01-01');
        $incomeMonth = $this->sum('accounting_incomes', 'amount', 'received_date >= :start AND record_state = "published"', ['start' => $monthStart]);
        $expenseMonth = $this->sum('accounting_expenses', 'amount', 'expense_date >= :start AND record_state = "published"', ['start' => $monthStart]);
        $incomeYear = $this->sum('accounting_incomes', 'amount', 'received_date >= :start AND record_state = "published"', ['start' => $yearStart]);
        $expenseYear = $this->sum('accounting_expenses', 'amount', 'expense_date >= :start AND record_state = "published"', ['start' => $yearStart]);

        return [
            'income_month' => $incomeMonth,
            'expense_month' => $expenseMonth,
            'net_month' => $incomeMonth - $expenseMonth,
            'income_year' => $incomeYear,
            'expense_year' => $expenseYear,
            'net_year' => $incomeYear - $expenseYear,
            'receivable_open' => $this->sum('accounting_receivables', 'GREATEST(amount - paid_amount, 0)', 'status != "paid" AND record_state = "published"', []),
            'draft_count' => $this->draftCount(),
            'verification_count' => $this->recordStateCount('verification'),
        ];
    }

    public function monthlyRecap(int $limit = 12): array
    {
        $stmt = $this->db->prepare(
            'SELECT period,
                    SUM(income) AS income,
                    SUM(expense) AS expense,
                    SUM(income) - SUM(expense) AS net
             FROM (
                SELECT DATE_FORMAT(received_date, "%Y-%m") AS period, SUM(amount) AS income, 0 AS expense
                FROM accounting_incomes WHERE record_state = "published" GROUP BY DATE_FORMAT(received_date, "%Y-%m")
                UNION ALL
                SELECT DATE_FORMAT(expense_date, "%Y-%m") AS period, 0 AS income, SUM(amount) AS expense
                FROM accounting_expenses WHERE record_state = "published" GROUP BY DATE_FORMAT(expense_date, "%Y-%m")
             ) x
             GROUP BY period
             ORDER BY period DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function yearlyRecap(): array
    {
        return $this->db->query(
            'SELECT period,
                    SUM(income) AS income,
                    SUM(expense) AS expense,
                    SUM(income) - SUM(expense) AS net
             FROM (
                SELECT YEAR(received_date) AS period, SUM(amount) AS income, 0 AS expense
                FROM accounting_incomes WHERE record_state = "published" GROUP BY YEAR(received_date)
                UNION ALL
                SELECT YEAR(expense_date) AS period, 0 AS income, SUM(amount) AS expense
                FROM accounting_expenses WHERE record_state = "published" GROUP BY YEAR(expense_date)
             ) x
             GROUP BY period
             ORDER BY period DESC
             LIMIT 6'
        )->fetchAll();
    }

    public function topIncomeSources(int $limit, ?string $from = null, ?string $to = null): array
    {
        $where = 'WHERE i.record_state = "published"';
        $params = [];
        if ($from && $to) {
            $where .= ' AND i.received_date BETWEEN :from AND :to';
            $params = ['from' => $from, 'to' => $to];
        }
        $stmt = $this->db->prepare(
            'SELECT COALESCE(s.name, "Uncategorized") AS source_name,
                    SUM(i.amount) AS total,
                    COUNT(i.id) AS transactions
             FROM accounting_incomes i
             LEFT JOIN accounting_income_sources s ON s.id = i.source_id
             ' . $where . '
             GROUP BY COALESCE(s.name, "Uncategorized")
             ORDER BY total DESC
             LIMIT :limit'
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function expenseByCategory(int $limit, ?string $from = null, ?string $to = null): array
    {
        $where = 'WHERE e.record_state = "published"';
        $params = [];
        if ($from && $to) {
            $where .= ' AND e.expense_date BETWEEN :from AND :to';
            $params = ['from' => $from, 'to' => $to];
        }
        $stmt = $this->db->prepare(
            'SELECT COALESCE(c.name, "Uncategorized") AS category_name,
                    SUM(e.amount) AS total,
                    COUNT(e.id) AS transactions
             FROM accounting_expenses e
             LEFT JOIN accounting_expense_categories c ON c.id = e.category_id
             ' . $where . '
             GROUP BY COALESCE(c.name, "Uncategorized")
             ORDER BY total DESC
             LIMIT :limit'
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function receivableStats(): array
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS total_items,
                    SUM(amount) AS total_amount,
                    SUM(paid_amount) AS paid_amount,
                    SUM(GREATEST(amount - paid_amount, 0)) AS outstanding,
                    SUM(status = "overdue") AS overdue_count
             FROM accounting_receivables
             WHERE record_state = "published"'
        )->fetch();
        $row = $row ?: ['total_items' => 0, 'total_amount' => 0, 'paid_amount' => 0, 'outstanding' => 0, 'overdue_count' => 0];
        $row['draft_count'] = $this->draftCount();
        $row['verification_count'] = $this->recordStateCount('verification');
        return $row;
    }

    private function recentIncomes(int $limit): array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, s.name AS source_name
             FROM accounting_incomes i
             LEFT JOIN accounting_income_sources s ON s.id = i.source_id
             ORDER BY i.received_date DESC, i.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function recentExpenses(int $limit): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, c.name AS category_name
             FROM accounting_expenses e
             LEFT JOIN accounting_expense_categories c ON c.id = e.category_id
             ORDER BY e.expense_date DESC, e.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function incomeSources(): array
    {
        return $this->db->query('SELECT * FROM accounting_income_sources ORDER BY name ASC')->fetchAll();
    }

    private function expenseCategories(): array
    {
        return $this->db->query('SELECT * FROM accounting_expense_categories ORDER BY name ASC')->fetchAll();
    }

    private function getIncome(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM accounting_incomes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function getExpense(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM accounting_expenses WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function getReceivable(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM accounting_receivables WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function incomePayload(?int $sourceId): array
    {
        return [
            'source_id' => $sourceId ?: null,
            'client_name' => trim($_POST['client_name'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'amount' => $this->money($_POST['amount'] ?? 0),
            'received_date' => $_POST['received_date'] ?: date('Y-m-d'),
            'payment_method' => trim($_POST['payment_method'] ?? ''),
            'reference_no' => trim($_POST['reference_no'] ?? ''),
            'record_state' => $this->recordState(),
            'notes' => trim($_POST['notes'] ?? ''),
        ];
    }

    private function expensePayload(?int $categoryId): array
    {
        return [
            'category_id' => $categoryId ?: null,
            'vendor_name' => trim($_POST['vendor_name'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'amount' => $this->money($_POST['amount'] ?? 0),
            'expense_date' => $_POST['expense_date'] ?: date('Y-m-d'),
            'payment_method' => trim($_POST['payment_method'] ?? ''),
            'reference_no' => trim($_POST['reference_no'] ?? ''),
            'record_state' => $this->recordState(),
            'notes' => trim($_POST['notes'] ?? ''),
        ];
    }

    private function receivablePayload(): array
    {
        $amount = $this->money($_POST['amount'] ?? 0);
        $paid = min($amount, $this->money($_POST['paid_amount'] ?? 0));
        $status = $_POST['status'] ?? 'open';
        if ($paid >= $amount && $amount > 0) {
            $status = 'paid';
        } elseif ($paid > 0 && $status === 'open') {
            $status = 'partial';
        }
        if ($status === 'open' && !empty($_POST['due_date']) && $_POST['due_date'] < date('Y-m-d')) {
            $status = 'overdue';
        }

        return [
            'debtor_name' => trim($_POST['debtor_name'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'amount' => $amount,
            'paid_amount' => $paid,
            'issued_date' => $_POST['issued_date'] ?: date('Y-m-d'),
            'due_date' => ($_POST['due_date'] ?? '') !== '' ? $_POST['due_date'] : null,
            'status' => in_array($status, ['open', 'partial', 'paid', 'overdue', 'written_off'], true) ? $status : 'open',
            'record_state' => $this->recordState(),
            'reference_no' => trim($_POST['reference_no'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ];
    }

    private function recordState(): string
    {
        $state = $_POST['record_state'] ?? 'published';
        return in_array($state, ['draft', 'verification', 'published'], true) ? $state : 'published';
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

        $income = $this->db->prepare('SELECT received_date AS period, SUM(amount) AS total FROM accounting_incomes WHERE record_state = "published" AND received_date BETWEEN :start AND :end GROUP BY received_date');
        $income->execute(['start' => $start, 'end' => $end]);
        foreach ($income->fetchAll() as $row) {
            $rows[$row['period']]['income'] = (float)$row['total'];
        }

        $expense = $this->db->prepare('SELECT expense_date AS period, SUM(amount) AS total FROM accounting_expenses WHERE record_state = "published" AND expense_date BETWEEN :start AND :end GROUP BY expense_date');
        $expense->execute(['start' => $start, 'end' => $end]);
        foreach ($expense->fetchAll() as $row) {
            $rows[$row['period']]['expense'] = (float)$row['total'];
        }

        $debt = $this->db->prepare('SELECT issued_date AS period, SUM(GREATEST(amount - paid_amount, 0)) AS total FROM accounting_receivables WHERE record_state = "published" AND issued_date BETWEEN :start AND :end GROUP BY issued_date');
        $debt->execute(['start' => $start, 'end' => $end]);
        foreach ($debt->fetchAll() as $row) {
            $rows[$row['period']]['debt'] = (float)$row['total'];
        }

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

        $income = $this->db->prepare('SELECT DATE_FORMAT(received_date, "%Y-%m") AS period, SUM(amount) AS total FROM accounting_incomes WHERE record_state = "published" AND YEAR(received_date) = :year GROUP BY DATE_FORMAT(received_date, "%Y-%m")');
        $income->execute(['year' => $year]);
        foreach ($income->fetchAll() as $row) {
            $rows[$row['period']]['income'] = (float)$row['total'];
        }

        $expense = $this->db->prepare('SELECT DATE_FORMAT(expense_date, "%Y-%m") AS period, SUM(amount) AS total FROM accounting_expenses WHERE record_state = "published" AND YEAR(expense_date) = :year GROUP BY DATE_FORMAT(expense_date, "%Y-%m")');
        $expense->execute(['year' => $year]);
        foreach ($expense->fetchAll() as $row) {
            $rows[$row['period']]['expense'] = (float)$row['total'];
        }

        $debt = $this->db->prepare('SELECT DATE_FORMAT(issued_date, "%Y-%m") AS period, SUM(GREATEST(amount - paid_amount, 0)) AS total FROM accounting_receivables WHERE record_state = "published" AND YEAR(issued_date) = :year GROUP BY DATE_FORMAT(issued_date, "%Y-%m")');
        $debt->execute(['year' => $year]);
        foreach ($debt->fetchAll() as $row) {
            $rows[$row['period']]['debt'] = (float)$row['total'];
        }

        foreach ($rows as &$row) {
            $row['net'] = $row['income'] - $row['expense'];
        }
        return array_values($rows);
    }

    private function availableAccountingYears(): array
    {
        $years = $this->db->query(
            'SELECT DISTINCT year_value FROM (
                SELECT YEAR(received_date) AS year_value FROM accounting_incomes
                UNION
                SELECT YEAR(expense_date) AS year_value FROM accounting_expenses
                UNION
                SELECT YEAR(issued_date) AS year_value FROM accounting_receivables
            ) y WHERE year_value IS NOT NULL ORDER BY year_value DESC'
        )->fetchAll();
        $out = array_map('intval', array_column($years, 'year_value'));
        $current = (int)date('Y');
        if (!in_array($current, $out, true)) {
            array_unshift($out, $current);
        }
        return $out;
    }

    private function resolveIncomeSource(): ?int
    {
        $newName = trim($_POST['new_source_name'] ?? '');
        if ($newName !== '') {
            return $this->findOrCreate('accounting_income_sources', $newName);
        }
        return (int)($_POST['source_id'] ?? 0) ?: null;
    }

    private function resolveExpenseCategory(): ?int
    {
        $newName = trim($_POST['new_category_name'] ?? '');
        if ($newName !== '') {
            return $this->findOrCreate('accounting_expense_categories', $newName);
        }
        return (int)($_POST['category_id'] ?? 0) ?: null;
    }

    private function findOrCreate(string $table, string $name): int
    {
        $stmt = $this->db->prepare("SELECT id FROM `$table` WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int)$id;
        }
        $stmt = $this->db->prepare("INSERT INTO `$table` (name, created_at) VALUES (:name, NOW())");
        $stmt->execute(['name' => $name]);
        return (int)$this->db->lastInsertId();
    }

    private function sum(string $table, string $column, string $where, array $params): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM($column), 0) FROM `$table` WHERE $where");
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }

    private function draftCount(): int
    {
        return $this->recordStateCount('draft');
    }

    private function recordStateCount(string $state): int
    {
        $total = 0;
        foreach (['accounting_incomes', 'accounting_expenses', 'accounting_receivables'] as $table) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM `$table` WHERE record_state = :state");
            $stmt->execute(['state' => $state]);
            $total += (int)$stmt->fetchColumn();
        }
        return $total;
    }

    private function money(mixed $value): float
    {
        return max(0, (float)str_replace([',', ' '], ['', ''], (string)$value));
    }

    private function ensureSchema(): void
    {
        $meta = new AccountingModuleMeta();
        foreach ($meta->tables() as $schema) {
            $this->db->exec($schema);
        }
        $this->addColumnIfMissing('accounting_incomes', 'record_state', 'VARCHAR(30) DEFAULT "published" AFTER reference_no');
        $this->addColumnIfMissing('accounting_expenses', 'record_state', 'VARCHAR(30) DEFAULT "published" AFTER reference_no');
        $this->addColumnIfMissing('accounting_receivables', 'record_state', 'VARCHAR(30) DEFAULT "published" AFTER status');
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
        $stmt->execute(['table' => $table, 'column' => $column]);
        if ((int)$stmt->fetchColumn() === 0) {
            $this->db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }

    private function notFound(string $message): void
    {
        http_response_code(404);
        echo '<div style="padding:40px;text-align:center;font-family:Inter,sans-serif;">';
        echo '<h2 style="color:#EB5757;">404 - ' . htmlspecialchars($message) . '</h2>';
        echo '<a href="' . htmlspecialchars(app_url('accounting')) . '" style="color:#3A6EA5;font-weight:700;">Kembali ke Accounting</a>';
        echo '</div>';
    }
}
