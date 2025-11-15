<?php
require_once __DIR__ . '/../../core/contentversioningsystem.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/router.php';

// List versions for content
Router::get('/content/{id}/versions', function($request, $response) {
    $contentId = (int)$request->params['id'];
    $userId = Auth::getUserId();
    
    if (!Auth::hasPermission($userId, 'content_view')) {
        return ['error' => 'Unauthorized', 'code' => 403];
    }

    $versions = ContentVersioningSystem::getVersions($request->pdo, $contentId);
    return ['data' => $versions];
});

// Create new version
Router::post('/content/{id}/versions', function($request, $response) {
    $contentId = (int)$request->params['id'];
    $userId = Auth::getUserId();
    $data = $request->data;
    
    if (!Auth::hasPermission($userId, 'content_edit')) {
        return ['error' => 'Unauthorized', 'code' => 403];
    }

    if (empty($data['content']) || empty($data['change_description'])) {
        return ['error' => 'Missing required fields', 'code' => 400];
    }

    $success = ContentVersioningSystem::createVersion(
        $request->pdo,
        $contentId,
        $data['content'],
        $userId,
        $data['change_description']
    );

    return $success
        ? ['status' => 'success']
        : ['error' => 'Version creation failed', 'code' => 500];
});

// Rollback to version
Router::post('/versions/{id}/rollback', function($request, $response) {
    $versionId = (int)$request->params['id'];
    $userId = Auth::getUserId();
    
    if (!Auth::hasPermission($userId, 'content_edit')) {
        return ['error' => 'Unauthorized', 'code' => 403];
    }

    $success = ContentVersioningSystem::rollbackToVersion($request->pdo, $versionId);
    
    return $success
        ? ['status' => 'success']
        : ['error' => 'Rollback failed', 'code' => 500];
});
