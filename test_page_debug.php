<?php
// Test endpoint for page controller
header('Content-Type: text/plain');

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PAGE CONTROLLER TEST ===\n\n";

// Define constants first
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', __DIR__);
}
if (!defined('CMS_APP')) {
    define('CMS_APP', __DIR__ . '/app');
}
if (!defined('CMS_CORE')) {
    define('CMS_CORE', __DIR__ . '/core');
}
if (!defined('CMS_CONFIG')) {
    define('CMS_CONFIG', __DIR__ . '/config');
}

// Load bootstrap
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/app/helpers/functions.php';

// Test database
echo "1. Database connection: ";
try {
    $pdo = db();
    echo "OK\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit;
}

// Test page query
$slug = 'test';
echo "\n2. Looking for page with slug: '$slug'\n";

$stmt = $pdo->prepare("SELECT id, title, slug, status, template FROM pages WHERE slug = ?");
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if ($page) {
    echo "   FOUND!\n";
    echo "   - ID: {$page['id']}\n";
    echo "   - Title: {$page['title']}\n";
    echo "   - Status: {$page['status']}\n";
    echo "   - Template: {$page['template']}\n";
} else {
    echo "   NOT FOUND\n";
}

// Test session
echo "\n3. Session test:\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "   - Session ID: " . session_id() . "\n";
echo "   - user_id: " . ($_SESSION['user_id'] ?? 'not set') . "\n";
echo "   - admin_logged_in: " . ($_SESSION['admin_logged_in'] ?? 'not set') . "\n";

// Test template paths
echo "\n4. Template paths:\n";
$templates = [
    'default' => CMS_APP . '/views/front/page.php',
    'contact' => CMS_APP . '/views/front/page-contact.php',
];
foreach ($templates as $name => $path) {
    echo "   - $name: " . (file_exists($path) ? 'EXISTS' : 'MISSING') . " ($path)\n";
}

// Test theme
echo "\n5. Active theme: " . get_active_theme() . "\n";
echo "   Theme page template: " . theme_path('templates/page.php') . "\n";
echo "   Exists: " . (file_exists(theme_path('templates/page.php')) ? 'YES' : 'NO') . "\n";

echo "\n=== END TEST ===\n";
