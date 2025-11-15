<?php

namespace CMS\User;

use CMS\Security\SecurityLog;

class UserManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createUser(array $userData): bool {
        $actingUserId = $_SESSION['user_id'] ?? 0;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        SecurityLog::logEvent(
            'user_create',
            $actingUserId,
            $ip,
            [
                'username' => $userData['username']
            ],
            'Attempting user creation'
        );

        // Check for existing user
        $exists = $this->db->query(
            "SELECT COUNT(*) FROM users WHERE email = ?",
            [$userData['email']]
        )->fetchColumn();
        
        if ($exists) {
            SecurityLog::logEvent(
                'user_create',
                $actingUserId,
                $ip,
                [
                    'username' => $userData['username'],
                    'error' => 'Email already exists'
                ],
                'User creation failed - duplicate email'
            );
            return false;
        }

        $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        unset($userData['password']);

        $success = $this->db->execute(
            "INSERT INTO users (username, password_hash, email, first_name, last_name)
            VALUES (?, ?, ?, ?, ?)",
            [
                $userData['username'],
                $userData['password_hash'],
                $userData['email'],
                $userData['first_name'] ?? '',
                $userData['last_name'] ?? ''
            ]
        );

        SecurityLog::logEvent(
            'user_create',
            $actingUserId,
            $ip,
            [
                'username' => $userData['username'],
                'success' => $success
            ],
            $success ? 'User created successfully' : 'User creation failed'
        );

        return $success;
    }

    public function updateUser(int $userId, array $userData): bool {
        $actingUserId = $_SESSION['user_id'] ?? 0;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        SecurityLog::logEvent(
            'user_update',
            $actingUserId,
            $ip,
            [
                'target_user_id' => $userId,
                'fields_updated' => array_keys($userData)
            ],
            'Attempting user update'
        );

        if (isset($userData['password'])) {
            $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            unset($userData['password']);
        }

        return $this->db->execute(
            "UPDATE users SET 
                username = ?,
                password_hash = ?,
                email = ?,
                first_name = ?,
                last_name = ?
            WHERE id = ?",
            [
                $userData['username'],
                $userData['password_hash'] ?? null,
                $userData['email'],
                $userData['first_name'] ?? '',
                $userData['last_name'] ?? '',
                $userId
            ]
        );
    }

    public function deleteUser(int $userId): bool {
        return $this->db->execute("DELETE FROM users WHERE id = ?", [$userId]);
    }

    public function getUser(int $userId): ?array {
        return $this->db->query("SELECT * FROM users WHERE id = ?", [$userId]);
    }

    public function listUsers(): array {
        return $this->db->queryAll("SELECT * FROM users");
    }
}
