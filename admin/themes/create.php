<?php
require_once __DIR__ . '/themebuilder.php';

header('Content-Type: application/json');

try {
    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    if (empty($input['themeName'])) {
        throw new Exception('Theme name is required');
    }

    $themeName = trim($input['themeName']);
    if (!preg_match('/^[a-zA-Z0-9-]{3,50}$/', $themeName)) {
        throw new Exception('Invalid theme name format');
    }

    // Create theme
    $themeBuilder = new ThemeBuilder($themeName);
    $success = $themeBuilder->createTheme();

    if (!$success) {
        throw new Exception('Failed to create theme - it may already exist or system error occurred');
    }

    echo json_encode([
        'success' => true
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
