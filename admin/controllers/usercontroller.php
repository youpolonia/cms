<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../includes/rate_limiter.php';
class UserController {
    const USERS_PER_PAGE = 20;

    private function verifyAdmin() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied');
        }
    }

    private function verifyCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cache = new \Core\Cache\SessionCacheAdapter(
                \Core\Cache\CacheFactory::make(),
                session_id()
            );
            $storedToken = $cache->get(session_id(), 'csrf_token');
            if (!isset($_POST['csrf_token']) || !hash_equals($storedToken, $_POST['csrf_token'])) {
                header('HTTP/1.0 403 Forbidden');
                exit('Invalid CSRF token');
            }
        }
    }

    public function index() {
        $this->verifyAdmin();
        
        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * self::USERS_PER_PAGE;

        $totalUsers = DB::queryFirstField("SELECT COUNT(*) FROM users");
        $totalPages = ceil($totalUsers / self::USERS_PER_PAGE);

        $users = DB::query(
            "SELECT id, username, email, created_at 
             FROM users 
             LIMIT %d OFFSET %d",
            self::USERS_PER_PAGE,
            $offset
        );

        require_once __DIR__ . '/../views/users/index.php';
    }

    public function create() {
        $this->verifyAdmin();
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'role' => $_POST['role'] ?? 'user'
            ];

            $result = User::create($userData);
            if (!$result['valid']) {
                $_SESSION['error'] = $result['error'];
                require_once __DIR__ . '/../views/users/create.php';
                return;
            }

            header('Location: /admin/users');
            exit;
        }

        require_once __DIR__ . '/../views/users/create.php';
    }

    public function update($userId) {
        $this->verifyAdmin();
        $this->verifyCsrfToken();
        
        if (!RateLimiter::check('user_update')) {
            http_response_code(429);
            die('Too many requests. Please try again later.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'username' => $_POST['username'],
                'email' => $_POST['email']
            ];

            // Only allow role changes if current user is admin
            if (isset($_POST['role']) && $_SESSION['user_role'] === 'admin') {
                $userData['role'] = $_POST['role'];
                
                // If updating own role, regenerate session
                if ($userId == $_SESSION['admin_id']) {
                    session_regenerate_id(true);
                    $_SESSION['user_role'] = $userData['role'];
                    
                    // Update session in database
                    DB::update('sessions', [
                        'csrf_token' => bin2hex(random_bytes(32)),
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
                    ], 'session_id = %s', session_id());
                }
            }

            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }

            $result = User::update($userId, $userData);
            if (!$result['valid']) {
                $_SESSION['error'] = $result['error'];
                require_once __DIR__ . '/../views/users/update.php';
                return;
            }

            header('Location: /admin/users');
            exit;
        }

        $user = DB::queryFirstRow("SELECT * FROM users WHERE id = %d", $userId);
        require_once __DIR__ . '/../views/users/update.php';
    }

    public function delete($userId) {
        $this->verifyAdmin();
        $this->verifyCsrfToken();

        // Cleanup sessions before deletion
        DB::delete('sessions', 'user_id = %d', $userId);
        DB::delete('users', 'id = %d', $userId);
        header('Location: /admin/users');
        exit;
    }

    public function deactivate($userId) {
        $this->verifyAdmin();
        $this->verifyCsrfToken();

        // Invalidate all sessions for deactivated user
        DB::delete('sessions', 'user_id = %d', $userId);
        DB::update('users', ['is_active' => 0], 'id = %d', $userId);
        header('Location: /admin/users');
        exit;
    }
}
