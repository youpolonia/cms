<?php
/**
 * AI SEO Analysis API Endpoint
 * Uses OpenAI for inline SEO analysis in Article Editor
 */

define('CMS_ROOT', realpath(__DIR__ . '/../..'));

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';

cms_session_start('admin');

header('Content-Type: application/json');

// Check auth
if (empty($_SESSION['admin_id']) && empty($_SESSION['admin_authenticated'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// CSRF validation
$csrfToken = $input['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (empty($csrfToken) || empty($sessionToken) || !hash_equals($sessionToken, $csrfToken)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Load AI settings
$aiSettingsFile = CMS_ROOT . '/config/ai_settings.json';
if (!file_exists($aiSettingsFile)) {
    echo json_encode(['ok' => false, 'error' => 'AI not configured']);
    exit;
}

$aiSettings = json_decode(file_get_contents($aiSettingsFile), true);
$apiKey = $aiSettings['providers']['openai']['api_key'] ?? '';
$model = $aiSettings['providers']['openai']['default_model'] ?? 'gpt-5.2';

if (empty($apiKey)) {
    echo json_encode(['ok' => false, 'error' => 'OpenAI API key not configured']);
    exit;
}

// Validate required fields
$focusKeyword = trim($input['focus_keyword'] ?? '');
$contentHtml = trim($input['content_html'] ?? '');

if (empty($focusKeyword)) {
    echo json_encode(['ok' => false, 'error' => 'Focus keyword is required']);
    exit;
}

if (empty($contentHtml)) {
    echo json_encode(['ok' => false, 'error' => 'Content is required for analysis']);
    exit;
}

// Build analysis data
$title = trim($input['title'] ?? '');
$secondaryKeywords = trim($input['secondary_keywords'] ?? '');
$contentType = trim($input['content_type'] ?? 'blog_post');
$language = trim($input['language'] ?? 'en');

// Truncate content if too long
$contentText = strip_tags($contentHtml);
if (strlen($contentText) > 8000) {
    $contentText = substr($contentText, 0, 8000) . '...';
}

// Build prompt
$prompt = "You are an expert SEO consultant. Analyze this content and provide SEO recommendations.

Title: {$title}
Focus Keyword: {$focusKeyword}
Secondary Keywords: {$secondaryKeywords}
Content Type: {$contentType}
Language: {$language}

Content:
{$contentText}

Return ONLY a valid JSON object (no markdown, no backticks) with this exact structure:
{
  \"health_score\": 0-100,
  \"summary\": \"Brief SEO assessment (1-2 sentences)\",
  \"content_score_breakdown\": {
    \"word_count\": {\"score\": 0-100, \"note\": \"explanation\"},
    \"headings\": {\"score\": 0-100, \"note\": \"explanation\"},
    \"keywords\": {\"score\": 0-100, \"note\": \"explanation\"},
    \"structure\": {\"score\": 0-100, \"note\": \"explanation\"},
    \"media\": {\"score\": 0-100, \"note\": \"explanation\"},
    \"links\": {\"score\": 0-100, \"note\": \"explanation\"}
  },
  \"keyword_difficulty\": [
    {\"keyword\": \"term\", \"difficulty\": 0-100, \"level\": \"easy|medium|hard\"}
  ],
  \"quick_wins\": [\"quick improvement 1\", \"quick improvement 2\", \"quick improvement 3\"],
  \"actionable_tasks\": [
    {\"priority\": \"high|medium|low\", \"task\": \"specific action to take\", \"category\": \"content|technical|on-page\"}
  ],
  \"on_page_checks\": {
    \"meta_suggestions\": {
      \"recommended_title\": \"optimal SEO title (max 60 chars)\",
      \"recommended_meta_description\": \"optimal meta description (max 155 chars)\"
    }
  }
}

Provide 3-5 quick wins, 5-8 actionable tasks, and analyze the focus keyword plus 2-3 related keywords.";

// Newer models (o-series, GPT-5.x, GPT-4.1.x) use max_completion_tokens
$useNewTokenParam = preg_match('/^(o[1-4]|gpt-[45]\.|gpt-5$)/', $model);

$payload = [
    'model' => $model,
    'messages' => [
        ['role' => 'system', 'content' => 'You are an SEO expert. Always respond with valid JSON only, no markdown formatting.'],
        ['role' => 'user', 'content' => $prompt]
    ]
];

if ($useNewTokenParam) {
    $payload['max_completion_tokens'] = 2000;
} else {
    $payload['max_tokens'] = 2000;
    $payload['temperature'] = 0.3;
}

// Call OpenAI API
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['ok' => false, 'error' => 'Connection error: ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    $error = json_decode($response, true);
    $errorMessage = $error['error']['message'] ?? 'API error (HTTP ' . $httpCode . ')';
    echo json_encode(['ok' => false, 'error' => $errorMessage]);
    exit;
}

$result = json_decode($response, true);

// Extract content from various response formats (GPT-4o, GPT-5.x, etc.)
$content = null;
if (isset($result['choices'][0]['message']['content'])) {
    $content = $result['choices'][0]['message']['content'];
} elseif (isset($result['output_text'])) {
    $content = $result['output_text'];
} elseif (isset($result['output']) && is_array($result['output'])) {
    foreach ($result['output'] as $item) {
        if (isset($item['content']) && is_array($item['content'])) {
            foreach ($item['content'] as $c) {
                if (isset($c['text'])) { $content = $c['text']; break 2; }
            }
        }
    }
} elseif (isset($result['choices'][0]['text'])) {
    $content = $result['choices'][0]['text'];
}

if (empty($content)) {
    echo json_encode(['ok' => false, 'error' => 'Empty response from AI']);
    exit;
}

// Clean and parse JSON
$content = trim($content);
$content = preg_replace('/^```json\s*/i', '', $content);
$content = preg_replace('/^```\s*/i', '', $content);
$content = preg_replace('/\s*```$/i', '', $content);
$content = trim($content);

$report = json_decode($content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON from AI: ' . json_last_error_msg()]);
    exit;
}

// Return analysis results
echo json_encode([
    'ok' => true,
    'report' => $report
]);
