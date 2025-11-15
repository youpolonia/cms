<?php
// Minimal SessionManager shim
if (!class_exists('SessionManager', false)) {
    class SessionManager {
        public static function start(): void {
            if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        }
    }
}
