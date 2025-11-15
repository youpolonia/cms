<?php
/**
 * Deployment Service
 *
 * Handles all deployment operations including:
 * - Database migrations
 * - File synchronization
 * - Version verification
 */

require_once __DIR__ . '/filesynchandler.php';
require_once __DIR__ . '/versionverifier.php';

class DeploymentService {
    private $dbMigrationRunner;
    private $fileSyncHandler;
    private $versionVerifier;
    
    public function __construct() {
        require_once __DIR__ . '/../../scripts/deployment/database_migration.php';
        $this->dbMigrationRunner = new DatabaseMigrationRunner();
        $this->fileSyncHandler = new FileSyncHandler();
        $this->versionVerifier = new VersionVerifier();
    }
    
    /**
     * Run database migrations
     */
    public function runMigrations() {
        return $this->dbMigrationRunner->run();
    }
    
    /**
     * Synchronize files between environments
     * @param string $source Source environment
     * @param string $target Target environment
     * @return array Operation status
     */
    public function syncFiles($source, $target) {
        try {
            return $this->fileSyncHandler->sync($source, $target);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'rollback_status' => $this->fileSyncHandler->rollback()
            ];
        }
    }
    
    /**
     * Verify system versions
     * @return array Verification report
     */
    public function verifyVersions() {
        $report = $this->versionVerifier->generateReport();
        $this->versionVerifier->storeReport($report);
        return $report;
    }
    
    /**
     * Execute full deployment
     */
    public function deploy() {
        $results = [
            'migrations' => $this->runMigrations(),
            'file_sync' => $this->syncFiles('staging', 'production'),
            'version_check' => $this->verifyVersions()
        ];
        
        return $results;
    }
}
