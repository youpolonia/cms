<?php
namespace Plugins\JessieSaasCore;

/**
 * SaaS Credits — usage tracking, limits, purchases, top-ups
 */
class SaasCredits {
    private \PDO $pdo;
    
    public function __construct() {
        if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
        require_once CMS_ROOT . '/db.php';
        $this->pdo = \core\Database::connection();
    }
    
    // ── Check if user can perform action ──
    public function canUse(int $userId, string $service, int $creditsNeeded = 1): array {
        // Check subscription
        $stmt = $this->pdo->prepare(
            "SELECT s.*, p.limits_json, p.credits_monthly 
             FROM saas_subscriptions s 
             JOIN saas_plans p ON s.plan_id = p.id 
             WHERE s.user_id = ? AND s.service = ? AND s.status IN ('active','trial')
             ORDER BY s.id DESC LIMIT 1"
        );
        $stmt->execute([$userId, $service]);
        $sub = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$sub) {
            return ['allowed' => false, 'reason' => 'No active subscription for this service'];
        }
        
        // Check credits
        $limits = json_decode($sub['limits_json'] ?? '{}', true) ?: [];
        $monthlyLimit = $sub['credits_limit'] ?: ($sub['credits_monthly'] ?: ($limits['credits_monthly'] ?? 0));
        
        if ($monthlyLimit > 0 && ($sub['credits_used'] + $creditsNeeded) > $monthlyLimit) {
            return ['allowed' => false, 'reason' => 'Monthly credit limit reached', 'used' => $sub['credits_used'], 'limit' => $monthlyLimit];
        }
        
        // Check global credits
        $stmt2 = $this->pdo->prepare("SELECT credits_remaining FROM saas_users WHERE id = ?");
        $stmt2->execute([$userId]);
        $user = $stmt2->fetch(\PDO::FETCH_ASSOC);
        
