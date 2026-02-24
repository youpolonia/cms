<?php
declare(strict_types=1);

class AffiliateAI
{
    /**
     * Generate promotional content for an affiliate program.
     */
    public static function generatePromo(string $programName, string $commissionInfo = '', string $language = 'en', string $type = 'social'): array
    {
        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $typeDesc = match ($type) {
            'email' => 'promotional email',
            'blog' => 'blog post intro paragraph',
            'banner' => 'banner ad copy (short headline + subheadline)',
            default => 'social media posts (3 variations for different platforms)',
        };

        $prompt = "Generate {$typeDesc} for an affiliate/referral program.\n\n"
            . "Program: {$programName}\n"
            . ($commissionInfo ? "Commission: {$commissionInfo}\n" : '')
            . "Language: {$language}\n\n"
            . "Return JSON: {\"content\": [\"variation1\", \"variation2\", \"variation3\"], \"headline\": \"catchy headline\", \"cta\": \"call to action text\"}\n"
            . "Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 600, 'temperature' => 0.6]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    /**
     * Generate an optimized affiliate landing page description.
     */
    public static function generateLanding(int $programId): array
    {
        $program = \AffiliateProgram::get($programId);
        if (!$program) return ['ok' => false, 'error' => 'Program not found'];

        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $commissionDesc = $program['commission_type'] === 'percentage'
            ? $program['commission_value'] . '% commission'
            : '$' . number_format((float)$program['commission_value'], 2) . ' per referral';

        $prompt = "Write a compelling affiliate program landing page description.\n\n"
            . "Program: {$program['name']}\n"
            . "Commission: {$commissionDesc}\n"
            . "Cookie Duration: {$program['cookie_days']} days\n"
            . "Min Payout: \${$program['min_payout']}\n"
            . "Current Description: " . substr($program['description'] ?? '', 0, 300) . "\n\n"
            . "Return JSON: {\"headline\": \"...\", \"description\": \"2-3 paragraphs\", \"benefits\": [\"benefit1\", \"benefit2\", \"benefit3\"], \"cta\": \"call to action\"}\n"
            . "Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 600, 'temperature' => 0.4]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'Generation failed'];
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
