<?php
/**
 * JTB API - Get Objects for Condition Type
 * GET /api/jtb/conditions-objects?type=single_post
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Authentication is already checked in router.php

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get page type
$pageType = $_GET['type'] ?? null;

if (!$pageType) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Page type is required']);
    exit;
}

// Validate page type
$validPageTypes = array_keys(JTB_Template_Conditions::getPageTypes());
if (!in_array($pageType, $validPageTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid page type']);
    exit;
}

try {
    // Get objects for the page type
    $objects = JTB_Template_Conditions::getObjectsForType($pageType);

    echo json_encode([
        'success' => true,
        'page_type' => $pageType,
        'objects' => $objects
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
