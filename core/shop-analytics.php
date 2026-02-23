<?php
declare(strict_types=1);

/**
 * Shop Analytics — tracking + query helpers
 * Zero frameworks, pure PHP 8.2+
 */
class ShopAnalytics
{
    // ─── TRACKING ───

    public static function track(string $eventType, ?int $productId = null, ?int $orderId = null, ?float $amount = null, array $meta = []): void
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $sid = session_id();
            $pdo = db();
            $stmt = $pdo->prepare(
                "INSERT INTO shop_analytics (event_type, product_id, order_id, session_id, amount, meta, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $eventType,
                $productId,
                $orderId,
                $sid ?: null,
                $amount,
                !empty($meta) ? json_encode($meta) : null,
            ]);
        } catch (\Throwable $e) {
            // Non-blocking — never throw
        }
    }

    public static function trackProductView(int $productId): void
    {
        self::track('view', $productId);
    }

    public static function trackAddToCart(int $productId): void
    {
        self::track('add_to_cart', $productId);
    }

    public static function trackCheckout(float $total): void
    {
        self::track('checkout', amount: $total);
    }

    public static function trackPurchase(int $orderId, float $total): void
    {
        self::track('purchase', orderId: $orderId, amount: $total);
    }

    public static function trackSearch(string $query): void
    {
        self::track('search', meta: ['query' => $query]);
    }

    // ─── ANALYTICS QUERIES ───

    /**
     * Daily revenue for last N days
     */
    public static function getRevenueChart(int $days = 30): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT DATE(created_at) AS date,
                    SUM(total) AS revenue,
                    COUNT(*) AS orders
             FROM orders
             WHERE status NOT IN ('cancelled','refunded')
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC"
        );
        $stmt->execute([$days]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fill in missing dates
        $map = [];
        foreach ($rows as $r) {
            $map[$r['date']] = ['date' => $r['date'], 'revenue' => (float)$r['revenue'], 'orders' => (int)$r['orders']];
        }
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = $map[$d] ?? ['date' => $d, 'revenue' => 0.0, 'orders' => 0];
        }
        return $result;
    }

    /**
     * Top products by purchase events or order items
     */
    public static function getBestsellers(int $limit = 10, int $days = 30): array
    {
        $pdo = db();
        // Use orders.items JSON: each item has id, name, quantity, price
        $stmt = $pdo->prepare(
            "SELECT o.items
             FROM orders o
             WHERE o.status NOT IN ('cancelled','refunded')
               AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)"
        );
        $stmt->execute([$days]);
        $orders = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $products = [];
        foreach ($orders as $itemsJson) {
            $items = json_decode($itemsJson, true);
            if (!is_array($items)) continue;
            foreach ($items as $item) {
                $pid = (int)($item['id'] ?? $item['product_id'] ?? 0);
                if ($pid <= 0) continue;
                if (!isset($products[$pid])) {
                    $products[$pid] = ['product_id' => $pid, 'name' => $item['name'] ?? 'Unknown', 'units' => 0, 'revenue' => 0.0];
                }
                $qty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
                $price = (float)($item['price'] ?? 0);
                $products[$pid]['units'] += $qty;
                $products[$pid]['revenue'] += $qty * $price;
            }
        }

        usort($products, fn($a, $b) => $b['units'] <=> $a['units']);
        return array_slice($products, 0, $limit);
    }

    /**
     * Conversion funnel: views → add_to_cart → checkout → purchase
     */
    public static function getConversionFunnel(int $days = 30): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT event_type, COUNT(*) AS cnt
             FROM shop_analytics
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
               AND event_type IN ('view','add_to_cart','checkout','purchase')
             GROUP BY event_type"
        );
        $stmt->execute([$days]);
        $rows = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        return [
            'views'       => (int)($rows['view'] ?? 0),
            'add_to_cart' => (int)($rows['add_to_cart'] ?? 0),
            'checkout'    => (int)($rows['checkout'] ?? 0),
            'purchase'    => (int)($rows['purchase'] ?? 0),
        ];
    }

    /**
     * Key Performance Indicators
     */
    public static function getKPIs(int $days = 30): array
    {
        $pdo = db();

        // Current period
        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(total),0) AS revenue, COUNT(*) AS orders
             FROM orders
             WHERE status NOT IN ('cancelled','refunded')
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)"
        );
        $stmt->execute([$days]);
        $cur = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Previous period
        $stmt2 = $pdo->prepare(
            "SELECT COALESCE(SUM(total),0) AS revenue, COUNT(*) AS orders
             FROM orders
             WHERE status NOT IN ('cancelled','refunded')
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
               AND created_at < DATE_SUB(CURDATE(), INTERVAL ? DAY)"
        );
        $stmt2->execute([$days * 2, $days]);
        $prev = $stmt2->fetch(\PDO::FETCH_ASSOC);

        $revenue = (float)$cur['revenue'];
        $orders = (int)$cur['orders'];
        $aov = $orders > 0 ? $revenue / $orders : 0.0;
        $prevRevenue = (float)$prev['revenue'];
        $prevOrders = (int)$prev['orders'];
        $prevAov = $prevOrders > 0 ? $prevRevenue / $prevOrders : 0.0;

        // Conversion rate
        $funnel = self::getConversionFunnel($days);
        $convRate = $funnel['views'] > 0 ? ($funnel['purchase'] / $funnel['views']) * 100 : 0.0;

        // Returning customers (sessions with >1 purchase)
        $stmtRet = $pdo->prepare(
            "SELECT COUNT(DISTINCT session_id) AS returning_sessions
             FROM shop_analytics
             WHERE event_type = 'purchase'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
               AND session_id IN (
                   SELECT session_id FROM shop_analytics
                   WHERE event_type = 'purchase'
                   GROUP BY session_id HAVING COUNT(*) > 1
               )"
        );
        $stmtRet->execute([$days]);
        $retSess = (int)$stmtRet->fetchColumn();
        $totalSess = $pdo->prepare(
            "SELECT COUNT(DISTINCT session_id)
             FROM shop_analytics
             WHERE event_type = 'purchase'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)"
        );
        $totalSess->execute([$days]);
        $totalPurchaseSessions = (int)$totalSess->fetchColumn();
        $returningPct = $totalPurchaseSessions > 0 ? ($retSess / $totalPurchaseSessions) * 100 : 0.0;

        return [
            'revenue'                => $revenue,
            'orders'                 => $orders,
            'aov'                    => round($aov, 2),
            'conversion_rate'        => round($convRate, 2),
            'returning_customers_pct'=> round($returningPct, 1),
            'prev_revenue'           => $prevRevenue,
            'prev_orders'            => $prevOrders,
            'prev_aov'               => round($prevAov, 2),
            'revenue_change'         => $prevRevenue > 0 ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0.0,
            'orders_change'          => $prevOrders > 0 ? round((($orders - $prevOrders) / $prevOrders) * 100, 1) : 0.0,
        ];
    }

    /**
     * Top categories by revenue
     */
    public static function getTopCategories(int $limit = 5, int $days = 30): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT o.items
             FROM orders o
             WHERE o.status NOT IN ('cancelled','refunded')
               AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)"
        );
        $stmt->execute([$days]);
        $orders = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Collect product IDs from orders
        $productIds = [];
        $productRevenue = [];
        foreach ($orders as $itemsJson) {
            $items = json_decode($itemsJson, true);
            if (!is_array($items)) continue;
            foreach ($items as $item) {
                $pid = (int)($item['id'] ?? $item['product_id'] ?? 0);
                if ($pid <= 0) continue;
                $productIds[] = $pid;
                $qty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
                $price = (float)($item['price'] ?? 0);
                $productRevenue[$pid] = ($productRevenue[$pid] ?? 0.0) + ($qty * $price);
            }
        }

        if (empty($productIds)) return [];

        // Map product → category
        $uniqueIds = array_unique($productIds);
        $placeholders = implode(',', array_fill(0, count($uniqueIds), '?'));
        $stmt2 = $pdo->prepare(
            "SELECT p.id, COALESCE(pc.name, 'Uncategorized') AS category_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             WHERE p.id IN ({$placeholders})"
        );
        $stmt2->execute(array_values($uniqueIds));
        $prodCat = $stmt2->fetchAll(\PDO::FETCH_KEY_PAIR);

        $categories = [];
        foreach ($productRevenue as $pid => $rev) {
            $cat = $prodCat[$pid] ?? 'Uncategorized';
            $categories[$cat] = ($categories[$cat] ?? 0.0) + $rev;
        }

        arsort($categories);
        $result = [];
        foreach (array_slice($categories, 0, $limit, true) as $name => $rev) {
            $result[] = ['name' => $name, 'revenue' => round($rev, 2)];
        }
        return $result;
    }

    /**
     * Recent analytics activity
     */
    public static function getRecentActivity(int $limit = 20): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT sa.*, p.name AS product_name
             FROM shop_analytics sa
             LEFT JOIN products p ON sa.product_id = p.id
             ORDER BY sa.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Popular search terms
     */
    public static function getPopularSearches(int $limit = 10, int $days = 30): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT JSON_UNQUOTE(JSON_EXTRACT(meta, '$.query')) AS query, COUNT(*) AS cnt
             FROM shop_analytics
             WHERE event_type = 'search'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
               AND meta IS NOT NULL
             GROUP BY query
             ORDER BY cnt DESC
             LIMIT ?"
        );
        $stmt->execute([$days, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Revenue by day with previous period comparison
     */
    public static function getRevenueByDay(int $days = 7): array
    {
        return self::getRevenueChart($days);
    }

    /**
     * Orders distribution by hour of day
     */
    public static function getHourlyDistribution(int $days = 30): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT HOUR(created_at) AS hour, COUNT(*) AS orders
             FROM orders
             WHERE status NOT IN ('cancelled','refunded')
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY HOUR(created_at)
             ORDER BY hour ASC"
        );
        $stmt->execute([$days]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['hour']] = (int)$r['orders'];
        }
        $result = [];
        for ($h = 0; $h < 24; $h++) {
            $result[] = ['hour' => $h, 'orders' => $map[$h] ?? 0];
        }
        return $result;
    }
}
