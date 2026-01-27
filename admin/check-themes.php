<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('CMS_ROOT', dirname(__DIR__));
$themesDir = CMS_ROOT . '/themes';

echo "<h1>Themes Check</h1>";

echo "<h2>All directories in /themes:</h2>";
foreach (glob($themesDir . '/*', GLOB_ONLYDIR) as $dir) {
    $name = basename($dir);
    $configFile = $dir . '/theme.json';
    $hasConfig = file_exists($configFile);
    $hasStyle = file_exists($dir . '/assets/css/style.css');
    
    echo "<p><strong>$name</strong> - ";
    echo "theme.json: " . ($hasConfig ? '✅' : '❌') . ", ";
    echo "style.css: " . ($hasStyle ? '✅' : '❌');
    
    if ($hasConfig) {
        $config = json_decode(file_get_contents($configFile), true);
        if ($config) {
            echo " - Title: " . ($config['title'] ?? 'N/A');
        } else {
            echo " - <span style='color:red'>Invalid JSON!</span>";
        }
    }
    echo "</p>";
}
