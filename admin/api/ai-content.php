<?php
/**
 * AI Content API Endpoint
 * Handles AI-powered content generation for article editor
 * Pure PHP, no CLI, FTP-only compatible
 */

declare(strict_types=1);

define('CMS_ROOT', realpath(__DIR__ . '/../..'));

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

header('Content-Type: application/json');

// Check auth (supports both MVC and legacy session vars)
if (empty($_SESSION['admin_id']) && empty($_SESSION['admin_authenticated'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get input - support both JSON and form data
$input = [];
$rawInput = file_get_contents('php://input');
if (!empty($rawInput)) {
    $jsonInput = json_decode($rawInput, true);
    if (is_array($jsonInput)) {
        $input = $jsonInput;
    }
}
if (empty($input)) {
    $input = $_POST;
}

// Validate CSRF
$csrfToken = $input['csrf_token'] ?? '';
if (!csrf_validate($csrfToken)) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$action = $input['action'] ?? '';
$text = $input['text'] ?? '';

if (empty($action)) {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    exit;
}

// Load AI settings
$aiSettingsFile = CMS_ROOT . '/config/ai_settings.json';
if (!file_exists($aiSettingsFile)) {
    echo json_encode(['success' => false, 'error' => 'AI not configured. Go to Settings to add API key.']);
    exit;
}

$aiSettings = json_decode(file_get_contents($aiSettingsFile), true);

// Get API key from nested structure: providers.openai.api_key
$apiKey = '';
$model = 'gpt-5.2';

if (isset($aiSettings['providers']['openai']['api_key'])) {
    $apiKey = $aiSettings['providers']['openai']['api_key'];
    $model = $aiSettings['providers']['openai']['default_model'] ?? 'gpt-5.2';
} elseif (isset($aiSettings['api_key'])) {
    $apiKey = $aiSettings['api_key'];
    $model = $aiSettings['model'] ?? 'gpt-5.2';
} elseif (isset($aiSettings['openai_api_key'])) {
    $apiKey = $aiSettings['openai_api_key'];
    $model = $aiSettings['openai_model'] ?? 'gpt-5.2';
}

if (empty($apiKey)) {
    echo json_encode(['success' => false, 'error' => 'OpenAI API key not configured']);
    exit;
}

// Define prompts for each action
$prompts = [
    'generate_title' => "Generate 5 catchy, SEO-optimized article titles for the following content. Return only the titles, one per line:\n\n{$text}",
    'generate_excerpt' => "Write a compelling 2-3 sentence excerpt/summary for SEO purposes for this article:\n\n{$text}",
    'generate_meta' => "Write an SEO-optimized meta description (max 155 characters) for this article:\n\n{$text}",
    'generate_keywords' => "Extract 5-8 relevant SEO keywords from this article, comma-separated:\n\n{$text}",
    'generate_focus_keyword' => "Analyze this content and suggest the single best focus keyword (2-4 words) for SEO optimization. Return ONLY the keyword phrase, nothing else:\n\n{$text}",
    'generate_faq' => "Based on this article content, generate 5 relevant FAQ questions and answers. Format each as:\nQ: [question]\nA: [answer]\n\nContent:\n{$text}",
    'generate_cta' => "Generate a compelling call-to-action for this article. Return exactly 3 lines:\nHeadline: [catchy headline]\nDescription: [1-2 sentence description]\nButton: [button text]\n\nContent:\n{$text}",
    'generate_testimonial' => "Generate a realistic customer testimonial related to this topic. Return exactly 3 lines:\nQuote: [testimonial quote in first person]\nName: [realistic full name]\nTitle: [job title at company]\n\n{$text}",
    'generate_image_alt' => "Based on this article content, generate a concise, descriptive alt text (max 125 characters) for its featured image. Return ONLY the alt text, nothing else:\n\n{$text}",
    'generate_image_title' => "Based on this article content, generate a short, engaging image title (max 60 characters) for its featured image. Return ONLY the title, nothing else:\n\n{$text}",
    'improve_content' => "Improve this text to be more engaging, clear, and SEO-friendly while keeping the same meaning:\n\n{$text}",
    'expand_content' => "Expand this text with more details, examples, and explanations:\n\n{$text}",
    'simplify' => "Simplify this text to be easier to read (aim for 8th grade reading level):\n\n{$text}",
    'translate_en' => "Translate this text to English:\n\n{$text}",
    'translate_pl' => "Translate this text to Polish:\n\n{$text}",
    'translate_de' => "Translate this text to German:\n\n{$text}",
    'fix_grammar' => "Fix any grammar, spelling, and punctuation errors in this text:\n\n{$text}",
    'make_formal' => "Rewrite this text in a formal, professional tone:\n\n{$text}",
    'make_casual' => "Rewrite this text in a casual, friendly tone:\n\n{$text}",
];

if (!isset($prompts[$action])) {
    echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
    exit;
}

$prompt = $prompts[$action];

// Newer models (o-series, GPT-5.x, GPT-4.1.x) use max_completion_tokens
$useNewTokenParam = preg_match('/^(o[1-4]|gpt-[45]\.|gpt-5$)/', $model);

$payload = [
    'model' => $model,
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful content writing assistant specializing in SEO and engaging content. Be concise and direct.'],
        ['role' => 'user', 'content' => $prompt]
    ]
];

if ($useNewTokenParam) {
    $payload['max_completion_tokens'] = 1000;
} else {
    $payload['max_tokens'] = 1000;
    $payload['temperature'] = 0.7;
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
    echo json_encode(['success' => false, 'error' => 'Connection error: ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    $error = json_decode($response, true);
    $errorMessage = $error['error']['message'] ?? 'API error (HTTP ' . $httpCode . ')';
    echo json_encode(['success' => false, 'error' => $errorMessage]);
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
    echo json_encode(['success' => false, 'error' => 'Empty response from AI']);
    exit;
}

echo json_encode(['success' => true, 'content' => trim($content)]);