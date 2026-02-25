<?php
declare(strict_types=1);

class EventManager
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'e.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['category'])) { $where[] = 'e.category = ?'; $params[] = $filters['category']; }
        if (!empty($filters['city'])) { $where[] = 'e.city = ?'; $params[] = $filters['city']; }
        if (!empty($filters['featured'])) { $where[] = 'e.is_featured = 1'; }
        if (!empty($filters['free'])) { $where[] = 'e.is_free = 1'; }
        if (!empty($filters['date_from'])) { $where[] = 'e.start_date >= ?'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where[] = 'e.start_date <= ?'; $params[] = $filters['date_to']; }
        if (!empty($filters['search'])) { $where[] = "MATCH(e.title, e.description, e.venue_name, e.city) AGAINST(? IN BOOLEAN MODE)"; $params[] = $filters['search']; }
        if (!empty($filters['month'])) {
            $where[] = "DATE_FORMAT(e.start_date, '%Y-%m') = ?";
            $params[] = $filters['month'];
        }
        $wSql = implode(' AND ', $where);
        $orderBy = 'e.start_date ASC';
        if (!empty($filters['sort'])) {
            $orderBy = match($filters['sort']) {
                'newest' => 'e.created_at DESC',
                'popular' => 'e.view_count DESC',
                'title' => 'e.title ASC',
                default => 'e.start_date ASC',
            };
        }

        $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS e.* FROM events e WHERE {$wSql} ORDER BY {$orderBy} LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;
        $stmt->execute($params);
        $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $total = (int)$pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

        return ['events' => $events, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / max(1, $perPage))];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT * FROM events WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::makeSlug(($data['title'] ?? null) ?: 'event');
        $stmt = $pdo->prepare("INSERT INTO events (title, slug, description, short_description, venue_name, venue_address, city, country, start_date, end_date, image, category, organizer_name, organizer_email, max_capacity, is_featured, is_free, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['title'],
            $slug,
            ($data['description'] ?? null) ?: null,
            ($data['short_description'] ?? null) ?: '',
            ($data['venue_name'] ?? null) ?: '',
            ($data['venue_address'] ?? null) ?: '',
            ($data['city'] ?? null) ?: '',
            ($data['country'] ?? null) ?: '',
            $data['start_date'],
            ($data['end_date'] ?? null) ?: null,
            ($data['image'] ?? null) ?: '',
            ($data['category'] ?? null) ?: '',
            ($data['organizer_name'] ?? null) ?: '',
            ($data['organizer_email'] ?? null) ?: '',
            !empty($data['max_capacity']) ? (int)$data['max_capacity'] : null,
            (int)($data['is_featured'] ?? 0),
            (int)($data['is_free'] ?? 0),
            ($data['status'] ?? null) ?: 'upcoming',
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('events.event.created', ['id' => $id, 'title' => $data['title']]);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['title','description','short_description','venue_name','venue_address','city','country','start_date','end_date','image','category','organizer_name','organizer_email','max_capacity','is_featured','is_free','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE events SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("DELETE FROM event_orders WHERE event_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM event_tickets WHERE event_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
    }

    public static function incrementViews(int $id): void
    {
        db()->prepare("UPDATE events SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $ev = $pdo->query("SELECT COUNT(*) AS total, SUM(status='upcoming') AS upcoming, SUM(status='ongoing') AS ongoing, SUM(is_featured=1) AS featured, SUM(view_count) AS total_views FROM events")->fetch(\PDO::FETCH_ASSOC);
        $orders = $pdo->query("SELECT COUNT(*) AS total, SUM(payment_status='paid') AS paid, SUM(payment_status='pending') AS pending, COALESCE(SUM(total),0) AS revenue, SUM(checked_in=1) AS checked_in FROM event_orders")->fetch(\PDO::FETCH_ASSOC);
        $tickets = $pdo->query("SELECT COUNT(*) AS total, COALESCE(SUM(quantity_sold),0) AS sold FROM event_tickets")->fetch(\PDO::FETCH_ASSOC);
        $today = $pdo->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS rev FROM event_orders WHERE DATE(created_at) = CURDATE()")->fetch(\PDO::FETCH_ASSOC);
        return [
            'events_total' => (int)$ev['total'],
            'events_upcoming' => (int)($ev['upcoming'] ?? 0),
            'events_ongoing' => (int)($ev['ongoing'] ?? 0),
            'events_featured' => (int)($ev['featured'] ?? 0),
            'total_views' => (int)($ev['total_views'] ?? 0),
            'orders_total' => (int)$orders['total'],
            'orders_paid' => (int)($orders['paid'] ?? 0),
            'orders_pending' => (int)($orders['pending'] ?? 0),
            'revenue_total' => (float)($orders['revenue'] ?? 0),
            'checked_in' => (int)($orders['checked_in'] ?? 0),
            'tickets_total' => (int)$tickets['total'],
            'tickets_sold' => (int)($tickets['sold'] ?? 0),
            'orders_today' => (int)$today['cnt'],
            'revenue_today' => (float)$today['rev'],
        ];
    }

    public static function getCategories(): array
    {
        return db()->query("SELECT DISTINCT category FROM events WHERE category != '' ORDER BY category")->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function getCities(): array
    {
        return db()->query("SELECT DISTINCT city FROM events WHERE city != '' ORDER BY city")->fetchAll(\PDO::FETCH_COLUMN);
    }

    // ─── Settings ───

    public static function getSetting(string $key, string $default = ''): string
    {
        $stmt = db()->prepare("SELECT setting_value FROM event_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() ?: $default;
    }

    public static function setSetting(string $key, string $value): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO event_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$key, $value]);
    }

    public static function getAllSettings(): array
    {
        return db()->query("SELECT setting_key, setting_value FROM event_settings")->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    private static function makeSlug(string $text): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($text)));
        $slug = trim($slug, '-');
        $pdo = db(); $base = $slug; $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE slug = ?");
            $stmt->execute([$slug]);
            if ((int)$stmt->fetchColumn() === 0) break;
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
