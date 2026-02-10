<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;

class AiThemeBuilderController
{
    /**
     * GET /admin/ai-theme-builder — Main UI page
     */
    public function index(Request $request): void
    {
        // Check if AI is available
        $aiAvailable = false;
        try {
            $aiCorePath = \CMS_ROOT . '/plugins/jessie-theme-builder/includes/ai/class-jtb-ai-core.php';
            if (file_exists($aiCorePath)) {
                require_once $aiCorePath;
                $ai = \JessieThemeBuilder\JTB_AI_Core::getInstance();
                $aiAvailable = $ai->isConfigured();
            }
        } catch (\Throwable $e) {
            // AI not available
        }

        // List existing AI-generated themes
        $generatedThemes = [];
        $themesDir = \CMS_ROOT . '/themes';
        if (is_dir($themesDir)) {
            foreach (glob($themesDir . '/*/theme.json') as $jsonFile) {
                $data = @json_decode(file_get_contents($jsonFile), true);
                if ($data && ($data['author'] ?? '') === 'AI Theme Builder') {
                    $generatedThemes[] = [
                        'slug' => basename(dirname($jsonFile)),
                        'name' => $data['name'] ?? 'Unnamed',
                        'description' => $data['description'] ?? '',
                    ];
                }
            }
        }

        $data = [
            'title' => 'AI Theme Builder',
            'aiAvailable' => $aiAvailable,
            'generatedThemes' => $generatedThemes,
            'csrfToken' => csrf_token(),
        ];

        extract($data);
        require \CMS_APP . '/views/admin/ai-theme-builder/index.php';
        exit;
    }

    /**
     * POST /api/ai-theme-builder/generate — Run generation pipeline
     */
    public function generate(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? [];
        
        $prompt   = trim($body['prompt'] ?? '');
        $industry = $body['industry'] ?? 'portfolio';
        $style    = $body['style'] ?? 'minimalist';
        $mood     = $body['mood'] ?? 'light';

        if (empty($prompt)) {
            Response::json(['ok' => false, 'error' => 'Please describe your website']);
            return;
        }

        // Validate inputs
        $validIndustries = ['restaurant', 'saas', 'portfolio', 'blog', 'ecommerce', 'agency', 'law', 'medical', 'fitness', 'education'];
        $validStyles = ['minimalist', 'bold', 'elegant', 'playful', 'corporate'];
        $validMoods = ['light', 'dark', 'colorful', 'monochrome'];

        if (!in_array($industry, $validIndustries)) $industry = 'portfolio';
        if (!in_array($style, $validStyles)) $style = 'minimalist';
        if (!in_array($mood, $validMoods)) $mood = 'light';

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder();

            $result = $builder->generate([
                'prompt' => $prompt,
                'industry' => $industry,
                'style' => $style,
                'mood' => $mood,
            ]);

            Response::json($result);
        } catch (\Throwable $e) {
            Response::json([
                'ok' => false,
                'error' => 'Generation failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /api/ai-theme-builder/apply — Activate a generated theme
     */
    public function apply(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? [];
        $slug = trim($body['slug'] ?? '');

        if (empty($slug) || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            Response::json(['ok' => false, 'error' => 'Invalid theme slug']);
            return;
        }

        $themeDir = \CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir) || !file_exists($themeDir . '/theme.json')) {
            Response::json(['ok' => false, 'error' => 'Theme not found: ' . $slug]);
            return;
        }

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder();
            $result = $builder->activateTheme($slug);

            Response::json([
                'ok' => $result,
                'error' => $result ? null : 'Failed to activate theme',
            ]);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * GET /admin/ai-theme-builder/preview — Preview a generated theme
     */
    public function preview(Request $request): void
    {
        $slug = $_GET['theme'] ?? '';
        
        if (empty($slug) || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            echo '<p style="padding:40px;text-align:center;color:#888;">No theme selected for preview.</p>';
            return;
        }

        $themeDir = \CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir)) {
            echo '<p style="padding:40px;text-align:center;color:#888;">Theme not found.</p>';
            return;
        }

        // Temporarily switch theme for this request
        $GLOBALS['_preview_theme_override'] = $slug;

        // Override get_active_theme to return our slug
        // We use a simpler approach: render a standalone preview
        $layoutFile = $themeDir . '/layout.php';
        $homeFile = $themeDir . '/templates/home.php';

        if (!file_exists($layoutFile) || !file_exists($homeFile)) {
            echo '<p style="padding:40px;text-align:center;color:#888;">Theme files missing.</p>';
            return;
        }

        // Build minimal page context
        $page = [
            'title' => 'Home',
            'slug' => 'home',
            'content' => '',
            'is_tb_page' => false,
        ];

        // Load some demo data
        try {
            $pdo = \core\Database::connection();
            
            $stmt = $pdo->query("SELECT id, title, slug, content, featured_image FROM pages WHERE status = 'published' ORDER BY id DESC LIMIT 6");
            $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stmt = $pdo->query("SELECT a.id, a.title, a.slug, a.content, a.excerpt, a.featured_image, a.published_at, a.created_at, a.views, c.name as category_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id WHERE a.status = 'published' ORDER BY a.published_at DESC LIMIT 4");
            $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $pages = [];
            $articles = [];
        }

        // Render home template content
        ob_start();
        require $homeFile;
        $content = ob_get_clean();

        // Render full layout with the content
        require $layoutFile;
        exit;
    }
}
