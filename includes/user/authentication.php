<?php

namespace CMS\User;

class Authentication {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login(string $username, string $password): bool {
        $user = $this->db->query("SELECT * FROM users WHERE username = ?", [$username]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return true;
        }
        
        return false;
    }

    public function logout(): void {
        session_destroy();
    }

    public function register(string $username, string $password, string $email): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->db->execute(
            "INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)",
            [$username, $hash, $email]
        );
    }

    public function resetPassword(string $email, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->execute(
            "UPDATE users SET password_hash = ? WHERE email = ?",
            [$hash, $email]
        );
    }
}
