<?php

use Core\Database;
use Core\DashboardCard;
use Core\ModuleMeta;
use Core\ModuleRegistry;
use Core\Rbac;

class CasesModuleMeta extends ModuleMeta
{
    public function __construct()
    {
        parent::__construct('cases', 'Cases', 'bi-folder2-open', dirname(__DIR__) . '/Cases');
    }

    public function menuItems(): array
    {
        return [
            [
                'slug' => 'cases-types',
                'label' => 'Case Types',
                'icon' => 'bi-folder-fill',
                'href' => app_url('cases'),
            ],
            [
                'slug' => 'cases-create',
                'label' => 'Tambah Case',
                'icon' => 'bi-plus-circle',
                'href' => app_url('cases/create'),
            ],
        ];
    }

    public function tables(): array
    {
        return [
            'case_types' => 'CREATE TABLE IF NOT EXISTS `case_types` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(120) NOT NULL,
                `slug` VARCHAR(140) NOT NULL UNIQUE,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'cases' => 'CREATE TABLE IF NOT EXISTS `cases` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `type_id` INT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT,
                `priority` VARCHAR(50) DEFAULT "normal",
                `status` VARCHAR(50) DEFAULT "verification",
                `deadline` DATE NULL,
                `personal_note` TEXT,
                `information` TEXT,
                `reporter_id` INT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_cases_type` (`type_id`),
                KEY `idx_cases_status` (`status`),
                KEY `idx_cases_deadline` (`deadline`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        ];
    }

    public function dashboardCards(): array
    {
        return [
            new DashboardCard([
                'id' => 'cases-stats',
                'title' => 'Cases by Type',
                'icon' => 'bi-folder2-open',
                'colspan' => 12,
                'order' => 1,
                'callback' => function () {
                    $db = Database::connection();
                    $rbac = new Rbac($db);
                    $user = \Core\Auth::getInstance()->user();
                    $rows = $db->query(
                        'SELECT t.id, t.name, COUNT(c.id) AS total,
                                SUM(c.status = "verification") AS verification_count,
                                SUM(c.status = "in_progress") AS progress_count,
                                SUM(c.status = "done") AS done_count,
                                SUM(c.status = "closed") AS closed_count
                         FROM case_types t
                         LEFT JOIN cases c ON c.type_id = t.id
                         GROUP BY t.id
                         ORDER BY t.name ASC'
                    )->fetchAll();
                    if (!$rbac->canViewAllCases($user)) {
                        $allCases = $db->query('SELECT * FROM cases')->fetchAll();
                        $visible = array_values(array_filter($allCases, fn($case) => $rbac->isCaseAssignedToUser($case, (int)$user['id'])));
                        $counts = [];
                        foreach ($visible as $case) {
                            $typeId = (int)$case['type_id'];
                            $counts[$typeId] ??= ['total' => 0, 'verification_count' => 0, 'progress_count' => 0, 'done_count' => 0, 'closed_count' => 0];
                            $counts[$typeId]['total']++;
                            if (($case['status'] ?? '') === 'verification') $counts[$typeId]['verification_count']++;
                            if (($case['status'] ?? '') === 'in_progress') $counts[$typeId]['progress_count']++;
                            if (($case['status'] ?? '') === 'done') $counts[$typeId]['done_count']++;
                            if (($case['status'] ?? '') === 'closed') $counts[$typeId]['closed_count']++;
                        }
                        $rows = array_values(array_filter(array_map(function ($row) use ($counts) {
                            $stats = $counts[(int)$row['id']] ?? null;
                            return $stats ? array_merge($row, $stats) : null;
                        }, $rows)));
                        $total = count($visible);
                    } else {
                        $total = (int)$db->query('SELECT COUNT(*) FROM cases')->fetchColumn();
                    }

                    ob_start();
                    ?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:16px;">
                        <div class="c-mini" style="background:#E8F0FB;border-radius:14px;padding:16px;border:1px solid #DDE8F4;">
                            <div style="font-weight:800;font-size:28px;color:#3A6EA5;"><?= $total; ?></div>
                            <div style="font-size:12px;color:#4A5E75;font-weight:700;">Total Cases</div>
                        </div>
                        <div class="c-mini" style="background:#E7F7F2;border-radius:14px;padding:16px;border:1px solid #DDE8F4;">
                            <div style="font-weight:800;font-size:28px;color:#1BA784;"><?= count($rows); ?></div>
                            <div style="font-size:12px;color:#4A5E75;font-weight:700;">Case Types</div>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
                        <?php foreach ($rows as $row): ?>
                            <a href="<?= app_url('cases/type/' . (int)$row['id']); ?>" style="display:block;text-decoration:none;background:rgba(255,255,255,.86);border:1px solid rgba(58,110,165,.14);border-radius:16px;padding:16px;box-shadow:0 10px 28px rgba(30,72,126,.08);">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                                    <div style="display:flex;align-items:center;gap:10px;color:#1C2B3A;font-weight:800;">
                                        <i class="bi bi-folder-fill" style="color:#1BA784;font-size:24px;"></i>
                                        <?= htmlspecialchars($row['name']); ?>
                                    </div>
                                    <span style="font-size:12px;color:#416C92;font-weight:700;"><?= (int)$row['total']; ?> case</span>
                                </div>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:12px;font-size:11px;font-weight:700;">
                                    <span style="background:#E8F0FB;color:#3A6EA5;border-radius:20px;padding:3px 8px;">Verify <?= (int)$row['verification_count']; ?></span>
                                    <span style="background:#FFF8E1;color:#E09F3E;border-radius:20px;padding:3px 8px;">Progress <?= (int)$row['progress_count']; ?></span>
                                    <span style="background:#E8F7EE;color:#27AE60;border-radius:20px;padding:3px 8px;">Done <?= (int)$row['done_count']; ?></span>
                                    <span style="background:#F0F2F5;color:#5A7089;border-radius:20px;padding:3px 8px;">Close <?= (int)$row['closed_count']; ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    return ob_get_clean();
                },
            ]),
        ];
    }

    public function listeners(): array
    {
        require_once __DIR__ . '/services/SalesWonListener.php';
        return [
            'sales.opportunity.won' => [new CasesSalesWonListener()],
        ];
    }

    public function boot(ModuleRegistry $registry): void
    {
        try {
            $db = Database::connection();
            $this->ensureSchema($db);
        } catch (Throwable $e) {
        }
    }

    private function ensureSchema(PDO $db): void
    {
        $db->exec('CREATE TABLE IF NOT EXISTS case_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(140) NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        $this->addColumnIfMissing($db, 'cases', 'type_id', 'INT NULL AFTER `id`');
        $this->addColumnIfMissing($db, 'cases', 'deadline', 'DATE NULL AFTER `status`');
        $this->addColumnIfMissing($db, 'cases', 'personal_note', 'TEXT AFTER `deadline`');
        $this->addColumnIfMissing($db, 'cases', 'information', 'TEXT AFTER `personal_note`');

        $db->exec("UPDATE cases SET status = 'verification' WHERE status = 'open'");
        $db->exec("UPDATE cases SET status = 'in_progress' WHERE status = 'process'");
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

return new CasesModuleMeta();
