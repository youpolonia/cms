<?php
/**
 * AI Insights Helper
 *
 * Read-only analytics over AI request logs (logs/ai_requests.log).
 * Provides functions to read, filter, and compute statistics from JSONL log entries.
 *
 * No DB access, no writes, no external API calls.
 */

// Load AI integrations for report generation
require_once __DIR__ . '/ai_hf.php';
require_once __DIR__ . '/ai_models.php';
require_once __DIR__ . '/ai_content.php';

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

/**
 * Get the absolute path to the AI requests log file.
 *
 * @return string Absolute path to logs/ai_requests.log
 */
function ai_insights_log_path(): string
{
    return CMS_ROOT . '/logs/ai_requests.log';
}

/**
 * Read the last N lines from the AI requests log file.
 * Returns entries in newest-first order.
 *
 * @param int $maxLines Maximum number of lines to read (default 2000)
 * @return array Array of log entries (each is an associative array)
 */
function ai_insights_read(int $maxLines = 2000): array
{
    $logPath = ai_insights_log_path();

    if (!file_exists($logPath) || !is_readable($logPath)) {
        return [];
    }

    $fileSize = filesize($logPath);
    if ($fileSize === 0) {
        return [];
    }

    $handle = @fopen($logPath, 'r');
    if (!$handle) {
        return [];
    }

    // Read from end of file in chunks
    $chunkSize = 8192;
    $buffer = '';
    $lines = [];
    $offset = $fileSize;

    while ($offset > 0 && count($lines) < $maxLines) {
        $readSize = min($chunkSize, $offset);
        $offset -= $readSize;

        fseek($handle, $offset);
        $chunk = fread($handle, $readSize);

        if ($chunk === false) {
            break;
        }

        $buffer = $chunk . $buffer;

        // Split on newlines
        $parts = explode("\n", $buffer);

        // Keep first part as new buffer (incomplete line)
        $buffer = array_shift($parts);

        // Add complete lines to our collection (in reverse order)
        while (count($parts) > 0 && count($lines) < $maxLines) {
            $line = array_pop($parts);
            if (trim($line) !== '') {
                $lines[] = $line;
            }
        }
    }

    // Don't forget the last buffer if we've read the entire file
    if ($offset === 0 && trim($buffer) !== '' && count($lines) < $maxLines) {
        $lines[] = $buffer;
    }

    fclose($handle);

    // Decode JSON entries
    $entries = [];
    foreach ($lines as $line) {
        $decoded = @json_decode($line, true);
        if (is_array($decoded)) {
            $entries[] = $decoded;
        }
    }

    return $entries;
}

/**
 * Compute statistics from log entries.
 *
 * @param array $entries Array of log entries from ai_insights_read()
 * @return array Statistics including totals, per-provider, per-model breakdowns
 */
function ai_insights_compute(array $entries): array
{
    $stats = [
        'total' => 0,
        'total_ok' => 0,
        'total_error' => 0,
        'providers' => [],
        'models' => [],
        'last_ts' => null,
        'first_ts' => null,
    ];

    $timestamps = [];

    foreach ($entries as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $stats['total']++;

        $provider = (string)($entry['provider'] ?? 'unknown');
        $model = (string)($entry['model'] ?? 'unknown');
        $ok = !empty($entry['ok']);

        if ($ok) {
            $stats['total_ok']++;
        } else {
            $stats['total_error']++;
        }

        // Provider stats
        if (!isset($stats['providers'][$provider])) {
            $stats['providers'][$provider] = ['total' => 0, 'ok' => 0, 'error' => 0];
        }
        $stats['providers'][$provider]['total']++;
        if ($ok) {
            $stats['providers'][$provider]['ok']++;
        } else {
            $stats['providers'][$provider]['error']++;
        }

        // Model stats
        if (!isset($stats['models'][$model])) {
            $stats['models'][$model] = ['total' => 0, 'ok' => 0, 'error' => 0];
        }
        $stats['models'][$model]['total']++;
        if ($ok) {
            $stats['models'][$model]['ok']++;
        } else {
            $stats['models'][$model]['error']++;
        }

        // Track timestamps
        if (isset($entry['ts']) && is_string($entry['ts'])) {
            $timestamps[] = $entry['ts'];
        }
    }

    // Determine first and last timestamps
    if (count($timestamps) > 0) {
        $stats['last_ts'] = $timestamps[0]; // Newest (first in array)
        $stats['first_ts'] = $timestamps[count($timestamps) - 1]; // Oldest (last in array)
    }

    return $stats;
}

