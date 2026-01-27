<?php
/**
 * AI Email Campaign Generator - Core Library
 * Generates structured email campaign specifications using HuggingFace
 * NO classes, NO database access, NO sessions
 */

// Guard against multiple includes
if (defined('AI_EMAIL_CAMPAIGNS_LOADED')) {
    return;
}
define('AI_EMAIL_CAMPAIGNS_LOADED', true);

// Detect CMS_ROOT if needed
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

// Load required dependencies
require_once CMS_ROOT . '/core/ai_hf.php';

/**
 * Generate email campaign specification using HuggingFace AI
 *
 * @param array $params Input parameters:
 *   - 'campaign_name': string - Name of the campaign
 *   - 'goal': string - Campaign goal (e.g., "convert free trial users to paid")
 *   - 'audience': string - Target audience
 *   - 'offer': string - Main offer/product
 *   - 'language': string - Language/locale (e.g., "pl" or "en")
 *   - 'tone': string - Tone (e.g., "professional", "friendly", "persuasive")
 *   - 'num_emails': int - Number of emails in sequence (1-20)
 * @return array Result with keys:
 *   - 'ok': bool - Success status
 *   - 'spec': array - Campaign specification (on success)
 *   - 'raw': string - Raw JSON response (on success)
 *   - 'error': string - Error message (on failure)
 *   - 'warning': string|null - Optional warning message
 */
function ai_email_campaign_generate_spec(array $params): array
{
    // Normalize and validate input parameters
    $campaignName = isset($params['campaign_name']) ? trim((string)$params['campaign_name']) : '';
    $goal = isset($params['goal']) ? trim((string)$params['goal']) : '';
    $audience = isset($params['audience']) ? trim((string)$params['audience']) : '';
    $offer = isset($params['offer']) ? trim((string)$params['offer']) : '';
    $language = isset($params['language']) ? trim((string)$params['language']) : 'en';
    $tone = isset($params['tone']) ? trim((string)$params['tone']) : 'professional';
    $numEmails = isset($params['num_emails']) ? (int)$params['num_emails'] : 5;

    // Clamp num_emails to 1-20
    if ($numEmails < 1) {
        $numEmails = 1;
    } elseif ($numEmails > 20) {
        $numEmails = 20;
    }

    // Normalize language to 2-letter lowercase code
    $language = strtolower(substr($language, 0, 2));
    if (!preg_match('/^[a-z]{2}$/', $language)) {
        $language = 'en';
    }

    // Build comprehensive prompt for HuggingFace
    $prompt = ai_email_campaign_build_prompt($campaignName, $goal, $audience, $offer, $language, $tone, $numEmails);

    // Attempt HuggingFace generation
    $config = ai_hf_config_load();
    $aiEnabled = ai_hf_is_configured($config);

    if (!$aiEnabled) {
        return [
            'ok' => false,
            'error' => 'Hugging Face is not configured or enabled. Please configure it in settings first.'
        ];
    }

    $rawResponse = null;
    try {
        $result = ai_hf_generate_text($prompt, [
            'params' => [
                'max_new_tokens' => 3000,
                'temperature' => 0.7,
                'top_p' => 0.9,
            ]
        ]);

        if ($result['ok'] === true && !empty($result['text'])) {
            $rawResponse = $result['text'];
        } else {
            error_log('[AI_EMAIL_CAMPAIGN] HuggingFace generation failed: ' . ($result['error'] ?? 'unknown'));
            return [
                'ok' => false,
                'error' => 'AI generation failed: ' . ($result['error'] ?? 'Unknown error')
            ];
        }
    } catch (\Throwable $e) {
        error_log('[AI_EMAIL_CAMPAIGN] Exception during HF call: ' . $e->getMessage());
        return [
            'ok' => false,
            'error' => 'An unexpected error occurred during AI generation.'
        ];
    }

    // Parse and validate response
    return ai_email_campaign_parse_response($rawResponse, $campaignName, $goal, $audience, $offer, $language, $tone, $numEmails);
}

