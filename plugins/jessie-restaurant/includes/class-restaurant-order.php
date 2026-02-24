<?php
declare(strict_types=1);

class RestaurantOrder
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['order_type'])) { $where[] = 'order_type = ?'; $params[] = $filters['order_type']; }
        if (!empty($filters['date'])) { $where[] = 'DATE(created_at) = ?'; $params[] = $filters['date']; }
        if (!empty($filters['search'])) { $where[] = "(customer_name LIKE ? OR order_number LIKE ? OR customer_phone LIKE ?)"; $s = '%'.$filters['search'].'%'; $params = array_merge($params, [$s, $s, $s]); }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM restaurant_orders WHERE {$wSql} ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $params[] = $perPage; $params[] = ($page - 1) * $perPage;
        $stmt->execute($params);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $total = (int)$pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

        foreach ($orders as &$o) { $o['items_json'] = json_decode($o['items_json'] ?: '[]', true); }
        return ['orders' => $orders, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM restaurant_orders WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) $r['items_json'] = json_decode($r['items_json'] ?: '[]', true);
        return $r ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $orderNum = 'ORD-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

        $items = $data['items'] ?? [];
        $subtotal = 0;
        foreach ($items as &$item) {
            $menuItem = \RestaurantMenu::getItem((int)$item['id']);
            if ($menuItem) {
                $item['name'] = $menuItem['name'];
                $item['unit_price'] = (float)($menuItem['sale_price'] ?: $menuItem['price']);
                $extrasTotal = 0;
                foreach (($item['extras'] ?? []) as $extra) $extrasTotal += (float)($extra['price'] ?? 0);
                $item['line_total'] = ($item['unit_price'] + $extrasTotal) * (int)($item['quantity'] ?? 1);
                $subtotal += $item['line_total'];
            }
        }

        $deliveryFee = $data['order_type'] === 'delivery' ? (float)\RestaurantMenu::getSetting('delivery_fee', '0') : 0;
        $taxRate = (float)\RestaurantMenu::getSetting('tax_rate', '0') / 100;
        $tax = round($subtotal * $taxRate, 2);
        $tip = (float)($data['tip'] ?? 0);
        $total = $subtotal + $deliveryFee + $tax + $tip;

        $stmt = $pdo->prepare("INSERT INTO restaurant_orders (order_number, customer_name, customer_email, customer_phone, order_type, delivery_address, delivery_notes, items_json, subtotal, delivery_fee, tax, tip, total, payment_method, status, estimated_time, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $orderNum, $data['customer_name'], $data['customer_email'] ?? '',
            $data['customer_phone'], $data['order_type'] ?? 'delivery',
            $data['delivery_address'] ?? null, $data['delivery_notes'] ?? null,
            json_encode($items), $subtotal, $deliveryFee, $tax, $tip, $total,
            $data['payment_method'] ?? 'cash', 'new',
            (int)\RestaurantMenu::getSetting($data['order_type'] === 'pickup' ? 'estimated_pickup_time' : 'estimated_delivery_time', '30'),
            $data['notes'] ?? null,
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('restaurant.order.created', ['order_id' => $id, 'order_number' => $orderNum, 'total' => $total]);
        return $id;
    }

    public static function updateStatus(int $id, string $status): void
    {
        $valid = ['new','confirmed','preparing','ready','delivering','completed','cancelled'];
        if (!in_array($status, $valid)) return;
        db()->prepare("UPDATE restaurant_orders SET status = ? WHERE id = ?")->execute([$status, $id]);
        if (function_exists('cms_event')) cms_event('restaurant.order.' . $status, ['order_id' => $id]);
    }

    public static function getActiveOrders(): array
    {
        $stmt = db()->query("SELECT * FROM restaurant_orders WHERE status IN ('new','confirmed','preparing','ready','delivering') ORDER BY created_at ASC");
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($orders as &$o) $o['items_json'] = json_decode($o['items_json'] ?: '[]', true);
        return $orders;
    }

    public static function getTodayStats(): array
    {
        $pdo = db();
        $row = $pdo->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS rev, COALESCE(AVG(total),0) AS avg_order FROM restaurant_orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")->fetch(\PDO::FETCH_ASSOC);
        return ['count' => (int)$row['cnt'], 'revenue' => (float)$row['rev'], 'avg_order' => (float)$row['avg_order']];
    }
}
