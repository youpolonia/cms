<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

require_once __DIR__ . '/../../includes/localization/languagemanager.php';
require_once __DIR__ . '/middleware/csrf.php';

header('Content-Type: application/json');
verifyCSRFToken();
try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('Invalid JSON input');
    }

    // Validate input
    $requiredFields = ['name', 'locale'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            throw new InvalidArgumentException("Field '$field' is required");
        }
    }

    // Prepare language data
    $languageData = [
        'name' => $input['name'],
        'locale' => $input['locale'],
        'is_default' => !empty($input['is_default'])
    ];

    // Add new language
    $success = CMS\Localization\LanguageManager::addLanguage($languageData);

    if (!$success) {
        throw new RuntimeException('Failed to add language');
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Language added successfully'
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (RuntimeException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred'
    ]);
}
