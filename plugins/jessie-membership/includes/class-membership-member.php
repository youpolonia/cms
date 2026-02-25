<?php
declare(strict_types=1);

class MembershipMember
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'm.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['plan_id'])) { $where[] = 'm.plan_id = ?'; $params[] = (int)$filters['plan_id']; }
        if (!empty($filters['search'])) { $where[] = '(m.email LIKE ? OR m.name LIKE ?)'; $params[] = '%'.$filters['search'].'%'; $params[] = '%'.$filters['search'].'%'; }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM membership_members m WHERE {$wSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT m.*, p.name AS plan_name, p.color AS plan_color, p.price AS plan_price FROM membership_members m LEFT JOIN membership_plans p ON m.plan_id = p.id WHERE {$wSql} ORDER BY m.created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        return ['members' => $stmt->fetchAll(\PDO::FETCH_ASSOC), 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT m.*, p.name AS plan_name, p.color AS plan_color FROM membership_members m LEFT JOIN membership_plans p ON m.plan_id = p.id WHERE m.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getByEmail(string $email): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT m.*, p.name AS plan_name FROM membership_members m LEFT JOIN membership_plans p ON m.plan_id = p.id WHERE m.email = ? AND m.status IN ('active','trial') ORDER BY m.created_at DESC LIMIT 1");
        $stmt->execute([strtolower(trim($email))]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getByUserId(int $userId): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT m.*, p.name AS plan_name, p.features FROM membership_members m LEFT JOIN membership_plans p ON m.plan_id = p.id WHERE m.user_id = ? AND m.status IN ('active','trial') ORDER BY m.created_at DESC LIMIT 1");
        $stmt->execute([$userId]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) $r['features'] = json_decode($r['features'] ?: '[]', true);
        return $r ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $plan = \MembershipPlan::get((int)($data['plan_id'] ?? 0));
        $trialDays = $plan ? (int)$plan['trial_days'] : 0;
        $billing = $plan['billing_period'] ?? 'monthly';

        $status = $trialDays > 0 ? 'trial' : 'active';
        $trialEnds = $trialDays > 0 ? date('Y-m-d H:i:s', strtotime("+{$trialDays} days")) : null;
        $expiresAt = self::calcExpiry($billing, $trialEnds);

        $stmt = $pdo->prepare("INSERT INTO membership_members (user_id, email, name, plan_id, status, started_at, expires_at, trial_ends_at, payment_method, payment_ref, notes) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'] ?? null, strtolower(trim($data['email'] ?? '')), $data['name'] ?? '',
            (int)$data['plan_id'], $status, $expiresAt, $trialEnds,
            $data['payment_method'] ?? null, $data['payment_ref'] ?? null, $data['notes'] ?? null,
        ]);
        $id = (int)$pdo->lastInsertId();

        if (function_exists('cms_event')) {
            cms_event('membership.created', ['member_id' => $id, 'plan' => $plan['name'] ?? '', 'email' => $data['email'] ?? '']);
        }
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $pdo = db();
        $allowed = ['plan_id','status','expires_at','cancelled_at','payment_method','payment_ref','notes','name'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) { $fields[] = "{$f} = ?"; $params[] = $data[$f]; }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $pdo->prepare("UPDATE membership_members SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function cancel(int $id): void
    {
        self::update($id, ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')]);
        $m = self::get($id);
        if ($m && function_exists('cms_event')) {
            cms_event('membership.cancelled', ['member_id' => $id, 'email' => $m['email']]);
        }
    }

    public static function checkExpired(): int
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT id FROM membership_members WHERE status IN ('active','trial') AND expires_at IS NOT NULL AND expires_at < NOW()");
        $expired = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($expired as $id) {
            $pdo->prepare("UPDATE membership_members SET status = 'expired' WHERE id = ?")->execute([$id]);
        }
        return count($expired);
    }

    public static function hasAccess(int $userId, array $requiredPlanIds): bool
    {
        $member = self::getByUserId($userId);
        if (!$member) return false;
        if ($member['status'] !== 'active' && $member['status'] !== 'trial') return false;
        if ($member['expires_at'] && strtotime($member['expires_at']) < time()) return false;
        return empty($requiredPlanIds) || in_array((int)$member['plan_id'], $requiredPlanIds);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $row = $pdo->query("SELECT COUNT(*) AS total, SUM(status='active') AS active, SUM(status='trial') AS trial, SUM(status='expired') AS expired, SUM(status='cancelled') AS cancelled FROM membership_members")->fetch(\PDO::FETCH_ASSOC);
        $revenue = $pdo->query("SELECT COALESCE(SUM(amount),0) AS total_revenue, COALESCE(SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN amount END),0) AS revenue_30d FROM membership_transactions WHERE status = 'completed' AND type = 'payment'")->fetch(\PDO::FETCH_ASSOC);
        return array_merge(array_map('intval', $row), ['total_revenue' => (float)$revenue['total_revenue'], 'revenue_30d' => (float)$revenue['revenue_30d']]);
    }

    public static function recordTransaction(int $memberId, int $planId, float $amount, string $type = 'payment', string $method = '', string $ref = ''): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO membership_transactions (member_id, plan_id, amount, type, payment_method, payment_ref) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$memberId, $planId, $amount, $type, $method, $ref]);
        return (int)$pdo->lastInsertId();
    }

    private static function calcExpiry(string $billing, ?string $from = null): ?string
    {
        $base = $from ? strtotime($from) : time();
        return match($billing) {
            'monthly' => date('Y-m-d H:i:s', strtotime('+1 month', $base)),
            'quarterly' => date('Y-m-d H:i:s', strtotime('+3 months', $base)),
            'yearly' => date('Y-m-d H:i:s', strtotime('+1 year', $base)),
            'lifetime' => null,
            'free' => null,
            default => date('Y-m-d H:i:s', strtotime('+1 month', $base)),
        };
    }
}
