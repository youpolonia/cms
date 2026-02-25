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

    // ── Hashtag Research ──

    public function researchHashtags(string $topic, string $platform, string $language = 'en'): array {
        require_once CMS_ROOT . '/core/ai_content.php';
        $prompt = "You are a social media hashtag expert. Research trending and relevant hashtags for: {$topic}\nPlatform: {$platform}\nLanguage: {$language}\n\nReturn JSON: {\"trending\":[{\"tag\":\"#...\",\"popularity\":\"high|medium|low\"}],\"niche\":[{\"tag\":\"#...\",\"relevance\":\"high|medium\"}],\"avoid\":[\"#banned1\"],\"strategy\":\"tip for hashtag usage\"}";
        $result = ai_content_generate(['topic' => $prompt]);
        if (!$result['ok']) return ['success' => false, 'error' => $result['error'] ?? 'AI failed'];
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $result['content'] ?? '');
        $raw = preg_replace('/\s*```\s*$/', '', $raw);
        $parsed = json_decode(trim($raw), true);
        return ['success' => true, 'hashtags' => $parsed ?? []];
    }

    // ── Bulk Schedule ──

    public function bulkSchedule(array $posts): array {
        $created = []; $errors = [];
        foreach ($posts as $i => $post) {
            try {
                $id = $this->createPost($post);
                $created[] = $id;
            } catch (\Exception $e) {
                $errors[] = "Post #{$i}: " . $e->getMessage();
            }
        }
        return ['success' => true, 'created' => count($created), 'ids' => $created, 'errors' => $errors];
    }

    // ── Content Recycling ──

    public function getTopPerformingPosts(int $limit = 10): array {
        $stmt = $this->pdo->prepare("SELECT p.*, a.account_name FROM social_posts p LEFT JOIN social_accounts a ON p.account_id = a.id WHERE p.user_id = ? AND p.status = 'published' ORDER BY p.engagement_score DESC, p.created_at DESC LIMIT ?");
        $stmt->execute([$this->userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function recyclePost(int $postId, ?string $scheduledAt = null): ?int {
        $post = $this->getPost($postId);
        if (!$post) return null;
        return $this->createPost([
            'account_id' => $post['account_id'], 'platform' => $post['platform'],
            'content' => $post['content'], 'media_urls' => $post['media_urls'],
            'hashtags' => $post['hashtags'], 'scheduled_at' => $scheduledAt,
            'status' => $scheduledAt ? 'scheduled' : 'draft'
        ]);
    }

    // ── Best Time to Post ──

    public function getBestTimes(string $platform = ''): array {
        $sql = "SELECT HOUR(scheduled_at) as hour, platform, COUNT(*) as posts, AVG(engagement_score) as avg_engagement FROM social_posts WHERE user_id = ? AND status = 'published' AND scheduled_at IS NOT NULL";
        $params = [$this->userId];
        if ($platform) { $sql .= " AND platform = ?"; $params[] = $platform; }
        $sql .= " GROUP BY hour, platform ORDER BY avg_engagement DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Post Analytics ──

    public function recordEngagement(int $postId, int $likes = 0, int $comments = 0, int $shares = 0, int $clicks = 0, int $impressions = 0): bool {
        $score = $likes + ($comments * 3) + ($shares * 5) + ($clicks * 2);
        $stmt = $this->pdo->prepare("UPDATE social_posts SET engagement_score = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$score, $postId, $this->userId]);
        $this->pdo->prepare("INSERT INTO social_analytics (post_id, user_id, likes, comments, shares, clicks, impressions, recorded_at) VALUES (?,?,?,?,?,?,?,NOW())")
            ->execute([$postId, $this->userId, $likes, $comments, $shares, $clicks, $impressions]);
        return true;
    }

    public function getPostAnalytics(int $postId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM social_analytics WHERE post_id = ? AND user_id = ? ORDER BY recorded_at DESC");
        $stmt->execute([$postId, $this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getEngagementOverview(string $startDate, string $endDate): array {
        $stmt = $this->pdo->prepare("SELECT platform, COUNT(DISTINCT p.id) as posts, SUM(a.likes) as total_likes, SUM(a.comments) as total_comments, SUM(a.shares) as total_shares, SUM(a.clicks) as total_clicks, SUM(a.impressions) as total_impressions FROM social_posts p LEFT JOIN social_analytics a ON p.id = a.post_id WHERE p.user_id = ? AND p.created_at BETWEEN ? AND ? GROUP BY platform");
        $stmt->execute([$this->userId, $startDate, $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Platform-Specific Content Optimization ──

    public function optimizeForPlatform(string $content, string $fromPlatform, string $toPlatform): array {
        require_once CMS_ROOT . '/core/ai_content.php';
        $prompt = "Adapt this {$fromPlatform} post for {$toPlatform}. Keep the core message, adjust length, tone, and format for the target platform.\n\nOriginal:\n{$content}\n\nReturn JSON: {\"content\":\"adapted post\",\"hashtags\":[],\"notes\":\"what was changed\"}";
        $result = ai_content_generate(['topic' => $prompt]);
        if (!$result['ok']) return ['success' => false, 'error' => $result['error'] ?? 'AI failed'];
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $result['content'] ?? '');
        $raw = preg_replace('/\s*```\s*$/', '', $raw);
        $parsed = json_decode(trim($raw), true);
        return ['success' => true, 'adapted' => $parsed ?? ['content' => $raw]];
    }

    // ── Content Queue (auto-scheduler) ──

    public function getQueue(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM social_posts WHERE user_id = ? AND status = 'queued' ORDER BY sort_order ASC, created_at ASC");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function addToQueue(int $postId): bool {
        $maxOrder = $this->pdo->prepare("SELECT MAX(sort_order) FROM social_posts WHERE user_id = ? AND status = 'queued'");
        $maxOrder->execute([$this->userId]);
        $next = (int)$maxOrder->fetchColumn() + 1;
        $stmt = $this->pdo->prepare("UPDATE social_posts SET status = 'queued', sort_order = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$next, $postId, $this->userId]);
        return $stmt->rowCount() > 0;
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
