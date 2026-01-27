<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('CMS_ROOT', dirname(__DIR__));
define('CMS_CORE', CMS_ROOT . '/core');
define('CMS_CONFIG', CMS_ROOT . '/config');
define('CMS_APP', CMS_ROOT . '/app');

require_once CMS_ROOT . '/config.php';
require_once CMS_CORE . '/database.php';
require_once CMS_CORE . '/session.php';
require_once CMS_CORE . '/csrf.php';

\Core\Session::start();

require_once CMS_APP . '/controllers/admin/aithemebuildercontroller.php';

echo "<h1>Test Generate</h1>";

// Fake POST data
$_POST = [
    'name' => 'test-theme',
    'type' => 'business',
    'description' => 'A professional construction company website',
    'style' => 'modern',
    'color_scheme' => 'dark',
    'primary_color' => '#f59e0b'
];
$_SERVER['REQUEST_METHOD'] = 'POST';

// Skip CSRF for test
function csrf_validate_or_403() { return true; }

try {
    $controller = new \App\Controllers\Admin\AiThemeBuilderController();
    echo "<p>Controller created</p>";
    
    // Call generate
    ob_start();
    $controller->generate();
    $output = ob_get_clean();
    
    echo "<h2>Response:</h2>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
