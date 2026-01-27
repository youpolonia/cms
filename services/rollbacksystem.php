<?php
declare(strict_types=1);

namespace CMS\Services;

use CMS\Includes\Database\DatabaseConnection;
use CMS\Services\CacheManager;
use CMS\Services\ChangeTracker;
use CMS\Services\DeploymentManager;

class RollbackSystem {
    private DatabaseConnection $db;
    private CacheManager $cacheManager;
    private ChangeTracker $changeTracker;
    private DeploymentManager $deploymentManager;
    private string $backupRoot;
    private array $config;

    public function __construct(
        DatabaseConnection $db,
        CacheManager $cacheManager,
        ChangeTracker $changeTracker,
        DeploymentManager $deploymentManager,
        string $backupRoot,
        array $config = []
    ) {
        $this->db = $db;
        $this->cacheManager = $cacheManager;
        $this->changeTracker = $changeTracker;
        $this->deploymentManager = $deploymentManager;
        $this->backupRoot = rtrim($backupRoot, '/') . '/';
        $this->config = $config;
    }

    /**
     * Create a rollback point
     */
    public function createRollbackPoint(string $name, ?string $description = null): bool {
        $changes = $this->changeTracker->getChangesSinceLastDeployment();
        if (empty($changes)) {
            return false;
        }

        $rollbackId = $this->db->insert('rollback_points', [
            'name' => $name,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->config['user_id'] ?? null
        ]);

        if (!$rollbackId) {
            return false;
        }

        foreach ($changes as $change) {
            $this->db->insert('rollback_point_changes', [
                'rollback_point_id' => $rollbackId,
                'change_id' => $change['id'],
                'backup_path' => $this->backupChange($change)
            ]);
        }

        return true;
    }

    private function backupChange(array $change): ?string {
        if ($change['change_type'] === 'deleted' || $change['change_type'] === 'directory') {
            return null;
        }

        $sourcePath = $this->deploymentManager->getDeploymentRoot() . $change['path'];
        $backupPath = $this->backupRoot . 'rollback_' . date('Ymd_His') . '_' . basename($change['path']);

        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }

        return copy($sourcePath, $backupPath) ? $backupPath : null;
    }

    /**
     * Execute rollback to specified point
     */
    public function executeRollback(int $rollbackPointId, array $ftpConfig): bool {
        $rollbackPoint = $this->getRollbackPoint($rollbackPointId);
        if (!$rollbackPoint) {
            return false;
        }

        $changes = $this->db->fetchAll(
            "SELECT c.*, r.backup_path 
             FROM rollback_point_changes r
             JOIN change_tracking c ON r.change_id = c.id
             WHERE r.rollback_point_id = ?",
            [$rollbackPointId]
        );

        if (empty($changes)) {
            return false;
        }

        try {
            $connection = $this->deploymentManager->connectToFtp($ftpConfig);
            if (!$connection) {
                throw new \RuntimeException('Failed to connect to FTP server');
            }

            $success = true;
            foreach ($changes as $change) {
                $remotePath = $ftpConfig['remote_root'] . $change['path'];

                switch ($change['change_type']) {
                    case 'modified':
                        if ($change['backup_path'] && file_exists($change['backup_path'])) {
                            $result = ftp_put(
                                $connection,
                                $remotePath,
                                $change['backup_path'],
                                FTP_BINARY
                            );
                        } else {
                            $result = false;
                            $this->logRollbackError($rollbackPointId, $change, 'Backup file missing');
                        }
                        break;
                
                    case 'created':
                        $result = ftp_delete($connection, $remotePath);
                        break;
                
                    case 'deleted':
                        if ($change['backup_path']) {
                            $result = ftp_put(
                                $connection,
                                $remotePath,
                                $change['backup_path'],
                                FTP_BINARY
                            );
                        } else {
                            $result = false;
                        }
                        break;
                
                    default:
                        $result = false;
                }

                if (!$result) {
                    $success = false;
                    $this->logRollbackError($rollbackPointId, $change, ftp_error($connection));
                    continue;
                }

                $this->logRollback($rollbackPointId, $change);
            }

            ftp_close($connection);
            return $success;
        } catch (\Exception $e) {
            $this->logRollbackError($rollbackPointId, ['path' => '', 'change_type' => 'system'], $e->getMessage());
            return false;
        }
    }

    private function getRollbackPoint(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM rollback_points WHERE id = ?",
            [$id]
        );
    }

    private function logRollback(int $rollbackPointId, array $change): bool {
        return $this->db->insert('rollback_logs', [
            'rollback_point_id' => $rollbackPointId,
            'change_id' => $change['id'],
            'path' => $change['path'],
            'change_type' => $change['change_type'],
            'status' => 'success',
            'rolled_back_at' => date('Y-m-d H:i:s'),
            'rolled_back_by' => $this->config['user_id'] ?? null
        ]);
    }

    private function logRollbackError(int $rollbackPointId, array $change, string $error): bool {
        return $this->db->insert('rollback_logs', [
            'rollback_point_id' => $rollbackPointId,
            'change_id' => $change['id'],
            'path' => $change['path'],
            'change_type' => $change['change_type'],
            'status' => 'failed',
            'error' => $error,
            'rolled_back_at' => date('Y-m-d H:i:s'),
            'rolled_back_by' => $this->config['user_id'] ?? null
        ]);
    }

    /**
     * Get available rollback points
     */
    public function getRollbackPoints(int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT * FROM rollback_points 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get rollback details
     */
    public function getRollbackDetails(int $rollbackPointId): array {
        $point = $this->getRollbackPoint($rollbackPointId);
        if (!$point) {
            return [];
        }

        $changes = $this->db->fetchAll(
            "SELECT c.* 
             FROM rollback_point_changes r
             JOIN change_tracking c ON r.change_id = c.id
             WHERE r.rollback_point_id = ?",
            [$rollbackPointId]
        );

        return [
            'point' => $point,
            'changes' => $changes,
            'change_count' => count($changes)
        ];
    }

    /**
     * Clean up old rollback points
     */
    public function cleanupOldRollbacks(int $keepDays = 30): bool {
        $this->db->execute(
            "DELETE FROM rollback_points 
             WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$keepDays]
        );

        // Cleanup orphaned backup files
        $backups = glob($this->backupRoot . 'rollback_*');
        $threshold = time() - ($keepDays * 24 * 60 * 60);

        foreach ($backups as $backup) {
            if (filemtime($backup) < $threshold) {
                unlink($backup);
            }
        }

        return true;
    }
}