/**
 * Build HuggingFace prompt for email campaign generation
 *
 * @param string $campaignName Campaign name
 * @param string $goal Campaign goal
 * @param string $audience Target audience
 * @param string $offer Main offer/product
 * @param string $language Language code
 * @param string $tone Tone of voice
 * @param int $numEmails Number of emails
 * @return string Formatted prompt
 */
function ai_email_campaign_build_prompt(string $campaignName, string $goal, string $audience, string $offer, string $language, string $tone, int $numEmails): string
{
    $prompt = "You are a professional email marketing copywriter. Generate a complete email campaign specification in STRICT JSON format. Do NOT include any natural language explanation, markdown code blocks, or comments. Return ONLY valid JSON.\n\n";
    $prompt .= "Requirements:\n";
    if ($campaignName !== '') {
        $prompt .= "- Campaign Name: {$campaignName}\n";
    }
    if ($goal !== '') {
        $prompt .= "- Goal: {$goal}\n";
    }
    if ($audience !== '') {
        $prompt .= "- Target Audience: {$audience}\n";
    }
    if ($offer !== '') {
        $prompt .= "- Main Offer/Product: {$offer}\n";
    }
    $prompt .= "- Language: {$language}\n";
    $prompt .= "- Tone: {$tone}\n";
    $prompt .= "- Number of emails in sequence: {$numEmails}\n\n";

    $prompt .= "JSON Structure (return EXACTLY this format):\n";
    $prompt .= "{\n";
    $prompt .= "  \"campaign_name\": \"Campaign title\",\n";
    $prompt .= "  \"language\": \"{$language}\",\n";
    $prompt .= "  \"goal\": \"Campaign goal description\",\n";
    $prompt .= "  \"audience\": \"Target audience description\",\n";
    $prompt .= "  \"offer\": \"Main offer description\",\n";
    $prompt .= "  \"tone\": \"{$tone}\",\n";
    $prompt .= "  \"emails\": [\n";
    $prompt .= "    {\n";
    $prompt .= "      \"index\": 1,\n";
    $prompt .= "      \"internal_name\": \"Email 1 - Welcome\",\n";
    $prompt .= "      \"subject\": \"Compelling subject line\",\n";
    $prompt .= "      \"preview_text\": \"Preview text (first line visible in inbox)\",\n";
    $prompt .= "      \"send_after_days\": 0,\n";
    $prompt .= "      \"primary_cta\": \"Call to action button text\",\n";
    $prompt .= "      \"body_html\": \"<p>Email body in HTML format (safe HTML, no scripts)</p>\"\n";
    $prompt .= "    }\n";
    $prompt .= "  ],\n";
    $prompt .= "  \"notes\": [\n";
    $prompt .= "    \"Implementation note 1\",\n";
    $prompt .= "    \"Implementation note 2\"\n";
    $prompt .= "  ]\n";
    $prompt .= "}\n\n";
    $prompt .= "Generate exactly {$numEmails} email(s) in the sequence.\n";
    $prompt .= "First email should have send_after_days = 0 (immediate).\n";
    $prompt .= "Subsequent emails should have progressively higher send_after_days values (e.g., 0, 3, 7, 14...).\n";
    $prompt .= "Each email should build on the previous one to guide the audience toward the goal.\n";
    $prompt .= "Return ONLY the JSON object, no other text.\n";

    return $prompt;
}

/**
 * Parse HuggingFace response into structured campaign spec
 *
 * @param string $rawResponse Raw text response from HF
 * @param string $campaignName Campaign name (for fallback)
 * @param string $goal Goal (for fallback)
 * @param string $audience Audience (for fallback)
 * @param string $offer Offer (for fallback)
 * @param string $language Language (for fallback)
 * @param string $tone Tone (for fallback)
 * @param int $numEmails Number of emails (for fallback)
 * @return array Result array
 */
