<?php

class UserController {
    private UserModel $userModel;

    public function __construct(\PDO $db) {
        $this->userModel = new UserModel($db);
    }

    public function getUser(int $id): array {
        $user = $this->userModel->findById($id);
        if (!$user) {
            return ['error' => 'User not found', 'status' => 404];
        }
        return ['data' => $user, 'status' => 200];
    }

    public function createUser(array $userData): array {
        require_once __DIR__ . '/../../csrf.php';
        csrf_validate_or_403();

        if (empty($userData['email']) || empty($userData['password'])) {
            return ['error' => 'Email and password are required', 'status' => 400];
        }

        if ($this->userModel->findByEmail($userData['email'])) {
            return ['error' => 'Email already exists', 'status' => 409];
        }

        $userId = $this->userModel->create($userData);
        return ['data' => ['id' => $userId], 'status' => 201];
    }

    public function updateUser(int $id, array $userData): array {
        require_once __DIR__ . '/../../csrf.php';
        csrf_validate_or_403();

        if (!$this->userModel->findById($id)) {
            return ['error' => 'User not found', 'status' => 404];
        }

        $success = $this->userModel->update($id, $userData);
        return $success 
            ? ['data' => ['success' => true], 'status' => 200]
            : ['error' => 'Update failed', 'status' => 400];
    }

    public function deleteUser(int $id): array {
        require_once __DIR__ . '/../../csrf.php';
        csrf_validate_or_403();

        if (!$this->userModel->findById($id)) {
            return ['error' => 'User not found', 'status' => 404];
        }

        $success = $this->userModel->delete($id);
        return $success 
            ? ['data' => ['success' => true], 'status' => 200]
            : ['error' => 'Delete failed', 'status' => 400];
    }

    public function login(string $email, string $password): array {
        if (!$this->userModel->verifyPassword($email, $password)) {
            return ['error' => 'Invalid credentials', 'status' => 401];
        }

        $user = $this->userModel->findByEmail($email);
        return ['data' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role']
        ], 'status' => 200];
    }

    public function getAllUsers(): array {
        $users = $this->userModel->getAllUsers();
        return ['data' => $users, 'status' => 200];
    }
}
