<?php
/**
 * JTB Debug Test
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>JTB Debug Test</h1>";

// Test 1: CMS_ROOT
define('CMS_ROOT', dirname(__DIR__));
echo "<p>CMS_ROOT: " . CMS_ROOT . "</p>";

// Test 2: Config
echo "<h2>Test 1: Config</h2>";
try {
    require_once CMS_ROOT . '/config.php';
    echo "<p style='color:green'>Config loaded OK</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>Config error: " . $e->getMessage() . "</p>";
}

// Test 3: Session
echo "<h2>Test 2: Session</h2>";
try {
    require_once CMS_ROOT . '/core/session.php';
    \Core\Session::start();
    echo "<p>Session started. Logged in: " . (\Core\Session::isLoggedIn() ? 'YES' : 'NO') . "</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>Session error: " . $e->getMessage() . "</p>";
}

// Test 4: Database
echo "<h2>Test 3: Database</h2>";
try {
    require_once CMS_ROOT . '/core/Database.php';
    $db = \core\Database::connection();
    $count = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    echo "<p style='color:green'>Database OK. Posts count: $count</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
}

// Test 5: Helpers
echo "<h2>Test 4: Helpers</h2>";
try {
    require_once CMS_ROOT . '/app/helpers/functions.php';
    echo "<p>esc() exists: " . (function_exists('esc') ? 'YES' : 'NO') . "</p>";
    echo "<p>csrf_token() exists: " . (function_exists('csrf_token') ? 'YES' : 'NO') . "</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>Helpers error: " . $e->getMessage() . "</p>";
}

// Test 6: JTB Plugin files
echo "<h2>Test 5: JTB Plugin Files</h2>";
$pluginPath = CMS_ROOT . '/plugins/jessie-theme-builder';
$files = [
    '/admin.php',
    '/controller.php',
    '/includes/class-jtb-element.php',
    '/includes/class-jtb-registry.php',
    '/includes/class-jtb-builder.php',
    '/views/builder.php'
];

foreach ($files as $file) {
    $fullPath = $pluginPath . $file;
    $exists = file_exists($fullPath);
    $color = $exists ? 'green' : 'red';
    echo "<p style='color:$color'>$file: " . ($exists ? 'EXISTS' : 'MISSING') . "</p>";
}

// Test 7: Load JTB classes
echo "<h2>Test 6: Load JTB Classes</h2>";
try {
    require_once $pluginPath . '/includes/class-jtb-element.php';
    echo "<p style='color:green'>class-jtb-element.php loaded</p>";

    require_once $pluginPath . '/includes/class-jtb-registry.php';
    echo "<p style='color:green'>class-jtb-registry.php loaded</p>";

    \JessieThemeBuilder\JTB_Registry::init();
    echo "<p style='color:green'>JTB_Registry::init() OK</p>";

} catch (Throwable $e) {
    echo "<p style='color:red'>JTB load error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 8: Router check
echo "<h2>Test 7: Routes Check</h2>";
try {
    $routesFile = CMS_ROOT . '/config/routes.php';
    $routesContent = file_get_contents($routesFile);

    if (strpos($routesContent, 'jessie-theme-builder') !== false) {
        echo "<p style='color:green'>JTB routes found in routes.php</p>";
    } else {
        echo "<p style='color:red'>JTB routes NOT found in routes.php</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>Routes error: " . $e->getMessage() . "</p>";
}

// Test 9: Controller check
echo "<h2>Test 8: Controller Check</h2>";
$controllerPath = CMS_ROOT . '/app/controllers/admin/jtbcontroller.php';
if (file_exists($controllerPath)) {
    echo "<p style='color:green'>JtbController exists</p>";

    // Check syntax
    $output = [];
    $returnVar = 0;
    exec("php -l " . escapeshellarg($controllerPath) . " 2>&1", $output, $returnVar);
    if ($returnVar === 0) {
        echo "<p style='color:green'>JtbController syntax OK</p>";
    } else {
        echo "<p style='color:red'>JtbController syntax error: " . implode("\n", $output) . "</p>";
    }
} else {
    echo "<p style='color:red'>JtbController MISSING</p>";
}

echo "<h2>Done</h2>";
