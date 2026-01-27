<?php
require_once __DIR__ . '/../../../core/services/translationservice.php';
require_once __DIR__ . '/../../../core/services/languagedetector.php';

header('Content-Type: application/json');

try {
    // Get request method and content type
    $method = $_SERVER['REQUEST_METHOD'];
    $contentType = $_SERVER['CONTENT_TYPE'] ?? 'application/json';

    // CSRF validation for POST requests
    if ($method === 'POST') {
        require_once __DIR__ . '/../../../core/csrf.php';
        csrf_validate_or_403();
    }


    // Handle different request methods
    if ($method === 'POST') {
        // Parse input based on content type
        $input = [];
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        // Validate input
        if (empty($input['text'])) {
            throw new Exception('Missing required field: text');
        }

        // Process translation request
        $targetLang = $input['target_language'] ?? 'en';
        $sourceLang = $input['source_language'] ?? null;
        $modelPreference = $input['model_preference'] ?? null;
        $options = $input['options'] ?? [];

        // Handle batch or single translation
        if (is_array($input['text'])) {
            $results = [];
            foreach ($input['text'] as $text) {
                $results[] = TranslationService::translate(
                    $text,
                    $targetLang,
                    $sourceLang,
                    $modelPreference,
                    $options
                );
            }
            echo json_encode(['translations' => $results]);
        } else {
            $result = TranslationService::translate(
                $input['text'],
                $targetLang,
                $sourceLang,
                $modelPreference,
                $options
            );
            echo json_encode($result);
        }
    } elseif ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'languages') {
        // Return supported languages
        echo json_encode([
            'languages' => TranslationService::getSupportedLanguages()
        ]);
    } else {
        throw new Exception('Invalid request method or action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'supported_languages' => TranslationService::getSupportedLanguages()
    ]);
}
