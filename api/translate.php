<?php
require_once __DIR__ . '/../core/translationservice.php';
require_once __DIR__ . '/../core/languagedetector.php';
require_once __DIR__ . '/../core/responsehandler.php';

header('Content-Type: application/json');

try {
    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('Invalid JSON input');
    }

    $text = $input['text'] ?? '';
    $targetLang = $input['target_lang'] ?? '';
    $sourceLang = $input['source_lang'] ?? null;
    $model = $input['model'] ?? null;

    if (empty($text) || empty($targetLang)) {
        throw new InvalidArgumentException('Missing required parameters');
    }

    // Initialize services
    $translationService = new TranslationService();
    $languageDetector = new LanguageDetector();

    // Detect language if not provided
    if (empty($sourceLang)) {
        $sourceLang = $languageDetector->detectLanguage($text);
    }

    // Perform translation
    $translated = $translationService->translate(
        $text,
        $targetLang,
        $sourceLang,
        $model
    );

    ResponseHandler::success([
        'translated_text' => $translated,
        'source_lang' => $sourceLang,
        'target_lang' => $targetLang
    ]);
} catch (Exception $e) {
    ResponseHandler::error($e->getMessage(), $e->getCode() ?: 400);
}
