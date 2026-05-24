<?php

use Core\Database;
use Core\DashboardCard;
use Core\ModuleMeta;
use Core\ModuleRegistry;

class SalesModuleMeta extends ModuleMeta
{
    public function __construct()
    {
        parent::__construct('sales', 'Sales', 'bi-briefcase', dirname(__DIR__) . '/Sales');
    }

    public function menuItems(): array
    {
        return [
            ['slug' => 'sales-overview', 'label' => 'Sales Overview', 'icon' => 'bi-speedometer2', 'href' => app_url('sales')],
            ['slug' => 'sales-leads', 'label' => 'Leads', 'icon' => 'bi-person-lines-fill', 'href' => app_url('sales/leads')],
            ['slug' => 'sales-opportunities', 'label' => 'Opportunities', 'icon' => 'bi-kanban', 'href' => app_url('sales/opportunities')],
            ['slug' => 'sales-services', 'label' => 'Services Catalog', 'icon' => 'bi-journal-check', 'href' => app_url('sales/services')],
            ['slug' => 'sales-followups', 'label' => 'Follow-ups', 'icon' => 'bi-calendar-check', 'href' => app_url('sales/followups')],
        ];
    }

    public function tables(): array
    {
        return [
            'sales_services' => 'CREATE TABLE IF NOT EXISTS `sales_services` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(180) NOT NULL,
                `category` VARCHAR(120) DEFAULT "",
                `base_price` DECIMAL(15,2) DEFAULT 0,
                `estimated_duration` VARCHAR(80) DEFAULT "",
                `required_documents` TEXT,
                `description` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'sales_leads' => 'CREATE TABLE IF NOT EXISTS `sales_leads` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `company_name` VARCHAR(180) NOT NULL,
                `pic_name` VARCHAR(160) DEFAULT "",
                `phone` VARCHAR(80) DEFAULT "",
                `email` VARCHAR(160) DEFAULT "",
                `source` VARCHAR(120) DEFAULT "",
                `service_id` INT NULL,
                `need_category` VARCHAR(140) DEFAULT "",
                `estimated_value` DECIMAL(15,2) DEFAULT 0,
                `status` VARCHAR(40) DEFAULT "new",
                `assigned_user_id` INT NULL,
                `notes` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_sales_leads_status` (`status`),
                KEY `idx_sales_leads_service` (`service_id`),
                KEY `idx_sales_leads_assigned` (`assigned_user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'sales_opportunities' => 'CREATE TABLE IF NOT EXISTS `sales_opportunities` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `lead_id` INT NULL,
                `service_id` INT NULL,
                `title` VARCHAR(220) NOT NULL,
                `client_name` VARCHAR(180) DEFAULT "",
                `stage` VARCHAR(60) DEFAULT "inquiry",
                `deal_value` DECIMAL(15,2) DEFAULT 0,
                `probability` INT DEFAULT 25,
                `expected_close_date` DATE NULL,
                `next_followup_date` DATE NULL,
                `assigned_user_id` INT NULL,
                `lost_reason` VARCHAR(220) DEFAULT "",
                `notes` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_sales_opp_stage` (`stage`),
                KEY `idx_sales_opp_close` (`expected_close_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'sales_followups' => 'CREATE TABLE IF NOT EXISTS `sales_followups` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `lead_id` INT NULL,
                `opportunity_id` INT NULL,
                `activity_type` VARCHAR(80) DEFAULT "call",
                `activity_date` DATE NOT NULL,
                `result` VARCHAR(220) DEFAULT "",
                `next_action` VARCHAR(220) DEFAULT "",
                `next_followup_date` DATE NULL,
                `assigned_user_id` INT NULL,
                `notes` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_sales_followup_date` (`activity_date`),
                KEY `idx_sales_followup_next` (`next_followup_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        ];
    }

    public function dashboardCards(): array
    {
        return [
            new DashboardCard([
                'id' => 'sales-pipeline',
                'title' => 'Sales Pipeline',
                'icon' => 'bi-briefcase',
                'colspan' => 12,
                'order' => 4,
                'callback' => function () {
                    require_once __DIR__ . '/controller.php';
                    return (new SalesController())->dashboardCard();
                },
            ]),
        ];
    }

    public function boot(ModuleRegistry $registry): void
    {
        try {
            $db = Database::connection();
            foreach ($this->tables() as $schema) {
                $db->exec($schema);
            }
            $this->addColumnIfMissing($db, 'sales_leads', 'service_id', 'INT NULL AFTER source');
            $this->seedServices($db);
        } catch (Throwable $e) {
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

    private function seedServices(PDO $db): void
    {
        $samples = [
            ['Pendirian PT / CV', 'Corporate Setup', 7500000, '7-14 hari'],
            ['OSS / NIB / Perizinan Usaha', 'Licensing', 5000000, '5-21 hari'],
            ['Legal Audit', 'Audit & Compliance', 15000000, '14-30 hari'],
            ['Contract Drafting & Review', 'Legal Drafting', 3500000, '3-10 hari'],
            ['Compliance Advisory', 'Audit & Compliance', 10000000, '14-30 hari'],
        ];
        $exists = $db->prepare('SELECT COUNT(*) FROM sales_services WHERE name = :name');
        $insert = $db->prepare('INSERT INTO sales_services (name, category, base_price, estimated_duration, required_documents, description, created_at) VALUES (:name, :category, :base_price, :estimated_duration, "", "", NOW())');
        foreach ($samples as $row) {
            $exists->execute(['name' => $row[0]]);
            if ((int)$exists->fetchColumn() === 0) {
                $insert->execute(['name' => $row[0], 'category' => $row[1], 'base_price' => $row[2], 'estimated_duration' => $row[3]]);
            }
        }
    }
}

return new SalesModuleMeta();
