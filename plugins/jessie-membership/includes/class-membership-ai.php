<?php
declare(strict_types=1);

class MembershipAI
{
    /**
     * Generate pricing plan features and description.
     */
    public static function generatePlanContent(string $planName, float $price, string $billing, string $industry = '', string $language = 'en'): array
    {
        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $prompt = "Create a membership plan description and feature list.\n\n"
            . "Plan: {$planName}\n"
            . "Price: \${$price}/{$billing}\n"
            . ($industry ? "Industry: {$industry}\n" : '')
            . "Language: {$language}\n\n"
            . "Return JSON: {\"description\": \"2-3 sentence description\", \"features\": [\"feature 1\", \"feature 2\", ...], \"value_proposition\": \"one sentence\"}\n"
            . "Include 5-8 features. Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 400, 'temperature' => 0.4]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    /**
     * Generate a pricing page copy.
     */
    public static function generatePricingPageCopy(string $language = 'en'): array
    {
        $plans = \MembershipPlan::getAll('active');
        if (empty($plans)) return ['ok' => false, 'error' => 'No active plans'];

        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $planList = '';
        foreach ($plans as $p) {
            $planList .= "- {$p['name']}: \${$p['price']}/{$p['billing_period']}" . ($p['features'] ? ' — ' . implode(', ', array_slice($p['features'], 0, 3)) : '') . "\n";
        }

        $prompt = "Write compelling pricing page copy for a membership site.\n\nPlans:\n{$planList}\nLanguage: {$language}\n\n"
            . "Return JSON: {\"headline\": \"...\", \"subheadline\": \"...\", \"faq\": [{\"q\": \"...\", \"a\": \"...\"}]}\n"
            . "Include 3-5 FAQ items. Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 600, 'temperature' => 0.5]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    /**
     * Churn risk analysis.
     */
    public static function analyzeChurnRisk(): array
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT m.*, p.name AS plan_name, p.price,
            DATEDIFF(m.expires_at, NOW()) AS days_left,
            (SELECT COUNT(*) FROM membership_transactions t WHERE t.member_id = m.id) AS tx_count
        FROM membership_members m LEFT JOIN membership_plans p ON m.plan_id = p.id
        WHERE m.status IN ('active','trial') AND m.expires_at IS NOT NULL
        ORDER BY days_left ASC LIMIT 20");
        $members = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $atRisk = [];
        foreach ($members as $m) {
            $risk = 'low';
            if ($m['days_left'] !== null && $m['days_left'] < 7) $risk = 'high';
            elseif ($m['days_left'] !== null && $m['days_left'] < 14) $risk = 'medium';
            if ($m['status'] === 'trial') $risk = max($risk, 'medium') === 'medium' ? 'high' : $risk;

            if ($risk !== 'low') {
                $atRisk[] = [
                    'member_id' => $m['id'],
                    'name' => $m['name'],
                    'email' => $m['email'],
                    'plan' => $m['plan_name'],
                    'days_left' => $m['days_left'],
                    'risk' => $risk,
                    'suggestion' => $risk === 'high' ? 'Send retention email with discount offer' : 'Send engagement reminder',
                ];
            }
        }

        return ['ok' => true, 'at_risk' => $atRisk, 'total_at_risk' => count($atRisk)];
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
