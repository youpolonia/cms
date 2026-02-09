<?php
/**
 * JTB AI API: Get Schema
 * Returns module schemas for AI integration
 *
 * GET /api/jtb/ai/get-schema
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Require authentication (check both admin_id and user_id like router.php)
if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Authentication required']);
    exit;
}

// Allow GET and POST
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Load AI classes
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-schema.php';

try {
    // Get query parameters
    $moduleSlug = $_GET['module'] ?? $_POST['module'] ?? null;
    $format = $_GET['format'] ?? $_POST['format'] ?? 'full';
    $category = $_GET['category'] ?? $_POST['category'] ?? null;

    // Return specific module schema
    if (!empty($moduleSlug)) {
        $schema = JTB_AI_Schema::exportModuleSchema($moduleSlug);

        if (empty($schema)) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Module not found: ' . $moduleSlug]);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'module' => $schema
        ]);
        exit;
    }

    // Return all schemas or filtered by category
    switch ($format) {
        case 'compact':
            $data = JTB_AI_Schema::getCompactModuleList();
            break;

        case 'content':
            $data = JTB_AI_Schema::getContentModules();
            break;

        case 'by_category':
            $data = JTB_AI_Schema::getModulesByCategory();
            break;

        case 'prompt_context':
            $data = JTB_AI_Schema::generatePromptContext();
            break;

        case 'field_types':
            $data = JTB_AI_Schema::getFieldTypes();
            break;

        case 'layouts':
            $data = JTB_AI_Schema::getColumnLayouts();
            break;

        case 'icons':
            $data = JTB_AI_Schema::getAvailableIcons();
            break;

        case 'full':
        default:
            if (!empty($category)) {
                $allModules = JTB_AI_Schema::getModulesByCategory();
                $data = $allModules[$category] ?? [];
            } else {
                $data = JTB_AI_Schema::exportAllModules();
            }
            break;
    }

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'format' => $format,
        'category' => $category,
        'data' => $data,
        'count' => is_array($data) ? count($data) : strlen($data)
    ]);

} catch (\Exception $e) {
    error_log('JTB AI get-schema error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'An error occurred while fetching schema'
    ]);
}
