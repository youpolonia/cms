<?php
// Minimal session helper (lowercase path, FTP-only, require_once only)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!class_exists('Session', false)) {
    class Session {
        public static function start(): void {
            if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        }
        public static function isAdmin(): bool {
            self::start();
            return !empty($_SESSION['is_admin']);
        }
        public static function requireAdmin(): void {
            self::start();
            if (!self::isAdmin()) { http_response_code(403); exit; }
        }
    }
}
