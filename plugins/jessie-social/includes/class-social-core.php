<?php
namespace Plugins\JessieSocial;

/**
 * Social Media Scheduler Core — accounts, posts, scheduling, AI generation
 */
class SocialCore {
    private \PDO $pdo;
    private int $userId;

    public function __construct(int $userId) {
        if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 3)); }
        require_once CMS_ROOT . '/db.php';
        $this->pdo = \core\Database::connection();
        $this->userId = $userId;
    }

    // ── Accounts ──

    public function getAccounts(): array {
        $stmt = $this->pdo->prepare("SELECT id, platform, account_name, profile_url, avatar_url, status, created_at FROM social_accounts WHERE user_id = ? ORDER BY platform");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function connectAccount(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO social_accounts (user_id, platform, account_name, access_token, refresh_token, token_expires_at, profile_url, avatar_url) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$this->userId, $data['platform'], $data['account_name'] ?? '', $data['access_token'] ?? '', $data['refresh_token'] ?? '', $data['token_expires_at'] ?? null, $data['profile_url'] ?? '', $data['avatar_url'] ?? '']);
        return (int)$this->pdo->lastInsertId();
    }

    public function disconnectAccount(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE social_accounts SET status = 'disconnected' WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        return $stmt->rowCount() > 0;
    }

    // ── Posts ──

    public function getPosts(int $limit = 50, ?string $status = null): array {
        $sql = "SELECT p.*, a.account_name, a.platform as account_platform FROM social_posts p LEFT JOIN social_accounts a ON p.account_id = a.id WHERE p.user_id = ?";
        $params = [$this->userId];
        if ($status) { $sql .= " AND p.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY COALESCE(p.scheduled_at, p.created_at) DESC LIMIT ?";
        $params[] = $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPost(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM social_posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function createPost(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO social_posts (user_id, account_id, platform, content, media_urls, hashtags, scheduled_at, status) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $this->userId, $data['account_id'] ?? null, $data['platform'] ?? 'twitter',
            $data['content'] ?? '', $data['media_urls'] ?? null, $data['hashtags'] ?? null,
            $data['scheduled_at'] ?? null, $data['status'] ?? 'draft'
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updatePost(int $id, array $data): bool {
        $allowed = ['content', 'media_urls', 'hashtags', 'scheduled_at', 'status', 'account_id', 'platform'];
        $sets = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) { $sets[] = "`$f` = ?"; $params[] = $data[$f]; }
        }
        if (empty($sets)) return false;
        $params[] = $id; $params[] = $this->userId;
        return $this->pdo->prepare("UPDATE social_posts SET " . implode(', ', $sets) . " WHERE id = ? AND user_id = ?")->execute($params);
    }

    public function deletePost(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM social_posts WHERE id = ? AND user_id = ? AND status IN ('draft','scheduled')");
        $stmt->execute([$id, $this->userId]);
        return $stmt->rowCount() > 0;
    }

    // ── Calendar ──

    public function getCalendar(string $startDate, string $endDate): array {
        $stmt = $this->pdo->prepare("SELECT id, platform, content, scheduled_at, status FROM social_posts WHERE user_id = ? AND scheduled_at BETWEEN ? AND ? ORDER BY scheduled_at");
        $stmt->execute([$this->userId, $startDate, $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Templates ──

    public function getTemplates(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM social_templates WHERE user_id = ? ORDER BY name");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function saveTemplate(array $data): int {
        if (!empty($data['id'])) {
            $this->pdo->prepare("UPDATE social_templates SET name=?, platform=?, content=?, hashtags=?, category=? WHERE id=? AND user_id=?")
                ->execute([$data['name'], $data['platform'] ?? 'all', $data['content'], $data['hashtags'] ?? '', $data['category'] ?? '', (int)$data['id'], $this->userId]);
            return (int)$data['id'];
        }
        $stmt = $this->pdo->prepare("INSERT INTO social_templates (user_id, name, platform, content, hashtags, category, is_ai_generated) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$this->userId, $data['name'] ?? 'Untitled', $data['platform'] ?? 'all', $data['content'] ?? '', $data['hashtags'] ?? '', $data['category'] ?? '', (int)($data['is_ai'] ?? 0)]);
        return (int)$this->pdo->lastInsertId();
    }

    // ── AI Generation ──

    public function generatePost(string $topic, string $platform, string $tone = 'professional', string $language = 'en'): array {
        require_once CMS_ROOT . '/core/ai_content.php';
        $limits = ['twitter' => 280, 'linkedin' => 3000, 'facebook' => 2000, 'instagram' => 2200, 'tiktok' => 300, 'pinterest' => 500];
        $charLimit = $limits[$platform] ?? 2000;
        $prompt = "You are a social media expert. Write a {$platform} post about: {$topic}\n\nTone: {$tone}\nMax chars: {$charLimit}\nLanguage: {$language}\n\nReturn JSON: {\"content\":\"...\",\"hashtags\":[\"tag1\",\"tag2\",...],\"best_time\":\"HH:MM\",\"tip\":\"...\"}";
        $result = ai_content_generate(['topic' => $prompt, 'language' => $language]);
        if (!$result['ok']) return ['success' => false, 'error' => $result['error'] ?? 'AI failed'];
        $raw = $result['content'] ?? '';
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```\s*$/', '', $raw);
        $parsed = json_decode(trim($raw), true);
        if (!is_array($parsed)) return ['success' => true, 'content' => $raw, 'hashtags' => []];
        return ['success' => true, 'content' => $parsed['content'] ?? $raw, 'hashtags' => $parsed['hashtags'] ?? [], 'best_time' => $parsed['best_time'] ?? '', 'tip' => $parsed['tip'] ?? ''];
    }

    // ── Stats ──

    public function getStats(): array {
        $uid = $this->userId;
        $accounts = $this->pdo->prepare("SELECT COUNT(*) FROM social_accounts WHERE user_id = ? AND status = 'active'"); $accounts->execute([$uid]);
        $total = $this->pdo->prepare("SELECT COUNT(*) FROM social_posts WHERE user_id = ?"); $total->execute([$uid]);
        $published = $this->pdo->prepare("SELECT COUNT(*) FROM social_posts WHERE user_id = ? AND status = 'published'"); $published->execute([$uid]);
        $scheduled = $this->pdo->prepare("SELECT COUNT(*) FROM social_posts WHERE user_id = ? AND status = 'scheduled'"); $scheduled->execute([$uid]);
        return ['accounts' => (int)$accounts->fetchColumn(), 'total_posts' => (int)$total->fetchColumn(), 'published' => (int)$published->fetchColumn(), 'scheduled' => (int)$scheduled->fetchColumn()];
    }
}
