<?php
/**
 * Human Worker model for CMS staff profiles
 *
 * @package CMS
 * @subpackage Models
 */

declare(strict_types=1);

namespace CMS\Models;

use CMS\Database\Connection;
use CMS\Exceptions\ModelException;
use CMS\Helpers\Security;

class HumanWorker extends Worker
{
    /**
     * @var string Table name
     */
    protected string $table = 'human_workers';

    /**
     * Get worker by email
     *
     * @param string $email
     * @return array|null
     */
    public function getByEmail(string $email): ?array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Update password with hashing
     *
     * @param int $id
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hashed = Security::hashPassword($newPassword);
        $stmt = $this->connection->prepare(
            "UPDATE {$this->table}
            SET password = ?, last_password_change = NOW()
            WHERE id = ?"
        );
        return $stmt->execute([$hashed, $id]);
    }

    /**
     * Update profile picture path
     *
     * @param int $id
     * @param string $imagePath
     * @return bool
     */
    public function updateProfilePicture(int $id, string $imagePath): bool
    {
        $stmt = $this->connection->prepare(
            "UPDATE {$this->table} SET profile_picture = ? WHERE id = ?"
        );
        return $stmt->execute([$imagePath, $id]);
    }

    /**
     * Set password reset token
     *
     * @param string $email
     * @param string $token
     * @param string $expiry
     * @return bool
     */
    public function setPasswordResetToken(
        string $email,
        string $token,
        string $expiry
    ): bool {
        $stmt = $this->connection->prepare(
            "UPDATE {$this->table}
            SET password_reset_token = ?, password_reset_expires = ?
            WHERE email = ?"
        );
        return $stmt->execute([$token, $expiry, $email]);
    }

    /**
     * Get worker activity logs
     *
     * @param int $workerId
     * @param int $limit
     * @return array
     */
    public function getActivityLogs(int $workerId, int $limit = 100): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM worker_activity_logs
            WHERE worker_id = ?
            ORDER BY created_at DESC
            LIMIT ?"
        );
        $stmt->execute([$workerId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Log worker activity
     *
     * @param int $workerId
     * @param string $action
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @param array $details
     * @return bool
     */
    public function logActivity(
        int $workerId,
        string $action,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $details = []
    ): bool {
        $stmt = $this->connection->prepare(
            "INSERT INTO worker_activity_logs
            (worker_id, action, ip_address, user_agent, details, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())"
        );
        
        $detailsJson = json_encode($details);
        
        return $stmt->execute([
            $workerId,
            $action,
            $ipAddress,
            $userAgent,
            $detailsJson
        ]);
    }

    /**
     * Get worker's last activity
     *
     * @param int $workerId
     * @return array|null
     */
    public function getLastActivity(int $workerId): ?array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM worker_activity_logs
            WHERE worker_id = ?
            ORDER BY created_at DESC
            LIMIT 1"
        );
        $stmt->execute([$workerId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get worker's IP address from last activity
     *
     * @param int $workerId
     * @return string|null
     */
    public function getLastIpAddress(int $workerId): ?string
    {
        $activity = $this->getLastActivity($workerId);
        return $activity['ip_address'] ?? null;
    }

    /**
     * Get worker's user agent from last activity
     *
     * @param int $workerId
     * @return string|null
     */
    public function getLastUserAgent(int $workerId): ?string
    {
        $activity = $this->getLastActivity($workerId);
        return $activity['user_agent'] ?? null;
    }
}
