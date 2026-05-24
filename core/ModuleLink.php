<?php

namespace Core;

use PDO;

class ModuleLink
{
    public static function ensure(PDO $db): void
    {
        $db->exec('CREATE TABLE IF NOT EXISTS module_links (
            id INT AUTO_INCREMENT PRIMARY KEY,
            source_module VARCHAR(80) NOT NULL,
            source_type VARCHAR(80) NOT NULL,
            source_id INT NOT NULL,
            target_module VARCHAR(80) NOT NULL,
            target_type VARCHAR(80) NOT NULL,
            target_id INT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_module_link (source_module, source_type, source_id, target_module, target_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    public static function targetId(PDO $db, string $sourceModule, string $sourceType, int $sourceId, string $targetModule, string $targetType): ?int
    {
        self::ensure($db);
        $stmt = $db->prepare('SELECT target_id FROM module_links WHERE source_module=:source_module AND source_type=:source_type AND source_id=:source_id AND target_module=:target_module AND target_type=:target_type LIMIT 1');
        $stmt->execute([
            'source_module' => $sourceModule,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'target_module' => $targetModule,
            'target_type' => $targetType,
        ]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public static function create(PDO $db, string $sourceModule, string $sourceType, int $sourceId, string $targetModule, string $targetType, int $targetId): void
    {
        self::ensure($db);
        $stmt = $db->prepare('INSERT IGNORE INTO module_links (source_module, source_type, source_id, target_module, target_type, target_id, created_at) VALUES (:source_module, :source_type, :source_id, :target_module, :target_type, :target_id, NOW())');
        $stmt->execute([
            'source_module' => $sourceModule,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'target_module' => $targetModule,
            'target_type' => $targetType,
            'target_id' => $targetId,
        ]);
    }
}
