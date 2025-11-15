<?php
require_once __DIR__ . '/pluginupdatechecker.php';

header('Content-Type: application/json');

function getLatestVersionFromRepository($pluginName) {
    // This would make an API call to plugin repository
    // For now returning a mock version
    return '1.2.0';
}

try {
    // Validate input
    if (empty($_GET['plugin'])) {
        throw new Exception('Plugin name is required');
    }
    if (empty($_GET['current_version'])) {
        throw new Exception('Current version is required');
    }

    $pluginName = htmlspecialchars($_GET['plugin']);
    $currentVersion = htmlspecialchars($_GET['current_version']);

    $latestVersion = getLatestVersionFromRepository($pluginName);

    $checker = new PluginUpdateChecker();
    $updateAvailable = $checker->checkForUpdates($pluginName, $currentVersion, $latestVersion);

    echo json_encode([
        'status' => 'success',
        'update_available' => $updateAvailable,
        'current_version' => $currentVersion,
        'latest_version' => $latestVersion,
        'plugin' => $pluginName
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
