<?php
namespace Plugins\JessieEmailMarketing;

/**
 * Email Marketing Core — lists, subscribers, campaigns, templates, AI, stats
 */
class EmailCore {
    private \PDO $pdo;
    private int $userId;

    public function __construct(int $userId) {
        if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 3)); }
        require_once CMS_ROOT . '/db.php';
        $this->pdo = \core\Database::connection();
        $this->userId = $userId;
    }

    // ── Lists ──
    public function getLists(): array {
        $stmt = $this->pdo->prepare("SELECT l.*, (SELECT COUNT(*) FROM em_subscribers s WHERE s.list_id = l.id AND s.status='active') as active_subscribers FROM em_lists l WHERE l.user_id = ? AND l.status = 'active' ORDER BY l.name");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function createList(string $name, string $desc = ''): int {
        $stmt = $this->pdo->prepare("INSERT INTO em_lists (user_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$this->userId, $name, $desc]);
        return (int)$this->pdo->lastInsertId();
    }
    public function deleteList(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE em_lists SET status = 'archived' WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]); return $stmt->rowCount() > 0;
    }

    // ── Subscribers ──
    public function getSubscribers(int $listId, int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare("SELECT * FROM em_subscribers WHERE list_id = ? AND user_id = ? ORDER BY subscribed_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$listId, $this->userId, $limit, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function addSubscriber(int $listId, string $email, string $name = '', string $tags = ''): array {
        $check = $this->pdo->prepare("SELECT id FROM em_subscribers WHERE list_id = ? AND email = ? AND user_id = ?");
        $check->execute([$listId, $email, $this->userId]);
        if ($check->fetch()) return ['success' => false, 'error' => 'Already subscribed'];
        $stmt = $this->pdo->prepare("INSERT INTO em_subscribers (user_id, list_id, email, name, tags) VALUES (?,?,?,?,?)");
        $stmt->execute([$this->userId, $listId, $email, $name, $tags]);
        $this->pdo->prepare("UPDATE em_lists SET subscriber_count = subscriber_count + 1 WHERE id = ?")->execute([$listId]);
        return ['success' => true, 'id' => (int)$this->pdo->lastInsertId()];
    }
    public function removeSubscriber(int $id): bool {
        $sub = $this->pdo->prepare("SELECT list_id FROM em_subscribers WHERE id = ? AND user_id = ?");
        $sub->execute([$id, $this->userId]); $row = $sub->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return false;
        $this->pdo->prepare("UPDATE em_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE id = ?")->execute([$id]);
        $this->pdo->prepare("UPDATE em_lists SET subscriber_count = GREATEST(0, subscriber_count - 1) WHERE id = ?")->execute([$row['list_id']]);
        return true;
    }
    public function importSubscribers(int $listId, array $rows): array {
        $added = 0; $skipped = 0;
        foreach ($rows as $row) {
            $email = trim($row['email'] ?? $row[0] ?? '');
            $name = trim($row['name'] ?? $row[1] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $skipped++; continue; }
            $result = $this->addSubscriber($listId, $email, $name);
            $result['success'] ? $added++ : $skipped++;
        }
        return ['added' => $added, 'skipped' => $skipped];
    }

    // ── Campaigns ──
    public function getCampaigns(int $limit = 50): array {
        $stmt = $this->pdo->prepare("SELECT c.*, l.name as list_name FROM em_campaigns c LEFT JOIN em_lists l ON c.list_id = l.id WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT ?");
        $stmt->execute([$this->userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getCampaign(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM em_campaigns WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    public function saveCampaign(array $d): int {
        if (!empty($d['id'])) {
            $this->pdo->prepare("UPDATE em_campaigns SET name=?, subject=?, preview_text=?, from_name=?, from_email=?, html_body=?, text_body=?, list_id=?, scheduled_at=?, status=? WHERE id=? AND user_id=?")->execute([
                $d['name']??'', $d['subject']??'', $d['preview_text']??'', $d['from_name']??'', $d['from_email']??'',
                $d['html_body']??'', $d['text_body']??'', $d['list_id']??null, $d['scheduled_at']??null, $d['status']??'draft',
                (int)$d['id'], $this->userId
            ]);
            return (int)$d['id'];
        }
        $this->pdo->prepare("INSERT INTO em_campaigns (user_id, name, subject, preview_text, from_name, from_email, html_body, text_body, list_id, scheduled_at, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)")->execute([
            $this->userId, $d['name']??'', $d['subject']??'', $d['preview_text']??'', $d['from_name']??'', $d['from_email']??'',
            $d['html_body']??'', $d['text_body']??'', $d['list_id']??null, $d['scheduled_at']??null, $d['status']??'draft'
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    // ── Templates ──
    public function getTemplates(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM em_templates WHERE user_id = ? OR is_global = 1 ORDER BY name");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function saveTemplate(array $d): int {
        if (!empty($d['id'])) {
            $this->pdo->prepare("UPDATE em_templates SET name=?, category=?, html_body=? WHERE id=? AND user_id=?")->execute([$d['name']??'', $d['category']??'general', $d['html_body']??'', (int)$d['id'], $this->userId]);
            return (int)$d['id'];
        }
        $this->pdo->prepare("INSERT INTO em_templates (user_id, name, category, html_body) VALUES (?,?,?,?)")->execute([$this->userId, $d['name']??'', $d['category']??'general', $d['html_body']??'']);
        return (int)$this->pdo->lastInsertId();
    }

    // ── AI ──
    public function generateEmail(string $topic, string $tone = 'professional', string $type = 'newsletter'): array {
        require_once CMS_ROOT . '/core/ai_content.php';
        $prompt = "You are an email marketing expert. Write a {$type} email about: {$topic}\nTone: {$tone}\n\nReturn JSON: {\"subject\":\"...\",\"preview_text\":\"...\",\"html_body\":\"<html>...</html>\",\"text_body\":\"...\"}";
        $result = ai_content_generate(['topic' => $prompt, 'tone' => $tone]);
        if (!$result['ok']) return ['success' => false, 'error' => $result['error'] ?? 'AI failed'];
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $result['content'] ?? '');
        $raw = preg_replace('/\s*```\s*$/', '', $raw);
        $parsed = json_decode(trim($raw), true);
        if (!is_array($parsed)) return ['success' => true, 'subject' => 'Generated Email', 'html_body' => $raw];
        return ['success' => true, 'subject' => $parsed['subject'] ?? '', 'preview_text' => $parsed['preview_text'] ?? '', 'html_body' => $parsed['html_body'] ?? '', 'text_body' => $parsed['text_body'] ?? ''];
    }
    public function generateSubjectLines(string $topic, int $count = 5): array {
        require_once CMS_ROOT . '/core/ai_content.php';
        $prompt = "Generate {$count} compelling email subject lines for: {$topic}\nReturn JSON array of strings: [\"subject1\",\"subject2\",...]";
        $result = ai_content_generate(['topic' => $prompt]);
        if (!$result['ok']) return ['success' => false, 'error' => $result['error'] ?? 'AI failed'];
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $result['content'] ?? '');
        $raw = preg_replace('/\s*```\s*$/', '', $raw);
        $lines = json_decode(trim($raw), true);
        return ['success' => true, 'subjects' => is_array($lines) ? $lines : [$raw]];
    }

    // ── Segmentation ──
    public function getSegmentedSubscribers(int $listId, array $criteria): array {
        $sql = "SELECT * FROM em_subscribers WHERE list_id = ? AND user_id = ? AND status = 'active'";
        $params = [$listId, $this->userId];
        if (!empty($criteria['tags'])) { $sql .= " AND tags LIKE ?"; $params[] = '%' . $criteria['tags'] . '%'; }
        if (!empty($criteria['subscribed_after'])) { $sql .= " AND subscribed_at >= ?"; $params[] = $criteria['subscribed_after']; }
        if (!empty($criteria['subscribed_before'])) { $sql .= " AND subscribed_at <= ?"; $params[] = $criteria['subscribed_before']; }
        if (!empty($criteria['opened_campaign'])) { $sql .= " AND email IN (SELECT subscriber_email FROM em_events WHERE campaign_id = ? AND event_type = 'open')"; $params[] = (int)$criteria['opened_campaign']; }
        if (!empty($criteria['clicked_campaign'])) { $sql .= " AND email IN (SELECT subscriber_email FROM em_events WHERE campaign_id = ? AND event_type = 'click')"; $params[] = (int)$criteria['clicked_campaign']; }
        if (isset($criteria['not_opened_days'])) {
            $days = (int)$criteria['not_opened_days'];
            $sql .= " AND email NOT IN (SELECT DISTINCT subscriber_email FROM em_events WHERE user_id = ? AND event_type = 'open' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY))";
            $params[] = $this->userId; $params[] = $days;
        }
        $stmt = $this->pdo->prepare($sql . " ORDER BY subscribed_at DESC");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Automation ──
    public function getAutomations(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM em_automations WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function createAutomation(array $d): int {
        $stmt = $this->pdo->prepare("INSERT INTO em_automations (user_id, name, trigger_type, trigger_config, action_type, action_config, delay_minutes, status) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$this->userId, $d['name']??'', $d['trigger_type']??'subscribe', json_encode($d['trigger_config']??[]), $d['action_type']??'send_email', json_encode($d['action_config']??[]), (int)($d['delay_minutes']??0), $d['status']??'active']);
        return (int)$this->pdo->lastInsertId();
    }
    public function updateAutomation(int $id, array $d): bool {
        $allowed = ['name','trigger_type','trigger_config','action_type','action_config','delay_minutes','status'];
        $sets = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $d)) {
                $sets[] = "`$f` = ?";
                $params[] = in_array($f, ['trigger_config','action_config']) ? json_encode($d[$f]) : $d[$f];
            }
        }
        if (empty($sets)) return false;
        $params[] = $id; $params[] = $this->userId;
        return $this->pdo->prepare("UPDATE em_automations SET " . implode(',', $sets) . " WHERE id=? AND user_id=?")->execute($params);
    }
    public function processAutomations(): array {
        $processed = 0;
        $automations = $this->pdo->prepare("SELECT * FROM em_automations WHERE user_id = ? AND status = 'active'");
        $automations->execute([$this->userId]);
        foreach ($automations->fetchAll(\PDO::FETCH_ASSOC) as $a) {
            // Check trigger conditions and execute (simplified — in production would be cron-based)
            $processed++;
        }
        return ['processed' => $processed];
    }

    // ── A/B Testing ──
    public function createABTest(int $campaignId, string $variantSubject, string $variantBody, int $testPercentage = 20): array {
        $campaign = $this->getCampaign($campaignId);
        if (!$campaign) return ['success' => false, 'error' => 'Campaign not found'];
        $variantId = $this->saveCampaign([
            'name' => $campaign['name'] . ' (Variant B)',
            'subject' => $variantSubject,
            'html_body' => $variantBody ?: $campaign['html_body'],
            'text_body' => $campaign['text_body'],
            'from_name' => $campaign['from_name'],
            'from_email' => $campaign['from_email'],
            'list_id' => $campaign['list_id'],
            'status' => 'ab_test'
        ]);
        return ['success' => true, 'variant_id' => $variantId, 'test_percentage' => $testPercentage];
    }

    // ── Tracking ──
    public function trackEvent(int $campaignId, string $email, string $eventType, string $metadata = ''): void {
        $this->pdo->prepare("INSERT INTO em_events (user_id, campaign_id, subscriber_email, event_type, metadata) VALUES (?,?,?,?,?)")
            ->execute([$this->userId, $campaignId, $email, $eventType, $metadata]);
        if ($eventType === 'open') {
            $this->pdo->prepare("UPDATE em_campaigns SET total_opened = total_opened + 1 WHERE id = ?")->execute([$campaignId]);
        } elseif ($eventType === 'click') {
            $this->pdo->prepare("UPDATE em_campaigns SET total_clicked = total_clicked + 1 WHERE id = ?")->execute([$campaignId]);
        } elseif ($eventType === 'bounce') {
            $this->pdo->prepare("UPDATE em_subscribers SET status = 'bounced' WHERE email = ? AND user_id = ?")->execute([$email, $this->userId]);
        } elseif ($eventType === 'unsubscribe') {
            $this->pdo->prepare("UPDATE em_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE email = ? AND user_id = ?")->execute([$email, $this->userId]);
        }
    }
    public function getCampaignEvents(int $campaignId): array {
        $stmt = $this->pdo->prepare("SELECT event_type, COUNT(*) as cnt FROM em_events WHERE campaign_id = ? AND user_id = ? GROUP BY event_type");
        $stmt->execute([$campaignId, $this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Bounce & List Hygiene ──
    public function cleanList(int $listId): array {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM em_subscribers WHERE list_id = ? AND user_id = ? AND status IN ('bounced','unsubscribed','invalid')");
        $stmt->execute([$listId, $this->userId]);
        $count = (int)$stmt->fetchColumn();
        $this->pdo->prepare("DELETE FROM em_subscribers WHERE list_id = ? AND user_id = ? AND status IN ('bounced','unsubscribed','invalid')")->execute([$listId, $this->userId]);
        $this->pdo->prepare("UPDATE em_lists SET subscriber_count = (SELECT COUNT(*) FROM em_subscribers WHERE list_id = ? AND status = 'active') WHERE id = ?")->execute([$listId, $listId]);
        return ['removed' => $count];
    }
    public function getListHealth(int $listId): array {
        $stmt = $this->pdo->prepare("SELECT status, COUNT(*) as cnt FROM em_subscribers WHERE list_id = ? AND user_id = ? GROUP BY status");
        $stmt->execute([$listId, $this->userId]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $health = [];
        foreach ($data as $d) $health[$d['status']] = (int)$d['cnt'];
        $total = array_sum($health);
        $health['total'] = $total;
        $health['health_score'] = $total > 0 ? round(($health['active'] ?? 0) / $total * 100) : 0;
        return $health;
    }

    // ── Export ──
    public function exportSubscribers(int $listId): array {
        $stmt = $this->pdo->prepare("SELECT email, name, tags, status, subscribed_at FROM em_subscribers WHERE list_id = ? AND user_id = ? ORDER BY subscribed_at DESC");
        $stmt->execute([$listId, $this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Stats ──
    public function getStats(): array {
        $uid = $this->userId;
        $lists = $this->pdo->prepare("SELECT COUNT(*) FROM em_lists WHERE user_id = ? AND status='active'"); $lists->execute([$uid]);
        $subs = $this->pdo->prepare("SELECT COUNT(*) FROM em_subscribers WHERE user_id = ? AND status='active'"); $subs->execute([$uid]);
        $camps = $this->pdo->prepare("SELECT COUNT(*) FROM em_campaigns WHERE user_id = ?"); $camps->execute([$uid]);
        $sent = $this->pdo->prepare("SELECT COALESCE(SUM(total_sent),0) FROM em_campaigns WHERE user_id = ?"); $sent->execute([$uid]);
        return ['lists' => (int)$lists->fetchColumn(), 'subscribers' => (int)$subs->fetchColumn(), 'campaigns' => (int)$camps->fetchColumn(), 'total_sent' => (int)$sent->fetchColumn()];
    }
    public function getCampaignStats(int $campaignId): array {
        $c = $this->getCampaign($campaignId);
        if (!$c) return [];
        $openRate = $c['total_sent'] > 0 ? round($c['total_opened'] / $c['total_sent'] * 100, 1) : 0;
        $clickRate = $c['total_sent'] > 0 ? round($c['total_clicked'] / $c['total_sent'] * 100, 1) : 0;
        return array_merge($c, ['open_rate' => $openRate, 'click_rate' => $clickRate]);
    }
}
