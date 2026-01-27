<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * Security Service Layer
 * Handles core security operations and integrations
 */
class SecurityService {
    /**
     * Log security event with additional context
     * @param string $eventType Type of event
     * @param int $userId User ID
     * @param array $context Additional context data
     * @return bool True if logged successfully
     */
    public static function logSecurityEvent(string $eventType, int $userId, array $context = []): bool {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $details = json_encode([
            'context' => $context,
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'uri' => $_SERVER['REQUEST_URI'] ?? ''
            ]
        ]);

        return SecurityLog::logEvent($eventType, $userId, $ipAddress, $details);
    }

    private static ?RBAC $rbacInstance = null;

    private static function getRBAC(): RBAC {
        if (self::$rbacInstance === null) {
            self::$rbacInstance = new RBAC(\core\Database::connection());
        }
        return self::$rbacInstance;
    }

    /**
     * Check if action is allowed by security policies
     * @param string $policyName Policy to check
     * @param string $action Action being performed
     * @param int $userId User ID
     * @return bool True if action is allowed
     */
    public static function isActionAllowed(string $policyName, string $action, int $userId): bool {
        $policy = SecurityPolicy::getPolicy($policyName);
        
        // Check direct permissions first
        if (isset($policy['permissions'][$action])) {
            return (bool)$policy['permissions'][$action];
        }

        // Check role-based permissions if available
        if (isset($policy['roles'])) {
            $rbac = self::getRBAC();
            $userRoles = $rbac->getUserRoles($userId);
            foreach ($userRoles as $role) {
                if (isset($role->id) && isset($policy['roles'][$role->id][$action])) {
                    return (bool)$policy['roles'][$role->id][$action];
                }
            }
        }

        return false;
    }

    /**
     * Validate and sanitize input
     * @param mixed $input Input to validate
     * @param string $type Input type (string, int, email, etc)
     * @return mixed Sanitized input or false if invalid
     */
    public static function validateInput($input, string $type) {
        switch ($type) {
            case 'string':
                return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
            case 'int':
                return filter_var($input, FILTER_VALIDATE_INT);
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_VALIDATE_URL);
            default:
                return false;
        }
    }

    /**
     * Encode output for safe display
     * @param string $output Output to encode
     * @return string Encoded output
     */
    public static function encodeOutput(string $output): string {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
}
