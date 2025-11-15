<?php

namespace Includes\Auth;

use Models\User;
use Includes\Database\Connection;
use Exception;

class AuthModule
{
    private Connection $db;
    private Session $session;

    public function __construct(Connection $db, Session $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Authenticate user with credentials
     * @param string $username
     * @param string $password
     * @return array ['success' => bool, 'message' => string, 'user' => User|null]
     */
    public function login(string $username, string $password): array
    {
        try {
            // Get PDO instance from Connection
            $pdo = $this->db->getPdo();
            
            // Find user by credentials
            $user = User::findByCredentials($pdo, $username, $password);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password',
                    'user' => null
                ];
            }

            if (!$user->is_active) {
                return [
                    'success' => false,
                    'message' => 'Account is disabled',
                    'user' => null
                ];
            }

            // Start secure session
            $this->session->start();
            $this->session->set('user_id', $user->id);
            $this->session->regenerateId();

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
            ];

        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Authentication service unavailable',
                'user' => null
            ];
        }
    }

    /**
     * Logout current user
     */
    public function logout(): void
    {
        $this->session->destroy();
    }

    /**
     * Get current authenticated user
     * @return User|null
     */
    public function getAuthenticatedUser(): ?User
    {
        if (!$this->session->isActive()) {
            return null;
        }

        $userId = $this->session->get('user_id');
        if (!$userId) {
            return null;
        }

        try {
            return User::findById($this->db->getPdo(), $userId);
        } catch (Exception $e) {
            error_log('Failed to fetch authenticated user: ' . $e->getMessage());
            return null;
        }
    }
}
