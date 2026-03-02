<?php
declare(strict_types=1);

/**
 * ShopVariants — Product variants management
 * Size, color, material, etc. for Jessie AI CMS
 */
class ShopVariants
{
        /**
     * Ensure the product_variants table exists
     */
    public static function ensureTable(): void
    {
        db()->exec("CREATE TABLE IF NOT EXISTS product_variants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            variant_name VARCHAR(255) NOT NULL DEFAULT '',
            options JSON DEFAULT NULL,
            price DECIMAL(10,2) DEFAULT NULL,
            sale_price DECIMAL(10,2) DEFAULT NULL,
            sku VARCHAR(100) DEFAULT NULL,
            stock INT DEFAULT -1,
            image VARCHAR(500) DEFAULT NULL,
            sort_order INT DEFAULT 0,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_product (product_id),
            INDEX idx_sku (sku)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

/**
     * Get all variants for a product, ordered by sort_order
     */
    public static function getForProduct(int $productId): array
    {
        $stmt = db()->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$productId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['options'] = !empty($row['options']) ? json_decode($row['options'], true) : [];
        }
        return $rows;
    }

    /**
     * Get a single variant by ID with parsed options
     */
    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM product_variants WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['options'] = !empty($row['options']) ? json_decode($row['options'], true) : [];
        return $row;
    }

    /**
     * Create a variant for a product
     */
    public static function create(int $productId, array $data): int
    {
        $pdo = db();
        $options = $data['options'] ?? [];
        if (is_array($options)) {
            $options = json_encode($options);
        }
        $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, variant_name, options, price, sale_price, sku, stock, image, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $productId,
            $data['name'] ?? $data['variant_name'] ?? '',
            $options,
            ($data['price'] ?? null) !== '' && ($data['price'] ?? null) !== null ? (float)$data['price'] : null,
            ($data['sale_price'] ?? null) !== '' && ($data['sale_price'] ?? null) !== null ? (float)$data['sale_price'] : null,
            ($data['sku'] ?? null) !== '' ? ($data['sku'] ?? null) : null,
            (int)($data['stock'] ?? -1),
            ($data['image'] ?? null) !== '' ? ($data['image'] ?? null) : null,
            (int)($data['sort_order'] ?? 0),
            $data['status'] ?? 'active',
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Update a variant
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = db();
        $fields = [];
        $params = [];

        $map = ['variant_name', 'options', 'price', 'sale_price', 'sku', 'stock', 'image', 'sort_order', 'status'];
        foreach ($map as $key) {
            if (array_key_exists($key, $data)) {
                $val = $data[$key];
                if ($key === 'options' && is_array($val)) {
                    $val = json_encode($val);
                }
                if ($key === 'price' || $key === 'sale_price') {
                    $val = ($val !== '' && $val !== null) ? (float)$val : null;
                }
                if ($key === 'stock') {
                    $val = (int)$val;
                }
                if ($key === 'sort_order') {
                    $val = (int)$val;
                }
                $fields[] = "`{$key}` = ?";
                $params[] = $val;
            }
        }
        if (!array_key_exists('variant_name', $data) && array_key_exists('name', $data)) {
            $fields[] = "`variant_name` = ?";
            $params[] = $data['name'];
        }

        if (empty($fields)) return false;
        $params[] = $id;
        return $pdo->prepare("UPDATE product_variants SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    /**
     * Delete a single variant
     */
    public static function delete(int $id): bool
    {
        return db()->prepare("DELETE FROM product_variants WHERE id = ?")->execute([$id]);
    }

    /**
     * Delete all variants for a product
     */
    public static function deleteAllForProduct(int $productId): bool
    {
        return db()->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$productId]);
    }

    /**
     * Bulk save — delete existing + create new (for form submission)
     */
    public static function bulkSave(int $productId, array $variantsData): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            self::deleteAllForProduct($productId);
            $sortOrder = 0;
            foreach ($variantsData as $vData) {
                $name = trim($vData['name'] ?? $vData['variant_name'] ?? '');
                if ($name === '') continue;

                $options = [];
                if (!empty($vData['options']) && is_array($vData['options'])) {
                    foreach ($vData['options'] as $opt) {
                        $optName = trim($opt['name'] ?? '');
                        $optValue = trim($opt['value'] ?? '');
                        if ($optName !== '' && $optValue !== '') {
                            $options[] = ['name' => $optName, 'value' => $optValue];
                        }
                    }
                }

                $vData['options'] = $options;
                $vData['sort_order'] = $sortOrder++;
                self::create($productId, $vData);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log('ShopVariants::bulkSave error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get effective price for a variant (variant price ?? product price)
     */
    public static function getEffectivePrice(array $variant, array $product): float
    {
        if ($variant['sale_price'] !== null && (float)$variant['sale_price'] > 0) {
            return (float)$variant['sale_price'];
        }
        if ($variant['price'] !== null && (float)$variant['price'] > 0) {
            return (float)$variant['price'];
        }
        if ($product['sale_price'] !== null && (float)$product['sale_price'] > 0) {
            return (float)$product['sale_price'];
        }
        return (float)$product['price'];
    }

    /**
     * Check if sufficient stock is available for a variant
     */
    public static function checkStock(int $variantId, int $quantity): bool
    {
        $variant = self::get($variantId);
        if (!$variant) return false;
        if ((int)$variant['stock'] === -1) return true;
        return (int)$variant['stock'] >= $quantity;
    }

    /**
     * Decrement stock for a variant (for orders)
     */
    public static function decrementStock(int $variantId, int $quantity): bool
    {
        return db()->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ? AND stock > 0")->execute([$quantity, $variantId]);
    }

    /**
     * Build a human-readable label: "Size: M, Color: Red"
     */
    public static function getVariantLabel(array $variant): string
    {
        $options = $variant['options'] ?? [];
        if (is_string($options)) {
            $options = json_decode($options, true) ?: [];
        }
        if (empty($options)) {
            return $variant['variant_name'] ?? '';
        }
        $parts = [];
        foreach ($options as $opt) {
            $name = $opt['name'] ?? '';
            $value = $opt['value'] ?? '';
            if ($name !== '' && $value !== '') {
                $parts[] = $name . ': ' . $value;
            }
        }
        return !empty($parts) ? implode(', ', $parts) : ($variant['variant_name'] ?? '');
    }
}
