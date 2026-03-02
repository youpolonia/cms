<?php
/**
 * Cache Clear Utility — Admin only
 */
if (!defined("CMS_ROOT")) {
    define("CMS_ROOT", realpath(__DIR__ . "/.."));
}
require_once CMS_ROOT . "/config.php";
require_once CMS_ROOT . "/core/session_boot.php";

// Auth check — must be logged in as admin
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION["admin_id"])) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

// Clear OPcache
$opcacheResult = "Not available";
if (function_exists("opcache_reset")) {
    $opcacheResult = opcache_reset() ? "Cleared" : "Failed";
}

// Clear file cache if exists
$cacheDir = CMS_ROOT . "/cache";
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . "/*.cache");
    $cleared = 0;
    foreach ($files as $file) {
        if (is_file($file) && @unlink($file)) {
            $cleared++;
        }
    }
}

// Redirect back with message
$referer = $_SERVER["HTTP_REFERER"] ?? "/admin";
header("Location: " . $referer . (strpos($referer, "?") !== false ? "&" : "?") . "cache_cleared=1");
exit;
