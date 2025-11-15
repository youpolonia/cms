<?php
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use ErrorHandler class
require_once __DIR__ . '/errorhandler.php';
ErrorHandler::register();

// Database connection test
function testDatabaseConnection() {
    try {
        require_once __DIR__ . '/core/database.php';
        $db = \core\Database::connect();
        return "Database connection successful";
    } catch (Exception $e) {
        error_log($e->getMessage());
        return "Database connection failed: Internal error";
    }
}

// Simple routing for testing
$route = $_GET['route'] ?? '';

switch ($route) {
    case 'db-test':
        error_log("DEBUG: db-test route executed", 3, __DIR__ . '/logs/debug.log');
        echo testDatabaseConnection();
        break;
    case 'trigger-error':
        error_log("DEBUG: trigger-error route executed", 3, __DIR__ . '/logs/debug.log');
        // Trigger different error types
        trigger_error("This is a test notice", E_USER_NOTICE);
        trigger_error("This is a test warning", E_USER_WARNING);
        trigger_error("This is a test error", E_USER_ERROR);
        break;
    case 'undefined-var':
        // Trigger undefined variable notice
        echo $undefinedVariable;
        break;
    default:
        echo "Debug Tools Available:";
        echo "
<ul>
            <li><a href='?route=db-test'>Test Database Connection</a></li>
            <li><a href='?route=trigger-error'>Trigger Test Errors</a></li>
            <li><a href='?route=undefined-var'>Trigger Undefined Variable</a></li>
        </ul>";
}
