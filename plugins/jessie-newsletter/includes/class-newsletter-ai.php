<?php
declare(strict_types=1);

class NewsletterAI
{
    /**
     * Generate email subject lines.
     */
    public static function generateSubjectLines(string $topic, string $tone = 'professional', string $language = 'en'): array
    {
        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $prompt = "Generate 5 compelling email subject lines for a newsletter.\n\n"
            . "Topic: {$topic}\n"
            . "Tone: {$tone}\n"
            . "Language: {$language}\n\n"
            . "Requirements: varied lengths (short 3-5 words + longer), some with emoji, some with urgency, some with curiosity.\n"
            . "Return JSON: {\"subjects\": [\"line1\", \"line2\", ...]}\n"
            . "Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 300, 'temperature' => 0.7]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'subjects' => $data['subjects'] ?? []] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    /**
     * Generate full email content from a brief.
     */
    public static function generateContent(string $brief, string $template = 'promotional', string $tone = 'friendly', string $language = 'en'): array
    {
        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $prompt = "Write email newsletter content.\n\n"
            . "Brief: {$brief}\n"
            . "Template style: {$template}\n"
            . "Tone: {$tone}\n"
            . "Language: {$language}\n\n"
            . "Requirements:\n"
            . "- Write the HTML body content (not full email, just the main content)\n"
            . "- Use <h2>, <p>, <ul>/<li>, <strong>, <a> tags\n"
            . "- Include a clear call-to-action\n"
            . "- 200-400 words\n"
            . "- Engaging opening line\n\n"
            . "Return JSON: {\"subject\": \"suggested subject\", \"preview_text\": \"preview text\", \"content_html\": \"<h2>...\", \"cta_text\": \"button text\", \"cta_url\": \"#\"}\n"
            . "Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 1000, 'temperature' => 0.5]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    /**
     * Improve/rewrite existing email content.
     */
    public static function improveContent(string $currentHtml, string $goal = 'engagement'): array
    {
        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $textContent = strip_tags($currentHtml);
        if (strlen($textContent) > 2000) $textContent = substr($textContent, 0, 2000) . '...';

        $prompt = "Improve this email newsletter content for better {$goal}.\n\n"
            . "Current content:\n{$textContent}\n\n"
            . "Goal: Maximize {$goal}\n"
            . "Return JSON: {\"improved_html\": \"<h2>...\", \"changes_summary\": \"what was changed\"}\n"
            . "Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 1000, 'temperature' => 0.4]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI improvement failed'];
    }

    /**
     * Suggest optimal send time based on past data.
     */
    public static function suggestSendTime(): array
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT
            DAYNAME(e.created_at) AS day_name,
            HOUR(e.created_at) AS hour,
            e.event_type,
            COUNT(*) AS cnt
        FROM newsletter_events e
        WHERE e.event_type IN ('opened','clicked') AND e.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
        GROUP BY day_name, hour, e.event_type
        ORDER BY cnt DESC");
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($data) < 5) {
            return ['ok' => true, 'suggestion' => 'Tuesday 10:00 AM', 'confidence' => 'low', 'note' => 'Not enough data — using industry best practice.'];
        }

        // Find best open time
        $opens = array_filter($data, fn($r) => $r['event_type'] === 'opened');
        $best = reset($opens);
        return [
            'ok' => true,
            'suggestion' => ($best['day_name'] ?? 'Tuesday') . ' ' . sprintf('%d:00', $best['hour'] ?? 10),
            'confidence' => count($opens) > 20 ? 'high' : 'medium',
            'top_times' => array_slice(array_map(fn($r) => ['day' => $r['day_name'], 'hour' => $r['hour'] . ':00', 'opens' => $r['cnt']], $opens), 0, 5),
        ];
    }

    /**
     * Analyze campaign performance and give recommendations.
     */
    public static function analyzeCampaign(int $campaignId): array
    {
        $campaign = \NewsletterCampaign::get($campaignId);
        if (!$campaign || $campaign['status'] !== 'sent') return ['ok' => false, 'error' => 'Campaign not found or not sent yet'];

        $sent = max(1, $campaign['stats_sent']);
        $openRate = round($campaign['stats_opened'] / $sent * 100, 1);
        $clickRate = round($campaign['stats_clicked'] / $sent * 100, 1);
        $unsubRate = round($campaign['stats_unsubscribed'] / $sent * 100, 2);

        $insights = [];
        if ($openRate < 15) $insights[] = '⚠️ Low open rate — try more compelling subject lines, test send times';
        elseif ($openRate > 30) $insights[] = '✅ Excellent open rate!';
        else $insights[] = '👍 Average open rate — room for improvement with A/B testing';

        if ($clickRate < 2) $insights[] = '⚠️ Low click rate — make CTAs more prominent, reduce content length';
        elseif ($clickRate > 5) $insights[] = '✅ Great click rate!';

        if ($unsubRate > 1) $insights[] = '🔴 High unsubscribe rate — review content relevance and frequency';

        return [
            'ok' => true,
            'metrics' => ['open_rate' => $openRate, 'click_rate' => $clickRate, 'unsubscribe_rate' => $unsubRate],
            'insights' => $insights,
            'benchmarks' => ['avg_open_rate' => 21.5, 'avg_click_rate' => 3.2, 'avg_unsub_rate' => 0.26],
        ];
    }

    private static function parseJson(string $response): ?array
    {
        $response = trim($response);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $response, $m)) $response = $m[1];
        $data = json_decode($response, true);
        if ($data) return $data;
        if (preg_match('/\{[\s\S]*\}/', $response, $m)) return json_decode($m[0], true);
        return null;
    }
}
