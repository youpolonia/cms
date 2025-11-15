<?php
declare(strict_types=1);

class AuthController {
    private AuthService $authService;
    private User $userModel;

    public function __construct(PDO $db) {
        $this->authService = new AuthService(
            new UserProvider($db),
            new SessionManager(),
            null,
            new \Includes\Models\LogModel($db)
        );
        $this->userModel = new User($db);
    }

    public function register(array $data): array {
        try {
            $success = $this->userModel->create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null
            ]);

            return [
                'success' => $success,
                'message' => $success ? 'User registered successfully' : 'Registration failed'
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function login(array $data): array {
        $success = $this->authService->login($data['username'], $data['password']);
        
        return [
            'success' => $success,
            'message' => $success ? 'Login successful' : 'Invalid credentials',
            'user' => $success ? $this->authService->getSessionData() : null
        ];
    }

    public function logout(): array {
        $this->authService->logout();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    public function getCurrentUser(): array {
        $user = $this->authService->getCurrentUser();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }

        return [
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'is_admin' => $user->isAdmin()
            ]
        ];
    }

    public function updateProfile(array $data): array {
        $user = $this->authService->getCurrentUser();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }

        try {
            $updateData = ['id' => $user->getId()];
            
            if (isset($data['username'])) {
                $updateData['username'] = $data['username'];
            }
            
            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
            }
            
            if (isset($data['first_name'])) {
                $updateData['first_name'] = $data['first_name'];
            }
            
            if (isset($data['last_name'])) {
                $updateData['last_name'] = $data['last_name'];
            }

            $success = $this->userModel->update($updateData);
            
            return [
                'success' => $success,
                'message' => $success ? 'Profile updated' : 'Update failed'
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function changePassword(array $data): array {
        $user = $this->authService->getCurrentUser();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }

        $success = $this->authService->changePassword(
            $user->getId(),
            $data['current_password'],
            $data['new_password']
        );

        return [
            'success' => $success,
            'message' => $success ? 'Password changed' : 'Password change failed'
        ];
    }

    public function requestPasswordReset(array $data): array {
        $success = $this->authService->initiatePasswordReset($data['email']);
        
        return [
            'success' => $success,
            'message' => $success ? 'Reset instructions sent if email exists' : 'Invalid request'
        ];
    }

    public function resetPassword(array $data): array {
        $success = $this->authService->completePasswordReset(
            $data['token'],
            $data['new_password']
        );

        return [
            'success' => $success,
            'message' => $success ? 'Password reset successful' : 'Invalid or expired token'
        ];
    }
}
