<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/session_boot.php';
namespace Core;

class AuthService {
    private static $instance = null;
    private $sessionStarted = false;
    private $userRoles = [];
    private $currentUser = null;

    private function __construct() {
        // Private constructor for singleton pattern
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function startSession(): bool {
        if (!$this->sessionStarted) {
            // Start session via centralized boot
            $this->sessionStarted = cms_session_start('public');

            if ($this->sessionStarted) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                
                // Set security headers
                header('X-Frame-Options: DENY');
                header('X-Content-Type-Options: nosniff');
                header('X-XSS-Protection: 1; mode=block');
                
                // Initialize session timestamp
                $_SESSION['last_activity'] = time();
            }
        }
        return $this->sessionStarted;
    }

    public function validateSession(): bool {
        if (!$this->sessionStarted) {
            return false;
        }

        // Check session timeout (30 minutes)
        if (isset($_SESSION['last_activity']) &&
            (time() - $_SESSION['last_activity'] > 1800)) {
            $this->logout();
            return false;
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }

    public function login(string $username, string $password): bool {
        if (!$this->startSession()) {
            return false;
        }

        // TODO: Replace with actual user lookup from database
        $storedHash = ''; // Get from database in real implementation
        
        if (password_verify($password, $storedHash)) {
            $this->currentUser = $username;
            $_SESSION['user'] = $username;
            $_SESSION['login_time'] = time();
            
            // Regenerate session ID after successful login
            session_regenerate_id(true);
            return true;
        }

        // Track failed attempts
        if (!isset($_SESSION['failed_attempts'])) {
            $_SESSION['failed_attempts'] = 0;
        }
        $_SESSION['failed_attempts']++;
        
        return false;
    }

    public function logout(): void {
        // Clear all session data
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
        $this->sessionStarted = false;
        $this->currentUser = null;
    }

    public function isLoggedIn(): bool {
        return $this->currentUser !== null;
    }

    public function getUserRoles(): array {
        return $this->userRoles;
    }

    public function assignRoles(array $roles): void {
        $this->userRoles = array_unique(array_merge($this->userRoles, $roles));
        if ($this->sessionStarted) {
            $_SESSION['user_roles'] = $this->userRoles;
        }
    }

    public function removeRole(string $role): void {
        $this->userRoles = array_diff($this->userRoles, [$role]);
        if ($this->sessionStarted) {
            $_SESSION['user_roles'] = $this->userRoles;
        }
    }

    public function hasRole(string $role): bool {
        return in_array($role, $this->userRoles, true);
    }

    public function hasAnyRole(array $roles): bool {
        return count(array_intersect($roles, $this->userRoles)) > 0;
    }

    public function hasAllRoles(array $roles): bool {
        return count(array_intersect($roles, $this->userRoles)) === count($roles);
    }

    public function getRoleHierarchy(): array {
        // TODO: Implement role hierarchy from configuration
        return [
            'admin' => ['editor', 'author'],
            'editor' => ['author']
        ];
    }

    public function hasRoleWithInheritance(string $role): bool {
        if ($this->hasRole($role)) {
            return true;
        }

        $hierarchy = $this->getRoleHierarchy();
        foreach ($this->userRoles as $userRole) {
            if (isset($hierarchy[$userRole]) && in_array($role, $hierarchy[$userRole], true)) {
                return true;
            }
        }

        return false;
    }
}
