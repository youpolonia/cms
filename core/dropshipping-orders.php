<?php
declare(strict_types=1);

/**
 * Dropshipping Orders — Order forwarding to suppliers + tracking sync
 * Faza 2: Auto-forward orders containing dropship products to suppliers
 */

require_once CMS_ROOT . '/core/dropshipping.php';

class DSOrders
{
    // ═══════════════════════════════════════════
    //  ORDER FORWARDING
    // ═══════════════════════════════════════════

    /**
     * Process a new order — check if it has dropship products and forward to suppliers.
     * Called from event handler: cms_event('shop.order.created')
     */
    public static function processOrder(int $orderId): array
    {
        $pdo = db();

        // Get order details
        $order = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $order->execute([$orderId]);
        $order = $order->fetch(\PDO::FETCH_ASSOC);
        if (!$order) {
            return ['ok' => false, 'error' => 'Order not found'];
        }

        // Get order items
        $items = $pdo->prepare("SELECT oi.*, p.name AS product_name, p.slug AS product_slug
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?");
        $items->execute([$orderId]);
        $items = $items->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($items)) {
            return ['ok' => false, 'error' => 'No items in order'];
        }

        // Find dropship items (those linked to a supplier)
        $productIds = array_column($items, 'product_id');
        if (empty($productIds)) {
            return ['ok' => true, 'forwarded' => 0, 'message' => 'No product IDs'];
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $links = $pdo->prepare("
            SELECT dl.*, s.name AS supplier_name, s.type AS supplier_type,
                   s.api_key, s.api_secret, s.api_base_url, s.contact_email, s.settings
            FROM ds_product_links dl
            JOIN ds_suppliers s ON dl.supplier_id = s.id
            WHERE dl.product_id IN ({$placeholders}) AND s.status = 'active'
        ");
        $links->execute($productIds);
        $links = $links->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($links)) {
            return ['ok' => true, 'forwarded' => 0, 'message' => 'No dropship items in order'];
        }

        // Group items by supplier
        $supplierItems = [];
        $linkMap = [];
        foreach ($links as $link) {
            $linkMap[$link['product_id']] = $link;
        }

        foreach ($items as $item) {
            $pid = (int)$item['product_id'];
            if (isset($linkMap[$pid])) {
                $sid = (int)$linkMap[$pid]['supplier_id'];
                if (!isset($supplierItems[$sid])) {
                    $supplierItems[$sid] = [
                        'supplier' => $linkMap[$pid],
                        'items'    => [],
                        'total'    => 0,
                    ];
                }
                $supplierItems[$sid]['items'][] = $item;
                $supplierItems[$sid]['total'] += (float)($linkMap[$pid]['supplier_price'] ?? 0) * (int)($item['quantity'] ?? 1);
            }
        }

        // Forward to each supplier
        $forwarded = 0;
        $results = [];
        foreach ($supplierItems as $supplierId => $data) {
            $result = self::forwardToSupplier($orderId, $supplierId, $data, $order);
            $results[] = $result;
            if ($result['ok']) $forwarded++;
        }

        return [
            'ok'        => true,
            'forwarded' => $forwarded,
            'total'     => count($supplierItems),
            'results'   => $results,
        ];
    }

    /**
     * Forward order items to a specific supplier.
     */
    public static function forwardToSupplier(int $orderId, int $supplierId, array $data, array $order): array
    {
        $pdo = db();
        $supplier = $data['supplier'];

        // Check if already forwarded
        $existing = $pdo->prepare("SELECT id FROM ds_order_forwards WHERE order_id = ? AND supplier_id = ?");
        $existing->execute([$orderId, $supplierId]);
        if ($existing->fetch()) {
            return ['ok' => false, 'error' => 'Already forwarded to this supplier', 'supplier_id' => $supplierId];
        }

        // Create forward record
        $stmt = $pdo->prepare("
            INSERT INTO ds_order_forwards (order_id, supplier_id, status, cost_total, notes, created_at)
            VALUES (?, ?, 'pending', ?, ?, NOW())
        ");
        $itemSummary = array_map(fn($i) => ($i['product_name'] ?? 'Product') . ' ×' . ($i['quantity'] ?? 1), $data['items']);
        $stmt->execute([$orderId, $supplierId, $data['total'], implode(', ', $itemSummary)]);
        $forwardId = (int)$pdo->lastInsertId();

        // Try to forward based on supplier type
        $type = $supplier['supplier_type'] ?? 'manual';
        $result = ['ok' => true, 'forward_id' => $forwardId, 'supplier_id' => $supplierId, 'method' => $type];

        switch ($type) {
            case 'aliexpress':
            case 'cjdropshipping':
            case 'generic_api':
                // API forwarding
                $apiResult = self::forwardViaApi($forwardId, $supplier, $data, $order);
                if ($apiResult['ok']) {
                    $pdo->prepare("UPDATE ds_order_forwards SET status = 'sent', supplier_order_id = ?, forwarded_at = NOW(), response_data = ? WHERE id = ?")
                        ->execute([$apiResult['supplier_order_id'] ?? null, json_encode($apiResult), $forwardId]);
                    $result['status'] = 'sent';
                    $result['supplier_order_id'] = $apiResult['supplier_order_id'] ?? null;
                } else {
                    $pdo->prepare("UPDATE ds_order_forwards SET status = 'failed', notes = ?, response_data = ? WHERE id = ?")
                        ->execute([$apiResult['error'] ?? 'API error', json_encode($apiResult), $forwardId]);
                    $result['status'] = 'failed';
                    $result['error'] = $apiResult['error'];
                }
                break;

            case 'manual':
            case 'csv':
            default:
                // Email notification to supplier
                $emailResult = self::notifySupplierByEmail($forwardId, $supplier, $data, $order);
                $pdo->prepare("UPDATE ds_order_forwards SET status = 'sent', forwarded_at = NOW() WHERE id = ?")
                    ->execute([$forwardId]);
                $result['status'] = 'sent';
                $result['method'] = 'email';
                $result['email_sent'] = $emailResult;
                break;
        }

        // Fire event
        if (function_exists('cms_event')) {
            cms_event('dropshipping.order.forwarded', [
                'forward_id'  => $forwardId,
                'order_id'    => $orderId,
                'supplier_id' => $supplierId,
                'status'      => $result['status'],
            ]);
        }

        return $result;
    }

    /**
     * Forward order via supplier API.
     */
    private static function forwardViaApi(int $forwardId, array $supplier, array $data, array $order): array
    {
        $baseUrl = $supplier['api_base_url'] ?? '';
        $apiKey = $supplier['api_key'] ?? '';

        if (empty($baseUrl) || empty($apiKey)) {
            return ['ok' => false, 'error' => 'Supplier API not configured (missing base URL or API key)'];
        }

        // Build order payload
        $payload = [
            'external_order_id' => $order['id'],
            'shipping_address'  => [
                'name'    => $order['customer_name'] ?? '',
                'email'   => $order['customer_email'] ?? '',
                'phone'   => $order['customer_phone'] ?? '',
                'address' => $order['shipping_address'] ?? $order['address'] ?? '',
                'city'    => $order['shipping_city'] ?? $order['city'] ?? '',
                'state'   => $order['shipping_state'] ?? $order['state'] ?? '',
                'zip'     => $order['shipping_zip'] ?? $order['zip'] ?? '',
                'country' => $order['shipping_country'] ?? $order['country'] ?? '',
            ],
            'items' => [],
        ];

        foreach ($data['items'] as $item) {
            $payload['items'][] = [
                'sku'      => $supplier['supplier_sku'] ?? '',
                'product_id' => $supplier['supplier_product_id'] ?? '',
                'quantity' => (int)($item['quantity'] ?? 1),
                'variant'  => $item['variant'] ?? null,
            ];
        }

        // POST to supplier API
        $url = rtrim($baseUrl, '/') . '/orders';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'X-Api-Key: ' . $apiKey,
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['ok' => false, 'error' => "cURL error: {$error}"];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'ok'                => true,
                'supplier_order_id' => $decoded['order_id'] ?? $decoded['id'] ?? null,
                'response'          => $decoded,
            ];
        }

        return [
            'ok'        => false,
            'error'     => "API returned HTTP {$httpCode}: " . ($decoded['message'] ?? $decoded['error'] ?? mb_substr($response, 0, 200)),
            'http_code' => $httpCode,
        ];
    }

