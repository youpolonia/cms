<?php
declare(strict_types=1);

class RestaurantMenu
{
    public static function getCategories(?string $status = null): array
    {
        $pdo = db();
        $sql = "SELECT * FROM restaurant_categories" . ($status ? " WHERE status = ?" : "") . " ORDER BY sort_order, name";
        $stmt = $status ? $pdo->prepare($sql) : $pdo->query($sql);
        if ($status) $stmt->execute([$status]);
        else $stmt = $pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getCategory(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM restaurant_categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function createCategory(array $data): int
    {
        $pdo = db();
        $slug = self::makeSlug($data['name'] ?? 'category', 'restaurant_categories');
        $stmt = $pdo->prepare("INSERT INTO restaurant_categories (name, slug, description, icon, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $slug, $data['description'] ?? '', $data['icon'] ?? '', (int)($data['sort_order'] ?? 0), $data['status'] ?? 'active']);
        return (int)$pdo->lastInsertId();
    }

    public static function updateCategory(int $id, array $data): void
    {
        $allowed = ['name','description','icon','sort_order','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) { if (array_key_exists($f, $data)) { $fields[] = "{$f} = ?"; $params[] = $data[$f]; } }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE restaurant_categories SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function deleteCategory(int $id): void
    {
        db()->prepare("DELETE FROM restaurant_categories WHERE id = ?")->execute([$id]);
    }

    // ─── Items ───

    public static function getItems(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'i.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['category_id'])) { $where[] = 'i.category_id = ?'; $params[] = (int)$filters['category_id']; }
        if (!empty($filters['search'])) { $where[] = "MATCH(i.name, i.description, i.allergens) AGAINST(? IN BOOLEAN MODE)"; $params[] = $filters['search']; }
        if (!empty($filters['vegetarian'])) { $where[] = 'i.is_vegetarian = 1'; }
        if (!empty($filters['vegan'])) { $where[] = 'i.is_vegan = 1'; }
        if (!empty($filters['gluten_free'])) { $where[] = 'i.is_gluten_free = 1'; }
        if (!empty($filters['available'])) { $where[] = 'i.is_available = 1'; }
        $wSql = implode(' AND ', $where);

        $total = (int)$pdo->prepare("SELECT COUNT(*) FROM restaurant_items i WHERE {$wSql}")->execute($params) ? $pdo->query("SELECT FOUND_ROWS()")->fetchColumn() : 0;
        $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS i.*, c.name AS category_name FROM restaurant_items i LEFT JOIN restaurant_categories c ON i.category_id = c.id WHERE {$wSql} ORDER BY i.sort_order, i.name LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;
        $stmt->execute($params);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $total = (int)$pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

        foreach ($items as &$item) {
            $item['options'] = json_decode($item['options'] ?: '[]', true);
            $item['extras'] = json_decode($item['extras'] ?: '[]', true);
            $item['gallery'] = json_decode($item['gallery'] ?: '[]', true);
        }
        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function getItem(int $id): ?array
    {
        $stmt = db()->prepare("SELECT i.*, c.name AS category_name FROM restaurant_items i LEFT JOIN restaurant_categories c ON i.category_id = c.id WHERE i.id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) {
            $r['options'] = json_decode($r['options'] ?: '[]', true);
            $r['extras'] = json_decode($r['extras'] ?: '[]', true);
            $r['gallery'] = json_decode($r['gallery'] ?: '[]', true);
        }
        return $r ?: null;
    }

    public static function createItem(array $data): int
    {
        $pdo = db();
        $slug = self::makeSlug($data['name'] ?? 'item', 'restaurant_items');
        foreach (['options','extras','gallery'] as $j) {
            if (isset($data[$j]) && is_array($data[$j])) $data[$j] = json_encode($data[$j]);
        }
        $stmt = $pdo->prepare("INSERT INTO restaurant_items (name, slug, category_id, description, short_description, price, sale_price, image, allergens, calories, prep_time_min, is_vegetarian, is_vegan, is_gluten_free, is_spicy, is_featured, is_available, sort_order, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['name'], $slug, !empty($data['category_id']) ? (int)$data['category_id'] : null,
            $data['description'] ?? '', $data['short_description'] ?? '',
            (float)($data['price'] ?? 0), !empty($data['sale_price']) ? (float)$data['sale_price'] : null,
            $data['image'] ?? '', $data['allergens'] ?? '',
            !empty($data['calories']) ? (int)$data['calories'] : null,
            !empty($data['prep_time_min']) ? (int)$data['prep_time_min'] : null,
            (int)($data['is_vegetarian'] ?? 0), (int)($data['is_vegan'] ?? 0),
            (int)($data['is_gluten_free'] ?? 0), (int)($data['is_spicy'] ?? 0),
            (int)($data['is_featured'] ?? 0), (int)($data['is_available'] ?? 1),
            (int)($data['sort_order'] ?? 0), $data['status'] ?? 'active',
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('restaurant.item.created', ['id' => $id, 'name' => $data['name']]);
        return $id;
    }

    public static function updateItem(int $id, array $data): void
    {
        $allowed = ['name','category_id','description','short_description','price','sale_price','image','options','extras','gallery','allergens','calories','prep_time_min','is_vegetarian','is_vegan','is_gluten_free','is_spicy','is_featured','is_available','sort_order','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $val = $data[$f];
                if (in_array($f, ['options','extras','gallery']) && is_array($val)) $val = json_encode($val);
                $params[] = $val;
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE restaurant_items SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function deleteItem(int $id): void
    {
        db()->prepare("DELETE FROM restaurant_items WHERE id = ?")->execute([$id]);
    }

    public static function getFullMenu(): array
    {
        $categories = self::getCategories('active');
        $allItems = self::getItems(['status' => 'active', 'available' => true], 1, 500);
        $menu = [];
        foreach ($categories as $cat) {
            $cat['items'] = array_filter($allItems['items'], fn($i) => (int)$i['category_id'] === (int)$cat['id']);
            $cat['items'] = array_values($cat['items']);
            $menu[] = $cat;
        }
        // Uncategorized
        $uncat = array_filter($allItems['items'], fn($i) => empty($i['category_id']));
        if (!empty($uncat)) {
            $menu[] = ['id' => 0, 'name' => 'Other', 'slug' => 'other', 'icon' => '🍽️', 'items' => array_values($uncat)];
        }
        return $menu;
    }

    public static function getStats(): array
    {
        $pdo = db();
        $items = $pdo->query("SELECT COUNT(*) AS total, SUM(status='active') AS active, SUM(is_featured=1) AS featured FROM restaurant_items")->fetch(\PDO::FETCH_ASSOC);
        $orders = $pdo->query("SELECT COUNT(*) AS total, SUM(status='new') AS new_orders, SUM(status='preparing') AS preparing, SUM(total) AS revenue FROM restaurant_orders")->fetch(\PDO::FETCH_ASSOC);
        $today = $pdo->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS rev FROM restaurant_orders WHERE DATE(created_at) = CURDATE()")->fetch(\PDO::FETCH_ASSOC);
        return [
            'items_total' => (int)$items['total'], 'items_active' => (int)$items['active'], 'items_featured' => (int)$items['featured'],
            'orders_total' => (int)$orders['total'], 'orders_new' => (int)($orders['new_orders'] ?? 0), 'orders_preparing' => (int)($orders['preparing'] ?? 0),
            'revenue_total' => (float)($orders['revenue'] ?? 0),
            'orders_today' => (int)$today['cnt'], 'revenue_today' => (float)$today['rev'],
            'categories' => (int)$pdo->query("SELECT COUNT(*) FROM restaurant_categories WHERE status='active'")->fetchColumn(),
        ];
    }

    // ─── Settings ───

    public static function getSetting(string $key, string $default = ''): string
    {
        $stmt = db()->prepare("SELECT setting_value FROM restaurant_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() ?: $default;
    }

    public static function setSetting(string $key, string $value): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO restaurant_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$key, $value]);
    }

    public static function getAllSettings(): array
    {
        return db()->query("SELECT setting_key, setting_value FROM restaurant_settings")->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    private static function makeSlug(string $text, string $table): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($text)));
        $slug = trim($slug, '-');
        $pdo = db(); $base = $slug; $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE slug = ?");
            $stmt->execute([$slug]);
            if ((int)$stmt->fetchColumn() === 0) break;
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
