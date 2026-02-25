<?php
declare(strict_types=1);

class Affiliate
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'a.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['program_id'])) { $where[] = 'a.program_id = ?'; $params[] = (int)$filters['program_id']; }
        if (!empty($filters['search'])) { $where[] = '(a.name LIKE ? OR a.email LIKE ? OR a.referral_code LIKE ?)'; $params[] = '%' . $filters['search'] . '%'; $params[] = '%' . $filters['search'] . '%'; $params[] = '%' . $filters['search'] . '%'; }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM affiliates a WHERE {$wSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT a.*, p.name AS program_name FROM affiliates a LEFT JOIN affiliate_programs p ON a.program_id = p.id WHERE {$wSql} ORDER BY a.created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['affiliates' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT a.*, p.name AS program_name, p.commission_type, p.commission_value FROM affiliates a LEFT JOIN affiliate_programs p ON a.program_id = p.id WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getByCode(string $code): ?array
    {
        $stmt = db()->prepare("SELECT a.*, p.name AS program_name, p.commission_type, p.commission_value, p.cookie_days FROM affiliates a LEFT JOIN affiliate_programs p ON a.program_id = p.id WHERE a.referral_code = ? AND a.status = 'active'");
        $stmt->execute([$code]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getByEmail(string $email): ?array
    {
        $stmt = db()->prepare("SELECT a.*, p.name AS program_name FROM affiliates a LEFT JOIN affiliate_programs p ON a.program_id = p.id WHERE a.email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $referralCode = $data['referral_code'] ?? self::generateCode();
        $stmt = $pdo->prepare("INSERT INTO affiliates (program_id, name, email, referral_code, website, payment_method, payment_details, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // Check auto-approve setting
        $autoApprove = '0';
        try {
            $s = $pdo->prepare("SELECT setting_value FROM affiliate_settings WHERE setting_key = 'auto_approve_affiliates'");
            $s->execute();
            $autoApprove = $s->fetchColumn() ?: '0';
        } catch (\Exception $e) {}

        $status = $autoApprove === '1' ? 'active' : 'pending';
        if (!empty($data['status'])) $status = $data['status'];

        $stmt->execute([
            (int)($data['program_id'] ?? 0),
            $data['name'],
            $data['email'],
            $referralCode,
            ($data['website'] ?? null) ?: '',
            ($data['payment_method'] ?? null) ?: '',
            ($data['payment_details'] ?? null) ?: '',
            $status,
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('affiliate.registered', ['affiliate_id' => $id, 'name' => $data['name'], 'email' => $data['email']]);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['program_id','name','email','website','payment_method','payment_details','status','total_clicks','total_conversions','total_earnings','pending_payout'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE affiliates SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("DELETE FROM affiliate_payouts WHERE affiliate_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM affiliate_conversions WHERE affiliate_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM affiliates WHERE id = ?")->execute([$id]);
    }

    public static function trackClick(string $code): bool
    {
        $affiliate = self::getByCode($code);
        if (!$affiliate) return false;
        db()->prepare("UPDATE affiliates SET total_clicks = total_clicks + 1 WHERE id = ?")->execute([$affiliate['id']]);
        if (function_exists('cms_event')) cms_event('affiliate.click', ['affiliate_id' => $affiliate['id'], 'code' => $code]);
        return true;
    }

    public static function recordConversion(int $affiliateId, int $programId, string $orderId, float $orderTotal, float $commission): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO affiliate_conversions (affiliate_id, program_id, order_id, order_total, commission, status, ip_address, user_agent, referred_url) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
        $stmt->execute([
            $affiliateId,
            $programId,
            $orderId,
            $orderTotal,
            $commission,
            $_SERVER['REMOTE_ADDR'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            $_SERVER['HTTP_REFERER'] ?? '',
        ]);
        $convId = (int)$pdo->lastInsertId();

        // Update affiliate stats
        $pdo->prepare("UPDATE affiliates SET total_conversions = total_conversions + 1, total_earnings = total_earnings + ?, pending_payout = pending_payout + ? WHERE id = ?")->execute([$commission, $commission, $affiliateId]);

        if (function_exists('cms_event')) cms_event('affiliate.conversion', ['affiliate_id' => $affiliateId, 'order_id' => $orderId, 'commission' => $commission]);
        return $convId;
    }

    public static function getConversions(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['affiliate_id'])) { $where[] = 'c.affiliate_id = ?'; $params[] = (int)$filters['affiliate_id']; }
        if (!empty($filters['program_id'])) { $where[] = 'c.program_id = ?'; $params[] = (int)$filters['program_id']; }
        if (!empty($filters['status'])) { $where[] = 'c.status = ?'; $params[] = $filters['status']; }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM affiliate_conversions c WHERE {$wSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT c.*, a.name AS affiliate_name, a.email AS affiliate_email, a.referral_code, p.name AS program_name FROM affiliate_conversions c LEFT JOIN affiliates a ON c.affiliate_id = a.id LEFT JOIN affiliate_programs p ON c.program_id = p.id WHERE {$wSql} ORDER BY c.created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['conversions' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function approveConversion(int $id): void
    {
        db()->prepare("UPDATE affiliate_conversions SET status = 'approved' WHERE id = ? AND status = 'pending'")->execute([$id]);
    }

    public static function rejectConversion(int $id): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT affiliate_id, commission FROM affiliate_conversions WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
        $conv = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($conv) {
            $pdo->prepare("UPDATE affiliate_conversions SET status = 'rejected' WHERE id = ?")->execute([$id]);
            $pdo->prepare("UPDATE affiliates SET total_earnings = total_earnings - ?, pending_payout = pending_payout - ? WHERE id = ?")->execute([$conv['commission'], $conv['commission'], $conv['affiliate_id']]);
        }
    }

    public static function getPayouts(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['affiliate_id'])) { $where[] = 'po.affiliate_id = ?'; $params[] = (int)$filters['affiliate_id']; }
        if (!empty($filters['status'])) { $where[] = 'po.status = ?'; $params[] = $filters['status']; }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM affiliate_payouts po WHERE {$wSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT po.*, a.name AS affiliate_name, a.email AS affiliate_email FROM affiliate_payouts po LEFT JOIN affiliates a ON po.affiliate_id = a.id WHERE {$wSql} ORDER BY po.created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['payouts' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function createPayout(int $affiliateId, float $amount, string $paymentMethod = '', string $reference = ''): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO affiliate_payouts (affiliate_id, amount, payment_method, payment_reference, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$affiliateId, $amount, $paymentMethod, $reference]);
        $id = (int)$pdo->lastInsertId();

        // Mark conversions as paid and reduce pending_payout
        $pdo->prepare("UPDATE affiliates SET pending_payout = GREATEST(0, pending_payout - ?) WHERE id = ?")->execute([$amount, $affiliateId]);
        $pdo->prepare("UPDATE affiliate_conversions SET status = 'paid' WHERE affiliate_id = ? AND status = 'approved'")->execute([$affiliateId]);

        if (function_exists('cms_event')) cms_event('affiliate.payout.created', ['affiliate_id' => $affiliateId, 'amount' => $amount]);
        return $id;
    }

    public static function completePayout(int $id, string $reference = ''): void
    {
        $fields = "status = 'completed'";
        $params = [];
        if ($reference) { $fields .= ", payment_reference = ?"; $params[] = $reference; }
        $params[] = $id;
        db()->prepare("UPDATE affiliate_payouts SET {$fields} WHERE id = ?")->execute($params);
    }

    public static function failPayout(int $id): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT affiliate_id, amount FROM affiliate_payouts WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
        $po = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($po) {
            $pdo->prepare("UPDATE affiliate_payouts SET status = 'failed' WHERE id = ?")->execute([$id]);
            $pdo->prepare("UPDATE affiliates SET pending_payout = pending_payout + ? WHERE id = ?")->execute([$po['amount'], $po['affiliate_id']]);
        }
    }

    public static function getSetting(string $key, string $default = ''): string
    {
        try {
            $stmt = db()->prepare("SELECT setting_value FROM affiliate_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            return $stmt->fetchColumn() ?: $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public static function setSetting(string $key, string $value): void
    {
        db()->prepare("INSERT INTO affiliate_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$key, $value]);
    }

    private static function generateCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        // Ensure uniqueness
        $pdo = db();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM affiliates WHERE referral_code = ?");
        $stmt->execute([$code]);
        if ((int)$stmt->fetchColumn() > 0) return self::generateCode();
        return $code;
    }
}
