<?php

namespace Includes\Auth;

class SessionManager {
    private $sessionStarted = false;

    public function __construct() {
        $this->startSession();
    }

    public function startSession(): void {
        if (!$this->sessionStarted) {
            require_once __DIR__ . '/../../../../config.php';
            require_once __DIR__ . '/../../../../core/session_boot.php';
            cms_session_start('public');
            $this->sessionStarted = true;
        }
    }

    public function regenerate(): void {
        session_regenerate_id(true);
    }

    public function put(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function forget(string $key): void {
        unset($_SESSION[$key]);
    }

    public function flush(): void {
        session_unset();
    }

    public function destroy(): void {
        session_destroy();
        $this->sessionStarted = false;
    }

    public function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public function id(): string {
        return session_id();
    }
}
