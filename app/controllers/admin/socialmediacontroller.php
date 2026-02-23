<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Social Media Manager Controller
 * Handles social media post generation, scheduling, and publishing
 */
class SocialMediaController
{
    /**
     * Ensure SocialManager is loaded
     */
    private function boot(): void
    {
        require_once CMS_ROOT . '/core/social_manager.php';
    }

    /**
     * Dashboard — overview of social media activity
     */
    public function dashboard(Request $request): void
    {
        $this->boot();

        $stats = \SocialManager::getStats();
        $accounts = \SocialManager::getAccounts();
        $scheduled = \SocialManager::getPosts(['status' => 'scheduled'], 10);
        $recent = \SocialManager::getPosts(['status' => 'published'], 10);
        $articles = \SocialManager::getArticlesList();

        $title = 'Social Media Manager';
        require_once CMS_APP . '/views/admin/social-media/dashboard.php';
    }

    /**
     * Accounts — manage connected social accounts
     */
    public function accounts(Request $request): void
    {
        $this->boot();

        $accounts = \SocialManager::getAccounts();
        $platforms = ['twitter', 'linkedin', 'facebook', 'instagram'];

        // Build status map
        $accountMap = [];
        foreach ($accounts as $acc) {
            $accountMap[$acc['platform']] = $acc;
        }

        $title = 'Social Accounts';
        require_once CMS_APP . '/views/admin/social-media/accounts.php';
    }

    /**
     * Connect — redirect to OAuth URL for given platform
     */
    public function connect(Request $request): void
    {
        $this->boot();
        require_once CMS_ROOT . '/core/social_adapters/base.php';

        $platform = $request->param('platform', '');
        $allowed = ['twitter', 'linkedin', 'facebook', 'instagram'];

        if (!in_array($platform, $allowed, true)) {
            Response::redirect('/admin/social-media/accounts?error=invalid_platform');
        }

        $adapterFile = CMS_ROOT . '/core/social_adapters/' . $platform . '.php';
        if (!file_exists($adapterFile)) {
            Response::redirect('/admin/social-media/accounts?error=adapter_not_found');
        }
        require_once $adapterFile;

        $classMap = [
            'twitter'   => 'TwitterAdapter',
            'linkedin'  => 'LinkedInAdapter',
            'facebook'  => 'FacebookAdapter',
            'instagram' => 'InstagramAdapter',
        ];

        $className = $classMap[$platform];
        $adapter = new $className();

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $callbackUrl = $scheme . '://' . $host . '/admin/social-media/callback/' . $platform;

        $authUrl = $adapter->getAuthUrl($callbackUrl);
        Response::redirect($authUrl);
    }

    /**
     * Callback — handle OAuth callback from platform
     */
    public function callback(Request $request): void
    {
        $this->boot();
        require_once CMS_ROOT . '/core/social_adapters/base.php';

        $platform = $request->param('platform', '');
        $code = $request->get('code', '');
        $error = $request->get('error', '');

        if ($error !== '') {
            Response::redirect('/admin/social-media/accounts?error=' . urlencode($error));
        }

        if (empty($code)) {
            Response::redirect('/admin/social-media/accounts?error=no_code');
        }

        $adapterFile = CMS_ROOT . '/core/social_adapters/' . $platform . '.php';
        if (!file_exists($adapterFile)) {
            Response::redirect('/admin/social-media/accounts?error=adapter_not_found');
        }
        require_once $adapterFile;

        $classMap = [
            'twitter'   => 'TwitterAdapter',
            'linkedin'  => 'LinkedInAdapter',
            'facebook'  => 'FacebookAdapter',
            'instagram' => 'InstagramAdapter',
        ];

        $className = $classMap[$platform] ?? null;
        if (!$className) {
            Response::redirect('/admin/social-media/accounts?error=invalid_platform');
        }

        $adapter = new $className();

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $callbackUrl = $scheme . '://' . $host . '/admin/social-media/callback/' . $platform;

        $success = $adapter->handleCallback($code, $callbackUrl);

        if ($success) {
            Response::redirect('/admin/social-media/accounts?success=' . urlencode($platform . ' connected'));
        } else {
            Response::redirect('/admin/social-media/accounts?error=' . urlencode($platform . ' connection failed'));
        }
    }

    /**
     * Calendar — visual calendar of scheduled posts
     */
    public function calendar(Request $request): void
    {
        $this->boot();

        $month = (int) $request->get('month', (int) date('n'));
        $year = (int) $request->get('year', (int) date('Y'));

        if ($month < 1 || $month > 12) { $month = (int) date('n'); }
        if ($year < 2020 || $year > 2030) { $year = (int) date('Y'); }

        $db = \core\Database::connection();
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $db->prepare(
            "SELECT * FROM social_posts
             WHERE (scheduled_at BETWEEN ? AND ? OR (published_at BETWEEN ? AND ?))
             ORDER BY COALESCE(scheduled_at, published_at) ASC"
        );
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59', $startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group posts by day
        $postsByDay = [];
        foreach ($posts as $post) {
            $day = date('j', strtotime($post['scheduled_at'] ?? $post['published_at'] ?? $post['created_at']));
            $postsByDay[$day][] = $post;
        }

        $title = 'Social Calendar';
        require_once CMS_APP . '/views/admin/social-media/calendar.php';
    }

