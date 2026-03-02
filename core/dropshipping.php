<?php
declare(strict_types=1);

/**
 * Dropshipping Core — Suppliers, Product Links, Stats
 * Main module for Jessie AI CMS Dropshipping
 */
class Dropshipping
{
    // ═══════════════════════════════════════════
    //  SUPPLIERS
    // ═══════════════════════════════════════════

    /**
     * Ensure all dropshipping tables exist
     */
    public static function ensureTables(): void
    {
        $pdo = db();

        $pdo->exec("CREATE TABLE IF NOT EXISTS ds_suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(50) DEFAULT 'manual',
            website VARCHAR(500) DEFAULT '',
            api_key VARCHAR(500) DEFAULT '',
            api_secret VARCHAR(500) DEFAULT '',
            api_base_url VARCHAR(500) DEFAULT '',
            contact_email VARCHAR(255) DEFAULT '',
            contact_name VARCHAR(255) DEFAULT '',
            notes TEXT DEFAULT NULL,
            settings JSON DEFAULT NULL,
            products_count INT DEFAULT 0,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS ds_product_links (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            supplier_id INT NOT NULL,
            supplier_product_id VARCHAR(255) DEFAULT NULL,
            supplier_product_url TEXT DEFAULT NULL,
            supplier_price DECIMAL(10,2) DEFAULT NULL,
            supplier_currency VARCHAR(10) DEFAULT 'USD',
            supplier_sku VARCHAR(100) DEFAULT NULL,
            our_price DECIMAL(10,2) DEFAULT NULL,
            profit_margin DECIMAL(10,2) DEFAULT NULL,
            variant_mapping JSON DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            sync_status VARCHAR(20) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_product_supplier (product_id, supplier_id),
            INDEX idx_supplier (supplier_id),
            INDEX idx_sync (sync_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS ds_imports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            source_url TEXT DEFAULT NULL,
            source_type VARCHAR(50) DEFAULT 'url',
            supplier_id INT DEFAULT NULL,
            product_id INT DEFAULT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            imported_data JSON DEFAULT NULL,
            ai_processed TINYINT(1) DEFAULT 0,
            ai_results JSON DEFAULT NULL,
            error_message TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_supplier (supplier_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS ds_order_forwards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            supplier_id INT NOT NULL,
            supplier_order_id VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            cost_total DECIMAL(10,2) DEFAULT 0,
            tracking_number VARCHAR(255) DEFAULT NULL,
            tracking_url VARCHAR(500) DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            response_data JSON DEFAULT NULL,
            forwarded_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL,
            INDEX idx_order (order_id),
            INDEX idx_supplier (supplier_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS ds_price_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(30) DEFAULT 'multiplier',
            value DECIMAL(10,2) DEFAULT 2.00,
            apply_to VARCHAR(20) DEFAULT 'all',
            apply_to_id INT DEFAULT NULL,
            min_price DECIMAL(10,2) DEFAULT 0,
            max_price DECIMAL(10,2) DEFAULT 0,
            round_to VARCHAR(10) DEFAULT '0.99',
            priority INT DEFAULT 0,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_priority (priority)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }


    public static function getSuppliers(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(s.name LIKE ? OR s.website LIKE ? OR s.contact_email LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }
        if (!empty($filters['status'])) {
            $where[] = 's.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[] = 's.type = ?';
            $params[] = $filters['type'];
        }

        $whereStr = implode(' AND ', $where);
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM ds_suppliers s WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT s.*,
                   (SELECT COUNT(*) FROM ds_product_links pl WHERE pl.supplier_id = s.id) AS linked_products
            FROM ds_suppliers s
            WHERE {$whereStr}
            ORDER BY s.name ASC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);

        return [
            'suppliers'  => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
        ];
    }

    public static function getSupplier(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM ds_suppliers WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getAllSuppliers(): array
    {
        $pdo = db();
        return $pdo->query("SELECT id, name, type, status FROM ds_suppliers WHERE status = 'active' ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function createSupplier(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO ds_suppliers (name, type, website, api_key, api_secret, api_base_url, contact_email, contact_name, notes, settings, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($data['name'] ?? ''),
            $data['type'] ?? 'manual',
            trim($data['website'] ?? ''),
            trim($data['api_key'] ?? ''),
            trim($data['api_secret'] ?? ''),
            trim($data['api_base_url'] ?? ''),
            trim($data['contact_email'] ?? ''),
            trim($data['contact_name'] ?? ''),
            trim($data['notes'] ?? ''),
            !empty($data['settings']) ? (is_string($data['settings']) ? $data['settings'] : json_encode($data['settings'])) : null,
            $data['status'] ?? 'active',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function updateSupplier(int $id, array $data): bool
    {
        $pdo = db();
        $fields = [];
        $params = [];

        $allowed = ['name', 'type', 'website', 'api_key', 'api_secret', 'api_base_url', 'contact_email', 'contact_name', 'notes', 'status'];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $fields[] = "{$key} = ?";
                $params[] = is_string($data[$key]) ? trim($data[$key]) : $data[$key];
            }
        }
        if (array_key_exists('settings', $data)) {
            $fields[] = "settings = ?";
            $params[] = is_string($data['settings']) ? $data['settings'] : json_encode($data['settings']);
        }

        if (empty($fields)) return false;

        $params[] = $id;
        $stmt = $pdo->prepare("UPDATE ds_suppliers SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function deleteSupplier(int $id): bool
    {
        $pdo = db();
        // Unlink products first
        $pdo->prepare("DELETE FROM ds_product_links WHERE supplier_id = ?")->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM ds_suppliers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ═══════════════════════════════════════════
    //  PRODUCT LINKS
    // ═══════════════════════════════════════════

    public static function getProductLinks(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['supplier_id'])) {
            $where[] = 'pl.supplier_id = ?';
            $params[] = (int)$filters['supplier_id'];
        }
        if (!empty($filters['sync_status'])) {
            $where[] = 'pl.sync_status = ?';
            $params[] = $filters['sync_status'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE ? OR pl.supplier_product_url LIKE ? OR pl.supplier_sku LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }

        $whereStr = implode(' AND ', $where);
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM ds_product_links pl LEFT JOIN products p ON pl.product_id = p.id WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT pl.*, p.name AS product_name, p.slug AS product_slug, p.image AS product_image,
                   p.price AS product_price, p.status AS product_status,
                   s.name AS supplier_name, s.type AS supplier_type
            FROM ds_product_links pl
            LEFT JOIN products p ON pl.product_id = p.id
            LEFT JOIN ds_suppliers s ON pl.supplier_id = s.id
            WHERE {$whereStr}
            ORDER BY pl.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);

        return [
            'links'      => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
        ];
    }

    public static function linkProduct(int $productId, int $supplierId, array $data = []): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO ds_product_links (product_id, supplier_id, supplier_product_id, supplier_product_url,
                supplier_price, supplier_currency, supplier_sku, our_price, profit_margin, variant_mapping, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                supplier_product_id = VALUES(supplier_product_id),
                supplier_product_url = VALUES(supplier_product_url),
                supplier_price = VALUES(supplier_price),
                supplier_currency = VALUES(supplier_currency),
                supplier_sku = VALUES(supplier_sku),
                our_price = VALUES(our_price),
                profit_margin = VALUES(profit_margin),
                variant_mapping = VALUES(variant_mapping),
                notes = VALUES(notes)
        ");

        $supplierPrice = isset($data['supplier_price']) ? (float)$data['supplier_price'] : null;
        $ourPrice = isset($data['our_price']) ? (float)$data['our_price'] : null;
        $margin = ($supplierPrice && $ourPrice) ? round($ourPrice - $supplierPrice, 2) : null;

        $stmt->execute([
            $productId,
            $supplierId,
            $data['supplier_product_id'] ?? null,
            $data['supplier_product_url'] ?? null,
            $supplierPrice,
            $data['supplier_currency'] ?? 'USD',
            $data['supplier_sku'] ?? null,
            $ourPrice,
            $margin,
            !empty($data['variant_mapping']) ? json_encode($data['variant_mapping']) : null,
            $data['notes'] ?? null,
        ]);

        // Update supplier product count
        self::updateSupplierProductCount($supplierId);

        return (int)$pdo->lastInsertId();
    }

    public static function unlinkProduct(int $productId, int $supplierId): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM ds_product_links WHERE product_id = ? AND supplier_id = ?");
        $result = $stmt->execute([$productId, $supplierId]);
        self::updateSupplierProductCount($supplierId);
        return $result;
    }

    public static function getProductLink(int $productId): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT pl.*, s.name AS supplier_name, s.type AS supplier_type
            FROM ds_product_links pl
            LEFT JOIN ds_suppliers s ON pl.supplier_id = s.id
            WHERE pl.product_id = ?
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private static function updateSupplierProductCount(int $supplierId): void
    {
        $pdo = db();
        $pdo->prepare("UPDATE ds_suppliers SET products_count = (SELECT COUNT(*) FROM ds_product_links WHERE supplier_id = ?) WHERE id = ?")
            ->execute([$supplierId, $supplierId]);
    }

    // ═══════════════════════════════════════════
    //  IMPORTS
    // ═══════════════════════════════════════════

    public static function createImport(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO ds_imports (source_url, source_type, supplier_id, status, imported_data)
            VALUES (?, ?, ?, 'pending', ?)
        ");
        $stmt->execute([
            $data['source_url'] ?? null,
            $data['source_type'] ?? 'url',
            !empty($data['supplier_id']) ? (int)$data['supplier_id'] : null,
            !empty($data['imported_data']) ? json_encode($data['imported_data']) : null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function updateImport(int $id, array $data): bool
    {
        $pdo = db();
        $fields = [];
        $params = [];

        foreach (['status', 'product_id', 'ai_processed', 'error_message'] as $key) {
            if (array_key_exists($key, $data)) {
                $fields[] = "{$key} = ?";
                $params[] = $data[$key];
            }
        }
        foreach (['imported_data', 'ai_results'] as $jsonKey) {
            if (array_key_exists($jsonKey, $data)) {
                $fields[] = "{$jsonKey} = ?";
                $params[] = is_string($data[$jsonKey]) ? $data[$jsonKey] : json_encode($data[$jsonKey]);
            }
        }

        if (empty($fields)) return false;
        $params[] = $id;
        return $pdo->prepare("UPDATE ds_imports SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function getImports(int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $total = (int)$pdo->query("SELECT COUNT(*) FROM ds_imports")->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT i.*, s.name AS supplier_name, p.name AS product_name
            FROM ds_imports i
            LEFT JOIN ds_suppliers s ON i.supplier_id = s.id
            LEFT JOIN products p ON i.product_id = p.id
            ORDER BY i.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute();

        return [
            'imports'    => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
        ];
    }

    // ═══════════════════════════════════════════
    //  DASHBOARD STATS
    // ═══════════════════════════════════════════

    public static function getDashboardStats(): array
    {
        $pdo = db();

        $suppliers = (int)$pdo->query("SELECT COUNT(*) FROM ds_suppliers WHERE status = 'active'")->fetchColumn();
        $linkedProducts = (int)$pdo->query("SELECT COUNT(*) FROM ds_product_links")->fetchColumn();
        $pendingImports = (int)$pdo->query("SELECT COUNT(*) FROM ds_imports WHERE status = 'pending'")->fetchColumn();
        $totalImports = (int)$pdo->query("SELECT COUNT(*) FROM ds_imports")->fetchColumn();
        $failedImports = (int)$pdo->query("SELECT COUNT(*) FROM ds_imports WHERE status = 'failed'")->fetchColumn();
        $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM ds_order_forwards WHERE status = 'pending'")->fetchColumn();

        // Profit stats
        $profitRow = $pdo->query("SELECT SUM(profit_margin) AS total_profit, AVG(profit_margin) AS avg_margin FROM ds_product_links WHERE profit_margin IS NOT NULL")->fetch(\PDO::FETCH_ASSOC);

        // Sync issues
        $syncErrors = (int)$pdo->query("SELECT COUNT(*) FROM ds_product_links WHERE sync_status = 'error'")->fetchColumn();

        // Recent imports
        $recentImports = $pdo->query("
            SELECT i.id, i.source_url, i.source_type, i.status, i.created_at, p.name AS product_name
            FROM ds_imports i LEFT JOIN products p ON i.product_id = p.id
            ORDER BY i.created_at DESC LIMIT 5
        ")->fetchAll(\PDO::FETCH_ASSOC);

        // Price rules count
        $priceRules = (int)$pdo->query("SELECT COUNT(*) FROM ds_price_rules WHERE status = 'active'")->fetchColumn();

        return [
            'suppliers'       => $suppliers,
            'linked_products' => $linkedProducts,
            'pending_imports' => $pendingImports,
            'total_imports'   => $totalImports,
            'failed_imports'  => $failedImports,
            'pending_orders'  => $pendingOrders,
            'total_profit'    => round((float)($profitRow['total_profit'] ?? 0), 2),
            'avg_margin'      => round((float)($profitRow['avg_margin'] ?? 0), 2),
            'sync_errors'     => $syncErrors,
            'price_rules'     => $priceRules,
            'recent_imports'  => $recentImports,
        ];
    }

    // ═══════════════════════════════════════════
    //  INSTALL / MIGRATE
    // ═══════════════════════════════════════════

    public static function isInstalled(): bool
    {
        try {
            $pdo = db();
            $pdo->query("SELECT 1 FROM ds_suppliers LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

// ─── Event: Auto-forward dropship orders to suppliers ───
if (function_exists('cms_on')) {
    cms_on('shop.order.created', function ($data) {
        $orderId = (int)($data['order_id'] ?? $data['id'] ?? 0);
        if ($orderId <= 0) return;

        // Check if auto-forward is enabled
        $pdo = db();
        $autoForward = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'ds_auto_forward'");
        $autoForward->execute();
        $enabled = $autoForward->fetchColumn();
        if ($enabled !== '1') return;

        require_once CMS_ROOT . '/core/dropshipping-orders.php';
        \DSOrders::processOrder($orderId);
    });
}
