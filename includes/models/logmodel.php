<?php

namespace Includes\Models;

class LogModel {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function logAuthAttempt(
        string $username,
        string $ip,
        bool $success,
        ?string $reason = null,
        ?int $userId = null
    ): bool {
        $stmt = $this->db->prepare("
            INSERT INTO auth_logs
            (user_id, username, ip_address, success, reason, created_at)
            VALUES (:user_id, :username, :ip, :success, :reason, NOW())
        ");

        return $stmt->execute([
            ':user_id' => $userId,
            ':username' => $username,
            ':ip' => $ip,
            ':success' => (int)$success,
            ':reason' => $reason
        ]);
    }

    public function logSettingChange(
        int $userId,
        string $settingName,
        ?string $oldValue,
        ?string $newValue
    ): bool {
        // Sanitize sensitive data
        if (in_array($settingName, ['smtp_password', 'smtp_username'])) {
            $oldValue = $oldValue ? '*****' : null;
            $newValue = $newValue ? '*****' : null;
        }

        $stmt = $this->db->prepare("
            INSERT INTO settings_logs
            (user_id, setting_name, old_value, new_value, created_at)
            VALUES (:user_id, :setting_name, :old_value, :new_value, NOW())
        ");

        return $stmt->execute([
            ':user_id' => $userId,
            ':setting_name' => $settingName,
            ':old_value' => $oldValue,
            ':new_value' => $newValue
        ]);
    }

    public function getClientIp(): string {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }
        return '0.0.0.0';
    }
}
