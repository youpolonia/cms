<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing AI Theme Builder</h1>";

define('CMS_ROOT', dirname(__DIR__));
define('CMS_CORE', CMS_ROOT . '/core');
define('CMS_CONFIG', CMS_ROOT . '/config');
define('CMS_APP', CMS_ROOT . '/app');

echo "<p>CMS_ROOT: " . CMS_ROOT . "</p>";
echo "<p>CMS_APP: " . CMS_APP . "</p>";

// Load essentials
require_once CMS_ROOT . '/config.php';
echo "<p>✓ config.php loaded</p>";

require_once CMS_CORE . '/database.php';
echo "<p>✓ database.php loaded</p>";

require_once CMS_CORE . '/session.php';
echo "<p>✓ session.php loaded</p>";

require_once CMS_CORE . '/csrf.php';
echo "<p>✓ csrf.php loaded</p>";

// Start session
\Core\Session::start();
csrf_boot();
echo "<p>✓ Session started</p>";

// Load controller
$controllerFile = CMS_APP . '/controllers/admin/aithemebuildercontroller.php';
echo "<p>Controller file: $controllerFile</p>";
echo "<p>Exists: " . (file_exists($controllerFile) ? 'YES' : 'NO') . "</p>";

require_once $controllerFile;
echo "<p>✓ Controller loaded</p>";

// Try to instantiate
try {
    $controller = new \App\Controllers\Admin\AiThemeBuilderController();
    echo "<p>✓ Controller instantiated</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR instantiating: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Try to call index
echo "<h2>Calling index():</h2>";
try {
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    echo "<p>✓ index() succeeded, output length: " . strlen($output) . "</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR in index(): " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
