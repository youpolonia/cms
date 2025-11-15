<?php
declare(strict_types=1);

/**
 * Deployment Verification Script
 * Checks system requirements and configuration
 */

require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/database.php';

class DeploymentVerifier {
    private array $errors = [];
    private array $requiredExtensions = [
        'pdo',
        'pdo_mysql',
        'json',
        'mbstring',
        'openssl'
    ];

    public function runChecks(): void {
        $this->checkPhpVersion();
        $this->checkExtensions();
        $this->checkFilePermissions();
        $this->testDatabaseConnection();
        $this->verifyMultiRegionConfig();
        $this->checkSecurityLayer();
        
        $this->outputResults();
    }

    private function checkPhpVersion(): void {
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            $this->errors[] = "PHP 8.1+ required (current: " . PHP_VERSION . ")";
        }
    }

    private function checkExtensions(): void {
        foreach ($this->requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->errors[] = "Missing required extension: $ext";
            }
        }
    }

    private function checkFilePermissions(): void {
        $requiredWritable = [
            'storage/logs',
            'storage/cache',
            'public/uploads'
        ];

        foreach ($requiredWritable as $dir) {
            if (!is_writable($dir)) {
                $this->errors[] = "Directory not writable: $dir";
            }
        }
    }

    private function testDatabaseConnection(): void {
        try {
            $pdo = \core\Database::connection();
            $pdo->query('SELECT 1');
        } catch (PDOException $e) {
            $this->errors[] = "Database connection failed: " . $e->getMessage();
        }
    }

    private function verifyMultiRegionConfig(): void {
        if (!file_exists('config_core/multisite.php')) {
            $this->errors[] = "Multi-region config missing";
            return;
        }

        $config = require_once 'config_core/multisite.php';
        if (empty($config['regions'])) {
            $this->errors[] = "No regions configured";
        }
    }

    private function checkSecurityLayer(): void {
        if (!file_exists('config/security.php')) {
            $this->errors[] = "Security config missing";
            return;
        }

        $security = require_once 'config/security.php';
        if (!$security['firewall_enabled']) {
            $this->errors[] = "Security firewall disabled";
        }
    }

    private function outputResults(): void {
        if (empty($this->errors)) {
            echo "✅ All deployment checks passed\n";
            return;
        }

        echo "❌ Deployment issues found:\n";
        foreach ($this->errors as $error) {
            echo "- $error\n";
        }
        exit(1);
    }
}

(new DeploymentVerifier())->runChecks();