        return [
            'allowed' => true,
            'credits_remaining' => $user['credits_remaining'] ?? 0,
            'credits_used' => $sub['credits_used'],
            'credits_limit' => $monthlyLimit,
            'subscription_id' => $sub['id']
        ];
    }
    
    // ── Consume credits ──
    public function consume(int $userId, string $service, string $endpoint, int $credits = 1, array $meta = []): bool {
        $check = $this->canUse($userId, $service, $credits);
        if (!$check['allowed']) return false;
        
        // Decrement user credits
        $this->pdo->prepare("UPDATE saas_users SET credits_remaining = GREATEST(0, credits_remaining - ?) WHERE id = ?")
            ->execute([$credits, $userId]);
        
        // Increment subscription usage
        $this->pdo->prepare("UPDATE saas_subscriptions SET credits_used = credits_used + ? WHERE id = ?")
            ->execute([$credits, $check['subscription_id']]);
        
        // Log usage
        $this->logUsage($userId, $service, $endpoint, $credits, $meta);
        
        // Log transaction
        $this->pdo->prepare(
            "INSERT INTO saas_transactions (user_id, subscription_id, type, credits, description, status)
             VALUES (?, ?, 'credit_usage', ?, ?, 'completed')"
        )->execute([$userId, $check['subscription_id'], $credits, "$service::$endpoint"]);
        
        return true;
    }
    
    // ── Add credits ──
    public function addCredits(int $userId, int $credits, string $reason = 'purchase', ?string $stripePaymentId = null): bool {
        $this->pdo->prepare("UPDATE saas_users SET credits_remaining = credits_remaining + ? WHERE id = ?")
            ->execute([$credits, $userId]);
        
        $this->pdo->prepare(
            "INSERT INTO saas_transactions (user_id, type, credits, description, stripe_payment_id, status)
             VALUES (?, 'credit_purchase', ?, ?, ?, 'completed')"
        )->execute([$userId, $credits, $reason, $stripePaymentId]);
        
        return true;
    }
    
    // ── Reset monthly usage (cron) ──
    public function resetMonthlyUsage(): int {
        $stmt = $this->pdo->prepare(
            "UPDATE saas_subscriptions SET credits_used = 0 
             WHERE status = 'active' AND current_period_end <= NOW()"
        );
        $stmt->execute();
        $count = $stmt->rowCount();
        
        // Also reset monthly credits for users
        $this->pdo->exec(
            "UPDATE saas_users u 
             SET u.credits_remaining = u.credits_monthly 
             WHERE u.credits_monthly > 0 AND u.status = 'active'"
        );
        
        return $count;
    }
    
    // ── Usage stats ──
    public function getUsageStats(int $userId, string $service, string $period = 'month'): array {
        $interval = match($period) {
            'day' => '1 DAY', 'week' => '7 DAY', 'month' => '30 DAY', 'year' => '365 DAY',
            default => '30 DAY'
        };
        
        $stmt = $this->pdo->prepare(
            "SELECT DATE(created_at) as date, SUM(credits_used) as credits, COUNT(*) as requests
             FROM saas_api_usage 
             WHERE user_id = ? AND service = ? AND created_at > DATE_SUB(NOW(), INTERVAL $interval)
             GROUP BY DATE(created_at) ORDER BY date"
        );
        $stmt->execute([$userId, $service]);
        $daily = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $stmt2 = $this->pdo->prepare(
            "SELECT endpoint, SUM(credits_used) as credits, COUNT(*) as requests, AVG(latency_ms) as avg_latency
             FROM saas_api_usage 
             WHERE user_id = ? AND service = ? AND created_at > DATE_SUB(NOW(), INTERVAL $interval)
             GROUP BY endpoint ORDER BY credits DESC"
        );
        $stmt2->execute([$userId, $service]);
        $byEndpoint = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        
        return ['daily' => $daily, 'by_endpoint' => $byEndpoint];
    }
    
    // ── Log API usage ──
    public function logUsage(int $userId, string $service, string $endpoint, int $credits = 1, array $meta = []): void {
        $this->pdo->prepare(
            "INSERT INTO saas_api_usage (user_id, api_key, service, endpoint, method, credits_used, tokens_in, tokens_out, latency_ms, status_code, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            $userId,
            $meta['api_key'] ?? null,
            $service,
            $endpoint,
            $_SERVER['REQUEST_METHOD'] ?? 'POST',
            $credits,
            $meta['tokens_in'] ?? 0,
            $meta['tokens_out'] ?? 0,
            $meta['latency_ms'] ?? 0,
            $meta['status_code'] ?? 200,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
        ]);
    }
    
    // ── Subscription management ──
    public function getSubscription(int $userId, string $service): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, p.name as plan_name, p.slug as plan_slug, p.price_monthly, p.price_yearly, p.features_json, p.limits_json, p.credits_monthly as plan_credits
             FROM saas_subscriptions s 
             JOIN saas_plans p ON s.plan_id = p.id 
             WHERE s.user_id = ? AND s.service = ? AND s.status IN ('active','trial')
             ORDER BY s.id DESC LIMIT 1"
        );
        $stmt->execute([$userId, $service]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    public function getAllSubscriptions(int $userId): array {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, p.name as plan_name, p.slug as plan_slug, p.service, p.price_monthly, p.features_json
             FROM saas_subscriptions s 
             JOIN saas_plans p ON s.plan_id = p.id 
             WHERE s.user_id = ? AND s.status IN ('active','trial')
             ORDER BY p.service"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function upgradePlan(int $userId, string $service, int $newPlanId, string $billingCycle = 'monthly'): array {
        // Get plan details
        $stmt = $this->pdo->prepare("SELECT * FROM saas_plans WHERE id = ? AND service = ? AND status = 'active'");
        $stmt->execute([$newPlanId, $service]);
        $plan = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$plan) return ['success' => false, 'error' => 'Plan not found'];
        
        // Cancel existing
        $this->pdo->prepare(
            "UPDATE saas_subscriptions SET status = 'cancelled', cancelled_at = NOW() WHERE user_id = ? AND service = ? AND status = 'active'"
        )->execute([$userId, $service]);
        
        // Create new
        $periodEnd = $billingCycle === 'yearly' ? 'DATE_ADD(NOW(), INTERVAL 1 YEAR)' : 'DATE_ADD(NOW(), INTERVAL 1 MONTH)';
        $this->pdo->prepare(
            "INSERT INTO saas_subscriptions (user_id, plan_id, service, billing_cycle, credits_limit, current_period_start, current_period_end, status)
             VALUES (?, ?, ?, ?, ?, NOW(), $periodEnd, 'active')"
        )->execute([$userId, $plan['id'], $service, $billingCycle, $plan['credits_monthly']]);
        
        // Update user plan
        $this->pdo->prepare("UPDATE saas_users SET plan = ?, credits_monthly = ? WHERE id = ?")->execute([$plan['slug'], $plan['credits_monthly'], $userId]);
        
        return ['success' => true, 'plan' => $plan['name']];
    }
    
    // ── Plans catalog ──
    public function getPlans(string $service): array {
        $stmt = $this->pdo->prepare("SELECT * FROM saas_plans WHERE service = ? AND status = 'active' ORDER BY sort_order, price_monthly");
        $stmt->execute([$service]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
