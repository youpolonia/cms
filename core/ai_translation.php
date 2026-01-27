<?php

if (!defined('CMS_ROOT')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

require_once CMS_ROOT . '/core/ai_hf.php';

function ai_translation_supported_languages(): array
{
    return [
        'Auto detect',
        'English',
        'Polish',
        'German',
        'French',
        'Spanish',
        'Italian',
        'Portuguese',
        'Dutch',
        'Norwegian',
        'Swedish',
        'Danish',
        'Finnish',
        'Czech',
        'Russian',
        'Ukrainian',
        'Japanese',
        'Chinese',
        'Korean'
    ];
}

function ai_translation_translate(array $params): array
{
    $text = trim($params['text'] ?? '');
    if ($text === '') {
        return ['ok' => false, 'error' => 'EMPTY_TEXT', 'message' => 'No text provided for translation'];
    }

    $sourceLang = trim($params['source_language'] ?? '');
    if ($sourceLang === '' || $sourceLang === 'Auto detect') {
        $sourceLang = 'auto';
    }

    $targetLang = trim($params['target_language'] ?? '');
    if ($targetLang === '') {
        $targetLang = 'English';
    }

    $tone = trim($params['tone'] ?? '');
    $context = trim($params['context'] ?? '');

    $prompt = "Translate the following text";
    if ($sourceLang !== 'auto') {
        $prompt .= " from {$sourceLang}";
    }
    $prompt .= " to {$targetLang}.";

    if ($tone !== '') {
        $prompt .= " Use a {$tone} tone.";
    }
    if ($context !== '') {
        $prompt .= " Context: {$context}.";
    }

    $prompt .= " Preserve the meaning and formatting (paragraphs, bullet points). Provide ONLY the translation, no explanations.\n\nText to translate:\n{$text}";

    $result = ai_hf_generate_text($prompt);

    if (!$result['ok']) {
        return ['ok' => false, 'error' => 'AI_ERROR', 'message' => $result['error'] ?? 'Translation failed'];
    }

    return [
        'ok' => true,
        'source_language' => $sourceLang,
        'target_language' => $targetLang,
        'tone' => $tone !== '' ? $tone : null,
        'context' => $context !== '' ? $context : null,
        'source_text' => $text,
        'translated_text' => $result['text'] ?? ''
    ];
}