function ai_email_campaign_parse_response(string $rawResponse, string $campaignName, string $goal, string $audience, string $offer, string $language, string $tone, int $numEmails): array
{
    // Try to extract JSON from response
    $cleaned = trim($rawResponse);

    // Remove markdown code blocks if present
    $cleaned = preg_replace('/^```json\s*/i', '', $cleaned);
    $cleaned = preg_replace('/^```\s*/i', '', $cleaned);
    $cleaned = preg_replace('/\s*```$/i', '', $cleaned);
    $cleaned = trim($cleaned);

    // Try to decode
    $data = @json_decode($cleaned, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        error_log('[AI_EMAIL_CAMPAIGN] Failed to parse JSON response: ' . json_last_error_msg());
        return [
            'ok' => false,
            'error' => 'Failed to parse AI response. The model may not have returned valid JSON.'
        ];
    }

    // Validate and normalize structure
    $spec = ai_email_campaign_normalize_spec($data, $campaignName, $goal, $audience, $offer, $language, $tone, $numEmails);

    // Re-encode to ensure valid JSON
    $rawJson = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($rawJson === false) {
        return [
            'ok' => false,
            'error' => 'Failed to encode normalized specification.'
        ];
    }

    return [
        'ok' => true,
        'spec' => $spec,
        'raw' => $rawJson,
        'warning' => null
    ];
}

/**
 * Normalize campaign specification
 *
 * @param array $data Raw data from AI
 * @param string $campaignName Fallback campaign name
 * @param string $goal Fallback goal
 * @param string $audience Fallback audience
 * @param string $offer Fallback offer
 * @param string $language Fallback language
 * @param string $tone Fallback tone
 * @param int $numEmails Expected number of emails
 * @return array Normalized spec
 */
function ai_email_campaign_normalize_spec(array $data, string $campaignName, string $goal, string $audience, string $offer, string $language, string $tone, int $numEmails): array
{
    // Normalize top-level fields
    $spec = [];
    $spec['campaign_name'] = isset($data['campaign_name']) ? trim((string)$data['campaign_name']) : '';
    if ($spec['campaign_name'] === '') {
        $spec['campaign_name'] = $campaignName !== '' ? $campaignName : 'Untitled Campaign';
    }

    $spec['language'] = isset($data['language']) ? strtolower(trim((string)$data['language'])) : $language;
    if (!preg_match('/^[a-z]{2}$/', $spec['language'])) {
        $spec['language'] = 'en';
    }

    $spec['goal'] = isset($data['goal']) ? trim((string)$data['goal']) : $goal;
    $spec['audience'] = isset($data['audience']) ? trim((string)$data['audience']) : $audience;
    $spec['offer'] = isset($data['offer']) ? trim((string)$data['offer']) : $offer;
    $spec['tone'] = isset($data['tone']) ? trim((string)$data['tone']) : $tone;

    // Normalize emails array
    $rawEmails = isset($data['emails']) && is_array($data['emails']) ? $data['emails'] : [];
    $spec['emails'] = ai_email_campaign_normalize_emails($rawEmails, $numEmails);

    // Normalize notes array
    $rawNotes = isset($data['notes']) && is_array($data['notes']) ? $data['notes'] : [];
    $spec['notes'] = [];
    foreach ($rawNotes as $note) {
        $noteStr = trim((string)$note);
        if ($noteStr !== '') {
            $spec['notes'][] = $noteStr;
        }
    }

    return $spec;
}

/**
 * Normalize emails array
 *
 * @param array $rawEmails Raw emails data
 * @param int $expectedCount Expected number of emails
 * @return array Normalized emails
 */
