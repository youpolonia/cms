<?php
/**
 * AI ALT Tag Generator
 * Generate SEO-friendly ALT tags for images using AI
 *
 * Features:
 * - Single image ALT generation
 * - Bulk ALT generation for all images
 * - Context-aware descriptions
 * - Keyword optimization
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/media_library.php';

/**
 * Extract meaningful words from filename
 *
 * @param string $filename Image filename
 * @return string Cleaned filename words
 */
function ai_alt_clean_filename(string $filename): string
{
    // Remove extension
    $name = preg_replace('/\.[^.]+$/', '', $filename);

    // Replace separators with spaces
    $name = str_replace(['-', '_', '.', '+'], ' ', $name);

    // Remove numbers at start (like timestamps)
    $name = preg_replace('/^\d+\s*/', '', $name);

    // Remove common prefixes
    $prefixes = ['img', 'image', 'photo', 'pic', 'screenshot', 'screen', 'dsc', 'img_', 'photo_'];
    foreach ($prefixes as $prefix) {
        $name = preg_replace('/^' . preg_quote($prefix, '/') . '\s*/i', '', $name);
    }

    // Remove file size indicators
    $name = preg_replace('/\b\d+x\d+\b/', '', $name);
    $name = preg_replace('/\b(small|medium|large|thumb|thumbnail)\b/i', '', $name);

    // Clean up whitespace
    $name = preg_replace('/\s+/', ' ', $name);
    $name = trim($name);

    return $name;
}

/**
 * Generate ALT tag using AI
 *
 * @param string $filename Image filename
 * @param string $context Additional context (page title, etc.)
 * @param string $keyword Optional focus keyword
 * @return array Result with ok, alt_text, or error
 */
function ai_alt_generate(string $filename, string $context = '', string $keyword = ''): array
{
    $cleanedName = ai_alt_clean_filename($filename);

    // If filename provides no info, use generic approach
    if (empty($cleanedName) || strlen($cleanedName) < 3) {
        $cleanedName = 'image';
    }

    $prompt = "Generate a concise, descriptive ALT tag for an image.

Image filename: {$filename}
Extracted keywords: {$cleanedName}";

    if (!empty($context)) {
        $prompt .= "\nPage context: {$context}";
    }

    if (!empty($keyword)) {
        $prompt .= "\nFocus keyword to include naturally: {$keyword}";
    }

    $prompt .= "

Requirements:
- 5-15 words maximum
- Descriptive and specific
- Do NOT start with 'Image of', 'Picture of', 'Photo of'
- Include the focus keyword naturally if provided
- Be SEO-friendly
- Return ONLY the ALT text, nothing else (no quotes, no explanation)";

    $result = ai_hf_generate_text($prompt, [
        'params' => [
            'max_new_tokens' => 60,
            'temperature' => 0.7,
        ]
    ]);

    if (!$result['ok']) {
        // Fallback: generate from filename
        $fallback = ai_alt_generate_fallback($cleanedName, $keyword);
        return [
            'ok' => true,
            'alt_text' => $fallback,
            'source' => 'fallback',
        ];
    }

    $altText = trim($result['text']);
    $altText = trim($altText, '"\'');

    // Remove common AI prefixes if present
    $altText = preg_replace('/^(Image of|Picture of|Photo of|A photo of|An image of)\s*/i', '', $altText);

    // Truncate if too long
    if (str_word_count($altText) > 20) {
        $words = explode(' ', $altText);
        $altText = implode(' ', array_slice($words, 0, 15));
    }

    return [
        'ok' => true,
        'alt_text' => $altText,
        'source' => 'ai',
    ];
}

/**
 * Generate fallback ALT from filename
 *
 * @param string $cleanedName Cleaned filename
 * @param string $keyword Optional keyword
 * @return string Generated ALT text
 */
function ai_alt_generate_fallback(string $cleanedName, string $keyword = ''): string
{
    $alt = ucfirst($cleanedName);

    if (!empty($keyword) && stripos($alt, $keyword) === false) {
        $alt = $keyword . ' - ' . $alt;
    }

    return $alt;
}

/**
 * Get all images from media library that need ALT tags
 *
 * @return array Images without ALT tags
 */
function ai_alt_get_missing(): array
{
    $index = media_library_load_index();
    $missing = [];

    foreach ($index as $id => $entry) {
        $alt = trim($entry['alt'] ?? '');
        $mime = $entry['mime'] ?? '';

        // Only process images
        if (strpos($mime, 'image/') !== 0) {
            continue;
        }

        if (empty($alt)) {
            $missing[] = [
                'id' => $id,
                'path' => $entry['path'] ?? '',
                'basename' => $entry['basename'] ?? '',
                'mime' => $mime,
            ];
        }
    }

    return $missing;
}

/**
 * Update ALT tag in media library
 *
 * @param string $mediaId Media ID
 * @param string $altText New ALT text
 * @return bool Success
 */
