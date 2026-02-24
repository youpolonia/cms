<?php
declare(strict_types=1);

/**
 * Dropshipping Price Rules Engine
 * Calculates sell prices based on supplier costs and configurable rules
 */
class DSPricing
{
    // ─── CRUD ───

    public static function getRules(): array
    {
        $pdo = db();
        return $pdo->query("SELECT * FROM ds_price_rules ORDER BY priority DESC, id ASC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getRule(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM ds_price_rules WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function createRule(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO ds_price_rules (name, type, value, apply_to, apply_to_id, min_price, max_price, round_to, priority, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($data['name'] ?? 'New Rule'),
            $data['type'] ?? 'multiplier',
            (float)($data['value'] ?? 2.0),
            $data['apply_to'] ?? 'all',
            !empty($data['apply_to_id']) ? (int)$data['apply_to_id'] : null,
            (float)($data['min_price'] ?? 0),
            (float)($data['max_price'] ?? 0),
            $data['round_to'] ?? '0.99',
            (int)($data['priority'] ?? 0),
            $data['status'] ?? 'active',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function updateRule(int $id, array $data): bool
    {
        $pdo = db();
        $fields = [];
        $params = [];
        $allowed = ['name', 'type', 'value', 'apply_to', 'apply_to_id', 'min_price', 'max_price', 'round_to', 'priority', 'status'];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $fields[] = "`{$key}` = ?";
                $params[] = $data[$key];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        return $pdo->prepare("UPDATE ds_price_rules SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function deleteRule(int $id): bool
    {
        $pdo = db();
        return $pdo->prepare("DELETE FROM ds_price_rules WHERE id = ?")->execute([$id]);
    }

    // ─── CALCULATION ───

    /**
     * Calculate sell price from supplier price.
     *
     * @param float $supplierPrice Cost price from supplier
     * @param int|null $categoryId Product category ID (for category-specific rules)
     * @param int|null $supplierId Supplier ID (for supplier-specific rules)
     * @param int|null $productId Product ID (for product-specific rules)
     * @return array ['price' => float, 'rule_name' => string, 'margin' => float, 'margin_pct' => float]
     */
    public static function calculatePrice(float $supplierPrice, ?int $categoryId = null, ?int $supplierId = null, ?int $productId = null): array
    {
        $rules = self::getActiveRules();

        // Find best matching rule (most specific wins, highest priority)
        $bestRule = null;

        foreach ($rules as $rule) {
            $matches = false;

            switch ($rule['apply_to']) {
                case 'product':
                    $matches = ($productId !== null && (int)$rule['apply_to_id'] === $productId);
                    break;
                case 'supplier':
                    $matches = ($supplierId !== null && (int)$rule['apply_to_id'] === $supplierId);
                    break;
                case 'category':
                    $matches = ($categoryId !== null && (int)$rule['apply_to_id'] === $categoryId);
                    break;
                case 'all':
                    $matches = true;
                    break;
            }

            if ($matches) {
                // More specific rules take priority; within same specificity, higher priority wins
                if ($bestRule === null) {
                    $bestRule = $rule;
                } else {
                    $specificity = ['all' => 0, 'category' => 1, 'supplier' => 2, 'product' => 3];
                    $newSpec = $specificity[$rule['apply_to']] ?? 0;
                    $oldSpec = $specificity[$bestRule['apply_to']] ?? 0;
                    if ($newSpec > $oldSpec || ($newSpec === $oldSpec && (int)$rule['priority'] > (int)$bestRule['priority'])) {
                        $bestRule = $rule;
                    }
                }
            }
        }

        // Default rule: 2x multiplier
        if ($bestRule === null) {
            $sellPrice = $supplierPrice * 2;
            return [
                'price'      => round($sellPrice, 2),
                'rule_name'  => 'Default (2× markup)',
                'rule_id'    => null,
                'margin'     => round($sellPrice - $supplierPrice, 2),
                'margin_pct' => $supplierPrice > 0 ? round((($sellPrice - $supplierPrice) / $supplierPrice) * 100, 1) : 0,
            ];
        }

        // Apply rule
        $value = (float)$bestRule['value'];
        $sellPrice = 0;

        switch ($bestRule['type']) {
            case 'multiplier':
                $sellPrice = $supplierPrice * $value;
                break;
            case 'fixed_markup':
                $sellPrice = $supplierPrice + $value;
                break;
            case 'percentage_markup':
                $sellPrice = $supplierPrice * (1 + $value / 100);
                break;
            default:
                $sellPrice = $supplierPrice * 2;
        }

        // Apply min/max
        $minPrice = (float)$bestRule['min_price'];
        $maxPrice = (float)$bestRule['max_price'];
        if ($minPrice > 0 && $sellPrice < $minPrice) {
            $sellPrice = $minPrice;
        }
        if ($maxPrice > 0 && $sellPrice > $maxPrice) {
            $sellPrice = $maxPrice;
        }

        // Apply rounding
        $sellPrice = self::applyRounding($sellPrice, $bestRule['round_to'] ?? 'none');

        $margin = round($sellPrice - $supplierPrice, 2);
        $marginPct = $supplierPrice > 0 ? round(($margin / $supplierPrice) * 100, 1) : 0;

        return [
            'price'      => $sellPrice,
            'rule_name'  => $bestRule['name'],
            'rule_id'    => (int)$bestRule['id'],
            'margin'     => $margin,
            'margin_pct' => $marginPct,
        ];
    }

    /**
     * Preview pricing for a supplier price with all applicable rules.
     */
    public static function previewAllRules(float $supplierPrice): array
    {
        $rules = self::getActiveRules();
        $previews = [];

        foreach ($rules as $rule) {
            $value = (float)$rule['value'];
            $sellPrice = 0;

            switch ($rule['type']) {
                case 'multiplier':
                    $sellPrice = $supplierPrice * $value;
                    break;
                case 'fixed_markup':
                    $sellPrice = $supplierPrice + $value;
                    break;
                case 'percentage_markup':
                    $sellPrice = $supplierPrice * (1 + $value / 100);
                    break;
            }

            $sellPrice = self::applyRounding($sellPrice, $rule['round_to'] ?? 'none');
            $margin = round($sellPrice - $supplierPrice, 2);

            $previews[] = [
                'rule_id'    => (int)$rule['id'],
                'rule_name'  => $rule['name'],
                'type'       => $rule['type'],
                'value'      => $value,
                'apply_to'   => $rule['apply_to'],
                'sell_price' => $sellPrice,
                'margin'     => $margin,
                'margin_pct' => $supplierPrice > 0 ? round(($margin / $supplierPrice) * 100, 1) : 0,
            ];
        }

        return $previews;
    }

    // ─── HELPERS ───

    private static function getActiveRules(): array
    {
        $pdo = db();
        return $pdo->query("SELECT * FROM ds_price_rules WHERE status = 'active' ORDER BY priority DESC, id ASC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    private static function applyRounding(float $price, string $roundTo): float
    {
        switch ($roundTo) {
            case '0.99':
                return floor($price) + 0.99;
            case '0.95':
                return floor($price) + 0.95;
            case '0.00':
                return round($price);
            default:
                return round($price, 2);
        }
    }
}