function ai_email_campaign_normalize_emails(array $rawEmails, int $expectedCount): array
{
    $normalized = [];
    $index = 1;

    foreach ($rawEmails as $email) {
        if (!is_array($email)) {
            continue;
        }

        // Extract and normalize fields
        $emailIndex = isset($email['index']) ? (int)$email['index'] : $index;
        if ($emailIndex < 1) {
            $emailIndex = $index;
        }

        $internalName = isset($email['internal_name']) ? trim((string)$email['internal_name']) : '';
        if ($internalName === '') {
            $internalName = 'Email ' . $emailIndex;
        }

        $subject = isset($email['subject']) ? trim((string)$email['subject']) : '';
        if ($subject === '') {
            $subject = 'Subject line for email ' . $emailIndex;
        }

        $previewText = isset($email['preview_text']) ? trim((string)$email['preview_text']) : '';
        if ($previewText === '') {
            $previewText = substr($subject, 0, 100);
        }

        $sendAfterDays = isset($email['send_after_days']) ? (int)$email['send_after_days'] : 0;
        // Clamp to 0-365
        if ($sendAfterDays < 0) {
            $sendAfterDays = 0;
        } elseif ($sendAfterDays > 365) {
            $sendAfterDays = 365;
        }

        $primaryCta = isset($email['primary_cta']) ? trim((string)$email['primary_cta']) : '';
        if ($primaryCta === '') {
            $primaryCta = 'Learn More';
        }

        $bodyHtml = isset($email['body_html']) ? trim((string)$email['body_html']) : '';
        if ($bodyHtml === '') {
            $bodyHtml = '<p>Email content goes here.</p>';
        }

        $normalized[] = [
            'index' => $emailIndex,
            'internal_name' => $internalName,
            'subject' => $subject,
            'preview_text' => $previewText,
            'send_after_days' => $sendAfterDays,
            'primary_cta' => $primaryCta,
            'body_html' => $bodyHtml,
        ];

        $index++;

        // Limit to 20 emails max
        if (count($normalized) >= 20) {
            break;
        }
    }

    // If we got fewer emails than expected, warn in error log
    if (count($normalized) < $expectedCount) {
        error_log('[AI_EMAIL_CAMPAIGN] Generated ' . count($normalized) . ' emails but expected ' . $expectedCount);
    }

    // Ensure at least 1 email
    if (count($normalized) === 0) {
        $normalized[] = [
            'index' => 1,
            'internal_name' => 'Email 1',
            'subject' => 'Welcome',
            'preview_text' => 'Welcome to our campaign',
            'send_after_days' => 0,
            'primary_cta' => 'Learn More',
            'body_html' => '<p>Welcome email content.</p>',
        ];
    }

    return $normalized;
}

/**
 * Get storage directory path for email campaigns
 * Ensures directory exists
 *
 * @return string Absolute path to storage directory
 */
function ai_email_campaign_storage_dir(): string
{
    $dir = CMS_ROOT . '/cms_storage/ai-email-campaigns';

    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0775, true)) {
            error_log('[AI_EMAIL_CAMPAIGN] Failed to create storage directory: ' . $dir);
            // Return the path anyway; callers will handle the error
        }
    }

    return $dir;
}

/**
 * Generate unique campaign ID
 *
 * @return string Campaign ID (e.g., "cmp_20250102_143022_a3f9")
 */
function ai_email_campaign_generate_id(): string
{
    $timestamp = date('Ymd_His');

    // Use random_bytes if available, otherwise fallback to mt_rand
    if (function_exists('random_bytes')) {
        $random = bin2hex(random_bytes(3));
    } else {
        $random = sprintf('%06x', mt_rand(0, 16777215));
    }

    return 'cmp_' . $timestamp . '_' . substr($random, 0, 4);
}

/**
 * Save campaign specification to JSON file
 *
 * @param array $spec Campaign specification
 * @return array Result with keys:
 *   - 'ok': bool - Success status
 *   - 'id': string - Campaign ID (on success)
 *   - 'filepath': string - Absolute file path (on success)
 *   - 'spec': array - Saved specification (on success)
 *   - 'error': string - Error message (on failure)
 */