/**
 * Filter entries to those within the last N minutes.
 *
 * @param array $entries Array of log entries
 * @param int $minutes Number of minutes to look back
 * @return array Filtered entries
 */
function ai_insights_recent(array $entries, int $minutes): array
{
    $cutoff = time() - ($minutes * 60);

    return array_filter($entries, function($entry) use ($cutoff) {
        if (!isset($entry['ts']) || !is_string($entry['ts'])) {
            return false;
        }

        $timestamp = @strtotime($entry['ts']);
        if ($timestamp === false) {
            return false;
        }

        return $timestamp >= $cutoff;
    });
}

/**
 * Generate an AI-powered SEO, UX and content strategy report.
 *
 * Takes structured input about a website (goals, audience, analytics summary, etc.)
 * and calls AI to produce a comprehensive JSON report with recommendations.
 *
 * @param array $spec Specification array with keys:
 *                    - site_name (required)
 *                    - site_url
 *                    - audience
 *                    - primary_goal (required)
 *                    - secondary_goals
 *                    - current_issues
 *                    - content_overview
 *                    - analytics_summary
 *                    - timeframe
 *                    - language (default: 'en')
 *                    - notes
 * @param string $provider AI provider (default: 'huggingface')
 * @param string $model Model ID (provider-specific)
 *
 * @return array Result with keys:
 *               - ok (bool): Success status
 *               - report (array): Parsed JSON report on success
 *               - json (string): Raw JSON output
 *               - prompt (string): The prompt sent to AI
 *               - error (string): Error message on failure
 */
