<?php

namespace Core;

use PDO;

class Auth
{
    private static ?Auth $instance = null;
    private PDO $db;

    public static function getInstance(): Auth
    {
        if (self::$instance === null) {
            self::$instance = new Auth();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->db = Database::connection();
    }

    public function check(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM `users` WHERE `id` = :id LIMIT 1');
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function attempt(string $username, string $password): bool
    {
        if ($username === '' || $password === '') {
            return false;
        }
        $stmt = $this->db->prepare('SELECT * FROM `users` WHERE `username` = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = (int)$user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name']  ?? $user['username'];
            $_SESSION['role']      = $user['role']       ?? 'user';
            return true;
        }
        return false;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p   = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
