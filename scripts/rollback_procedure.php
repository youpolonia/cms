<?php
declare(strict_types=1);

/**
 * Rollback Procedure Script
 * Handles safe rollback to previous deployment version
 */

// Load core dependencies
require_once __DIR__ . '/../core/logger.php';
require_once __DIR__ . '/../includes/core/mcpalert.php';
use core\Logger\LoggerFactory;

// DEV gate — this utility must not run in production
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403); exit;
}

// Configuration
const BACKUP_DIR = __DIR__ . '/../backups/latest';
const ARCHIVE_DIR = __DIR__ . '/../cms_storage/tmp/rollback_archives';
const EXCLUDE_DIRS = ['vendor', 'node_modules', '.git'];

// Initialize logging
require_once __DIR__ . '/../core/logger/LoggerFactory.php';
$logger = LoggerFactory::create('file', [
    'file_path' => __DIR__ . '/../logs/rollback.log',
    'type' => 'file'
]);
$alert = new MCPAlert();

try {
    $logger->info('Starting rollback procedure');
    
    // 1. Archive current deployment
    archiveCurrentDeployment();
    
    // 2. Restore previous version 
    restorePreviousVersion();
    
    // 3. Verify database consistency
    verifyDatabaseConsistency();
    
    // 4. Validate multi-region sync
    validateMultiRegionSync();
    
    // 5. Confirm security layer integrity
    confirmSecurityIntegrity();
    
    $logger->info('Rollback completed successfully');
    $alert->logAlert('Rollback completed', 'System rolled back to previous version', 'INFO');
    exit(0);
} catch (Throwable $e) {
    $logger->error("Rollback failed: " . $e->getMessage());
    $alert->logAlert('Rollback failed', $e->getMessage(), 'CRITICAL');
    exit(1);
}

function archiveCurrentDeployment(): void {
    global $logger;
    
    $logger->info('Archiving current deployment');
    
    $timestamp = date('Ymd_His');
    $archivePath = ARCHIVE_DIR . "/pre_rollback_$timestamp";
    
    if (!mkdir($archivePath, 0755, true)) {
        throw new RuntimeException("Failed to create archive directory");
    }
    
    // Copy all files except excluded directories
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/..'),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        if ($item->isDir() && in_array($item->getFilename(), EXCLUDE_DIRS)) {
            $iterator->next();
            continue;
        }
        
        if ($item->isFile()) {
            $relativePath = substr($item->getPathname(), strlen(__DIR__ . '/../') + 1);
            $destPath = $archivePath . '/' . $relativePath;
            
            if (!is_dir(dirname($destPath))) {
                mkdir(dirname($destPath), 0755, true);
            }
            
            if (!copy($item->getPathname(), $destPath)) {
                throw new RuntimeException("Failed to copy file: " . $item->getPathname());
            }
        }
    }
    
    $logger->info("Current deployment archived to $archivePath");
}

function restorePreviousVersion(): void {
    global $logger;
    
    $logger->info('Restoring previous version from backup');
    
    if (!is_dir(BACKUP_DIR)) {
        throw new RuntimeException("Backup directory not found: " . BACKUP_DIR);
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(BACKUP_DIR),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            continue;
        }
        
        $relativePath = substr($item->getPathname(), strlen(BACKUP_DIR) + 1);
        $destPath = __DIR__ . '/../' . $relativePath;
        
        if (!is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }
        
        if (!copy($item->getPathname(), $destPath)) {
            throw new RuntimeException("Failed to restore file: " . $item->getPathname());
        }
    }
    
    $logger->info('Previous version restored successfully');
}

function verifyDatabaseConsistency(): void {
    global $logger;

    $logger->info('Verifying database consistency');

    // Get database config
    $dbConfig = []; // legacy alt DB config removed
    
    try {
        $pdo = \core\Database::connection();
        
        // Check version table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'system_versions'");
        if ($stmt->rowCount() === 0) {
            throw new RuntimeException("Version tracking table missing");
        }
        
        // Get current version
        $stmt = $pdo->query("SELECT version FROM system_versions ORDER BY created_at DESC LIMIT 1");
        $version = $stmt->fetchColumn();
        
        if (!$version) {
            throw new RuntimeException("No version information found in database");
        }
        
        $logger->info("Database version verified: $version");
    } catch (PDOException $e) {
        error_log($e->getMessage());
        throw new RuntimeException("Database verification failed");
    }
}

function validateMultiRegionSync(): void {
    global $logger;

    $logger->info('Validating multi-region sync status');

    if (!file_exists(__DIR__ . '/../config_core/multisite.php')) {
        throw new RuntimeException("Multi-region config missing");
    }

    $config = require_once __DIR__ . '/../config_core/multisite.php';
    
    if (empty($config['regions'])) {
        throw new RuntimeException("No regions configured");
    }
    
    // Verify each region endpoint
    foreach ($config['regions'] as $region => $endpoint) {
        $response = file_get_contents("$endpoint/health-check");
        if ($response !== 'OK') {
            throw new RuntimeException("Region $region not responding correctly");
        }
    }
    
    $logger->info('All regions synchronized');
}

function confirmSecurityIntegrity(): void {
    global $logger;
    
    $logger->info('Confirming security layer integrity');
    
    if (!file_exists(__DIR__ . '/../config/security.php')) {
        throw new RuntimeException("Security config missing");
    }
    
    $security = require_once __DIR__ . '/../config/security.php';
    
    if (!$security['firewall_enabled']) {
        throw new RuntimeException("Security firewall disabled");
    }
    
    // Verify security modules
    $requiredModules = ['encryption', 'rate_limiting', 'csrf_protection'];
    foreach ($requiredModules as $module) {
        if (!$security[$module . '_enabled']) {
            throw new RuntimeException("Security module $module disabled");
        }
    }
    
    $logger->info('Security layer verified');
}
