<?php
/**
 * AI API Endpoint
 * AI-powered content generation via API
 */

require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_content_rewrite.php';

function handle_ai(string $method, ?string $id, ?string $action): void
{
    if ($method !== 'POST') {
        api_error('Method not allowed. Use POST.', 405);
    }

    $endpoint = $id ?? $action ?? '';

    switch ($endpoint) {
        case 'generate':
            ai_generate();
            break;

        case 'rewrite':
            ai_rewrite();
            break;

        case 'seo-analyze':
        case 'seo':
            ai_seo_analyze();
            break;

        case 'summarize':
            ai_summarize();
            break;

        default:
            api_error('Unknown AI endpoint: ' . $endpoint, 404);
    }
}

function ai_generate(): void
{
    $data = get_request_body();

    if (empty($data['prompt'])) {
        api_error('Prompt is required', 400);
    }

    $maxTokens = min(2000, max(50, (int)($data['max_tokens'] ?? 500)));
    $temperature = min(1.0, max(0.1, (float)($data['temperature'] ?? 0.7)));

    $result = ai_hf_generate_text($data['prompt'], [
        'params' => [
            'max_new_tokens' => $maxTokens,
            'temperature' => $temperature,
        ]
    ]);

    if (!$result['ok']) {
        api_error($result['error'] ?? 'AI generation failed', 500);
    }

    api_response([
        'text' => $result['text'],
        'tokens_used' => $result['tokens'] ?? null,
    ]);
}

function ai_rewrite(): void
{
    $data = get_request_body();

    if (empty($data['content'])) {
        api_error('Content is required', 400);
    }

    $mode = $data['mode'] ?? 'paraphrase';
    $tone = $data['tone'] ?? 'neutral';

    $validModes = array_keys(ai_rewrite_get_modes());
    if (!in_array($mode, $validModes)) {
        api_error('Invalid mode. Valid: ' . implode(', ', $validModes), 400);
    }

    $result = ai_rewrite_content($data['content'], $mode, [
        'tone' => $tone,
        'keyword' => $data['keyword'] ?? '',
        'target_length' => $data['target_length'] ?? null,
    ]);

    if (!$result['ok']) {
        api_error($result['error'] ?? 'Rewrite failed', 500);
    }

    api_response([
        'original' => $result['original'],
        'rewritten' => $result['rewritten'],
        'mode' => $result['mode'],
        'original_words' => $result['original_words'],
        'new_words' => $result['new_words'],
        'change_percent' => $result['change_percent'],
    ]);
}

function ai_seo_analyze(): void
{
    $data = get_request_body();

    if (empty($data['content']) && empty($data['url'])) {
        api_error('Content or URL is required', 400);
    }

    $content = $data['content'] ?? '';
    $keyword = $data['keyword'] ?? '';

    // Basic SEO analysis
    $textContent = strip_tags($content);
    $wordCount = str_word_count($textContent);

    // Heading analysis
    preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $content, $h1);
    preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $content, $h2);

    // Image analysis
    preg_match_all('/<img[^>]*>/i', $content, $images);
    $imagesWithoutAlt = 0;
    foreach ($images[0] as $img) {
        if (!preg_match('/alt=["\'][^"\']+["\']/', $img)) {
            $imagesWithoutAlt++;
        }
    }

    // Keyword density
    $keywordCount = 0;
    $keywordDensity = 0;
    if ($keyword) {
        $keywordCount = substr_count(strtolower($textContent), strtolower($keyword));
        $keywordDensity = $wordCount > 0 ? round(($keywordCount / $wordCount) * 100, 2) : 0;
    }

    // Calculate score
    $score = 100;
    $issues = [];

    if (count($h1[0]) === 0) {
        $score -= 15;
        $issues[] = 'Missing H1 heading';
    } elseif (count($h1[0]) > 1) {
        $score -= 5;
        $issues[] = 'Multiple H1 headings';
    }

    if ($wordCount < 300) {
        $score -= 10;
        $issues[] = 'Thin content (less than 300 words)';
    }

    if ($imagesWithoutAlt > 0) {
        $score -= min(15, $imagesWithoutAlt * 3);
        $issues[] = "{$imagesWithoutAlt} image(s) missing ALT tags";
    }

    if ($keyword && $keywordDensity < 0.5) {
        $score -= 5;
        $issues[] = 'Low keyword density';
    } elseif ($keyword && $keywordDensity > 3) {
        $score -= 5;
        $issues[] = 'Keyword stuffing detected';
    }

    api_response([
        'score' => max(0, $score),
        'word_count' => $wordCount,
        'headings' => [
            'h1' => count($h1[0]),
            'h2' => count($h2[0]),
        ],
        'images' => [
            'total' => count($images[0]),
            'missing_alt' => $imagesWithoutAlt,
        ],
        'keyword' => $keyword ? [
            'count' => $keywordCount,
            'density' => $keywordDensity . '%',
        ] : null,
        'issues' => $issues,
    ]);
}

function ai_summarize(): void
{
    $data = get_request_body();

    if (empty($data['content'])) {
        api_error('Content is required', 400);
    }

    $targetLength = $data['target_length'] ?? 100;

    $result = ai_rewrite_content($data['content'], 'summarize', [
        'target_length' => $targetLength,
    ]);

    if (!$result['ok']) {
        api_error($result['error'] ?? 'Summarization failed', 500);
    }

    api_response([
        'summary' => $result['rewritten'],
        'original_words' => $result['original_words'],
        'summary_words' => $result['new_words'],
    ]);
}
