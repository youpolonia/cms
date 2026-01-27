<?php
/**
 * AI Toolkit - Generic text processing utility
 * Provides multiple AI operations on arbitrary input text
 * Uses existing AI infrastructure from core/ai_content.php
 */

// Detect CMS_ROOT if needed
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/ai_content.php';

/**
 * Run AI Toolkit operation on input text
 *
 * @param string $mode Operation mode:
 *   - 'summarize': Condense text into concise summary
 *   - 'expand': Add more detail to text
 *   - 'rewrite': Rewrite in clearer, more natural style
 *   - 'simplify': Simplify for easier understanding
 *   - 'bullet_points': Convert to bullet point list
 *   - 'title': Generate compelling title
 *   - 'meta_description': Generate SEO meta description
 *   - 'translate_en': Translate to English
 *   - 'translate_pl': Translate to Polish
 * @param string $input Input text to process
 * @param array $options Optional parameters (e.g., 'target_language')
 * @return array Result with keys:
 *   - 'ok': bool - Success status
 *   - 'output': string|null - Processed text on success
 *   - 'error': string|null - Error message on failure
 */
function ai_toolkit_run(string $mode, string $input, array $options = []): array {
    // Validate input
    $input = trim($input);
    if ($input === '') {
        return [
            'ok' => false,
            'output' => null,
            'error' => 'Input text is required.',
        ];
    }

    // Load AI configuration
    $config = ai_config_load();
    $aiEnabled = !empty($config['provider']) && (!empty($config['model']) || !empty($config['base_url']));

    // Build prompt topic based on mode
    $topic = ai_toolkit_build_topic($mode);
    if ($topic === null) {
        return [
            'ok' => false,
            'output' => null,
            'error' => 'Invalid operation mode: ' . $mode,
        ];
    }

    // Combine topic with input
    $prompt = $topic . "\n\n---\nTEXT:\n" . $input;

    // Determine language parameter
    $language = 'en'; // default
    if ($mode === 'translate_en') {
        $language = 'en';
    } elseif ($mode === 'translate_pl') {
        $language = 'pl';
    }

    // Try AI generation if enabled
    if ($aiEnabled) {
        try {
            $result = ai_content_generate([
                'topic' => $prompt,
                'keywords' => '',
                'language' => $language,
                'tone' => '',
                'length_hint' => 'medium',
            ]);

            // Success - return AI-generated content
            if ($result['ok'] === true && !empty($result['content'])) {
                return [
                    'ok' => true,
                    'output' => (string)$result['content'],
                    'error' => null,
                ];
            }

            // AI generation failed - log and fall through to fallback
            $errorMsg = $result['error'] ?? 'unknown error';
            error_log('[AI_TOOLKIT] ai_content_generate failed: ' . $errorMsg);
        } catch (\Throwable $e) {
            // Exception during AI generation - log and fall through to fallback
            error_log('[AI_TOOLKIT] Exception: ' . $e->getMessage());
        }
    }

    // Fallback: Simple text processing without AI
    return ai_toolkit_fallback($mode, $input);
}

/**
 * Build prompt topic string for given mode
 *
 * @param string $mode Operation mode
 * @return string|null Topic string or null if mode is invalid
 */
function ai_toolkit_build_topic(string $mode): ?string {
    $topics = [
        'summarize' => 'Summarize the following text in a concise way:',
        'expand' => 'Expand the following text with more detail:',
        'rewrite' => 'Rewrite the following text in a clearer, more natural style:',
        'simplify' => 'Simplify the following text so it is easy to understand:',
        'bullet_points' => 'Convert the following text into clear bullet points:',
        'title' => 'Generate a compelling title for the following text:',
        'meta_description' => 'Generate an SEO meta description (max ~160 characters) for the following text:',
        'translate_en' => 'Translate the following text into English:',
        'translate_pl' => 'Translate the following text into Polish:',
    ];

    return $topics[$mode] ?? null;
}

/**
 * Generate fallback output when AI is unavailable or failed
 *
 * @param string $mode Operation mode
 * @param string $input Input text
 * @return array Result array
 */
function ai_toolkit_fallback(string $mode, string $input): array {
    try {
        $output = null;

        switch ($mode) {
            case 'summarize':
                // Return first 2-3 sentences or first 300 characters
                $sentences = preg_split('/(?<=[.!?])\s+/u', $input, 3);
                if (count($sentences) >= 2) {
                    $output = trim($sentences[0] . ' ' . $sentences[1]);
                } else {
                    $output = mb_substr($input, 0, 300, 'UTF-8');
                    if (mb_strlen($input, 'UTF-8') > 300) {
                        $output .= '...';
                    }
                }
                break;

            case 'expand':
                // Append static expansion note
                $output = $input . "\n\n[More detail can be added here.]";
                break;

            case 'rewrite':
            case 'simplify':
                // No change - return original
                $output = $input;
                break;

            case 'bullet_points':
                // Split by sentences or line breaks into bullet list
                $lines = preg_split('/(?:[.!?]\s+|\n)/u', $input);
                $bullets = [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line !== '' && mb_strlen($line, 'UTF-8') > 10) {
                        $bullets[] = '- ' . $line;
                    }
                }
                $output = implode("\n", $bullets);
                if ($output === '') {
                    $output = '- ' . $input;
                }
                break;

            case 'title':
                // Return first 60-80 characters
                $output = mb_substr($input, 0, 70, 'UTF-8');
                // Try to break at word boundary
                if (mb_strlen($input, 'UTF-8') > 70) {
                    $lastSpace = mb_strrpos($output, ' ', 0, 'UTF-8');
                    if ($lastSpace !== false && $lastSpace > 40) {
                        $output = mb_substr($output, 0, $lastSpace, 'UTF-8');
                    }
                    $output .= '...';
                }
                break;

            case 'meta_description':
                // Return first 150-160 characters
                $output = mb_substr($input, 0, 155, 'UTF-8');
                // Try to break at word boundary
                if (mb_strlen($input, 'UTF-8') > 155) {
                    $lastSpace = mb_strrpos($output, ' ', 0, 'UTF-8');
                    if ($lastSpace !== false && $lastSpace > 120) {
                        $output = mb_substr($output, 0, $lastSpace, 'UTF-8');
                    }
                    $output .= '...';
                }
                break;

            case 'translate_en':
            case 'translate_pl':
                // No real translation available
                $output = $input . "\n\n[Translation unavailable: AI not configured]";
                break;

            default:
                return [
                    'ok' => false,
                    'output' => null,
                    'error' => 'Unknown operation mode: ' . $mode,
                ];
        }

        return [
            'ok' => true,
            'output' => $output,
            'error' => null,
        ];
    } catch (\Throwable $e) {
        error_log('[AI_TOOLKIT] Fallback exception: ' . $e->getMessage());
        return [
            'ok' => false,
            'output' => null,
            'error' => 'AI Toolkit operation failed. Please try again later.',
        ];
    }
}