    /**
     * Notify supplier by email about a new order.
     */
    private static function notifySupplierByEmail(int $forwardId, array $supplier, array $data, array $order): bool
    {
        $email = $supplier['contact_email'] ?? '';
        if (empty($email)) {
            return false;
        }

        $itemLines = '';
        foreach ($data['items'] as $item) {
            $name = $item['product_name'] ?? 'Product';
            $qty = (int)($item['quantity'] ?? 1);
            $sku = $supplier['supplier_sku'] ?? 'N/A';
            $url = $supplier['supplier_product_url'] ?? '';
            $itemLines .= "- {$name} × {$qty} (SKU: {$sku})" . ($url ? " — {$url}" : '') . "\n";
        }

        $subject = "New Order #{$order['id']} — Dropship Fulfillment Request";
        $body = "Hello {$supplier['supplier_name']},\n\n"
            . "A new order requires fulfillment:\n\n"
            . "Order #: {$order['id']}\n"
            . "Date: " . date('Y-m-d H:i') . "\n\n"
            . "Items:\n{$itemLines}\n"
            . "Ship To:\n"
            . ($order['customer_name'] ?? '') . "\n"
            . ($order['shipping_address'] ?? $order['address'] ?? '') . "\n"
            . ($order['shipping_city'] ?? $order['city'] ?? '') . ', '
            . ($order['shipping_state'] ?? $order['state'] ?? '') . ' '
            . ($order['shipping_zip'] ?? $order['zip'] ?? '') . "\n"
            . ($order['shipping_country'] ?? $order['country'] ?? '') . "\n\n"
            . "Please confirm receipt and provide tracking information.\n\n"
            . "Thank you.";

        $headers = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n"
            . "Reply-To: " . ($order['customer_email'] ?? '') . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8";

        return @mail($email, $subject, $body, $headers);
    }

