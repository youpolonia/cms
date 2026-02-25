<?php
declare(strict_types=1);

class EventTicket
{
    public static function getByEvent(int $eventId): array
    {
        $stmt = db()->prepare("SELECT * FROM event_tickets WHERE event_id = ? ORDER BY price ASC, name ASC");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM event_tickets WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO event_tickets (event_id, name, description, price, currency, quantity_total, quantity_sold, max_per_order, sale_start, sale_end, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            (int)$data['event_id'],
            $data['name'],
            ($data['description'] ?? null) ?: '',
            (float)($data['price'] ?? 0),
            ($data['currency'] ?? null) ?: 'GBP',
            (int)($data['quantity_total'] ?? 100),
            0,
            (int)($data['max_per_order'] ?? 10),
            ($data['sale_start'] ?? null) ?: null,
            ($data['sale_end'] ?? null) ?: null,
            ($data['status'] ?? null) ?: 'active',
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('events.ticket.created', ['id' => $id, 'event_id' => (int)$data['event_id']]);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['name','description','price','currency','quantity_total','max_per_order','sale_start','sale_end','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE event_tickets SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("DELETE FROM event_orders WHERE ticket_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM event_tickets WHERE id = ?")->execute([$id]);
    }

    public static function getAvailable(int $eventId): array
    {
        $now = date('Y-m-d H:i:s');
        $stmt = db()->prepare("SELECT * FROM event_tickets WHERE event_id = ? AND status = 'active' AND quantity_sold < quantity_total AND (sale_start IS NULL OR sale_start <= ?) AND (sale_end IS NULL OR sale_end >= ?) ORDER BY price ASC");
        $stmt->execute([$eventId, $now, $now]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function incrementSold(int $id, int $qty): void
    {
        $pdo = db();
        $pdo->prepare("UPDATE event_tickets SET quantity_sold = quantity_sold + ? WHERE id = ?")->execute([$qty, $id]);
        // Auto soldout
        $pdo->prepare("UPDATE event_tickets SET status = 'soldout' WHERE id = ? AND quantity_sold >= quantity_total")->execute([$id]);
    }
}
