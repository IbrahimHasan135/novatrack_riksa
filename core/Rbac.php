<?php

namespace Core;

use PDO;

class Rbac
{
    private PDO $db;
    private Auth $auth;

    public function __construct(?PDO $db = null, ?Auth $auth = null)
    {
        $this->db = $db ?: Database::connection();
        $this->auth = $auth ?: Auth::getInstance();
    }

    public function migrate(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(140) NOT NULL UNIQUE,
            is_system TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        $this->db->exec('CREATE TABLE IF NOT EXISTS role_module_permissions (
            role_id INT NOT NULL,
            module_slug VARCHAR(120) NOT NULL,
            PRIMARY KEY (role_id, module_slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        $this->db->exec('CREATE TABLE IF NOT EXISTS role_creatable_roles (
            role_id INT NOT NULL,
            creatable_role_id INT NOT NULL,
            PRIMARY KEY (role_id, creatable_role_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        $this->ensureUserRoleColumn();
        $this->addColumnIfMissing('cases', 'assigned_user_ids', 'JSON NULL AFTER reporter_id');

        $this->seedRole('Super Admin', 'super_admin', true);
        $this->seedRole('Admin', 'admin', true);
        $this->seedRole('Legal Consultant', 'legal_consultan', true);
        $this->seedSuperAdminUser();
    }

    public function currentRoleSlug(): string
    {
        $user = $this->auth->user();
        return $user['role'] ?? 'user';
    }

    public function isSuperAdmin(?array $user = null): bool
    {
        $user = $user ?: $this->auth->user();
        return ($user['role'] ?? '') === 'super_admin';
    }

    public function isAdminLike(?array $user = null): bool
    {
        $user = $user ?: $this->auth->user();
        $role = $user['role'] ?? '';
        return $role === 'super_admin' || $role === 'admin' || str_starts_with($role, 'admin_');
    }

    public function canAccessModule(string $moduleSlug, ?array $user = null): bool
    {
        $user = $user ?: $this->auth->user();
        if (!$user) {
            return false;
        }
        if ($this->isSuperAdmin($user) || ($user['role'] ?? '') === 'admin') {
            return true;
        }

        $role = $this->getRoleBySlug($user['role'] ?? '');
        if (!$role) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM role_module_permissions WHERE role_id = :role_id AND module_slug = :module');
        $stmt->execute(['role_id' => $role['id'], 'module' => $moduleSlug]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function canManageRoles(?array $user = null): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function canManageUsers(?array $user = null): bool
    {
        return $this->isAdminLike($user);
    }

    public function canCreateRoleSlug(string $targetSlug, ?array $user = null): bool
    {
        $user = $user ?: $this->auth->user();
        if (!$user) {
            return false;
        }
        if ($this->isSuperAdmin($user)) {
            return true;
        }
        if ($this->isAdminLike($user) && $targetSlug !== 'super_admin') {
            return true;
        }

        $role = $this->getRoleBySlug($user['role'] ?? '');
        $target = $this->getRoleBySlug($targetSlug);
        if (!$role || !$target) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM role_creatable_roles WHERE role_id = :role_id AND creatable_role_id = :target_id');
        $stmt->execute(['role_id' => $role['id'], 'target_id' => $target['id']]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function canViewAllCases(?array $user = null): bool
    {
        return $this->isAdminLike($user);
    }

    public function canViewPersonalNote(array $case, ?array $user = null): bool
    {
        $user = $user ?: $this->auth->user();
        if (!$user) {
            return false;
        }
        if ($this->isSuperAdmin($user)) {
            return true;
        }
        return $this->isCaseAssignedToUser($case, (int)$user['id']);
    }

    public function isCaseAssignedToUser(array $case, int $userId): bool
    {
        $ids = json_decode($case['assigned_user_ids'] ?? '[]', true);
        if (!is_array($ids)) {
            return false;
        }
        return in_array($userId, array_map('intval', $ids), true);
    }

    public function allRoles(): array
    {
        return $this->db->query('SELECT * FROM roles ORDER BY is_system DESC, name ASC')->fetchAll();
    }

    public function allUsers(): array
    {
        return $this->db->query('SELECT id, username, full_name, role, created_at FROM users ORDER BY full_name ASC, username ASC')->fetchAll();
    }

    public function manageableUsers(?array $user = null): array
    {
        $user = $user ?: $this->auth->user();
        if ($this->isSuperAdmin($user)) {
            return $this->allUsers();
        }
        return $this->db->query('SELECT id, username, full_name, role, created_at FROM users WHERE role != "super_admin" ORDER BY full_name ASC, username ASC')->fetchAll();
    }

    public function getUser(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, username, full_name, role, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function getRoleBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $role = $stmt->fetch();
        return $role ?: null;
    }

    public function getRole(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $role = $stmt->fetch();
        return $role ?: null;
    }

    public function modulesForRole(int $roleId): array
    {
        $stmt = $this->db->prepare('SELECT module_slug FROM role_module_permissions WHERE role_id = :role_id');
        $stmt->execute(['role_id' => $roleId]);
        return array_column($stmt->fetchAll(), 'module_slug');
    }

    public function creatableRolesForRole(int $roleId): array
    {
        $stmt = $this->db->prepare('SELECT creatable_role_id FROM role_creatable_roles WHERE role_id = :role_id');
        $stmt->execute(['role_id' => $roleId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'creatable_role_id'));
    }

    public function saveRole(string $name, array $modules, array $creatableRoleIds, ?int $id = null): int
    {
        $slug = $this->slugify($name);
        if ($id) {
            $role = $this->getRole($id);
            if ($role && (int)$role['is_system'] === 1) {
                $slug = $role['slug'];
            }
            $stmt = $this->db->prepare('UPDATE roles SET name = :name, slug = :slug WHERE id = :id');
            $stmt->execute(['name' => $name, 'slug' => $slug, 'id' => $id]);
            $roleId = $id;
        } else {
            $stmt = $this->db->prepare('INSERT INTO roles (name, slug, is_system, created_at) VALUES (:name, :slug, 0, NOW())');
            $stmt->execute(['name' => $name, 'slug' => $slug]);
            $roleId = (int)$this->db->lastInsertId();
        }

        $this->syncRoleModules($roleId, $modules);
        $this->syncCreatableRoles($roleId, $creatableRoleIds);
        return $roleId;
    }

    public function deleteRole(int $id): bool
    {
        $role = $this->getRole($id);
        if (!$role || (int)$role['is_system'] === 1) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $stmt->execute(['role' => $role['slug']]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false;
        }

        $this->db->prepare('DELETE FROM role_module_permissions WHERE role_id = :id')->execute(['id' => $id]);
        $this->db->prepare('DELETE FROM role_creatable_roles WHERE role_id = :id OR creatable_role_id = :id')->execute(['id' => $id]);
        $this->db->prepare('DELETE FROM roles WHERE id = :id')->execute(['id' => $id]);
        return true;
    }

    public function createUser(array $data): bool
    {
        $role = $data['role'] ?? '';
        if (!$this->canCreateRoleSlug($role)) {
            return false;
        }

        $stmt = $this->db->prepare('INSERT INTO users (username, password, full_name, role, created_at) VALUES (:username, :password, :full_name, :role, NOW())');
        return $stmt->execute([
            'username' => trim($data['username'] ?? ''),
            'password' => password_hash($data['password'] ?? '', PASSWORD_BCRYPT),
            'full_name' => trim($data['full_name'] ?? ''),
            'role' => $role,
        ]);
    }

    public function updateUserAccount(int $id, array $data): bool
    {
        if (!$this->canManageUserId($id)) {
            return false;
        }

        $fields = [
            'username' => trim($data['username'] ?? ''),
            'full_name' => trim($data['full_name'] ?? ''),
            'id' => $id,
        ];
        if ($fields['username'] === '' || $fields['full_name'] === '') {
            return false;
        }

        if (trim($data['password'] ?? '') !== '') {
            $stmt = $this->db->prepare('UPDATE users SET username = :username, full_name = :full_name, password = :password WHERE id = :id');
            $fields['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET username = :username, full_name = :full_name WHERE id = :id');
        }

        try {
            return $stmt->execute($fields);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function deleteUserAccount(int $id): bool
    {
        $currentUser = $this->auth->user();
        if (!$this->canManageUserId($id) || (int)($currentUser['id'] ?? 0) === $id) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function canManageUserId(int $id, ?array $user = null): bool
    {
        $user = $user ?: $this->auth->user();
        if (!$this->canManageUsers($user)) {
            return false;
        }

        $target = $this->getUser($id);
        if (!$target) {
            return false;
        }
        if (!$this->isSuperAdmin($user) && ($target['role'] ?? '') === 'super_admin') {
            return false;
        }
        return true;
    }

    public function updateOwnPassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        if (trim($newPassword) === '') {
            return false;
        }

        $stmt = $this->db->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($currentPassword, $hash)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        return $stmt->execute([
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
            'id' => $userId,
        ]);
    }

    private function syncRoleModules(int $roleId, array $modules): void
    {
        $this->db->prepare('DELETE FROM role_module_permissions WHERE role_id = :role_id')->execute(['role_id' => $roleId]);
        $stmt = $this->db->prepare('INSERT INTO role_module_permissions (role_id, module_slug) VALUES (:role_id, :module)');
        foreach (array_unique($modules) as $module) {
            $stmt->execute(['role_id' => $roleId, 'module' => $module]);
        }
    }

    private function syncCreatableRoles(int $roleId, array $roleIds): void
    {
        $this->db->prepare('DELETE FROM role_creatable_roles WHERE role_id = :role_id')->execute(['role_id' => $roleId]);
        $stmt = $this->db->prepare('INSERT INTO role_creatable_roles (role_id, creatable_role_id) VALUES (:role_id, :target)');
        foreach (array_unique(array_map('intval', $roleIds)) as $targetId) {
            if ($targetId > 0 && $targetId !== $roleId) {
                $stmt->execute(['role_id' => $roleId, 'target' => $targetId]);
            }
        }
    }

    private function seedRole(string $name, string $slug, bool $system): int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int)$id;
        }
        $stmt = $this->db->prepare('INSERT INTO roles (name, slug, is_system, created_at) VALUES (:name, :slug, :system, NOW())');
        $stmt->execute(['name' => $name, 'slug' => $slug, 'system' => $system ? 1 : 0]);
        return (int)$this->db->lastInsertId();
    }

    private function seedSuperAdminUser(): void
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
        $stmt->execute(['username' => 'superadmin']);
        if ((int)$stmt->fetchColumn() > 0) {
            return;
        }
        $stmt = $this->db->prepare('INSERT INTO users (username, password, full_name, role, created_at) VALUES (:username, :password, :full_name, :role, NOW())');
        $stmt->execute([
            'username' => 'superadmin',
            'password' => password_hash('super123', PASSWORD_BCRYPT),
            'full_name' => 'Super Admin',
            'role' => 'super_admin',
        ]);
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        if (!$this->tableExists($table)) {
            return;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
        $stmt->execute(['table' => $table, 'column' => $column]);
        if ((int)$stmt->fetchColumn() === 0) {
            $this->db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }

    private function ensureUserRoleColumn(): void
    {
        if ($this->tableExists('users')) {
            $this->db->exec('ALTER TABLE `users` MODIFY COLUMN `role` VARCHAR(140) DEFAULT "user"');
        }
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
        $stmt->execute(['table' => $table]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function slugify(string $value): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $value), '_'));
        return $slug !== '' ? $slug : 'role_' . time();
    }
}
