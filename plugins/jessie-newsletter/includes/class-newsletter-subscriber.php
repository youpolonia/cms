<?php
declare(strict_types=1);

class NewsletterSubscriber
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $pdo = db();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) { $where[] = 'status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['list_id'])) { $where[] = 'JSON_CONTAINS(lists, ?)'; $params[] = json_encode((int)$filters['list_id']); }
        if (!empty($filters['search'])) { $where[] = '(email LIKE ? OR name LIKE ?)'; $params[] = '%'.$filters['search'].'%'; $params[] = '%'.$filters['search'].'%'; }

        $wSql = implode(' AND ', $where);
        $total = (int)$pdo->prepare("SELECT COUNT(*) FROM newsletter_subscribers WHERE {$wSql}")->execute($params) ? $pdo->query("SELECT FOUND_ROWS()")->fetchColumn() : 0;
        // Recalc total properly
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_subscribers WHERE {$wSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers WHERE {$wSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) { $r['lists'] = json_decode($r['lists'] ?: '[]', true); $r['custom_fields'] = json_decode($r['custom_fields'] ?: '{}', true); }

        return ['subscribers' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['lists'] = json_decode($r['lists'] ?: '[]', true); $r['custom_fields'] = json_decode($r['custom_fields'] ?: '{}', true); }
        return $r ?: null;
    }

    public static function getByEmail(string $email): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers WHERE email = ?");
        $stmt->execute([strtolower(trim($email))]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['lists'] = json_decode($r['lists'] ?: '[]', true); }
        return $r ?: null;
    }

    public static function subscribe(string $email, string $name = '', array $listIds = [], string $source = 'form'): array
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'error' => 'Invalid email'];

        $pdo = db();
        $existing = self::getByEmail($email);

        if ($existing) {
            // Re-subscribe if unsubscribed
            if ($existing['status'] === 'unsubscribed' || $existing['status'] === 'bounced') {
                $mergedLists = array_values(array_unique(array_merge($existing['lists'], $listIds)));
                $pdo->prepare("UPDATE newsletter_subscribers SET status = 'active', lists = ?, name = COALESCE(NULLIF(?, ''), name), confirmed_at = NOW(), unsubscribed_at = NULL WHERE id = ?")
                    ->execute([json_encode($mergedLists), $name, $existing['id']]);
                self::recountLists($mergedLists);
                return ['ok' => true, 'id' => $existing['id'], 'resubscribed' => true];
            }
            // Already active — just add to new lists
            $mergedLists = array_values(array_unique(array_merge($existing['lists'], $listIds)));
            if ($mergedLists !== $existing['lists']) {
                $pdo->prepare("UPDATE newsletter_subscribers SET lists = ? WHERE id = ?")->execute([json_encode($mergedLists), $existing['id']]);
                self::recountLists($mergedLists);
            }
            return ['ok' => true, 'id' => $existing['id'], 'already_subscribed' => true];
        }

        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, name, status, lists, source, ip_address, confirmed_at) VALUES (?, ?, 'active', ?, ?, ?, NOW())");
        $stmt->execute([$email, $name, json_encode($listIds), $source, $_SERVER['REMOTE_ADDR'] ?? null]);
        $id = (int)$pdo->lastInsertId();

        self::recountLists($listIds);

        if (function_exists('cms_event')) {
            cms_event('newsletter.subscribed', ['subscriber_id' => $id, 'email' => $email, 'lists' => $listIds]);
        }

        return ['ok' => true, 'id' => $id];
    }

    public static function unsubscribe(string $email): array
    {
        $email = strtolower(trim($email));
        $pdo = db();
        $sub = self::getByEmail($email);
        if (!$sub) return ['ok' => false, 'error' => 'Not found'];

        $pdo->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE id = ?")->execute([$sub['id']]);
        self::recountLists($sub['lists']);

        if (function_exists('cms_event')) {
            cms_event('newsletter.unsubscribed', ['subscriber_id' => $sub['id'], 'email' => $email]);
        }

        return ['ok' => true];
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $sub = self::get($id);
        $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?")->execute([$id]);
        if ($sub) self::recountLists($sub['lists']);
    }

    public static function importCSV(string $csvContent, int $listId): array
    {
        $lines = array_filter(explode("\n", trim($csvContent)));
        $header = str_getcsv(array_shift($lines));
        $emailIdx = array_search('email', array_map('strtolower', $header));
        $nameIdx = array_search('name', array_map('strtolower', $header));
        if ($emailIdx === false) return ['ok' => false, 'error' => 'CSV must have an "email" column'];

        $imported = 0; $skipped = 0; $errors = 0;
        foreach ($lines as $line) {
            $cols = str_getcsv($line);
            $email = trim($cols[$emailIdx] ?? '');
            $name = trim($cols[$nameIdx ?? -1] ?? '');
            if (!$email) continue;
            $result = self::subscribe($email, $name, [$listId], 'csv_import');
            if ($result['ok']) { if (!empty($result['already_subscribed'])) $skipped++; else $imported++; }
            else $errors++;
        }
        return ['ok' => true, 'imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }

    public static function getStats(): array
    {
        $pdo = db();
        $row = $pdo->query("SELECT
            COUNT(*) AS total,
            SUM(status='active') AS active,
            SUM(status='unsubscribed') AS unsubscribed,
            SUM(status='bounced') AS bounced,
            SUM(status='pending') AS pending,
            SUM(created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status='active') AS new_30d
        FROM newsletter_subscribers")->fetch(\PDO::FETCH_ASSOC);
        return array_map('intval', $row);
    }

    public static function getForCampaign(int $listId, int $limit = 0, int $offset = 0): array
    {
        $pdo = db();
        $sql = "SELECT id, email, name FROM newsletter_subscribers WHERE status = 'active' AND JSON_CONTAINS(lists, ?)";
        $params = [json_encode($listId)];
        if ($limit) $sql .= " LIMIT {$limit} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function countForList(int $listId): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'active' AND JSON_CONTAINS(lists, ?)");
        $stmt->execute([json_encode($listId)]);
        return (int)$stmt->fetchColumn();
    }

    private static function recountLists(array $listIds): void
    {
        $pdo = db();
        foreach ($listIds as $lid) {
            $count = self::countForList((int)$lid);
            $pdo->prepare("UPDATE newsletter_lists SET subscriber_count = ? WHERE id = ?")->execute([$count, (int)$lid]);
        }
    }
}
