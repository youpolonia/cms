<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../includes/securelogger.php';

/**
 * System Health Controller
 * Provides system monitoring and diagnostics
 */
class SystemHealthController {
    public function index() {
        $healthData = $this->gatherHealthData();
        require_once __DIR__ . '/../views/system/health.php';
    }
    
    private function gatherHealthData() {
        return [
            'system' => [
                'php_version' => phpversion(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'cms_version' => defined('CMS_VERSION') ? CMS_VERSION : 'Unknown'
            ],
            'resources' => [
                'disk_space' => [
                    'total' => disk_total_space(__DIR__ . '/../../'),
                    'free' => disk_free_space(__DIR__ . '/../../')
                ],
                'memory' => [
                    'usage' => memory_get_usage(true),
                    'peak' => memory_get_peak_usage(true),
                    'limit' => ini_get('memory_limit')
                ]
            ],
            'database' => $this->checkDatabaseStatus(),
            'checks' => [
                'writable_dirs' => $this->checkWritableDirectories(),
                'required_extensions' => $this->checkRequiredExtensions()
            ]
        ];
    }
    
    private function checkDatabaseStatus() {
        try {
            require_once __DIR__ . '/../../includes/database/connection.php';
            $db = new DatabaseConnection();
            return [
                'status' => 'Connected',
                'version' => $db->getVersion()
            ];
        } catch (Exception $e) {
            SecureLogger::logError('Database connection failed', $e);
            return [
                'status' => 'Database error',
                'version' => 'Unknown'
            ];
        }
    }
    
    private function checkWritableDirectories() {
        $dirs = [
            'logs' => __DIR__ . '/../../logs/',
            'cache' => __DIR__ . '/../../cache/',
            'backups' => __DIR__ . '/../../backups/'
        ];
        
        $results = [];
        foreach ($dirs as $name => $path) {
            $results[$name] = is_writable($path);
        }
        return $results;
    }
    
    private function checkRequiredExtensions() {
        $required = ['pdo', 'mbstring', 'json', 'zip'];
        $results = [];
        foreach ($required as $ext) {
            $results[$ext] = extension_loaded($ext);
        }
        return $results;
    }
}
