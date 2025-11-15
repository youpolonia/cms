<?php

class UserModel {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(array $userData): int {
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password, name, role, created_at) 
            VALUES (:email, :password, :name, :role, NOW())
        ");
        
        $stmt->execute([
            ':email' => $userData['email'],
            ':password' => $hashedPassword,
            ':name' => $userData['name'] ?? '',
            ':role' => $userData['role'] ?? 'user'
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $userData): bool {
        $updates = [];
        $params = [':id' => $id];
        
        if (isset($userData['email'])) {
            $updates[] = 'email = :email';
            $params[':email'] = $userData['email'];
        }
        
        if (isset($userData['name'])) {
            $updates[] = 'name = :name';
            $params[':name'] = $userData['name'];
        }
        
        if (isset($userData['role'])) {
            $updates[] = 'role = :role';
            $params[':role'] = $userData['role'];
        }
        
        if (isset($userData['password'])) {
            $updates[] = 'password = :password';
            $params[':password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getAllUsers(): array {
        $stmt = $this->db->query("SELECT id, email, name, role, created_at FROM users");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getUsersByRole(string $role): array {
        $stmt = $this->db->prepare("
            SELECT id, email, name, role, created_at 
            FROM users 
            WHERE role = :role
        ");
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function verifyPassword(string $email, string $password): bool {
        $user = $this->findByEmail($email);
        if (!$user || !isset($user['password'])) {
            return false;
        }
        return password_verify($password, $user['password']);
    }
}
