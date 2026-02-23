<?php
declare(strict_types=1);

/**
 * Social Media Manager
 * AI-powered social post generation and publishing for Jessie AI-CMS
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/ai_content.php';

class SocialManager
{
    /**
     * Platform character limits
     */
    private const LIMITS = [
        'twitter'   => 280,
        'linkedin'  => 3000,
        'facebook'  => 63206,
        'instagram' => 2200,
    ];

    /**
     * Generate social posts for all platforms from an article
     */
    public static function generateFromArticle(int $articleId): array
    {
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT id, title, slug, excerpt, content FROM articles WHERE id = ? LIMIT 1");
        $stmt->execute([$articleId]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$article) {
            return ['ok' => false, 'error' => 'Article not found', 'posts' => []];
        }

        $title   = $article['title'] ?? '';
        $excerpt = $article['excerpt'] ?? '';
        $slug    = $article['slug'] ?? '';
        $body    = $article['content'] ?? '';

        $plainBody = strip_tags($body);
        if (mb_strlen($plainBody) > 1500) {
            $plainBody = mb_substr($plainBody, 0, 1500) . '...';
        }

        $url = '/article/' . $slug;
        $contentForAI = $excerpt !== '' ? $excerpt : $plainBody;

        return self::generateFromText($title, $contentForAI, $url);
    }

    /**
     * Generate social posts from custom text using AI
     */
    public static function generateFromText(string $title, string $content, string $url): array
    {
        $systemPrompt = "You are a social media marketing expert. Generate engaging posts for 4 platforms from the provided article.\n\nRules:\n- Twitter: Max 280 chars. Punchy, use 1-2 relevant hashtags. Include space for a link.\n- LinkedIn: Professional tone, 1-3 paragraphs, 2-3 hashtags. Thought-leadership style.\n- Facebook: Conversational, engaging, ask a question or share insight. 1-2 paragraphs.\n- Instagram: Visual/inspiring caption, use 5-10 relevant hashtags at the end.\n\nReturn ONLY valid JSON in this exact format (no markdown, no code fences):\n{\n  \"twitter\": \"tweet text here\",\n  \"linkedin\": \"linkedin post here\",\n  \"facebook\": \"facebook post here\",\n  \"instagram\": \"instagram caption here\"\n}";

        $userPrompt = "Title: {$title}\n\nContent: {$content}\n\nURL: {$url}";

        $settings = ai_config_load_full();
        $provider = $settings['default_provider'] ?? 'openai';
        $providerConfig = $settings['providers'][$provider] ?? [];
        $model = $providerConfig['default_model'] ?? 'gpt-4o-mini';

        if (empty($providerConfig['enabled']) || empty($providerConfig['api_key'])) {
            foreach ($settings['providers'] ?? [] as $pName => $pConfig) {
                if (!empty($pConfig['enabled']) && !empty($pConfig['api_key'])) {
                    $provider = $pName;
                    $model = $pConfig['default_model'] ?? 'gpt-4o-mini';
                    break;
                }
            }
        }

        $result = ai_universal_generate($provider, $model, $systemPrompt, $userPrompt, [
            'max_tokens' => 2000,
            'temperature' => 0.8,
        ]);

        if (!$result['ok']) {
            return self::generateFallbackPosts($title, $content, $url);
        }

        $raw = $result['content'] ?? '';
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/i', '', $raw);

        $parsed = json_decode(trim($raw), true);

        if (!is_array($parsed)) {
            if (preg_match('/\{.*"twitter".*\}/s', $raw, $m)) {
                $parsed = json_decode($m[0], true);
            }
        }

        if (!is_array($parsed)) {
            return self::generateFallbackPosts($title, $content, $url);
        }

        $posts = [];
        foreach (['twitter', 'linkedin', 'facebook', 'instagram'] as $platform) {
            $text = $parsed[$platform] ?? '';
            if ($text !== '') {
                $limit = self::LIMITS[$platform];
                if (mb_strlen($text) > $limit) {
                    $text = mb_substr($text, 0, $limit - 1) . '...';
                }
            }
            $posts[] = [
                'platform' => $platform,
                'content'  => $text,
                'link_url' => $url,
            ];
        }

        return ['ok' => true, 'posts' => $posts, 'error' => null];
    }

    /**
     * Fallback template-based post generation (when AI is unavailable)
     */
    private static function generateFallbackPosts(string $title, string $content, string $url): array
    {
        $shortContent = mb_strlen($content) > 150 ? mb_substr($content, 0, 147) . '...' : $content;
        $shortTitle   = mb_strlen($title) > 100 ? mb_substr($title, 0, 97) . '...' : $title;

        $posts = [
            [
                'platform' => 'twitter',
                'content'  => mb_strlen($shortTitle) > 200
                    ? mb_substr($shortTitle, 0, 200) . '... ' . $url
                    : $shortTitle . ' ' . $url,
                'link_url' => $url,
            ],
            [
                'platform' => 'linkedin',
                'content'  => $title . "\n\n" . $shortContent . "\n\nRead more: " . $url,
                'link_url' => $url,
            ],
            [
                'platform' => 'facebook',
                'content'  => $title . "\n\n" . $shortContent . "\n\n" . $url,
                'link_url' => $url,
            ],
            [
                'platform' => 'instagram',
                'content'  => $title . "\n\n" . $shortContent . "\n\n#content #article #blog",
                'link_url' => $url,
            ],
        ];

        return ['ok' => true, 'posts' => $posts, 'error' => 'AI unavailable, using templates'];
    }

    /**
     * Save generated posts to the social_posts table
     */
    public static function savePosts(array $posts, ?int $articleId = null, ?int $pageId = null): array
    {
        $db = \core\Database::connection();
        $ids = [];

        $stmt = $db->prepare(
            "INSERT INTO social_posts (article_id, page_id, platform, content, link_url, status, created_at)
             VALUES (?, ?, ?, ?, ?, 'draft', NOW())"
        );

        foreach ($posts as $post) {
            $platform = $post['platform'] ?? '';
            $content  = $post['content'] ?? '';
            $linkUrl  = $post['link_url'] ?? '';

            if ($platform === '' || $content === '') {
                continue;
            }

            $stmt->execute([$articleId, $pageId, $platform, $content, $linkUrl]);
            $ids[] = (int) $db->lastInsertId();
        }

        return $ids;
    }

    /**
     * Get scheduled posts that are due for publishing
     */
    public static function getDuePosts(): array
    {
        $db = \core\Database::connection();
        $stmt = $db->prepare(
            "SELECT sp.*, sa.account_name, sa.access_token
             FROM social_posts sp
             LEFT JOIN social_accounts sa ON sa.platform = sp.platform AND sa.active = 1
             WHERE sp.status = 'scheduled'
               AND sp.scheduled_at <= NOW()
             ORDER BY sp.scheduled_at ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Publish a single post (delegates to the platform adapter)
     */
    public static function publish(int $postId): bool
    {
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM social_posts WHERE id = ? LIMIT 1");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$post) {
            return false;
        }

        $adapter = self::getAdapter($post['platform']);
        if ($adapter === null) {
            $db->prepare("UPDATE social_posts SET status = 'failed', error_message = ? WHERE id = ?")
               ->execute(['No adapter configured for platform: ' . $post['platform'], $postId]);
            return false;
        }

        if (!$adapter->isConnected()) {
            $db->prepare("UPDATE social_posts SET status = 'failed', error_message = ? WHERE id = ?")
               ->execute(['Platform not connected: ' . $post['platform'], $postId]);
            return false;
        }

        $result = $adapter->publish(
            $post['content'],
            $post['media_url'] ?? null,
            $post['link_url'] ?? null
        );

        if (!empty($result['ok'])) {
            $db->prepare(
                "UPDATE social_posts SET status = 'published', published_at = NOW(), external_id = ?, error_message = NULL WHERE id = ?"
            )->execute([$result['external_id'] ?? null, $postId]);
            return true;
        }

        $db->prepare(
            "UPDATE social_posts SET status = 'failed', error_message = ? WHERE id = ?"
        )->execute([$result['error'] ?? 'Unknown error', $postId]);

        return false;
    }

    /**
     * Get platform adapter instance
     */
    private static function getAdapter(string $platform): ?SocialAdapter
    {
        $adapterDir = __DIR__ . '/social_adapters';

        $baseFile = $adapterDir . '/base.php';
        if (!file_exists($baseFile)) {
            return null;
        }
        require_once $baseFile;

        $adapterFile = $adapterDir . '/' . $platform . '.php';
        if (!file_exists($adapterFile)) {
            return null;
        }
        require_once $adapterFile;

        $classMap = [
            'twitter'   => 'TwitterAdapter',
            'linkedin'  => 'LinkedInAdapter',
            'facebook'  => 'FacebookAdapter',
            'instagram' => 'InstagramAdapter',
        ];

        $className = $classMap[$platform] ?? null;
        if ($className === null || !class_exists($className)) {
            return null;
        }

        return new $className();
    }

    /**
     * Get a single post by ID
     */
    public static function getPost(int $postId): ?array
    {
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM social_posts WHERE id = ? LIMIT 1");
        $stmt->execute([$postId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Update post content/schedule
     */
    public static function updatePost(int $postId, array $data): bool
    {
        $db = \core\Database::connection();
        $fields = [];
        $params = [];

        if (array_key_exists('content', $data)) {
            $fields[] = 'content = ?';
            $params[] = $data['content'];
        }
        if (array_key_exists('scheduled_at', $data)) {
            $fields[] = 'scheduled_at = ?';
            $params[] = $data['scheduled_at'];
        }
        if (array_key_exists('status', $data)) {
            $fields[] = 'status = ?';
            $params[] = $data['status'];
        }
        if (array_key_exists('media_url', $data)) {
            $fields[] = 'media_url = ?';
            $params[] = $data['media_url'];
        }
        if (array_key_exists('link_url', $data)) {
            $fields[] = 'link_url = ?';
            $params[] = $data['link_url'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $postId;
        $sql = "UPDATE social_posts SET " . implode(', ', $fields) . " WHERE id = ?";
        return $db->prepare($sql)->execute($params);
    }

    /**
     * Delete a post
     */
    public static function deletePost(int $postId): bool
    {
        $db = \core\Database::connection();
        return $db->prepare("DELETE FROM social_posts WHERE id = ?")->execute([$postId]);
    }

    /**
     * Get posts with optional filters
     */
    public static function getPosts(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $db = \core\Database::connection();
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['platform'])) {
            $where[] = 'platform = ?';
            $params[] = $filters['platform'];
        }
        if (!empty($filters['article_id'])) {
            $where[] = 'article_id = ?';
            $params[] = (int)$filters['article_id'];
        }

        $sql = "SELECT * FROM social_posts";
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get connected social accounts
     */
    public static function getAccounts(): array
    {
        $db = \core\Database::connection();
        $stmt = $db->query("SELECT * FROM social_accounts ORDER BY platform ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get stats for the dashboard
     */
    public static function getStats(): array
    {
        $db = \core\Database::connection();

        $total = (int) $db->query("SELECT COUNT(*) FROM social_posts")->fetchColumn();
        $published = (int) $db->query("SELECT COUNT(*) FROM social_posts WHERE status = 'published'")->fetchColumn();
        $scheduled = (int) $db->query("SELECT COUNT(*) FROM social_posts WHERE status = 'scheduled'")->fetchColumn();
        $failed = (int) $db->query("SELECT COUNT(*) FROM social_posts WHERE status = 'failed'")->fetchColumn();
        $thisWeek = (int) $db->query("SELECT COUNT(*) FROM social_posts WHERE status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();

        return [
            'total'     => $total,
            'published' => $published,
            'scheduled' => $scheduled,
            'failed'    => $failed,
            'this_week' => $thisWeek,
        ];
    }

    /**
     * Get all articles for the article selector
     */
    public static function getArticlesList(): array
    {
        $db = \core\Database::connection();
        $stmt = $db->query("SELECT id, title, slug FROM articles ORDER BY created_at DESC LIMIT 100");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
