<?php
/* NOTE: This project is FTP-only and does not use CLI. This file is kept for reference only. */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "403 — CLI entry is disabled.";
    exit;
}
fwrite(STDERR, "CLI is disabled for this project. File kept for reference.\n");
exit(1);

/**
 * CLI Bootstrap File
 * Initializes core components for command-line execution
 * (Disabled - kept for reference only)
 */

// 1. Initialize ErrorHandler
require_once __DIR__ . '/includes/errorhandler.php';
ErrorHandler::register();

// 2. Load configuration
require_once __DIR__ . '/includes/config.php';
Config::load(__DIR__ . '/config/app.php');

// 3. Initialize PluginManager
require_once __DIR__ . '/includes/pluginmanager.php';
$pluginManager = new Includes\PluginManager();
$pluginManager->initialize();

echo "Plugin system initialized successfully\n";

// 4. Handle CLI commands
if (isset($argv[1])) {
    switch ($argv[1]) {
        case 'process-analytics':
            require_once __DIR__ . '/includes/analytics/eventprocessor.php';
            $processor = new Includes\Analytics\EventProcessor();
            $processor->processDaily();
            break;
        default:
            echo "Unknown command\n";
    }
}
