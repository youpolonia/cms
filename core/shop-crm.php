<?php
/**
 * Jessie CMS — Shop ↔ CRM Integration
 * Syncs orders to CRM contacts, tracks CLV, auto-tags VIPs,
 * segmentation, export, lifetime analytics
 */

class ShopCRM
{
    /**
     * Sync an order to CRM — create/update contact, add activity, calculate CLV
     */
    public static function syncOrderToContact(int $orderId): void
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order || empty($order['customer_email'])) {
                return;
            }

            require_once CMS_ROOT . '/core/crm_manager.php';

            $email = trim($order['customer_email']);
            $name = trim($order['customer_name'] ?? '');
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0] ?: 'Customer';
            $lastName = $nameParts[1] ?? '';
            $orderNumber = $order['order_number'] ?? ('ORD-' . $orderId);
            $total = number_format((float)($order['total'] ?? 0), 2);
            $currency = $order['currency'] ?? 'USD';

            // Check if contact exists
            $contact = CrmManager::getContactByEmail($email);

            if ($contact) {
                $contactId = (int)$contact['id'];
            } else {
                // Create new contact
                $contactId = CrmManager::createContact([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $order['customer_phone'] ?? '',
                    'source' => 'shop',
                    'status' => 'active',
                    'notes' => 'Auto-created from shop order ' . $orderNumber,
                ]);
            }

            if ($contactId < 1) {
                return;
            }

            // Add purchase activity
            CrmManager::addActivity(
                $contactId,
                'purchase',
                "Purchase: Order #{$orderNumber} — {$total} {$currency}",
                "Order placed on " . date('Y-m-d H:i', strtotime($order['created_at'] ?? 'now'))
            );

            // Calculate CLV and update tags
            $stats = self::getCustomerStats($email);
            $clv = $stats['total_spent'] ?? 0;

            // Build tags
            $existingTags = '';
            if ($contact) {
                $existingTags = $contact['tags'] ?? '';
            }
            $tags = array_filter(array_map('trim', explode(',', $existingTags)));

            // Add 'customer' tag if not present
            if (!in_array('customer', $tags)) {
                $tags[] = 'customer';
            }

            // VIP threshold from settings (default 500)
            $vipThreshold = (float)(function_exists('get_setting') ? get_setting('shop_vip_threshold', '500') : 500);
            if ($clv >= $vipThreshold && !in_array('VIP', $tags)) {
                $tags[] = 'VIP';
            }

            // Repeat customer tag
            if ($stats['total_orders'] > 1 && !in_array('repeat', $tags)) {
                $tags[] = 'repeat';
            }

            // Update contact with CLV info and tags
            $clvNote = "CLV: {$currency} " . number_format($clv, 2) . " | Orders: {$stats['total_orders']}";
            CrmManager::updateContact($contactId, [
                'tags' => implode(',', array_unique($tags)),
                'notes' => $clvNote,
            ]);

            if (function_exists('cms_event')) {
                cms_event('shop.crm.synced', [
                    'order_id' => $orderId,
                    'contact_id' => $contactId,
                    'clv' => $clv,
                    'is_new_contact' => !$contact,
                ]);
            }
        } catch (\Throwable $e) {
            error_log('ShopCRM::syncOrderToContact error: ' . $e->getMessage());
        }
    }

    /**
     * Get all orders for an email address
     */
    public static function getContactOrders(string $email): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT id, order_number, total, currency, status, payment_status, created_at
             FROM orders
             WHERE customer_email = ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([trim($email)]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get customer stats for an email address
     */
    public static function getCustomerStats(string $email): array
    {
        $pdo = db();
        $email = trim($email);

        $stmt = $pdo->prepare(
            "SELECT
                COUNT(*) as total_orders,
                COALESCE(SUM(total), 0) as total_spent,
                COALESCE(AVG(total), 0) as avg_order_value,
                MIN(created_at) as first_purchase,
                MAX(created_at) as last_purchase
             FROM orders
             WHERE customer_email = ?"
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total_orders' => (int)($row['total_orders'] ?? 0),
            'total_spent' => (float)($row['total_spent'] ?? 0),
            'avg_order_value' => round((float)($row['avg_order_value'] ?? 0), 2),
            'first_purchase' => $row['first_purchase'] ?? null,
            'last_purchase' => $row['last_purchase'] ?? null,
        ];
    }

    /**
     * Get all customers with their CLV, sorted by value
     */
    public static function getAllCustomers(int $page = 1, int $perPage = 20, string $sortBy = 'total_spent', string $sortDir = 'DESC'): array
    {
        $pdo = db();

        $allowedSort = ['total_spent', 'total_orders', 'avg_order_value', 'last_purchase', 'first_purchase'];
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'total_spent';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $countStmt = $pdo->query("SELECT COUNT(DISTINCT customer_email) FROM orders WHERE customer_email IS NOT NULL AND customer_email != ''");
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT
                customer_email AS email,
                MAX(customer_name) AS name,
                COUNT(*) AS total_orders,
                COALESCE(SUM(total), 0) AS total_spent,
                ROUND(COALESCE(AVG(total), 0), 2) AS avg_order_value,
                MIN(created_at) AS first_purchase,
                MAX(created_at) AS last_purchase
            FROM orders
            WHERE customer_email IS NOT NULL AND customer_email != ''
            GROUP BY customer_email
            ORDER BY {$sortBy} {$sortDir}
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);

        return [
            'customers' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * Segment customers by behavior
     */
    public static function getSegments(): array
    {
        $pdo = db();
        $vipThreshold = (float)(function_exists('get_setting') ? get_setting('shop_vip_threshold', '500') : 500);

        // VIP customers (CLV >= threshold)
        $vip = (int)$pdo->prepare(
            "SELECT COUNT(DISTINCT customer_email) FROM orders
             WHERE customer_email != ''
             GROUP BY customer_email HAVING SUM(total) >= ?"
        )->execute([$vipThreshold]) ? $pdo->query(
            "SELECT COUNT(*) FROM (
                SELECT customer_email FROM orders WHERE customer_email != ''
                GROUP BY customer_email HAVING SUM(total) >= {$vipThreshold}
            ) t"
        )->fetchColumn() : 0;

        // Repeat customers (>1 order)
        $repeat = (int)$pdo->query(
            "SELECT COUNT(*) FROM (
                SELECT customer_email FROM orders WHERE customer_email != ''
                GROUP BY customer_email HAVING COUNT(*) > 1
            ) t"
        )->fetchColumn();

        // New customers (1 order, last 30 days)
        $newCustomers = (int)$pdo->query(
            "SELECT COUNT(*) FROM (
                SELECT customer_email FROM orders
                WHERE customer_email != '' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY customer_email HAVING COUNT(*) = 1
            ) t"
        )->fetchColumn();

        // At-risk (no order in 90+ days, had orders before)
        $atRisk = (int)$pdo->query(
            "SELECT COUNT(*) FROM (
                SELECT customer_email FROM orders WHERE customer_email != ''
                GROUP BY customer_email
                HAVING MAX(created_at) < DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                   AND COUNT(*) >= 1
            ) t"
        )->fetchColumn();

        // Total unique customers
        $totalCustomers = (int)$pdo->query(
            "SELECT COUNT(DISTINCT customer_email) FROM orders WHERE customer_email IS NOT NULL AND customer_email != ''"
        )->fetchColumn();

        return [
            'total' => $totalCustomers,
            'vip' => (int)$vip,
            'repeat' => (int)$repeat,
            'new' => (int)$newCustomers,
            'at_risk' => (int)$atRisk,
            'vip_threshold' => $vipThreshold,
        ];
    }

    /**
     * Get CRM dashboard stats
     */
    public static function getDashboardStats(): array
    {
        $pdo = db();

        $totalCustomers = (int)$pdo->query(
            "SELECT COUNT(DISTINCT customer_email) FROM orders WHERE customer_email IS NOT NULL AND customer_email != ''"
        )->fetchColumn();

        $totalRevenue = (float)$pdo->query(
            "SELECT COALESCE(SUM(total), 0) FROM orders WHERE status NOT IN ('cancelled','refunded')"
        )->fetchColumn();

        $avgClv = (float)$pdo->query(
            "SELECT COALESCE(AVG(clv), 0) FROM (
                SELECT SUM(total) AS clv FROM orders
                WHERE customer_email != '' AND status NOT IN ('cancelled','refunded')
                GROUP BY customer_email
            ) t"
        )->fetchColumn();

        // New customers this month
        $newThisMonth = (int)$pdo->query(
            "SELECT COUNT(*) FROM (
                SELECT customer_email, MIN(created_at) AS first_order
                FROM orders WHERE customer_email != ''
                GROUP BY customer_email
                HAVING first_order >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
            ) t"
        )->fetchColumn();

        $segments = self::getSegments();

        return [
            'total_customers' => $totalCustomers,
            'total_revenue' => round($totalRevenue, 2),
            'avg_clv' => round($avgClv, 2),
            'new_this_month' => $newThisMonth,
            'segments' => $segments,
        ];
    }

    /**
     * Export customers as CSV
     */
    public static function exportCustomersCSV(): string
    {
        $pdo = db();
        $stmt = $pdo->query("
            SELECT
                customer_email AS email,
                MAX(customer_name) AS name,
                MAX(customer_phone) AS phone,
                COUNT(*) AS total_orders,
                ROUND(COALESCE(SUM(total), 0), 2) AS total_spent,
                ROUND(COALESCE(AVG(total), 0), 2) AS avg_order,
                MIN(created_at) AS first_purchase,
                MAX(created_at) AS last_purchase
            FROM orders
            WHERE customer_email IS NOT NULL AND customer_email != ''
            GROUP BY customer_email
            ORDER BY total_spent DESC
        ");

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Email', 'Name', 'Phone', 'Orders', 'Total Spent', 'Avg Order', 'First Purchase', 'Last Purchase']);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Search customers by email or name
     */
    public static function searchCustomers(string $query, int $limit = 20): array
    {
        $pdo = db();
        $like = '%' . trim($query) . '%';
        $stmt = $pdo->prepare("
            SELECT
                customer_email AS email,
                MAX(customer_name) AS name,
                COUNT(*) AS total_orders,
                ROUND(COALESCE(SUM(total), 0), 2) AS total_spent,
                MAX(created_at) AS last_purchase
            FROM orders
            WHERE (customer_email LIKE ? OR customer_name LIKE ?)
              AND customer_email IS NOT NULL AND customer_email != ''
            GROUP BY customer_email
            ORDER BY total_spent DESC
            LIMIT ?
        ");
        $stmt->execute([$like, $like, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

// ─── Event: Auto-sync orders to CRM ───
if (function_exists('cms_on')) {
    cms_on('shop.order.created', function ($data) {
        $orderId = (int)($data['order_id'] ?? $data['id'] ?? 0);
        if ($orderId > 0) {
            ShopCRM::syncOrderToContact($orderId);
        }
    });
}
