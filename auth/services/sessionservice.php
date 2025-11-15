<?php

declare(strict_types=1);

namespace Auth\Services;

use RuntimeException;
use App\Includes\MultiSite;
use Includes\EmergencyLogger;

class SessionService
{
    private string $sessionPath;
    private bool $sessionStarted = false;
    private string $sessionName;

    private EmergencyLogger $logger;

    public function __construct(string $sessionPath = null, ?EmergencyLogger $logger = null)
    {
        $this->logger = $logger ?? new EmergencyLogger();
        
        if (!class_exists('\App\Includes\MultiSite')) {
            $multiSitePath = __DIR__ . '/../../config_core/multisite.php';
            if (file_exists($multiSitePath)) {
                // Secure include: validate base dir and extension before loading
                $__projectBase = realpath(dirname(__DIR__, 2));
                $__target      = is_string($multiSitePath) ? realpath($multiSitePath) : false;
                $__okPath      = ($__target !== false) && (strpos($__target, $__projectBase . DIRECTORY_SEPARATOR) === 0);
                $__okExt       = ($__target !== false) && (pathinfo($__target, PATHINFO_EXTENSION) === 'php');
                if (!$__okPath || !$__okExt) {
                    http_response_code(400);
                    echo 'Invalid multi-site include path.';
                } else {
                    require_once __DIR__ . '/../../config_core/multisite.php';
                }
            }
        }
        
        if (class_exists('\App\Includes\MultiSite') && \App\Includes\MultiSite::isEnabled()) {
            if (\App\Includes\MultiSite::getCurrentSite() === null) {
                 \App\Includes\MultiSite::initialize();
            }
            $this->sessionPath = $sessionPath ?? \App\Includes\MultiSite::getSiteStoragePath('sessions');
            $siteHandle = \App\Includes\MultiSite::getCurrentSiteId() ?? 'default';
            $safeSiteHandle = preg_replace('/[^a-zA-Z0-9_]/', '_', $siteHandle);
            $this->sessionName = 'SESS_' . strtoupper($safeSiteHandle);
        } else {
            require_once __DIR__ . '/../../core/tmp_sandbox.php';
            $this->sessionPath = $sessionPath ?? cms_tmp_path('sessions_global');
            $this->sessionName = 'CMSSESSID';
        }

        if (!is_dir($this->sessionPath)) {
            if (!mkdir($this->sessionPath, 0700, true) && !is_dir($this->sessionPath)) {
                error_log("SessionService: Failed to create session save path: " . $this->sessionPath);
                require_once __DIR__ . '/../../core/tmp_sandbox.php';
                $this->sessionPath = cms_tmp_dir();
            }
        }
    }

    public function start(): void
    {
        if ($this->sessionStarted) {
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            if (session_name() !== $this->sessionName) {
                error_log("SessionService: Session already active with name '" . session_name() . "', expected '" . $this->sessionName . "'.");
            }
            $this->sessionStarted = true;
            return;
        }

        ini_set('session.save_path', $this->sessionPath);
        session_name($this->sessionName);

        session_set_cookie_params([
            'lifetime' => 1800, // 30 minutes
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.gc_maxlifetime', '1800');
        ini_set('session.cookie_lifetime', '1800');

        require_once __DIR__ . '/../../config.php';
        require_once __DIR__ . '/../../core/session_boot.php';
        if (!cms_session_start('public')) {
            $this->logger->log('Session start failed', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            throw new RuntimeException('Failed to start session.');
        }
        $this->sessionStarted = true;
        
        $_SESSION['last_activity'] = time();

        if (rand(1, 100) <= 10) {
            $this->regenerate();
        }
    }

    public function regenerate(): void
    {
        $this->ensureStarted();
        $oldId = session_id();
        session_regenerate_id(true);
        $this->logger->log("Session regenerated from $oldId to " . session_id(),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

    public function destroy(): void
    {
        $this->ensureStarted();
        $_SESSION = [];
        $sessionId = session_id();
        session_destroy();
        $this->sessionStarted = false;
        $this->logger->log("Session destroyed: $sessionId",
            $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

    public function set(string $key, $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    public function addFlash(string $type, string $message): void
    {
        $this->ensureStarted();
        $_SESSION['flash'][$type][] = $message;
    }

    public function getFlash(string $type): array
    {
        $this->ensureStarted();
        $messages = $_SESSION['flash'][$type] ?? [];
        unset($_SESSION['flash'][$type]);
        return $messages;
    }

    public function hasFlash(string $type): bool
    {
        $this->ensureStarted();
        return !empty($_SESSION['flash'][$type]);
    }

    public function isExpired(): bool
    {
        $this->ensureStarted();
        return isset($_SESSION['last_activity']) &&
               (time() - $_SESSION['last_activity'] > 1800);
    }

    public function updateActivity(): void
    {
        $this->ensureStarted();
        $_SESSION['last_activity'] = time();
    }

    public function getLastActivity(): ?int
    {
        $this->ensureStarted();
        return $_SESSION['last_activity'] ?? null;
    }

    private function ensureStarted(): void
    {
        if (!$this->sessionStarted) {
            $this->start();
        }
    }
}