    /**
     * Generate — AJAX: generate posts from article_id using AI
     */
    public function generate(Request $request): void
    {
        $this->boot();

        $articleId = (int) $request->post('article_id', 0);

        if ($articleId > 0) {
            $result = \SocialManager::generateFromArticle($articleId);
        } else {
            $title = trim($request->post('title', ''));
            $content = trim($request->post('content', ''));
            $url = trim($request->post('url', ''));

            if ($title === '' || $content === '') {
                Response::json(['ok' => false, 'error' => 'Title and content are required']);
            }

            $result = \SocialManager::generateFromText($title, $content, $url);
        }

        // Save as drafts if generation succeeded
        if ($result['ok'] && !empty($result['posts'])) {
            $ids = \SocialManager::savePosts($result['posts'], $articleId > 0 ? $articleId : null);
            $result['saved_ids'] = $ids;
        }

        Response::json($result);
    }

    /**
     * Schedule — AJAX: save/schedule a post
     */
    public function schedule(Request $request): void
    {
        $this->boot();

        $postId = (int) $request->post('post_id', 0);
        $content = trim($request->post('content', ''));
        $scheduledAt = trim($request->post('scheduled_at', ''));
        $platform = trim($request->post('platform', ''));

        if ($postId > 0) {
            // Update existing post
            $data = [];
            if ($content !== '') {
                $data['content'] = $content;
            }
            if ($scheduledAt !== '') {
                $data['scheduled_at'] = $scheduledAt;
                $data['status'] = 'scheduled';
            }
            $ok = \SocialManager::updatePost($postId, $data);
            Response::json(['ok' => $ok]);
        } else {
            // Create new post
            if ($platform === '' || $content === '') {
                Response::json(['ok' => false, 'error' => 'Platform and content required']);
            }

            $posts = [[
                'platform' => $platform,
                'content' => $content,
                'link_url' => trim($request->post('link_url', '')),
            ]];

            $ids = \SocialManager::savePosts($posts);

            if (!empty($ids) && $scheduledAt !== '') {
                \SocialManager::updatePost($ids[0], [
                    'scheduled_at' => $scheduledAt,
                    'status' => 'scheduled',
                ]);
            }

            Response::json(['ok' => true, 'id' => $ids[0] ?? null]);
        }
    }

    /**
     * Publish — AJAX: publish a post now
     */
    public function publishPost(Request $request): void
    {
        $this->boot();

        $postId = (int) $request->param('id', 0);
        if ($postId <= 0) {
            Response::json(['ok' => false, 'error' => 'Invalid post ID']);
        }

        $ok = \SocialManager::publish($postId);
        $post = \SocialManager::getPost($postId);

        Response::json([
            'ok' => $ok,
            'status' => $post['status'] ?? 'unknown',
            'error' => $post['error_message'] ?? null,
        ]);
    }

    /**
     * Delete — AJAX: delete a post
     */
    public function delete(Request $request): void
    {
        $this->boot();

        $postId = (int) $request->param('id', 0);
        if ($postId <= 0) {
            Response::json(['ok' => false, 'error' => 'Invalid post ID']);
        }

        $ok = \SocialManager::deletePost($postId);
        Response::json(['ok' => $ok]);
    }

    /**
     * Settings — auto-post configuration
     */
    public function settings(Request $request): void
    {
        $this->boot();

        $configPath = CMS_ROOT . '/config/social_settings.json';

        if ($request->isPost()) {
            $settings = [
                'auto_post' => [
                    'enabled' => (bool) $request->post('auto_post_enabled', 0),
                    'platforms' => array_filter([
                        'twitter' => (bool) $request->post('auto_twitter', 0),
                        'linkedin' => (bool) $request->post('auto_linkedin', 0),
                        'facebook' => (bool) $request->post('auto_facebook', 0),
                        'instagram' => (bool) $request->post('auto_instagram', 0),
                    ]),
                    'delay_minutes' => max(0, (int) $request->post('delay_minutes', 0)),
                ],
                'default_hashtags' => trim($request->post('default_hashtags', '')),
                'url_prefix' => trim($request->post('url_prefix', '')),
            ];

            file_put_contents($configPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Response::json(['ok' => true, 'message' => 'Settings saved']);
        }

        $settings = [];
        if (file_exists($configPath)) {
            $settings = json_decode(file_get_contents($configPath), true) ?: [];
        }

        Response::json(['ok' => true, 'settings' => $settings]);
    }
}