    // ═══════════════════════════════════════════
    //  TRACKING SYNC
    // ═══════════════════════════════════════════

    /**
     * Update tracking information for a forwarded order.
     */
    public static function updateTracking(int $forwardId, string $trackingNumber, string $trackingUrl = '', string $status = 'shipped'): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare("
            UPDATE ds_order_forwards
            SET tracking_number = ?, tracking_url = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $result = $stmt->execute([$trackingNumber, $trackingUrl, $status, $forwardId]);

        // Also update the main order if there's a tracking field
        if ($result) {
            $forward = $pdo->prepare("SELECT order_id FROM ds_order_forwards WHERE id = ?");
            $forward->execute([$forwardId]);
            $forward = $forward->fetch(\PDO::FETCH_ASSOC);
            if ($forward) {
                // Try to update order status + tracking
                $pdo->prepare("UPDATE orders SET status = 'shipped', tracking_number = ? WHERE id = ? AND status IN ('processing','paid')")
                    ->execute([$trackingNumber, $forward['order_id']]);
            }

            if (function_exists('cms_event')) {
                cms_event('dropshipping.tracking.updated', [
                    'forward_id'      => $forwardId,
                    'tracking_number' => $trackingNumber,
                    'status'          => $status,
                ]);
            }
        }

        return $result;
    }

    /**
     * Sync tracking info from supplier API (for API-connected suppliers).
     */
    public static function syncTracking(): array
    {
        $pdo = db();

        // Get all forwarded orders with API suppliers that are sent but not delivered
        $forwards = $pdo->query("
            SELECT f.*, s.type AS supplier_type, s.api_key, s.api_base_url, s.settings
            FROM ds_order_forwards f
            JOIN ds_suppliers s ON f.supplier_id = s.id
            WHERE f.status IN ('sent','confirmed','shipped')
              AND s.type IN ('aliexpress','cjdropshipping','generic_api')
              AND s.api_key IS NOT NULL AND s.api_key != ''
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $updated = 0;
        $errors = 0;

        foreach ($forwards as $fwd) {
            if (empty($fwd['supplier_order_id'])) continue;

            $result = self::fetchTrackingFromApi($fwd);
            if ($result['ok'] && !empty($result['tracking_number'])) {
                self::updateTracking((int)$fwd['id'], $result['tracking_number'], $result['tracking_url'] ?? '', $result['status'] ?? 'shipped');
                $updated++;
            } elseif (!$result['ok']) {
                $errors++;
            }

            usleep(500000); // 0.5s between API calls
        }

        return ['ok' => true, 'checked' => count($forwards), 'updated' => $updated, 'errors' => $errors];
    }

    /**
     * Fetch tracking info from supplier API.
     */
    private static function fetchTrackingFromApi(array $forward): array
    {
        $baseUrl = $forward['api_base_url'] ?? '';
        $apiKey = $forward['api_key'] ?? '';
        $supplierOrderId = $forward['supplier_order_id'] ?? '';

        if (empty($baseUrl) || empty($supplierOrderId)) {
            return ['ok' => false, 'error' => 'Missing API config or order ID'];
        }

        $url = rtrim($baseUrl, '/') . '/orders/' . urlencode($supplierOrderId) . '/tracking';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'X-Api-Key: ' . $apiKey,
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['ok' => false, 'error' => "HTTP {$httpCode}"];
        }

        $decoded = json_decode($response, true);
        return [
            'ok'              => true,
            'tracking_number' => $decoded['tracking_number'] ?? $decoded['tracking'] ?? null,
            'tracking_url'    => $decoded['tracking_url'] ?? null,
            'status'          => $decoded['status'] ?? 'shipped',
        ];
    }

