<?php
declare(strict_types=1);

namespace CMS\Services;

use CMS\Includes\Database\DatabaseConnection;
use CMS\Services\CacheManager;

class ChangeTracker {
    private DatabaseConnection $db;
    private CacheManager $cacheManager;
    private string $rootPath;
    private array $config;

    public function __construct(
        DatabaseConnection $db,
        CacheManager $cacheManager,
        string $rootPath,
        array $config = []
    ) {
        $this->db = $db;
        $this->cacheManager = $cacheManager;
        $this->rootPath = rtrim($rootPath, '/') . '/';
        $this->config = $config;
    }

    /**
     * Track a file change
     */
    public function trackFileChange(string $path, string $changeType = 'modified'): bool {
        $fullPath = $this->rootPath . ltrim($path, '/');
        
        if (!file_exists($fullPath)) {
            return false;
        }

        $hash = $this->getFileHash($fullPath);
        $lastChange = $this->getLastChange($path);

        if ($lastChange && $lastChange['hash'] === $hash) {
            return true; // No actual change
        }

        return $this->db->insert('change_tracking', [
            'path' => $path,
            'change_type' => $changeType,
            'hash' => $hash,
            'size' => filesize($fullPath),
            'modified_at' => date('Y-m-d H:i:s', filemtime($fullPath)),
            'tracked_at' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'changed_by' => $this->config['user_id'] ?? null
        ]);
    }

    /**
     * Track a directory creation
     */
    public function trackDirectoryCreation(string $path): bool {
        return $this->db->insert('change_tracking', [
            'path' => $path,
            'change_type' => 'directory',
            'tracked_at' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'changed_by' => $this->config['user_id'] ?? null
        ]);
    }

    /**
     * Track a deletion
     */
    public function trackDeletion(string $path): bool {
        return $this->db->insert('change_tracking', [
            'path' => $path,
            'change_type' => 'deleted',
            'tracked_at' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'changed_by' => $this->config['user_id'] ?? null
        ]);
    }

    private function getFileHash(string $path): string {
        return md5_file($path);
    }

    private function getLastChange(string $path): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM change_tracking 
             WHERE path = ? 
             ORDER BY tracked_at DESC 
             LIMIT 1",
            [$path]
        );
    }

    /**
     * Get changes since last deployment
     */
    public function getChangesSinceLastDeployment(): array {
        return $this->db->fetchAll(
            "SELECT * FROM change_tracking 
             WHERE status = 'pending'
             ORDER BY tracked_at ASC"
        );
    }

    /**
     * Get all changes within time period
     */
    public function getChangesInPeriod(string $period = '7d'): array {
        $interval = $this->parseTimePeriod($period);

        return $this->db->fetchAll(
            "SELECT * FROM change_tracking 
             WHERE tracked_at > DATE_SUB(NOW(), INTERVAL ? DAY)
             ORDER BY tracked_at DESC",
            [$interval]
        );
    }

    private function parseTimePeriod(string $period): int {
        $unit = substr($period, -1);
        $value = (int)substr($period, 0, -1);

        return match($unit) {
            'd' => $value,
            'w' => $value * 7,
            'm' => $value * 30,
            'y' => $value * 365,
            default => 7
        };
    }

    /**
     * Mark changes as deployed
     */
    public function markChangesAsDeployed(array $changeIds): bool {
        if (empty($changeIds)) {
            return true;
        }

        $placeholders = implode(',', array_fill(0, count($changeIds), '?'));
        return $this->db->execute(
            "UPDATE change_tracking 
             SET status = 'deployed', 
                 deployed_at = NOW() 
             WHERE id IN ($placeholders)",
            $changeIds
        );
    }

    /**
     * Get file change history
     */
    public function getFileHistory(string $path, int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT * FROM change_tracking 
             WHERE path = ? 
             ORDER BY tracked_at DESC 
             LIMIT ?",
            [$path, $limit]
        );
    }

    /**
     * Get recent changes summary
     */
    public function getRecentChangesSummary(): array {
        return $this->db->fetchAll(
            "SELECT 
                change_type,
                COUNT(*) as count,
                MAX(tracked_at) as last_change
             FROM change_tracking
             WHERE tracked_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY change_type
             ORDER BY count DESC"
        );
    }
}
