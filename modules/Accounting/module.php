<?php

use Core\Database;
use Core\DashboardCard;
use Core\ModuleMeta;
use Core\ModuleRegistry;

class AccountingModuleMeta extends ModuleMeta
{
    public function __construct()
    {
        parent::__construct('accounting', 'Accounting', 'bi-calculator', dirname(__DIR__) . '/Accounting');
    }

    public function menuItems(): array
    {
        return [
            ['slug' => 'accounting-overview', 'label' => 'Accounting', 'icon' => 'bi-speedometer2', 'href' => app_url('accounting')],
            ['slug' => 'accounting-income', 'label' => 'Pemasukan', 'icon' => 'bi-arrow-down-circle', 'href' => app_url('accounting/income')],
            ['slug' => 'accounting-expense', 'label' => 'Pengeluaran', 'icon' => 'bi-arrow-up-circle', 'href' => app_url('accounting/expenses')],
            ['slug' => 'accounting-receivable', 'label' => 'Utang', 'icon' => 'bi-receipt', 'href' => app_url('accounting/receivables')],
        ];
    }

    public function tables(): array
    {
        return [
            'accounting_income_sources' => 'CREATE TABLE IF NOT EXISTS `accounting_income_sources` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(160) NOT NULL,
                `description` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_income_source_name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'accounting_incomes' => 'CREATE TABLE IF NOT EXISTS `accounting_incomes` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `source_id` INT NULL,
                `client_name` VARCHAR(180) DEFAULT "",
                `title` VARCHAR(220) NOT NULL,
                `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
                `received_date` DATE NOT NULL,
                `payment_method` VARCHAR(80) DEFAULT "",
                `reference_no` VARCHAR(120) DEFAULT "",
                `record_state` VARCHAR(30) DEFAULT "published",
                `notes` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_income_source` (`source_id`),
                KEY `idx_income_date` (`received_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'accounting_expense_categories' => 'CREATE TABLE IF NOT EXISTS `accounting_expense_categories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(160) NOT NULL,
                `description` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_expense_category_name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'accounting_expenses' => 'CREATE TABLE IF NOT EXISTS `accounting_expenses` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `category_id` INT NULL,
                `vendor_name` VARCHAR(180) DEFAULT "",
                `title` VARCHAR(220) NOT NULL,
                `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
                `expense_date` DATE NOT NULL,
                `payment_method` VARCHAR(80) DEFAULT "",
                `reference_no` VARCHAR(120) DEFAULT "",
                `record_state` VARCHAR(30) DEFAULT "published",
                `notes` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_expense_category` (`category_id`),
                KEY `idx_expense_date` (`expense_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'accounting_receivables' => 'CREATE TABLE IF NOT EXISTS `accounting_receivables` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `debtor_name` VARCHAR(180) NOT NULL,
                `title` VARCHAR(220) NOT NULL,
                `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
                `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
                `issued_date` DATE NOT NULL,
                `due_date` DATE NULL,
                `status` VARCHAR(40) DEFAULT "open",
                `record_state` VARCHAR(30) DEFAULT "published",
                `reference_no` VARCHAR(120) DEFAULT "",
                `notes` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_receivable_status` (`status`),
                KEY `idx_receivable_due` (`due_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        ];
    }

    public function dashboardCards(): array
    {
        return [
            new DashboardCard([
                'id' => 'accounting-recap',
                'title' => 'Accounting Recap',
                'icon' => 'bi-calculator',
                'colspan' => 12,
                'order' => 2,
                'callback' => function () {
                    require_once __DIR__ . '/controller.php';
                    $controller = new AccountingController();
                    return $controller->dashboardCard();
                },
            ]),
            new DashboardCard([
                'id' => 'accounting-month-chart',
                'title' => 'Recap Bulan Ini',
                'icon' => 'bi-graph-up',
                'colspan' => 12,
                'order' => 3,
                'callback' => function () {
                    require_once __DIR__ . '/controller.php';
                    $controller = new AccountingController();
                    return $controller->dashboardChartCard();
                },
            ]),
        ];
    }

    public function listeners(): array
    {
        require_once __DIR__ . '/services/SalesWonListener.php';
        return [
            'sales.opportunity.won' => [new AccountingSalesWonListener()],
        ];
    }

    public function boot(ModuleRegistry $registry): void
    {
        try {
            $db = Database::connection();
            $this->seedDefaults($db);
        } catch (Throwable $e) {
        }
    }

    private function seedDefaults(PDO $db): void
    {
        foreach ($this->tables() as $schema) {
            $db->exec($schema);
        }
        $this->addColumnIfMissing($db, 'accounting_incomes', 'record_state', 'VARCHAR(30) DEFAULT "published" AFTER reference_no');
        $this->addColumnIfMissing($db, 'accounting_expenses', 'record_state', 'VARCHAR(30) DEFAULT "published" AFTER reference_no');
        $this->addColumnIfMissing($db, 'accounting_receivables', 'record_state', 'VARCHAR(30) DEFAULT "published" AFTER status');
        foreach (['Legal Retainer', 'Corporate Litigation', 'Contract Review', 'Compliance Advisory', 'Notary & Documentation'] as $name) {
            $stmt = $db->prepare('INSERT IGNORE INTO accounting_income_sources (name, description) VALUES (:name, "")');
            $stmt->execute(['name' => $name]);
        }
        foreach (['Operational', 'Professional Fee', 'Court & Filing', 'Marketing', 'Office Supplies'] as $name) {
            $stmt = $db->prepare('INSERT IGNORE INTO accounting_expense_categories (name, description) VALUES (:name, "")');
            $stmt->execute(['name' => $name]);
        }
    }

    private function addColumnIfMissing(PDO $db, string $table, string $column, string $definition): void
    {
        $stmt = $db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
        $stmt->execute(['table' => $table, 'column' => $column]);
        if ((int)$stmt->fetchColumn() === 0) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }
}

return new AccountingModuleMeta();
