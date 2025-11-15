<?php
declare(strict_types=1);

namespace CMS\Services;

use CMS\Includes\Database\DatabaseConnection;
use CMS\Services\CacheManager;
use CMS\Services\ChangeTracker;

class DeploymentManager {
    private DatabaseConnection $db;
    private CacheManager $cacheManager;
    private ChangeTracker $changeTracker;
    private string $deploymentRoot;
    private array $config;

    public function __construct(
        DatabaseConnection $db,
        CacheManager $cacheManager,
        ChangeTracker $changeTracker,
        string $deploymentRoot,
        array $config = []
    ) {
        $this->db = $db;
        $this->cacheManager = $cacheManager;
        $this->changeTracker = $changeTracker;
        $this->deploymentRoot = rtrim($deploymentRoot, '/') . '/';
        $this->config = $config;
    }

    /**
     * Deploy changes via FTP
     */
    public function deployViaFtp(array $changes, array $ftpConfig): bool {
        $connection = $this->connectToFtp($ftpConfig);
        if (!$connection) {
            return false;
        }

        $success = true;
        foreach ($changes as $change) {
            $localPath = $this->deploymentRoot . $change['path'];
            $remotePath = $ftpConfig['remote_root'] . $change['path'];

            switch ($change['type']) {
                case 'file':
                    $result = ftp_put(
                        $connection,
                        $remotePath,
                        $localPath,
                        FTP_BINARY
                    );
                    break;
                
                case 'directory':
                    $result = $this->createRemoteDirectory($connection, $remotePath);
                    break;
                
                case 'delete':
                    $result = ftp_delete($connection, $remotePath);
                    break;
                
                default:
                    $result = false;
            }

            if (!$result) {
                $success = false;
                $this->logDeploymentError($change, ftp_error($connection));
                continue;
            }

            $this->logDeployment($change);
        }

        ftp_close($connection);
        return $success;
    }

    public function connectToFtp(array $config) {
        $connection = ftp_connect($config['host'], $config['port'] ?? 21, $config['timeout'] ?? 30);
        if (!$connection) {
            return false;
        }

        if (!ftp_login($connection, $config['username'], $config['password'])) {
            ftp_close($connection);
            return false;
        }

        if (isset($config['passive']) && $config['passive']) {
            ftp_pasv($connection, true);
        }

        return $connection;
    }

    private function createRemoteDirectory($connection, string $path): bool {
        $parts = explode('/', trim($path, '/'));
        $currentPath = '';

        foreach ($parts as $part) {
            $currentPath .= '/' . $part;
            if (!@ftp_chdir($connection, $currentPath)) {
                if (!ftp_mkdir($connection, $currentPath)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function logDeployment(array $change): bool {
        return $this->db->insert('deployment_logs', [
            'change_id' => $change['id'] ?? null,
            'path' => $change['path'],
            'change_type' => $change['type'],
            'status' => 'success',
            'deployed_at' => date('Y-m-d H:i:s'),
            'deployed_by' => $this->config['user_id'] ?? null
        ]);
    }

    private function logDeploymentError(array $change, string $error): bool {
        return $this->db->insert('deployment_logs', [
            'change_id' => $change['id'] ?? null,
            'path' => $change['path'],
            'change_type' => $change['type'],
            'status' => 'failed',
            'error' => $error,
            'deployed_at' => date('Y-m-d H:i:s'),
            'deployed_by' => $this->config['user_id'] ?? null
        ]);
    }

    /**
     * Get pending changes for deployment
     */
    public function getPendingChanges(): array {
        return $this->changeTracker->getChangesSinceLastDeployment();
    }

    /**
     * Rollback last deployment
     */
    public function rollbackLastDeployment(): bool {
        $lastDeployment = $this->db->fetchOne(
            "SELECT * FROM deployment_logs 
             WHERE status = 'success'
             ORDER BY deployed_at DESC 
             LIMIT 1"
        );

        if (!$lastDeployment) {
            return false;
        }

        return $this->db->update(
            'deployment_logs',
            ['status' => 'rolled_back'],
            ['id' => $lastDeployment['id']]
        );
    }

    /**
     * Verify deployment integrity
     */
    public function verifyDeployment(array $ftpConfig): array {
        $connection = $this->connectToFtp($ftpConfig);
        if (!$connection) {
            return ['success' => false, 'error' => 'FTP connection failed'];
        }

        $verification = [
            'success' => true,
            'files_verified' => 0,
            'files_missing' => 0,
            'files_different' => 0,
            'details' => []
        ];

        $changes = $this->getPendingChanges();
        foreach ($changes as $change) {
            if ($change['type'] !== 'file') continue;

            $localPath = $this->deploymentRoot . $change['path'];
            $remotePath = $ftpConfig['remote_root'] . $change['path'];

            $localSize = filesize($localPath);
            $remoteSize = ftp_size($connection, $remotePath);

            if ($remoteSize === -1) {
                $verification['files_missing']++;
                $verification['details'][] = [
                    'path' => $change['path'],
                    'status' => 'missing'
                ];
                $verification['success'] = false;
                continue;
            }

            if ($localSize !== $remoteSize) {
                $verification['files_different']++;
                $verification['details'][] = [
                    'path' => $change['path'],
                    'status' => 'size_mismatch',
                    'local_size' => $localSize,
                    'remote_size' => $remoteSize
                ];
                $verification['success'] = false;
                continue;
            }

            $verification['files_verified']++;
        }

        ftp_close($connection);
        return $verification;
    }
}