function ai_email_campaign_save_spec(array $spec): array
{
    try {
        $storageDir = ai_email_campaign_storage_dir();

        // Verify directory is writable
        if (!is_dir($storageDir) || !is_writable($storageDir)) {
            error_log('[AI_EMAIL_CAMPAIGN] Storage directory not writable: ' . $storageDir);
            return [
                'ok' => false,
                'error' => 'Storage directory is not writable. Please check file permissions.'
            ];
        }

        // Generate unique ID
        $id = ai_email_campaign_generate_id();
        $filepath = $storageDir . '/' . $id . '.json';

        // Add metadata to spec
        $specWithMeta = $spec;
        $specWithMeta['id'] = $id;
        $specWithMeta['created_at'] = date('Y-m-d H:i:s');

        // Encode to JSON
        $json = json_encode($specWithMeta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            error_log('[AI_EMAIL_CAMPAIGN] Failed to encode spec to JSON');
            return [
                'ok' => false,
                'error' => 'Failed to encode campaign specification to JSON.'
            ];
        }

        // Write to file
        $result = @file_put_contents($filepath, $json . "\n", LOCK_EX);
        if ($result === false) {
            error_log('[AI_EMAIL_CAMPAIGN] Failed to write spec file: ' . $filepath);
            return [
                'ok' => false,
                'error' => 'Failed to write campaign file. Please check file permissions.'
            ];
        }

        return [
            'ok' => true,
            'id' => $id,
            'filepath' => $filepath,
            'spec' => $specWithMeta,
        ];
    } catch (\Throwable $e) {
        error_log('[AI_EMAIL_CAMPAIGN] Exception in save_spec: ' . $e->getMessage());
        return [
            'ok' => false,
            'error' => 'An unexpected error occurred while saving the campaign.'
        ];
    }
}

/**
 * List all saved campaign specifications
 *
 * @return array Array of campaign summaries, sorted by created_at DESC
 *   Each entry contains: id, campaign_name, language, created_at, num_emails
 */
function ai_email_campaign_list_specs(): array
{
    $storageDir = ai_email_campaign_storage_dir();

    if (!is_dir($storageDir)) {
        return [];
    }

    $files = @glob($storageDir . '/*.json');
    if ($files === false) {
        error_log('[AI_EMAIL_CAMPAIGN] Failed to list files in storage directory');
        return [];
    }

    $campaigns = [];

    foreach ($files as $filepath) {
        $json = @file_get_contents($filepath);
        if ($json === false) {
            continue;
        }

        $data = @json_decode($json, true);
        if (!is_array($data)) {
            continue;
        }

        // Extract ID from filename
        $basename = basename($filepath, '.json');
        $id = isset($data['id']) ? $data['id'] : $basename;

        // Extract campaign info
        $campaignName = isset($data['campaign_name']) ? trim((string)$data['campaign_name']) : 'Untitled';
        $language = isset($data['language']) ? trim((string)$data['language']) : 'en';
        $numEmails = isset($data['emails']) && is_array($data['emails']) ? count($data['emails']) : 0;

        // Get creation time (from file metadata or spec)
        $createdAt = null;
        if (isset($data['created_at'])) {
            $createdAt = $data['created_at'];
        } else {
            $mtime = @filemtime($filepath);
            if ($mtime !== false) {
                $createdAt = date('Y-m-d H:i:s', $mtime);
            }
        }

        $campaigns[] = [
            'id' => $id,
            'campaign_name' => $campaignName,
            'language' => $language,
            'created_at' => $createdAt,
            'num_emails' => $numEmails,
            'filepath' => $filepath,
        ];
    }

    // Sort by created_at DESC
    usort($campaigns, function($a, $b) {
        $timeA = $a['created_at'] ?? '';
        $timeB = $b['created_at'] ?? '';
        return strcmp($timeB, $timeA);
    });

    return $campaigns;
}

/**
 * Load a single campaign specification by ID
 *
 * @param string $id Campaign ID
 * @return array|null Campaign spec or null if not found
 */
