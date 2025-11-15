<?php

require_once __DIR__ . '/../core/loggerfactory.php';
require_once __DIR__.'/../includes/security/authservicewrapper.php';
declare(strict_types=1);

require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/version_functions.php';

AuthServiceWrapper::checkAuth();

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Verify CSRF token for POST requests
    if ($method === 'POST') {
        verify_csrf_token();
    }

    switch ($action) {
        case 'list':
            handleVersionList();
            break;
        case 'create':
            handleVersionCreate();
            break;
        case 'restore':
            handleVersionRestore();
            break;
        case 'diff':
            handleVersionDiff();
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function handleVersionList(): void {
    check_permission('version_view');
    
    $versions = VersionFunctions::getAllVersions();
    echo json_encode([
        'success' => true,
        'versions' => $versions
    ]);
}

function handleVersionCreate(): void {
    check_permission('version_create');
    
    $versionId = VersionFunctions::createVersion();
    audit_log('Version created', ['version_id' => $versionId]); // Still works via updated audit_log()
    LoggerFactory::create()->warning('Version created', ['version_id' => $versionId]);
    
    echo json_encode([
        'success' => true,
        'version_id' => $versionId
    ]);
}

function handleVersionRestore(): void {
    check_permission('version_restore');
    
    $versionId = (int)($_POST['id'] ?? 0);
    if ($versionId <= 0) {
        throw new InvalidArgumentException('Invalid version ID');
    }
    
    VersionFunctions::restoreVersion($versionId);
    audit_log('Version restored', ['version_id' => $versionId]); // Still works via updated audit_log()
    LoggerFactory::create()->warning('Version restored', ['version_id' => $versionId]);
    
    echo json_encode(['success' => true]);
}

function handleVersionDiff(): void {
    check_permission('version_compare');
    
    $version1 = (int)($_GET['version1'] ?? 0);
    $version2 = (int)($_GET['version2'] ?? 0);
    
    if ($version1 <= 0 || $version2 <= 0) {
        throw new InvalidArgumentException('Invalid version IDs');
    }
    
    $diff = VersionFunctions::getVersionDiff($version1, $version2);
    echo json_encode([
        'success' => true,
        'diff' => $diff
    ]);
}
