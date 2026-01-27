<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/admin/models/settingsmodel.php';

$db = \core\Database::connection();
$settingsModel = new SettingsModel();

$pexelsKey = $settingsModel->getValue('pexels_api_key', '');

echo "Pexels API Key: " . ($pexelsKey ? substr($pexelsKey, 0, 10) . '...' : 'NOT SET') . "\n";

// Test the API
if ($pexelsKey) {
    $ch = curl_init('https://api.pexels.com/v1/search?query=dog&per_page=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $pexelsKey]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: " . $httpCode . "\n";
    echo "Response: " . substr($response, 0, 200) . "\n";
}