function ai_email_campaign_load_spec(string $id): ?array
{
    $id = trim($id);
    if ($id === '') {
        return null;
    }

    // Sanitize ID to prevent directory traversal
    if (preg_match('/[^a-zA-Z0-9_\-]/', $id)) {
        return null;
    }

    $storageDir = ai_email_campaign_storage_dir();
    $filepath = $storageDir . '/' . $id . '.json';

    if (!is_file($filepath) || !is_readable($filepath)) {
        return null;
    }

    $json = @file_get_contents($filepath);
    if ($json === false) {
        return null;
    }

    $data = @json_decode($json, true);
    if (!is_array($data)) {
        return null;
    }

    return $data;
}

/**
 * Queue test emails from a campaign spec to ad-hoc recipients
 *
 * @param array $spec Campaign specification (decoded JSON)
 * @param int $emailIndex Index of the email in spec to send (0-based), or -1 for first email
 * @param array $recipients Array of email address strings
 * @return array Result with keys:
 *   - 'ok': bool
 *   - 'queued': int (on success)
 *   - 'invalid': int (on success)
 *   - 'recipients': array (on success)
 *   - 'error': string (on failure)
 */
function ai_email_campaigns_queue_test(array $spec, int $emailIndex, array $recipients): array
{
    // Normalize and validate recipients
    $validRecipients = [];
    $invalidCount = 0;

    foreach ($recipients as $email) {
        $email = trim((string)$email);
        if ($email === '') {
            continue;
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validRecipients[] = $email;
        } else {
            $invalidCount++;
        }
    }

    if (count($validRecipients) === 0) {
        return ['ok' => false, 'error' => 'NO_VALID_RECIPIENTS'];
    }

    // Extract email content from spec
    $emails = isset($spec['emails']) && is_array($spec['emails']) ? $spec['emails'] : [];
    if (count($emails) === 0) {
        return ['ok' => false, 'error' => 'NO_EMAILS_IN_SPEC'];
    }

    // Select the email to send (default to first)
    if ($emailIndex < 0 || $emailIndex >= count($emails)) {
        $emailIndex = 0;
    }
    $emailData = $emails[$emailIndex];

    // Derive subject and body
    $subject = isset($emailData['subject']) ? trim((string)$emailData['subject']) : '';
    if ($subject === '') {
        $subject = isset($spec['campaign_name']) ? trim((string)$spec['campaign_name']) : 'AI Email Campaign';
    }

    $body = isset($emailData['body_html']) ? trim((string)$emailData['body_html']) : '';
    if ($body === '') {
        // Fallback: compose from available fields
        $body = '<p>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</p>';
    }

    // Get from_email from config or use default
    $fromEmail = defined('DEFAULT_FROM_EMAIL') ? DEFAULT_FROM_EMAIL : 'noreply@localhost';

    // Queue emails using database
    require_once CMS_ROOT . '/core/database.php';

    try {
        $db = \core\Database::connection();

        // Check if scheduled_at column exists
        $checkStmt = $db->query("SHOW COLUMNS FROM email_queue LIKE 'scheduled_at'");
        $hasScheduledAt = $checkStmt->fetch() !== false;

        $queuedCount = 0;
        foreach ($validRecipients as $toEmail) {
            if ($hasScheduledAt) {
                $stmt = $db->prepare(
                    "INSERT INTO email_queue (to_email, from_email, subject, body, status, scheduled_at, created_at)
                     VALUES (:to_email, :from_email, :subject, :body, 'pending', NOW(), NOW())"
                );
            } else {
                $stmt = $db->prepare(
                    "INSERT INTO email_queue (to_email, from_email, subject, body, status, created_at)
                     VALUES (:to_email, :from_email, :subject, :body, 'pending', NOW())"
                );
            }
            $stmt->execute([
                ':to_email' => $toEmail,
                ':from_email' => $fromEmail,
                ':subject' => '[TEST] ' . $subject,
                ':body' => $body,
            ]);
            $queuedCount++;
        }

        return [
            'ok' => true,
            'queued' => $queuedCount,
            'invalid' => $invalidCount,
            'recipients' => $validRecipients,
        ];

    } catch (\Throwable $e) {
        error_log('[AI_EMAIL_CAMPAIGN] Queue test error: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'QUEUE_ERROR'];
    }
}
