<?php
declare(strict_types=1);

class MembershipAccess
{
    /**
     * Check if a user can access specific content.
     */
    public static function canAccess(int $userId, string $contentType, int $contentId): bool
    {
        $rules = self::getRulesFor($contentType, $contentId);
        if (empty($rules)) return true; // No rules = public

        $member = \MembershipMember::getByUserId($userId);
        if (!$member) return false;
        if (!in_array($member['status'], ['active', 'trial'])) return false;

        foreach ($rules as $rule) {
            $planIds = json_decode($rule['plan_ids'] ?: '[]', true);
            if ($rule['rule_type'] === 'exclude') {
                if (in_array((int)$member['plan_id'], $planIds)) return false;
            } elseif ($rule['rule_type'] === 'require_any') {
                if (!in_array((int)$member['plan_id'], $planIds)) return false;
            } elseif ($rule['rule_type'] === 'require_all') {
                if (!in_array((int)$member['plan_id'], $planIds)) return false;
            }
        }
        return true;
    }

    /**
     * Get content restriction message for gated content.
     */
    public static function getGateMessage(string $contentType, int $contentId): string
    {
        $rules = self::getRulesFor($contentType, $contentId);
        foreach ($rules as $r) {
            if ($r['message']) return $r['message'];
        }
        return 'This content is available to members only. Please upgrade your plan to access it.';
    }

    /**
     * Get all rules for a specific piece of content.
     */
    public static function getRulesFor(string $contentType, int $contentId): array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM membership_content_rules WHERE content_type = ? AND (content_id = ? OR content_id IS NULL)");
        $stmt->execute([$contentType, $contentId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all content rules with plan names.
     */
    public static function getAllRules(): array
    {
        $pdo = db();
        $rules = $pdo->query("SELECT * FROM membership_content_rules ORDER BY content_type, content_id")->fetchAll(\PDO::FETCH_ASSOC);
        $plans = $pdo->query("SELECT id, name FROM membership_plans")->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($rules as &$rule) {
            $planIds = json_decode($rule['plan_ids'] ?: '[]', true);
            $names = [];
            foreach ($planIds as $pid) {
                if (isset($plans[(int)$pid])) {
                    $names[] = $plans[(int)$pid];
                }
            }
            $rule['plan_names'] = implode(', ', $names);
        }
        return $rules;
    }

    /**
     * Create a content rule.
     */
    public static function createRule(array $data): int
    {
        $pdo = db();
        $planIds = is_array($data['plan_ids'] ?? null) ? json_encode(array_map('intval', $data['plan_ids'])) : ($data['plan_ids'] ?? '[]');
        $stmt = $pdo->prepare("INSERT INTO membership_content_rules (content_type, content_id, content_pattern, plan_ids, rule_type, message) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['content_type'] ?? 'page',
            !empty($data['content_id']) ? (int)$data['content_id'] : null,
            $data['content_pattern'] ?? null,
            $planIds,
            $data['rule_type'] ?? 'require_any',
            $data['message'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Delete a content rule.
     */
    public static function deleteRule(int $id): void
    {
        db()->prepare("DELETE FROM membership_content_rules WHERE id = ?")->execute([$id]);
    }

    /**
     * Middleware: call in page/article controllers to gate content.
     */
    public static function gate(string $contentType, int $contentId, int $userId = 0): bool
    {
        if (!$userId && isset($_SESSION['user_id'])) $userId = (int)$_SESSION['user_id'];
        if (!$userId) return true;

        $rules = self::getRulesFor($contentType, $contentId);
        if (empty($rules)) return true;

        return self::canAccess($userId, $contentType, $contentId);
    }
}