function ai_insights_generate_report(array $spec, string $provider = 'huggingface', string $model = ''): array
{
    try {
        // Normalize inputs
        $siteName        = trim((string)($spec['site_name'] ?? ''));
        $siteUrl         = trim((string)($spec['site_url'] ?? ''));
        $audience        = trim((string)($spec['audience'] ?? ''));
        $primaryGoal     = trim((string)($spec['primary_goal'] ?? ''));
        $secondaryGoals  = trim((string)($spec['secondary_goals'] ?? ''));
        $currentIssues   = trim((string)($spec['current_issues'] ?? ''));
        $contentOverview = trim((string)($spec['content_overview'] ?? ''));
        $analyticsSummary= trim((string)($spec['analytics_summary'] ?? ''));
        $timeframe       = trim((string)($spec['timeframe'] ?? ''));
        $language        = trim((string)($spec['language'] ?? ''));
        $notes           = trim((string)($spec['notes'] ?? ''));

        // Apply defaults
        if ($language === '') {
            $language = 'en';
        }
        if ($timeframe === '') {
            $timeframe = 'last 30 days';
        }

        // Basic validation
        if ($siteName === '' || $primaryGoal === '') {
            return [
                'ok'    => false,
                'error' => 'Site name and primary goal are required.'
            ];
        }

        // Build the AI prompt
        $prompt = "You are an expert in SEO, content strategy and UX.\n\n";
        $prompt .= "Analyze the website based on the following information and produce a structured report in {$language}.\n\n";
        $prompt .= "Site name: {$siteName}\n";
        if ($siteUrl !== '') {
            $prompt .= "Site URL: {$siteUrl}\n";
        }
        if ($audience !== '') {
            $prompt .= "Target audience: {$audience}\n";
        }
        $prompt .= "Primary goal: {$primaryGoal}\n";
        if ($secondaryGoals !== '') {
            $prompt .= "Secondary goals: {$secondaryGoals}\n";
        }
        if ($currentIssues !== '') {
            $prompt .= "Current issues (as described by admin):\n{$currentIssues}\n\n";
        }
        if ($contentOverview !== '') {
            $prompt .= "Content overview:\n{$contentOverview}\n\n";
        }
        if ($analyticsSummary !== '') {
            $prompt .= "Analytics summary for {$timeframe}:\n{$analyticsSummary}\n\n";
        }
        if ($notes !== '') {
            $prompt .= "Additional notes from admin:\n{$notes}\n\n";
        }

        $prompt .= "Requirements:\n";
        $prompt .= "- Output a single JSON object.\n";
        $prompt .= "- Do NOT wrap in markdown or backticks.\n";
        $prompt .= "- Respond in {$language}.\n";
        $prompt .= "- JSON structure:\n\n";
        $prompt .= "{\n";
        $prompt .= "  \"summary\": \"...\",\n";
        $prompt .= "  \"strengths\": [\"...\", \"...\"],\n";
        $prompt .= "  \"issues\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"area\": \"SEO | UX | content | performance | analytics\",\n";
        $prompt .= "      \"description\": \"...\",\n";
        $prompt .= "      \"impact\": \"high | medium | low\",\n";
        $prompt .= "      \"priority\": 1\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"recommendations\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"area\": \"SEO | UX | content | performance | analytics\",\n";
        $prompt .= "      \"action\": \"...\",\n";
        $prompt .= "      \"why\": \"...\",\n";
        $prompt .= "      \"impact\": \"high | medium | low\",\n";
        $prompt .= "      \"difficulty\": \"easy | medium | hard\",\n";
        $prompt .= "      \"timeframe\": \"short_term | mid_term | long_term\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"quick_wins\": [\"...\", \"...\"],\n";
        $prompt .= "  \"content_ideas\": [\"...\", \"...\"],\n";
        $prompt .= "  \"technical_seo_todos\": [\"...\", \"...\"]\n";
        $prompt .= "}\n\n";
        $prompt .= "- Provide 3–10 strengths.\n";
        $prompt .= "- Provide 3–10 issues and corresponding recommendations.\n";
        $prompt .= "- quick_wins: 3–10 very concrete actions.\n";
        $prompt .= "- content_ideas: 5–15 article/page ideas.\n";
        $prompt .= "- technical_seo_todos: technical tasks only (no copy changes).\n";
        $prompt .= "- Be concise, specific and practical.\n";

        // Call AI provider
        if ($provider === 'huggingface') {
            // Use HuggingFace
            $params = [
                'temperature'    => 0.4,
                'max_new_tokens' => 2200,
            ];
            $hfOptions = ['params' => $params];
            if ($model !== '') {
                $hfOptions['model'] = $model;
            }
            $result = ai_hf_generate_text($prompt, $hfOptions);

            if (!$result['ok']) {
                return [
                    'ok'    => false,
                    'error' => $result['error'] ?? 'Unknown error from Hugging Face.'
                ];
            }
            $text = trim($result['text']);
        } else {
            // Use universal provider (OpenAI, Anthropic, Google, DeepSeek, Ollama)
            $result = ai_universal_generate($provider, $model, '', $prompt, [
                'max_tokens' => 2200,
                'temperature' => 0.4,
            ]);

            if (!$result['ok']) {
                return [
                    'ok'    => false,
                    'error' => $result['error'] ?? 'AI generation failed'
                ];
            }
            $text = trim($result['content'] ?? $result['text'] ?? '');
        }

        // Clean and decode JSON

        // Remove common wrappers
        $text = preg_replace('/^json:\s*/i', '', $text);
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        $data = @json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('AI Insights: JSON decode failed - ' . json_last_error_msg());
            return [
                'ok'    => false,
                'error' => 'The AI did not return valid JSON. Please try again.'
            ];
        }

        // Validate minimal structure
        if (!isset($data['summary']) || !is_string($data['summary'])) {
            return [
                'ok'    => false,
                'error' => 'The generated report is incomplete (missing summary). Please refine your inputs and try again.'
            ];
        }

        if (!isset($data['recommendations']) || !is_array($data['recommendations']) || count($data['recommendations']) === 0) {
            return [
                'ok'    => false,
                'error' => 'The generated report is incomplete (no recommendations). Please refine your inputs and try again.'
            ];
        }

        // Success
        return [
            'ok'     => true,
            'report' => $data,
            'json'   => $text,
            'prompt' => $prompt,
        ];

    } catch (Exception $e) {
        error_log('AI Insights generate_report exception: ' . $e->getMessage());
        return [
            'ok'    => false,
            'error' => 'Unexpected error while generating the report. Please try again.'
        ];
    }
}
