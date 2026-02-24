<?php
declare(strict_types=1);

class EventOrder
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['event_id'])) { $where[] = 'o.event_id = ?'; $params[] = (int)$filters['event_id']; }
        if (!empty($filters['payment_status'])) { $where[] = 'o.payment_status = ?'; $params[] = $filters['payment_status']; }
        if (!empty($filters['checked_in'])) { $where[] = 'o.checked_in = 1'; }
        if (!empty($filters['search'])) { $where[] = "(o.buyer_name LIKE ? OR o.order_number LIKE ? OR o.buyer_email LIKE ?)"; $s = '%'.$filters['search'].'%'; $params = array_merge($params, [$s, $s, $s]); }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS o.*, e.title AS event_title, t.name AS ticket_name FROM event_orders o LEFT JOIN events e ON o.event_id = e.id LEFT JOIN event_tickets t ON o.ticket_id = t.id WHERE {$wSql} ORDER BY o.created_at DESC LIMIT ? OFFSET ?");
        $params[] = $perPage; $params[] = ($page - 1) * $perPage;
        $stmt->execute($params);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $total = (int)$pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

        return ['orders' => $orders, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / max(1, $perPage))];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT o.*, e.title AS event_title, t.name AS ticket_name FROM event_orders o LEFT JOIN events e ON o.event_id = e.id LEFT JOIN event_tickets t ON o.ticket_id = t.id WHERE o.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getByQr(string $qrCode): ?array
    {
        $stmt = db()->prepare("SELECT o.*, e.title AS event_title, t.name AS ticket_name FROM event_orders o LEFT JOIN events e ON o.event_id = e.id LEFT JOIN event_tickets t ON o.ticket_id = t.id WHERE o.qr_code = ?");
        $stmt->execute([$qrCode]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $orderNum = 'EVT-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        $salt = \EventManager::getSetting('checkin_salt', 'jessie-events-2024');
        $qrCode = md5($orderNum . $salt);

        $ticket = \EventTicket::get((int)$data['ticket_id']);
        if (!$ticket) throw new \RuntimeException('Ticket not found');

        $qty = (int)($data['quantity'] ?? 1);
        $remaining = (int)$ticket['quantity_total'] - (int)$ticket['quantity_sold'];
        if ($qty > $remaining) throw new \RuntimeException('Not enough tickets available');
        if ($qty > (int)$ticket['max_per_order']) throw new \RuntimeException('Exceeds max per order (' . $ticket['max_per_order'] . ')');

        $unitPrice = (float)$ticket['price'];
        $total = $unitPrice * $qty;

        $stmt = $pdo->prepare("INSERT INTO event_orders (event_id, ticket_id, order_number, buyer_name, buyer_email, buyer_phone, quantity, unit_price, total, payment_status, qr_code) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            (int)$data['event_id'],
            (int)$data['ticket_id'],
            $orderNum,
            $data['buyer_name'],
            ($data['buyer_email'] ?? null) ?: '',
            ($data['buyer_phone'] ?? null) ?: '',
            $qty,
            $unitPrice,
            $total,
            $unitPrice > 0 ? 'pending' : 'paid',
            $qrCode,
        ]);
        $id = (int)$pdo->lastInsertId();

        // Increment sold count
        \EventTicket::incrementSold((int)$data['ticket_id'], $qty);

        if (function_exists('cms_event')) cms_event('events.order.created', ['order_id' => $id, 'order_number' => $orderNum, 'total' => $total]);
        return $id;
    }

    public static function updatePaymentStatus(int $id, string $status): void
    {
        $valid = ['pending','paid','refunded'];
        if (!in_array($status, $valid)) return;
        db()->prepare("UPDATE event_orders SET payment_status = ? WHERE id = ?")->execute([$status, $id]);
    }

    public static function checkIn(string $qrCode): array
    {
        $order = self::getByQr($qrCode);
        if (!$order) return ['ok' => false, 'error' => 'Invalid QR code'];
        if ($order['checked_in']) return ['ok' => false, 'error' => 'Already checked in at ' . $order['checked_in_at']];
        if ($order['payment_status'] !== 'paid') return ['ok' => false, 'error' => 'Order not paid'];

        db()->prepare("UPDATE event_orders SET checked_in = 1, checked_in_at = NOW() WHERE id = ?")->execute([(int)$order['id']]);
        if (function_exists('cms_event')) cms_event('events.order.checked_in', ['order_id' => $order['id']]);
        return ['ok' => true, 'order' => $order];
    }

    public static function getByEvent(int $eventId): array
    {
        $stmt = db()->prepare("SELECT o.*, t.name AS ticket_name FROM event_orders o LEFT JOIN event_tickets t ON o.ticket_id = t.id WHERE o.event_id = ? ORDER BY o.created_at DESC");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
