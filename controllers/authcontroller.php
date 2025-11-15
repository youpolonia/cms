<?php
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class AuthController
{
    private Database $db;
    private AuthService $auth;
    private PasswordResetService $passwordReset;
    private RateLimiter $rateLimiter;
    private EmergencyLogger $logger;

    public function __construct(
        Database $db,
        AuthService $auth,
        PasswordResetService $passwordReset,
        ?RateLimiter $rateLimiter = null,
        ?EmergencyLogger $logger = null
    ) {
        $this->db = $db;
        $this->auth = $auth;
        $this->passwordReset = $passwordReset;
        $this->rateLimiter = $rateLimiter ?? new RateLimiter($db, new EmergencyLogger());
        $this->logger = $logger ?? new EmergencyLogger();
    }

    public function login(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $identifier = 'login_attempt';

        if (!$this->rateLimiter->check($ip, $identifier)) {
            $this->logger->log("Login rate limit exceeded from $ip", $ip);
            http_response_code(429);
            echo json_encode(['error' => 'Too many login attempts. Please try again later.']);
            return;
        }

        $this->rateLimiter->recordAttempt($ip, $identifier);

        // Handle login form submission
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void
    {
        // Clear cache-based session
        $cache = new \Core\Cache\SessionCacheAdapter(
            \Core\Cache\CacheFactory::make(),
            session_id()
        );
        $cache->clear(session_id());
        
        // Destroy PHP session
        session_destroy();
        header('Location: /');
        exit;
    }

    public function register(): void
    {
        // Handle registration form
        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function requestPasswordReset(): void
    {
        csrf_validate_or_403();
        
        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email required']);
            return;
        }

        $success = $this->passwordReset->requestReset($email);
        echo json_encode(['success' => $success]);
    }

    public function resetPassword(): void
    {
        csrf_validate_or_403();
        
        $token = $_POST['token'] ?? '';
        $newPassword = $_POST['password'] ?? '';

        if (empty($token) || empty($newPassword)) {
            http_response_code(400);
            echo json_encode(['error' => 'Token and password required']);
            return;
        }

        $success = $this->passwordReset->resetPassword($token, $newPassword);
        echo json_encode(['success' => $success]);
    }
}
