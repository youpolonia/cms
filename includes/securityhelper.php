<?php
// Security Helper v2.0
// Provides centralized security checks and sanitization

class SecurityHelper {
    // Existing role and ownership checks
    public static function checkRole($requiredRole) {
        $userRole = $_SESSION['user_role'] ?? 'guest';
        if ($userRole !== $requiredRole) {
            throw new Exception("Unauthorized: Requires $requiredRole role");
        }
    }

    public static function validateContentOwnership($tenantId, $contentId) {
        if (!DBSupport::contentBelongsToTenant($tenantId, $contentId)) {
            throw new Exception("Invalid content reference for tenant");
        }
    }

    public static function validateVersionOwnership($tenantId, $versionId) {
        if (!DBSupport::versionBelongsToTenant($tenantId, $versionId)) {
            throw new Exception("Invalid version reference for tenant");
        }
    }

    public static function validateTenantBoundary($tenantId, $targetTenants) {
        foreach ($targetTenants as $target) {
            if (!DBSupport::isAllowedTenantPair($tenantId, $target)) {
                throw new Exception("Invalid cross-tenant operation");
            }
        }
    }

    // New security functions
    public static function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map(fn($item) => self::sanitizeInput($item, $type), $input);
        }

        $input = trim($input);
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'string':
            default:
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
    }

    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception("Invalid CSRF token");
        }
    }

    public static function preventXss($data) {
        if (is_array($data)) {
            return array_map([self::class, 'preventXss'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    public static function safeSql($db, $query, $params = []) {
        $stmt = $db->prepare($query);
        if ($stmt === false) {
            throw new Exception("SQL preparation failed");
        }
        $stmt->execute($params);
        return $stmt;
    }
}
