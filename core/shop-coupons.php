<?php
declare(strict_types=1);

/**
 * Shop Coupons — Discount code system for Jessie AI CMS
 */
class ShopCoupons
{
        /**
     * Ensure the coupons table exists
     */
    public static function ensureTable(): void
    {
        db()->exec("CREATE TABLE IF NOT EXISTS coupons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            type VARCHAR(20) NOT NULL DEFAULT 'percentage',
            value DECIMAL(10,2) NOT NULL DEFAULT 0,
            min_order DECIMAL(10,2) DEFAULT NULL,
            max_discount DECIMAL(10,2) DEFAULT NULL,
            max_uses INT DEFAULT NULL,
            per_customer_limit INT DEFAULT 1,
            used_count INT DEFAULT 0,
            valid_from DATETIME DEFAULT NULL,
            valid_until DATETIME DEFAULT NULL,
            applies_to VARCHAR(20) DEFAULT 'all',
            applies_to_ids TEXT DEFAULT NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_code (code),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

/**
     * Validate a coupon code against cart subtotal and optional category
     */
    public static function validate(string $code, float $subtotal, ?int $categoryId = null): array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return ['valid' => false, 'discount' => 0, 'message' => 'Please enter a coupon code.', 'coupon' => []];
        }

        $coupon = self::getByCode($code);
        if (!$coupon) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Invalid coupon code.', 'coupon' => []];
        }

        if ($coupon['status'] !== 'active') {
            return ['valid' => false, 'discount' => 0, 'message' => 'This coupon is no longer active.', 'coupon' => $coupon];
        }

        $now = date('Y-m-d H:i:s');
        if ($coupon['valid_from'] && $now < $coupon['valid_from']) {
            return ['valid' => false, 'discount' => 0, 'message' => 'This coupon is not yet valid.', 'coupon' => $coupon];
        }
        if ($coupon['valid_until'] && $now > $coupon['valid_until']) {
            return ['valid' => false, 'discount' => 0, 'message' => 'This coupon has expired.', 'coupon' => $coupon];
        }

        if ($coupon['max_uses'] !== null && $coupon['used_count'] >= $coupon['max_uses']) {
            return ['valid' => false, 'discount' => 0, 'message' => 'This coupon has reached its usage limit.', 'coupon' => $coupon];
        }

        if ($coupon['min_order'] !== null && $subtotal < (float)$coupon['min_order']) {
            $minFormatted = number_format((float)$coupon['min_order'], 2);
            return ['valid' => false, 'discount' => 0, 'message' => "Minimum order of {$minFormatted} required for this coupon.", 'coupon' => $coupon];
        }

        // Check applies_to
        if ($coupon['applies_to'] === 'category' && $categoryId !== null && !empty($coupon['applies_to_ids'])) {
            $ids = array_map('intval', explode(',', $coupon['applies_to_ids']));
            if (!in_array($categoryId, $ids)) {
                return ['valid' => false, 'discount' => 0, 'message' => 'This coupon does not apply to items in your cart.', 'coupon' => $coupon];
            }
        }

        // Calculate discount
        $discount = 0;
        switch ($coupon['type']) {
            case 'percentage':
                $discount = round($subtotal * ((float)$coupon['value'] / 100), 2);
                break;
            case 'fixed':
                $discount = min((float)$coupon['value'], $subtotal);
                break;
            case 'free_shipping':
                $discount = 0; // handled separately in cart
                break;
        }

        // Cap at max_discount
        if ($coupon['max_discount'] !== null && $discount > (float)$coupon['max_discount']) {
            $discount = (float)$coupon['max_discount'];
        }

        $msg = 'Coupon applied!';
        if ($coupon['type'] === 'percentage') {
            $msg = "Coupon applied: {$coupon['value']}% off";
        } elseif ($coupon['type'] === 'fixed') {
            $msg = "Coupon applied: " . number_format((float)$coupon['value'], 2) . " off";
        } elseif ($coupon['type'] === 'free_shipping') {
            $msg = "Coupon applied: Free shipping!";
        }

