<?php
// modules/Cases/service.php
// Global scope, dipanggil oleh CasesController

use Core\Database;

class CasesService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** @return array<int, array<string,mixed>> */
    public function all(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM `cases` ORDER BY `created_at` DESC LIMIT :l OFFSET :o'
        );
        $stmt->bindValue(':l', $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public function byId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM `cases` WHERE `id` = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** @return array<string,int> */
    public function statusCounts(): array
    {
        return [
            'verification' => (int)$this->db->query("SELECT COUNT(*) FROM `cases` WHERE `status`='verification'")->fetchColumn(),
            'in_progress'  => (int)$this->db->query("SELECT COUNT(*) FROM `cases` WHERE `status`='in_progress'")->fetchColumn(),
            'done'         => (int)$this->db->query("SELECT COUNT(*) FROM `cases` WHERE `status`='done'")->fetchColumn(),
            'closed'       => (int)$this->db->query("SELECT COUNT(*) FROM `cases` WHERE `status`='closed'")->fetchColumn(),
        ];
    }

    public function total(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM `cases`')->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO `cases` (`title`,`description`,`priority`,`status`,`created_at`) VALUES (:t,:d,:p,:s,NOW())'
        );
        return $stmt->execute([
            't' => $data['title'],
            'd' => $data['description'] ?? '',
            'p' => $data['priority']   ?? 'normal',
            's' => $data['status']     ?? 'verification',
        ]);
    }
}
