<?php
declare(strict_types=1);

class RealEstateProperty
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'p.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['property_type'])) { $where[] = 'p.property_type = ?'; $params[] = $filters['property_type']; }
        if (!empty($filters['listing_type'])) { $where[] = 'p.listing_type = ?'; $params[] = $filters['listing_type']; }
        if (!empty($filters['city'])) { $where[] = 'p.city = ?'; $params[] = $filters['city']; }
        if (!empty($filters['agent_id'])) { $where[] = 'p.agent_id = ?'; $params[] = (int)$filters['agent_id']; }
        if (!empty($filters['bedrooms_min'])) { $where[] = 'p.bedrooms >= ?'; $params[] = (int)$filters['bedrooms_min']; }
        if (!empty($filters['bathrooms_min'])) { $where[] = 'p.bathrooms >= ?'; $params[] = (int)$filters['bathrooms_min']; }
        if (!empty($filters['price_min'])) { $where[] = 'p.price >= ?'; $params[] = (float)$filters['price_min']; }
        if (!empty($filters['price_max'])) { $where[] = 'p.price <= ?'; $params[] = (float)$filters['price_max']; }
        if (!empty($filters['featured'])) { $where[] = 'p.is_featured = 1'; }
        if (!empty($filters['search'])) { $where[] = "MATCH(p.title, p.description, p.address, p.city) AGAINST(? IN BOOLEAN MODE)"; $params[] = $filters['search']; }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM re_properties p WHERE {$wSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $orderBy = match($filters['sort'] ?? '') {
            'price_asc' => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest' => 'p.created_at DESC',
            'oldest' => 'p.created_at ASC',
            'bedrooms' => 'p.bedrooms DESC',
            'area' => 'p.area_sqft DESC',
            default => 'p.is_featured DESC, p.created_at DESC',
        };
        $stmt = $pdo->prepare("SELECT p.*, a.name AS agent_name, a.phone AS agent_phone, a.photo AS agent_photo FROM re_properties p LEFT JOIN re_agents a ON p.agent_id = a.id WHERE {$wSql} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['images'] = json_decode($r['images'] ?: '[]', true);
            $r['features'] = json_decode($r['features'] ?: '[]', true);
        }
        return ['properties' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / max(1, $perPage))];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT p.*, a.name AS agent_name, a.email AS agent_email, a.phone AS agent_phone, a.photo AS agent_photo, a.bio AS agent_bio, a.license_number AS agent_license FROM re_properties p LEFT JOIN re_agents a ON p.agent_id = a.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['images'] = json_decode($r['images'] ?: '[]', true); $r['features'] = json_decode($r['features'] ?: '[]', true); }
        return $r ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT p.*, a.name AS agent_name, a.email AS agent_email, a.phone AS agent_phone, a.photo AS agent_photo, a.bio AS agent_bio, a.license_number AS agent_license FROM re_properties p LEFT JOIN re_agents a ON p.agent_id = a.id WHERE p.slug = ?");
        $stmt->execute([$slug]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['images'] = json_decode($r['images'] ?: '[]', true); $r['features'] = json_decode($r['features'] ?: '[]', true); }
        return $r ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::makeSlug($data['title'] ?? 'property');
        foreach (['images','features'] as $j) { if (isset($data[$j]) && is_array($data[$j])) $data[$j] = json_encode($data[$j]); }
        $stmt = $pdo->prepare("INSERT INTO re_properties (title, slug, description, short_description, property_type, listing_type, price, price_period, currency, bedrooms, bathrooms, area_sqft, lot_size, year_built, address, city, state, zip, country, latitude, longitude, images, floor_plan, virtual_tour, features, agent_id, is_featured, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['title'], $slug,
            ($data['description'] ?? null) ?: null,
            ($data['short_description'] ?? null) ?: null,
            $data['property_type'] ?? 'house',
            $data['listing_type'] ?? 'sale',
            (float)($data['price'] ?? 0),
            $data['price_period'] ?? 'total',
            $data['currency'] ?? 'GBP',
            !empty($data['bedrooms']) ? (int)$data['bedrooms'] : null,
            !empty($data['bathrooms']) ? (int)$data['bathrooms'] : null,
            !empty($data['area_sqft']) ? (int)$data['area_sqft'] : null,
            !empty($data['lot_size']) ? (int)$data['lot_size'] : null,
            !empty($data['year_built']) ? (int)$data['year_built'] : null,
            ($data['address'] ?? null) ?: null,
            ($data['city'] ?? null) ?: null,
            ($data['state'] ?? null) ?: null,
            ($data['zip'] ?? null) ?: null,
            ($data['country'] ?? null) ?: null,
            !empty($data['latitude']) ? (float)$data['latitude'] : null,
            !empty($data['longitude']) ? (float)$data['longitude'] : null,
            ($data['images'] ?? null) ?: null,
            ($data['floor_plan'] ?? null) ?: null,
            ($data['virtual_tour'] ?? null) ?: null,
            ($data['features'] ?? null) ?: null,
            !empty($data['agent_id']) ? (int)$data['agent_id'] : null,
            (int)($data['is_featured'] ?? 0),
            $data['status'] ?? 'active',
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('realestate.property.created', ['id' => $id, 'title' => $data['title']]);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['title','description','short_description','property_type','listing_type','price','price_period','currency','bedrooms','bathrooms','area_sqft','lot_size','year_built','address','city','state','zip','country','latitude','longitude','images','floor_plan','virtual_tour','features','agent_id','is_featured','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $val = $data[$f];
                if (in_array($f, ['images','features']) && is_array($val)) $val = json_encode($val);
                $params[] = $val;
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE re_properties SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("DELETE FROM re_inquiries WHERE property_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM re_properties WHERE id = ?")->execute([$id]);
    }

    public static function incrementViews(int $id): void
    {
        db()->prepare("UPDATE re_properties SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $props = $pdo->query("SELECT COUNT(*) AS total, SUM(status='active') AS active, SUM(status='pending') AS pending, SUM(status='sold') AS sold, SUM(status='rented') AS rented, SUM(is_featured=1) AS featured FROM re_properties")->fetch(\PDO::FETCH_ASSOC);
        $agents = (int)$pdo->query("SELECT COUNT(*) FROM re_agents WHERE status='active'")->fetchColumn();
        $inquiries = $pdo->query("SELECT COUNT(*) AS total, SUM(status='new') AS new_count FROM re_inquiries")->fetch(\PDO::FETCH_ASSOC);
        $avgPrice = (float)$pdo->query("SELECT COALESCE(AVG(price),0) FROM re_properties WHERE status='active' AND listing_type='sale'")->fetchColumn();
        return [
            'properties_total' => (int)($props['total'] ?? 0),
            'properties_active' => (int)($props['active'] ?? 0),
            'properties_pending' => (int)($props['pending'] ?? 0),
            'properties_sold' => (int)($props['sold'] ?? 0),
            'properties_rented' => (int)($props['rented'] ?? 0),
            'properties_featured' => (int)($props['featured'] ?? 0),
            'agents_active' => $agents,
            'inquiries_total' => (int)($inquiries['total'] ?? 0),
            'inquiries_new' => (int)($inquiries['new_count'] ?? 0),
            'avg_price' => $avgPrice,
        ];
    }

    // ─── Settings ───

    public static function getSetting(string $key, string $default = ''): string
    {
        $stmt = db()->prepare("SELECT setting_value FROM re_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() ?: $default;
    }

    public static function setSetting(string $key, string $value): void
    {
        db()->prepare("INSERT INTO re_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$key, $value]);
    }

    public static function getAllSettings(): array
    {
        return db()->query("SELECT setting_key, setting_value FROM re_settings")->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    private static function makeSlug(string $text): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($text)));
        $slug = trim($slug, '-');
        $pdo = db(); $base = $slug; $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM re_properties WHERE slug = ?");
            $stmt->execute([$slug]);
            if ((int)$stmt->fetchColumn() === 0) break;
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
