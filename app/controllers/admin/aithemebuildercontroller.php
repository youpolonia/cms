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

        // Load available AI models — only capable ones for theme generation
        $aiModels = [];
        $settingsPath = \CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($settingsPath)) {
            $settings = @json_decode(file_get_contents($settingsPath), true);
            $defaultProvider = $settings['default_provider'] ?? '';
            // Providers not suitable for theme generation
            $skipProviders = ['huggingface', 'ollama'];
            // Patterns that indicate too-small models (word boundaries with - or space)
            $skipPatterns = ['-mini', ' mini', '-nano', ' nano', 'haiku', '-flash', ' flash'];

            foreach ($settings['providers'] ?? [] as $providerKey => $providerConf) {
                if (empty($providerConf['enabled']) || empty($providerConf['api_key'])) continue;
                if (in_array($providerKey, $skipProviders)) continue;

                foreach ($providerConf['models'] ?? [] as $modelKey => $modelConf) {
                    // Skip legacy models
                    if (!empty($modelConf['legacy'])) continue;
                    // Skip small models by name pattern
                    $keyLower = strtolower($modelKey);
                    $nameLower = strtolower($modelConf['name'] ?? '');
                    $skip = false;
                    foreach ($skipPatterns as $pat) {
                        if (str_contains($keyLower, $pat) || str_contains($nameLower, $pat)) {
                            $skip = true;
                            break;
                        }
                    }
                    if ($skip) continue;

                    // Rate model quality for theme generation
                    // Rate model quality for theme generation based on provider + cost
                    $tier = 'recommended';
                    $tierLabel = '';
                    $cost = (float)($modelConf['cost_per_1k_output'] ?? 0);
                    // Budget: DeepSeek, very cheap models — limited output tokens
                    if ($providerKey === 'deepseek' || $cost < 0.001) {
                        $tier = 'budget';
                        $tierLabel = '⚠️ Lower quality, shorter output';
                    } elseif ($cost >= 0.02) {
                        $tier = 'premium';
                        $tierLabel = '⭐ Best quality';
                    }

                    $aiModels[] = [
                        'provider' => $providerKey,
                        'model' => $modelKey,
                        'name' => ($modelConf['name'] ?? $modelKey),
                        'isDefault' => ($providerKey === $defaultProvider && $modelKey === ($providerConf['default_model'] ?? '')),
                        'tier' => $tier,
                        'tierLabel' => $tierLabel,
                    ];
                }
            }
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
            'aiModels' => $aiModels,
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
        $provider = trim($body['provider'] ?? '');
        $model    = trim($body['model'] ?? '');
        $language = trim($body['language'] ?? 'English');

        if (empty($prompt)) {
            Response::json(['ok' => false, 'error' => 'Please describe your website']);
            return;
        }

        // Validate inputs
        $validIndustries = [
            'restaurant','cafe','bar','hotel','catering',
            'saas','startup','ai','app','crypto',
            'portfolio','photography','agency','music','film',
            'blog','magazine','podcast','news',
            'ecommerce','fashion','jewelry','realestate',
            'law','finance','consulting','accounting','insurance',
            'medical','dental','fitness','yoga','spa','veterinary',
            'education','course','coaching',
            'nonprofit','church','events','travel','architecture',
            'construction','automotive','gaming','sports','wedding',
        ];
        $validStyles = ['minimalist','bold','elegant','playful','corporate','brutalist','retro','futuristic','organic','artdeco','glassmorphism','neubrutalism','editorial','geometric'];
        $validMoods = ['light','dark','colorful','monochrome','warm','cool','pastel','neon','earth','luxury'];

        if (!in_array($industry, $validIndustries)) $industry = 'portfolio';
        if (!in_array($style, $validStyles)) $style = 'minimalist';
        if (!in_array($mood, $validMoods)) $mood = 'light';

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder([
                'provider' => $provider,
                'model'    => $model,
                'language' => $language,
            ]);

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

        // Override get_active_theme() for this request
        // theme-customizer uses this to resolve theme_get() values
        $GLOBALS['_active_theme_override'] = $slug;

        // Variables needed by layout.php and templates
        try {
            $pdo = \core\Database::connection();
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
            $stmt->execute(['site_name']);
            $siteName = $stmt->fetchColumn() ?: 'My Website';
        } catch (\Throwable $e) {
            $siteName = 'My Website';
        }
        $siteLogo = '';
        $tsLogo = theme_get('brand.logo', '');
        $themePath = '/themes/' . $slug;

        // Build minimal page context
        $page = [
            'title' => 'Home',
            'slug' => 'home',
            'content' => '',
            'is_tb_page' => false,
        ];

        // Load demo data — prefer theme-specific content, fallback to generic
        try {
            $pdo = \core\Database::connection();
            
            // Try theme-specific pages first, fallback to untagged
            $stmt = $pdo->prepare("SELECT id, title, slug, content, featured_image FROM pages WHERE status = 'published' AND (theme_slug = ? OR theme_slug IS NULL) ORDER BY theme_slug DESC, id DESC LIMIT 6");
            $stmt->execute([$slug]);
            $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT a.id, a.title, a.slug, a.content, a.excerpt, a.featured_image, a.published_at, a.created_at, a.views, c.name as category_name FROM articles a LEFT JOIN article_categories c ON a.category_id = c.id WHERE a.status = 'published' AND (a.theme_slug = ? OR a.theme_slug IS NULL) ORDER BY a.theme_slug DESC, a.published_at DESC LIMIT 4");
            $stmt->execute([$slug]);
            $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $pages = [];
            $articles = [];
        }

        // Render home template content
        ob_start();
        try {
            require $homeFile;
        } catch (\Throwable $e) {
            echo '<!-- Home template error: ' . htmlspecialchars($e->getMessage()) . ' -->';
        }
        $content = ob_get_clean();

        // Render full layout with the content
        try {
            require $layoutFile;
        } catch (\Throwable $e) {
            echo '<pre>Layout error: ' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</pre>';
        }
        exit;
    }

    /**
     * POST /api/ai-theme-builder/delete — Delete a generated theme
     */
    public function delete(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? [];
        $slug = trim($body['slug'] ?? '');

        if (empty($slug) || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            Response::json(['ok' => false, 'error' => 'Invalid theme slug']);
            return;
        }

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder();
            $result = $builder->deleteTheme($slug);

            Response::json([
                'ok' => $result,
                'error' => $result ? null : 'Cannot delete (active theme or not AI-generated)',
            ]);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