    // ═══════════════════════════════════════════
    //  MANUAL OPERATIONS
    // ═══════════════════════════════════════════

    /**
     * Manually forward an order to a supplier.
     */
    public static function manualForward(int $orderId, int $supplierId): array
    {
        $pdo = db();

        $order = $pdo->prepare("SELECT * FROM orders WHERE id = ?")->fetch(\PDO::FETCH_ASSOC) ?: null;
        if (!$order) {
            $order = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $order->execute([$orderId]);
            $order = $order->fetch(\PDO::FETCH_ASSOC);
        }
        if (!$order) {
            return ['ok' => false, 'error' => 'Order not found'];
        }

        $items = $pdo->prepare("SELECT oi.*, p.name AS product_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $items->execute([$orderId]);
        $items = $items->fetchAll(\PDO::FETCH_ASSOC);

        // Get supplier link data
        $link = $pdo->prepare("SELECT * FROM ds_product_links WHERE supplier_id = ? AND product_id IN (" . implode(',', array_column($items, 'product_id') ?: [0]) . ")");
        $link->execute([$supplierId]);
        $supplier = $link->fetch(\PDO::FETCH_ASSOC);

        // Get full supplier
        $supplierRow = $pdo->prepare("SELECT * FROM ds_suppliers WHERE id = ?");
        $supplierRow->execute([$supplierId]);
        $supplierRow = $supplierRow->fetch(\PDO::FETCH_ASSOC);

        if (!$supplierRow) {
            return ['ok' => false, 'error' => 'Supplier not found'];
        }

        $data = [
            'supplier' => array_merge($supplierRow, $supplier ?: []),
            'items'    => $items,
            'total'    => array_sum(array_map(fn($i) => (float)($supplier['supplier_price'] ?? 0) * (int)($i['quantity'] ?? 1), $items)),
        ];

        return self::forwardToSupplier($orderId, $supplierId, $data, $order);
    }

    /**
     * Mark a forwarded order with a new status.
     */
    public static function updateForwardStatus(int $forwardId, string $status, ?string $notes = null): bool
    {
        $pdo = db();
        $validStatuses = ['pending', 'sent', 'confirmed', 'shipped', 'delivered', 'failed', 'cancelled'];
        if (!in_array($status, $validStatuses)) return false;

        $sql = "UPDATE ds_order_forwards SET status = ?, updated_at = NOW()";
        $params = [$status];
        if ($notes !== null) {
            $sql .= ", notes = ?";
            $params[] = $notes;
        }
        $sql .= " WHERE id = ?";
        $params[] = $forwardId;

        return $pdo->prepare($sql)->execute($params);
    }

    /**
     * Get forwarded order details.
     */
    public static function getForward(int $forwardId): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT f.*, s.name AS supplier_name, s.type AS supplier_type, s.contact_email,
                   o.customer_name, o.customer_email, o.total AS order_total, o.status AS order_status
            FROM ds_order_forwards f
            LEFT JOIN ds_suppliers s ON f.supplier_id = s.id
            LEFT JOIN orders o ON f.order_id = o.id
            WHERE f.id = ?
        ");
        $stmt->execute([$forwardId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all forwards for an order.
     */
    public static function getForwardsForOrder(int $orderId): array
    {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT f.*, s.name AS supplier_name
            FROM ds_order_forwards f
            LEFT JOIN ds_suppliers s ON f.supplier_id = s.id
            WHERE f.order_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get pending forwards count.
     */
    public static function getPendingCount(): int
    {
        return (int)db()->query("SELECT COUNT(*) FROM ds_order_forwards WHERE status IN ('pending','sent')")->fetchColumn();
    }
}
