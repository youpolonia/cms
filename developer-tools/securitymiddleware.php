<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
/**
 * Developer Tools Security Middleware
 * Handles authentication and data sanitization
 */
class DeveloperToolsSecurity {
    private $allowedRoles = ['developer', 'admin'];
    
    public function checkAccess(): bool {
        if (!isset($_SESSION['user_role'])) {
            return false;
        }
        return in_array($_SESSION['user_role'], $this->allowedRoles);
    }

    public function sanitizeOutput(string $output): string {
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function filterSensitiveData(array $data): array {
        $filtered = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'password') !== false || 
                strpos($key, 'token') !== false ||
                strpos($key, 'secret') !== false) {
                $filtered[$key] = '***REDACTED***';
            } else {
                $filtered[$key] = is_array($value) ? 
                    $this->filterSensitiveData($value) : 
                    $value;
            }
        }
        return $filtered;
    }
}