function ai_alt_update(string $mediaId, string $altText): bool
{
    $index = media_library_load_index();

    if (!isset($index[$mediaId])) {
        return false;
    }

    $index[$mediaId]['alt'] = $altText;
    $index[$mediaId]['updated'] = gmdate('Y-m-d H:i:s');

    return media_library_save_index($index);
}

/**
 * Generate and save ALT for single image
 *
 * @param string $mediaId Media ID
 * @param string $context Optional context
 * @param string $keyword Optional keyword
 * @return array Result
 */
function ai_alt_generate_and_save(string $mediaId, string $context = '', string $keyword = ''): array
{
    $index = media_library_load_index();

    if (!isset($index[$mediaId])) {
        return ['ok' => false, 'error' => 'Media not found'];
    }

    $entry = $index[$mediaId];
    $filename = $entry['basename'] ?? basename($entry['path'] ?? '');

    $result = ai_alt_generate($filename, $context, $keyword);

    if (!$result['ok']) {
        return $result;
    }

    $saved = ai_alt_update($mediaId, $result['alt_text']);

    if (!$saved) {
        return ['ok' => false, 'error' => 'Failed to save ALT tag'];
    }

    return [
        'ok' => true,
        'media_id' => $mediaId,
        'alt_text' => $result['alt_text'],
        'source' => $result['source'],
    ];
}

/**
 * Bulk generate ALT tags for all images missing them
 *
 * @param string $keyword Optional global keyword
 * @param int $limit Max images to process (0 = all)
 * @return array Results summary
 */
function ai_alt_bulk_generate(string $keyword = '', int $limit = 50): array
{
    $missing = ai_alt_get_missing();

    if (empty($missing)) {
        return [
            'ok' => true,
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'message' => 'All images already have ALT tags',
        ];
    }

    // Apply limit
    if ($limit > 0 && count($missing) > $limit) {
        $missing = array_slice($missing, 0, $limit);
    }

    $success = 0;
    $failed = 0;
    $results = [];

    foreach ($missing as $image) {
        $result = ai_alt_generate_and_save($image['id'], '', $keyword);

        if ($result['ok']) {
            $success++;
            $results[] = [
                'id' => $image['id'],
                'filename' => $image['basename'],
                'alt' => $result['alt_text'],
                'status' => 'success',
            ];
        } else {
            $failed++;
            $results[] = [
                'id' => $image['id'],
                'filename' => $image['basename'],
                'error' => $result['error'] ?? 'Unknown error',
                'status' => 'failed',
            ];
        }

        // Rate limiting - 0.3 second delay between API calls
        usleep(300000);
    }

    return [
        'ok' => true,
        'processed' => count($missing),
        'success' => $success,
        'failed' => $failed,
        'results' => $results,
    ];
}

/**
 * Get statistics about ALT tags in media library
 *
 * @return array Statistics
 */
function ai_alt_get_stats(): array
{
    $index = media_library_load_index();

    $totalImages = 0;
    $withAlt = 0;
    $withoutAlt = 0;

    foreach ($index as $entry) {
        $mime = $entry['mime'] ?? '';

        if (strpos($mime, 'image/') !== 0) {
            continue;
        }

        $totalImages++;
        $alt = trim($entry['alt'] ?? '');

        if (!empty($alt)) {
            $withAlt++;
        } else {
            $withoutAlt++;
        }
    }

    $coverage = $totalImages > 0 ? round(($withAlt / $totalImages) * 100) : 100;

    return [
        'total_images' => $totalImages,
        'with_alt' => $withAlt,
        'without_alt' => $withoutAlt,
        'coverage_percent' => $coverage,
        'status' => $coverage >= 90 ? 'good' : ($coverage >= 70 ? 'fair' : 'needs_work'),
    ];
}

/**
 * Suggest ALT improvements for existing tags
 *
 * @param string $currentAlt Current ALT tag
 * @param string $filename Image filename
 * @return array Suggestion result
 */
function ai_alt_suggest_improvement(string $currentAlt, string $filename): array
{
    if (empty(trim($currentAlt))) {
        return ai_alt_generate($filename);
    }

    // Check if current ALT is too short or generic
    $wordCount = str_word_count($currentAlt);
    $isGeneric = preg_match('/^(image|photo|picture|img)\d*$/i', $currentAlt);

    if ($wordCount >= 5 && !$isGeneric) {
        return [
            'ok' => true,
            'needs_improvement' => false,
            'current' => $currentAlt,
            'message' => 'Current ALT tag is adequate',
        ];
    }

    $newAlt = ai_alt_generate($filename);

    return [
        'ok' => true,
        'needs_improvement' => true,
        'current' => $currentAlt,
        'suggested' => $newAlt['alt_text'] ?? '',
        'reason' => $isGeneric ? 'Current ALT is too generic' : 'Current ALT is too short',
    ];
}
