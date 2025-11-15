<?php
/**
 * CMS Settings Export
 *
 * Exports all system settings as a downloadable JSON file
 * PHP 8.1+ compatible, FTP-deployable
 */

// Admin session validation
require_once __DIR__ . '/includes/admin_auth.php';
if (!isAdminLoggedIn()) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

// Database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Query system_settings table
$stmt = $pdo->query("SELECT * FROM system_settings");
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert to JSON with pretty print for readability
$json = json_encode($settings, JSON_PRETTY_PRINT);

// Set download headers
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="system_settings_export_' . date('Y-m-d') . '.json"');
header('Content-Length: ' . strlen($json));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Output JSON
echo $json;
exit();
