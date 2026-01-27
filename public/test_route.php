<?php
/**
 * Test JTB Route
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('CMS_ROOT', dirname(__DIR__));
define('CMS_PUBLIC', __DIR__);
define('CMS_APP', CMS_ROOT . '/app');
define('CMS_CORE', CMS_ROOT . '/core');
define('CMS_CONFIG', CMS_ROOT . '/config');
define('CMS_STORAGE', CMS_ROOT . '/storage');

$_SERVER['REQUEST_URI'] = '/admin/jessie-theme-builder';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "<h1>Testing JTB Route</h1>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";

try {
    require_once CMS_ROOT . '/config.php';
    echo "<p style='color:green'>Config loaded</p>";

    require_once CMS_CORE . '/app.php';
    echo "<p style='color:green'>App loaded</p>";

    echo "<p>About to run app...</p>";
    $app = new \Core\App();
    $app->run();

} catch (Throwable $e) {
    echo "<h2 style='color:red'>ERROR</h2>";
    echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
