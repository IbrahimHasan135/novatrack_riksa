<?php

namespace Core;

use PDO;
use Throwable;

class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $cfg       = require __DIR__ . '/../config/database.php';
            $serverDsn = sprintf(
                'mysql:host=%s;port=%d;charset=%s',
                $cfg['host'], (int)$cfg['port'], $cfg['charset']
            );
            $server = new PDO($serverDsn, $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $database = str_replace('`', '``', $cfg['database']);
            $server->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$cfg['charset']} COLLATE {$cfg['charset']}_unicode_ci");

            $dsn       = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'], (int)$cfg['port'], $cfg['database'], $cfg['charset']
            );
            self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$instance;
    }

    /**
     * Table schemas yang di-register oleh Modules, di key namai nama table.
     * Value bisa berupa:
     *   - string SQL: CREATE TABLE IF NOT EXISTS `nama` (...)
     *   - array key=>value untuk schema per-field (dikonversi jadi SQL)
     */
    public static function autoMigrate(array $rawSchemas = []): void
    {
        $pdo    = self::connection();
        $tables = array_merge((ModuleRegistry::getInstance())->allTables(), $rawSchemas);

        foreach ($tables as $tableName => $schema) {
            // Jika value adalah string SQL langsung, jalankan itu
            if (is_string($schema)) {
                $exists = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($tableName))->fetchColumn();
                if ($exists) continue;
                $pdo->exec($schema);
                continue;
            }
            // Jika array key=>value (field => definition), generate SQL
            if (is_array($schema) && !empty($schema)) {
                $exists = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($tableName))->fetchColumn();
                if ($exists) continue;
                $cols  = [];
                foreach ($schema as $col => $def) {
                    $cols[] = "`$col` $def";
                }
                $sql = 'CREATE TABLE `' . $tableName . '` (' . implode(', ', $cols) . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
                $pdo->exec($sql);
                continue;
            }
        }

        // Always ensure users table + super admin seeder
        self::ensureUsersTable($pdo);
    }

    private static function ensureUsersTable(PDO $pdo): void
    {
        try {
            $exists = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
            if (!$exists) {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `users` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `username` VARCHAR(100) NOT NULL UNIQUE,
                        `password` VARCHAR(255) NOT NULL,
                        `full_name` VARCHAR(200) DEFAULT '',
                        `role` VARCHAR(50) DEFAULT 'user',
                        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
            }

            $legacySuperAdminExists = $pdo->query("SELECT COUNT(*) FROM `users` WHERE `username`='superadmin'")->fetchColumn();
            $superAdminExists = $pdo->query("SELECT COUNT(*) FROM `users` WHERE `username`='novalynk_sadmin'")->fetchColumn();
            if ($superAdminExists == 0 && $legacySuperAdminExists > 0) {
                $stmt = $pdo->prepare('UPDATE `users` SET `username` = :u, `password` = :p, `full_name` = :f, `role` = :r WHERE `username` = :old');
                $stmt->execute([
                    'old' => 'superadmin',
                    'u' => 'novalynk_sadmin',
                    'p' => password_hash('N0v4.lynk', PASSWORD_BCRYPT),
                    'f' => 'NovaLynk Super Admin',
                    'r' => 'super_admin',
                ]);
                return;
            }

            if ($superAdminExists == 0) {
                $stmt = $pdo->prepare(
                    'INSERT INTO `users` (`username`,`password`,`full_name`,`role`) VALUES (:u,:p,:f,:r)'
                );
                $stmt->execute([
                    'u' => 'novalynk_sadmin',
                    'p' => password_hash('N0v4.lynk', PASSWORD_BCRYPT),
                    'f' => 'NovaLynk Super Admin',
                    'r' => 'super_admin',
                ]);
            }
        } catch (Throwable $e) {}
    }
}