        return ['valid' => true, 'discount' => $discount, 'message' => $msg, 'coupon' => $coupon];
    }

    /**
     * Apply coupon after order: increment used_count, fire event
     */
    public static function apply(string $code, int $orderId, float $discount): bool
    {
        $code = strtoupper(trim($code));
        $pdo = db();
        $ok = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?")->execute([$code]);

        if ($ok && function_exists('cms_event')) {
            cms_event('shop.coupon.used', [
                'code' => $code,
                'order_id' => $orderId,
                'discount' => $discount,
            ]);
        }

        return $ok;
    }

    /**
     * Create a new coupon
     */
    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, min_order, max_discount, max_uses, per_customer_limit, valid_from, valid_until, applies_to, applies_to_ids, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            strtoupper(trim($data['code'] ?? '')),
            $data['type'] ?? 'percentage',
            (float)($data['value'] ?? 0),
            (!empty($data['min_order']) && $data['min_order'] !== '') ? (float)$data['min_order'] : null,
            (!empty($data['max_discount']) && $data['max_discount'] !== '') ? (float)$data['max_discount'] : null,
            (!empty($data['max_uses']) && $data['max_uses'] !== '') ? (int)$data['max_uses'] : null,
            (int)($data['per_customer_limit'] ?? 1),
            (!empty($data['valid_from']) && $data['valid_from'] !== '') ? $data['valid_from'] : null,
            (!empty($data['valid_until']) && $data['valid_until'] !== '') ? $data['valid_until'] : null,
            $data['applies_to'] ?? 'all',
            (!empty($data['applies_to_ids']) && $data['applies_to_ids'] !== '') ? $data['applies_to_ids'] : null,
            $data['status'] ?? 'active',
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Update an existing coupon
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = db();
        $fields = [];
        $params = [];
        $map = ['code', 'type', 'value', 'min_order', 'max_discount', 'max_uses', 'per_customer_limit', 'valid_from', 'valid_until', 'applies_to', 'applies_to_ids', 'status'];

        foreach ($map as $key) {
            if (array_key_exists($key, $data)) {
                $val = $data[$key];
                if ($key === 'code') $val = strtoupper(trim((string)$val));
                if ($key === 'value') $val = (float)$val;
                if (in_array($key, ['min_order', 'max_discount'])) $val = ($val !== '' && $val !== null) ? (float)$val : null;
                if ($key === 'max_uses') $val = ($val !== '' && $val !== null) ? (int)$val : null;
                if ($key === 'per_customer_limit') $val = (int)$val;
                if (in_array($key, ['valid_from', 'valid_until'])) $val = ($val !== '' && $val !== null) ? $val : null;
                if ($key === 'applies_to_ids') $val = ($val !== '' && $val !== null) ? $val : null;
                $fields[] = "`{$key}` = ?";
                $params[] = $val;
            }
        }

        if (empty($fields)) return false;
        $params[] = $id;
        return $pdo->prepare("UPDATE coupons SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    /**
     * Delete a coupon
     */
    public static function delete(int $id): bool
    {
        return db()->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
    }

    /**
     * Get a coupon by ID
     */
    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get a coupon by code
     */
    public static function getByCode(string $code): ?array
    {
        $stmt = db()->prepare("SELECT * FROM coupons WHERE code = ?");
        $stmt->execute([strtoupper(trim($code))]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get all coupons with pagination and filters
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = 'code LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'type = ?';
            $params[] = $filters['type'];
        }

        $whereStr = implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM coupons WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE {$whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        $coupons = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return compact('coupons', 'total', 'page', 'perPage', 'totalPages');
    }

    /**
     * Generate a unique random coupon code
     */
    public static function generateCode(string $prefix = '', int $length = 8): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $pdo = db();
        $attempts = 0;
        do {
            $code = $prefix;
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM coupons WHERE code = ?");
            $stmt->execute([$code]);
            $exists = (int)$stmt->fetchColumn() > 0;
            $attempts++;
        } while ($exists && $attempts < 100);

        return $code;
    }

    /**
     * Get coupon statistics
     */
    public static function getStats(): array
    {
        $pdo = db();
        $totalCoupons = (int)$pdo->query("SELECT COUNT(*) FROM coupons")->fetchColumn();
        $activeCoupons = (int)$pdo->query("SELECT COUNT(*) FROM coupons WHERE status = 'active'")->fetchColumn();
        $totalUsed = (int)$pdo->query("SELECT COALESCE(SUM(used_count), 0) FROM coupons")->fetchColumn();

        $mostUsedStmt = $pdo->query("SELECT code, used_count FROM coupons ORDER BY used_count DESC LIMIT 1");
        $mostUsed = $mostUsedStmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total_coupons' => $totalCoupons,
            'active_coupons' => $activeCoupons,
            'total_used' => $totalUsed,
            'most_used' => $mostUsed ?: null,
        ];
    }
}
