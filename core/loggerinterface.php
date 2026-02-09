<?php
namespace Core;

interface LoggerInterface {
    public function emergency(string $message, array $context = []);
    public function alert(string $message, array $context = []);
    public function critical(string $message, array $context = []);
    public function error(string $message, array $context = []);
    public function warning(string $message, array $context = []);
    public function notice(string $message, array $context = []);
    public function info(string $message, array $context = []);
    public function debug(string $message, array $context = []);
}

class EmergencyLogger implements LoggerInterface {
    public function emergency(string $message, array $context = []) { error_log("[EMERGENCY] $message"); }
    public function alert(string $message, array $context = []) { error_log("[ALERT] $message"); }
    public function critical(string $message, array $context = []) { error_log("[CRITICAL] $message"); }
    public function error(string $message, array $context = []) { error_log("[ERROR] $message"); }
    public function warning(string $message, array $context = []) { error_log("[WARNING] $message"); }
    public function notice(string $message, array $context = []) { error_log("[NOTICE] $message"); }
    public function info(string $message, array $context = []) { error_log("[INFO] $message"); }
    public function debug(string $message, array $context = []) { error_log("[DEBUG] $message"); }
}
