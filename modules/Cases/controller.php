<?php

use Core\Database;
use Core\Module;

class CasesController
{
    private PDO $db;

    private array $validStatuses = ['verification', 'in_progress', 'done', 'closed'];
    private array $validPriorities = ['normal', 'medium', 'high', 'critical'];

    public function __construct()
    {
        $this->db = Database::connection();
        $this->ensureSchema();
    }

    public function index(): void
    {
        $types = $this->db->query(
            'SELECT t.*, COUNT(c.id) AS case_count,
                    SUM(c.status = "verification") AS verification_count,
                    SUM(c.status = "in_progress") AS progress_count,
                    SUM(c.status = "done") AS done_count,
                    SUM(c.status = "closed") AS closed_count
             FROM case_types t
             LEFT JOIN cases c ON c.type_id = t.id
             GROUP BY t.id
             ORDER BY t.name ASC'
        )->fetchAll();

        Module::renderView('Cases/views/index', compact('types'));
    }

    public function storeType(): void
    {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            header('Location: ' . app_url('cases?type_error=empty'));
            return;
        }

        $this->findOrCreateType($name);
        header('Location: ' . app_url('cases?type_created=1'));
    }

    public function deleteType(int $id): void
    {
        $count = $this->countCasesByType($id);
        if ($count > 0) {
            header('Location: ' . app_url('cases?type_error=not_empty'));
            return;
        }

        $stmt = $this->db->prepare('DELETE FROM case_types WHERE id = :id');
        $stmt->execute(['id' => $id]);
        header('Location: ' . app_url('cases?type_deleted=1'));
    }

    public function type(int $id): void
    {
        $type = $this->getType($id);
        if (!$type) {
            $this->notFound('Type case tidak ditemukan');
            return;
        }

        $stmt = $this->db->prepare(
            'SELECT c.*, t.name AS type_name
             FROM cases c
             JOIN case_types t ON t.id = c.type_id
             WHERE c.type_id = :type_id
             ORDER BY COALESCE(c.deadline, "9999-12-31") ASC, c.created_at DESC'
        );
        $stmt->execute(['type_id' => $id]);
        $cases = $stmt->fetchAll();

        Module::renderView('Cases/views/type', compact('type', 'cases'));
    }

    public function create(): void
    {
        $case = null;
        $types = $this->getTypes();
        $selectedTypeId = (int)($_GET['type_id'] ?? 0);
        $mode = 'create';
        Module::renderView('Cases/views/form', compact('case', 'types', 'selectedTypeId', 'mode'));
    }

    public function store(): void
    {
        $data = $this->readCaseInput();
        if ($data['title'] === '') {
            header('Location: ' . app_url('cases/create?error=empty'));
            return;
        }

        $typeId = $this->resolveTypeId();
        if ($typeId <= 0) {
            header('Location: ' . app_url('cases/create?error=type'));
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO cases
                (type_id, title, description, priority, status, deadline, personal_note, information, created_at)
             VALUES
                (:type_id, :title, :description, :priority, :status, :deadline, :personal_note, :information, NOW())'
        );
        $stmt->execute([
            'type_id' => $typeId,
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => $data['status'],
            'deadline' => $data['deadline'],
            'personal_note' => $data['personal_note'],
            'information' => $data['information'],
        ]);

        header('Location: ' . app_url('cases/type/' . $typeId . '?created=1'));
    }

    public function edit(int $id): void
    {
        $case = $this->getCase($id);
        if (!$case) {
            $this->notFound('Case tidak ditemukan');
            return;
        }

        $types = $this->getTypes();
        $selectedTypeId = (int)$case['type_id'];
        $mode = 'edit';
        Module::renderView('Cases/views/form', compact('case', 'types', 'selectedTypeId', 'mode'));
    }

    public function update(int $id): void
    {
        $case = $this->getCase($id);
        if (!$case) {
            $this->notFound('Case tidak ditemukan');
            return;
        }

        $data = $this->readCaseInput();
        if ($data['title'] === '') {
            header('Location: ' . app_url('cases/edit/' . $id . '?error=empty'));
            return;
        }

        $typeId = $this->resolveTypeId();
        if ($typeId <= 0) {
            header('Location: ' . app_url('cases/edit/' . $id . '?error=type'));
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE cases
             SET type_id = :type_id,
                 title = :title,
                 description = :description,
                 priority = :priority,
                 status = :status,
                 deadline = :deadline,
                 personal_note = :personal_note,
                 information = :information,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'type_id' => $typeId,
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => $data['status'],
            'deadline' => $data['deadline'],
            'personal_note' => $data['personal_note'],
            'information' => $data['information'],
        ]);

        header('Location: ' . app_url('cases/detail/' . $id . '?updated=1'));
    }

    public function delete(int $id): void
    {
        $case = $this->getCase($id);
        if (!$case) {
            $this->notFound('Case tidak ditemukan');
            return;
        }

        $typeId = (int)$case['type_id'];
        $stmt = $this->db->prepare('DELETE FROM cases WHERE id = :id');
        $stmt->execute(['id' => $id]);
        header('Location: ' . app_url('cases/type/' . $typeId . '?deleted=1'));
    }

    public function detail(int $id): void
    {
        $case = $this->getCase($id);
        if (!$case) {
            $this->notFound('Case tidak ditemukan');
            return;
        }

        Module::renderView('Cases/views/detail', compact('case'));
    }

    private function readCaseInput(): array
    {
        $priority = $_POST['priority'] ?? 'normal';
        $status = $_POST['status'] ?? 'verification';

        return [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'priority' => in_array($priority, $this->validPriorities, true) ? $priority : 'normal',
            'status' => in_array($status, $this->validStatuses, true) ? $status : 'verification',
            'deadline' => ($_POST['deadline'] ?? '') !== '' ? $_POST['deadline'] : null,
            'personal_note' => trim($_POST['personal_note'] ?? ''),
            'information' => trim($_POST['information'] ?? ''),
        ];
    }

    private function resolveTypeId(): int
    {
        $newType = trim($_POST['new_type_name'] ?? '');
        if ($newType !== '') {
            return $this->findOrCreateType($newType);
        }

        return (int)($_POST['type_id'] ?? 0);
    }

    private function getTypes(): array
    {
        return $this->db->query('SELECT * FROM case_types ORDER BY name ASC')->fetchAll();
    }

    private function getType(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM case_types WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $type = $stmt->fetch();
        return $type ?: null;
    }

    private function getCase(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, t.name AS type_name
             FROM cases c
             LEFT JOIN case_types t ON t.id = c.type_id
             WHERE c.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $case = $stmt->fetch();
        return $case ?: null;
    }

    private function countCasesByType(int $typeId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM cases WHERE type_id = :type_id');
        $stmt->execute(['type_id' => $typeId]);
        return (int)$stmt->fetchColumn();
    }

    private function findOrCreateType(string $name): int
    {
        $name = trim($name);
        $slug = $this->slugify($name);

        $stmt = $this->db->prepare('SELECT id FROM case_types WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int)$id;
        }

        $stmt = $this->db->prepare('INSERT INTO case_types (name, slug, created_at) VALUES (:name, :slug, NOW())');
        $stmt->execute(['name' => $name, 'slug' => $slug]);
        return (int)$this->db->lastInsertId();
    }

    private function slugify(string $value): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));
        return $slug !== '' ? $slug : 'type-' . time();
    }

    private function ensureSchema(): void
    {
        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS case_types (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                slug VARCHAR(140) NOT NULL UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS cases (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type_id INT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                priority VARCHAR(50) DEFAULT "normal",
                status VARCHAR(50) DEFAULT "verification",
                deadline DATE NULL,
                personal_note TEXT,
                information TEXT,
                reporter_id INT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_cases_type (type_id),
                KEY idx_cases_status (status),
                KEY idx_cases_deadline (deadline)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $this->addColumnIfMissing('cases', 'type_id', 'INT NULL AFTER id');
        $this->addColumnIfMissing('cases', 'deadline', 'DATE NULL AFTER status');
        $this->addColumnIfMissing('cases', 'personal_note', 'TEXT AFTER deadline');
        $this->addColumnIfMissing('cases', 'information', 'TEXT AFTER personal_note');

        $defaultTypeId = $this->findOrCreateType('General');
        $stmt = $this->db->prepare('UPDATE cases SET type_id = :type_id WHERE type_id IS NULL OR type_id = 0');
        $stmt->execute(['type_id' => $defaultTypeId]);

        $this->db->exec("UPDATE cases SET status = 'verification' WHERE status = 'open'");
        $this->db->exec("UPDATE cases SET status = 'in_progress' WHERE status = 'process'");
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column'
        );
        $stmt->execute(['table' => $table, 'column' => $column]);
        if ((int)$stmt->fetchColumn() === 0) {
            $this->db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }

    private function notFound(string $message): void
    {
        http_response_code(404);
        echo '<div style="padding:40px;text-align:center;font-family:Inter,sans-serif;">';
        echo '<h2 style="color:#EB5757;">404 &mdash; ' . htmlspecialchars($message) . '</h2>';
        echo '<a href="' . htmlspecialchars(app_url('cases')) . '" style="color:#3A6EA5;font-weight:600;">&larr; Kembali ke Cases</a>';
        echo '</div>';
    }
}
