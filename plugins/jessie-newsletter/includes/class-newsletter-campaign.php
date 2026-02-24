<?php
declare(strict_types=1);

class NewsletterCampaign
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'c.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['list_id'])) { $where[] = 'c.list_id = ?'; $params[] = (int)$filters['list_id']; }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_campaigns c WHERE {$wSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT c.*, l.name AS list_name FROM newsletter_campaigns c LEFT JOIN newsletter_lists l ON c.list_id = l.id WHERE {$wSql} ORDER BY c.updated_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        return ['campaigns' => $stmt->fetchAll(\PDO::FETCH_ASSOC), 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT c.*, l.name AS list_name FROM newsletter_campaigns c LEFT JOIN newsletter_lists l ON c.list_id = l.id WHERE c.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO newsletter_campaigns (name, subject, preview_text, from_name, from_email, reply_to, content_html, content_text, template_id, list_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')");
        $stmt->execute([
            $data['name'] ?? 'Untitled Campaign',
            $data['subject'] ?? '',
            $data['preview_text'] ?? '',
            $data['from_name'] ?? '',
            $data['from_email'] ?? '',
            $data['reply_to'] ?? '',
            $data['content_html'] ?? '',
            $data['content_text'] ?? '',
            !empty($data['template_id']) ? (int)$data['template_id'] : null,
            !empty($data['list_id']) ? (int)$data['list_id'] : null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = db();
        $allowed = ['name','subject','preview_text','from_name','from_email','reply_to','content_html','content_text','template_id','list_id','status','scheduled_at','segment_conditions'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $pdo->prepare("UPDATE newsletter_campaigns SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("DELETE FROM newsletter_events WHERE campaign_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM newsletter_campaigns WHERE id = ?")->execute([$id]);
    }

    public static function duplicate(int $id): ?int
    {
        $c = self::get($id);
        if (!$c) return null;
        return self::create([
            'name'         => $c['name'] . ' (Copy)',
            'subject'      => $c['subject'],
            'preview_text' => $c['preview_text'],
            'from_name'    => $c['from_name'],
            'from_email'   => $c['from_email'],
            'reply_to'     => $c['reply_to'],
            'content_html' => $c['content_html'],
            'content_text' => $c['content_text'],
            'template_id'  => $c['template_id'],
            'list_id'      => $c['list_id'],
        ]);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $row = $pdo->query("SELECT
            COUNT(*) AS total,
            SUM(status='draft') AS drafts,
            SUM(status='sent') AS sent,
            SUM(status='scheduled') AS scheduled,
            SUM(stats_sent) AS total_sent,
            SUM(stats_opened) AS total_opened,
            SUM(stats_clicked) AS total_clicked
        FROM newsletter_campaigns")->fetch(\PDO::FETCH_ASSOC);
        $row = array_map('intval', $row);
        $row['open_rate'] = $row['total_sent'] > 0 ? round($row['total_opened'] / $row['total_sent'] * 100, 1) : 0;
        $row['click_rate'] = $row['total_sent'] > 0 ? round($row['total_clicked'] / $row['total_sent'] * 100, 1) : 0;
        return $row;
    }

    /**
     * Personalize HTML content with subscriber merge tags.
     */
    public static function personalize(string $html, array $subscriber, array $campaign = []): string
    {
        $replacements = [
            '{{email}}'          => $subscriber['email'] ?? '',
            '{{name}}'           => $subscriber['name'] ?? 'Subscriber',
            '{{first_name}}'     => explode(' ', $subscriber['name'] ?? '')[0] ?? 'there',
            '{{subscriber_id}}'  => (string)($subscriber['id'] ?? ''),
            '{{subject}}'        => $campaign['subject'] ?? '',
            '{{company_name}}'   => $campaign['from_name'] ?? '',
            '{{sender_name}}'    => $campaign['from_name'] ?? '',
            '{{unsubscribe_url}}'=> '/newsletter/unsubscribe?email=' . urlencode($subscriber['email'] ?? ''),
            '{{current_year}}'   => date('Y'),
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }
}
