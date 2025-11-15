<?php
/**
 * Audit Logger
 * Provides logging functionality for system actions
 */

/**
 * Logs an action to the audit log
 * 
 * @param int $user_id User ID performing the action
 * @param string $action Action description
 * @param string $details Optional details (JSON encoded)
 * @param int|null $tenant_id Tenant ID (null for system-wide actions)
 * @return bool True if log was successful, false otherwise
 */
function log_action($user_id, $action, $details = '', $tenant_id = null) {
    // Validate required parameters
    if (empty($user_id) || !is_numeric($user_id) || $user_id <= 0) {
        trigger_error('User ID must be a positive integer', E_USER_WARNING);
        return false;
    }

    if (empty($action) || !is_string($action)) {
        trigger_error('Action must be a non-empty string', E_USER_WARNING);
        return false;
    }

    // Prepare SQL with parameter binding
    $sql = "INSERT INTO audit_logs (
        tenant_id,
        user_id,
        action,
        details,
        ip_address,
        timestamp
    ) VALUES (?, ?, ?, ?, ?, NOW())";

    // Get client IP address
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Execute query and handle result
    try {
        $result = db_query($sql, [
            $tenant_id,
            $user_id,
            $action,
            $details,
            $ip
        ]);
        
        if (!$result) {
            trigger_error('Failed to log audit action', E_USER_WARNING);
            return false;
        }
        return true;

        return $result !== false;
    } catch (Exception $e) {
        trigger_error('Audit log failed: ' . $e->getMessage(), E_USER_WARNING);
        return false;
    }
}
