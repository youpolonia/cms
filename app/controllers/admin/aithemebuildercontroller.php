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
        // Redirect to the wizard — the old index UI is deprecated
        header('Location: /admin/ai-theme-builder/wizard');
        exit;
    }

    /**
     * GET /admin/ai-theme-builder/wizard — Multi-step wizard UI
     */
    public function wizard(Request $request): void
    {
        $aiAvailable = false;
        $aiModels = [];
        $stylePresets = [];
        $availableSections = [];
        $contentTones = [];

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $aiAvailable = true;
        } catch (\Throwable $e) {}

        // Static methods — don't need constructor
        if ($aiAvailable) {
            $stylePresets = \AiThemeBuilder::getStylePresets();
            $availableSections = \AiThemeBuilder::getAvailableSections();
            $contentTones = \AiThemeBuilder::getContentTones();
        }

        // Load AI models from config (same logic as index())
        $settingsPath = \CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($settingsPath)) {
            $settings = @json_decode(file_get_contents($settingsPath), true);
            $defaultProvider = $settings['default_provider'] ?? '';
            $skipProviders = ['huggingface', 'ollama'];

            // Skip patterns per provider — Google Flash is a frontier model, don't skip it
            $defaultSkipPatterns = ['-mini', ' mini', '-nano', ' nano', 'haiku'];
            $providerSkipPatterns = [
                'openai'    => ['-mini', ' mini', '-nano', ' nano'],
                'anthropic' => ['haiku'],
                'google'    => ['-nano', ' nano', '-lite', ' lite'],  // Flash is fine, only skip nano/lite
                'deepseek'  => [],  // DeepSeek models are all budget — let them through
            ];

            foreach ($settings['providers'] ?? [] as $providerKey => $providerConf) {
                if (empty($providerConf['enabled']) || empty($providerConf['api_key'])) continue;
                if (in_array($providerKey, $skipProviders)) continue;

                $skipPatterns = $providerSkipPatterns[$providerKey] ?? $defaultSkipPatterns;

                foreach ($providerConf['models'] ?? [] as $modelKey => $modelConf) {
                    if (!empty($modelConf['legacy'])) continue;
                    $keyLower = strtolower($modelKey);
                    $nameLower = strtolower($modelConf['name'] ?? '');
                    $skip = false;
                    foreach ($skipPatterns as $pat) {
                        if (str_contains($keyLower, $pat) || str_contains($nameLower, $pat)) { $skip = true; break; }
                    }
                    if ($skip) continue;

                    $tier = 'recommended';
                    $cost = (float)($modelConf['cost_per_1k_output'] ?? 0);
                    if ($providerKey === 'deepseek' || $cost < 0.001) $tier = 'budget';
                    elseif ($cost >= 0.02) $tier = 'premium';

                    $aiModels[] = [
                        'provider' => $providerKey,
                        'model' => $modelKey,
                        'name' => ($modelConf['name'] ?? $modelKey),
                        'isDefault' => ($providerKey === $defaultProvider && $modelKey === ($providerConf['default_model'] ?? '')),
                        'tier' => $tier,
                    ];
                }
            }
        }

        $username = \Core\Session::getAdminUsername() ?? 'Admin';

        require \CMS_ROOT . '/app/views/admin/ai-theme-builder/wizard.php';
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
            'restaurant','cafe','bar','bakery','foodtruck','catering','hotel','resort','winery',
            'saas','startup','ai','app','crypto','cybersecurity','devtools','hosting','itsupport','gamedev',
            'portfolio','design','photography','videography','animation','agency','marketing',
            'music','film','art','architecture','interior','tattoo',
            'blog','personal','magazine','news','podcast','newsletter','author','influencer',
            'ecommerce','fashion','jewelry','beauty','furniture','electronics','bookshop','grocery','pets','florist','marketplace',
            'law','finance','consulting','accounting','insurance','recruiting','translation',
            'realestate','propertymanagement',
            'medical','dental','fitness','yoga','spa','veterinary','therapy','mentalhealth','nutrition','physiotherapy','pharmacy',
            'education','onlinecourse','coaching','tutoring','language','driving','childcare','library','training',
            'construction','plumbing','electrical','hvac','roofing','painting','landscaping','cleaning','moving','handyman','solar',
            'automotive','mechanic','carwash','taxi','trucking','motorcycle','boating',
            'events','wedding','party','venue','theater','cinema','escape','festival',
            'travel','tourism','camping','skiing','diving','golf','marina',
            'nonprofit','church','volunteer','political','community','association',
            'government','police','military','embassy',
            'resume','wiki','directory','landing','comingsoon','memorial','other',
            'sports','saas-landing',
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
        // Suppress warnings in preview — AI-generated PHP may have undefined keys
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);
        $slug = $_GET['theme'] ?? '';
        
        if (empty($slug) || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            echo '<p style="padding:40px;text-align:center;color:#888;">No theme selected for preview.</p>';
            return;
        }

        $themeDir = \CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir)) {
            echo '<p style="padding:40px;text-align:center;color:#888;">Theme not found: <code>' . htmlspecialchars($slug) . '</code></p>';
            echo '<p style="text-align:center;font-size:12px;color:#666;">Available: ' . implode(', ', array_map('basename', glob(\CMS_ROOT . '/themes/*', GLOB_ONLYDIR))) . '</p>';
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

        // Determine which page to preview (home or sub-page)
        $previewPage = trim($_GET['page'] ?? 'home');
        if (!preg_match('/^[a-z0-9-]+$/', $previewPage)) $previewPage = 'home';

        // Build page context — load real content from DB for this specific page
        $page = [
            'title' => ucfirst(str_replace('-', ' ', $previewPage)),
            'slug' => $previewPage,
            'content' => '',
            'is_tb_page' => false,
        ];

        // Load specific page content from DB
        if ($previewPage !== 'home') {
            try {
                $pdo = \core\Database::connection();
                $pageSlug = $slug . '-' . $previewPage;
                $stmt = $pdo->prepare("SELECT title, slug, content, featured_image FROM pages WHERE slug = ? AND theme_slug = ? AND status = 'published' LIMIT 1");
                $stmt->execute([$pageSlug, $slug]);
                $dbPage = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($dbPage) {
                    $page['title'] = $dbPage['title'];
                    $page['content'] = $dbPage['content'] ?? '';
                    $page['featured_image'] = $dbPage['featured_image'] ?? '';
                    // Rich content (starts with <style or <section or <!--rich-->) = full-width
                    $trimmed = ltrim($page['content']);
                    if (str_starts_with($trimmed, '<style') || str_starts_with($trimmed, '<section') || str_starts_with($trimmed, '<!--rich-->')) {
                        $page['is_tb_page'] = true;
                    }
                }
            } catch (\Throwable $e) {
                // Fallback — empty content
            }
        }

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

        // Choose template file based on page parameter
        if ($previewPage !== 'home') {
            $templateFile = $themeDir . '/templates/' . $previewPage . '.php';
            if (!file_exists($templateFile)) {
                // Fallback to generic page template
                $templateFile = $themeDir . '/templates/page.php';
            }
        } else {
            $templateFile = $homeFile;
        }

        // Render template content
        ob_start();
        try {
            require $templateFile;
        } catch (\Throwable $e) {
            echo '<!-- Template error: ' . htmlspecialchars($e->getMessage()) . ' -->';
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
     * POST /api/ai-theme-builder/generate-stream — SSE streaming generation
     */
    public function generateStream(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);
        $body = $GLOBALS['_JSON_DATA'] ?? [];
        $prompt   = trim($body['prompt'] ?? '');
        $industry = $body['industry'] ?? 'portfolio';
        $style    = $body['style'] ?? 'minimalist';
        $mood     = $body['mood'] ?? 'light';
        $provider = trim($body['provider'] ?? '');
        $model    = trim($body['model'] ?? '');
        $language = trim($body['language'] ?? 'English');

        // SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        // Disable output buffering
        while (ob_get_level()) ob_end_flush();

        $sendSSE = function(string $event, array $data) {
            echo "event: {$event}\ndata: " . json_encode($data) . "\n\n";
            flush();
        };

        if (empty($prompt)) {
            $sendSSE('error', ['error' => 'Please describe your website']);
            exit;
        }

        // Validate inputs
        $validIndustries = [
            'restaurant','cafe','bar','bakery','foodtruck','catering','hotel','resort','winery',
            'saas','startup','ai','app','crypto','cybersecurity','devtools','hosting','itsupport','gamedev',
            'portfolio','design','photography','videography','animation','agency','marketing',
            'music','film','art','architecture','interior','tattoo',
            'blog','personal','magazine','news','podcast','newsletter','author','influencer',
            'ecommerce','fashion','jewelry','beauty','furniture','electronics','bookshop','grocery','pets','florist','marketplace',
            'law','finance','consulting','accounting','insurance','recruiting','translation',
            'realestate','propertymanagement',
            'medical','dental','fitness','yoga','spa','veterinary','therapy','mentalhealth','nutrition','physiotherapy','pharmacy',
            'education','onlinecourse','coaching','tutoring','language','driving','childcare','library','training',
            'construction','plumbing','electrical','hvac','roofing','painting','landscaping','cleaning','moving','handyman','solar',
            'automotive','mechanic','carwash','taxi','trucking','motorcycle','boating',
            'events','wedding','party','venue','theater','cinema','escape','festival',
            'travel','tourism','camping','skiing','diving','golf','marina',
            'nonprofit','church','volunteer','political','community','association',
            'government','police','military','embassy',
            'resume','wiki','directory','landing','comingsoon','memorial','other',
            'sports','saas-landing',
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

            // Set progress callback for SSE
            $builder->setProgressCallback(function(string $event, array $data) use ($sendSSE) {
                $sendSSE($event, $data);
            });

            $result = $builder->generate([
                'prompt' => $prompt,
                'industry' => $industry,
                'style' => $style,
                'mood' => $mood,
            ]);

            $sendSSE('complete', $result);
        } catch (\Throwable $e) {
            $sendSSE('error', ['error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * POST /api/ai-theme-builder/regenerate-css — Regenerate CSS only
     */
    public function regenerateCss(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? [];
        $slug = trim($body['slug'] ?? '');
        $instructions = trim($body['instructions'] ?? '');
        $provider = trim($body['provider'] ?? '');
        $model = trim($body['model'] ?? '');

        if (empty($slug) || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            Response::json(['ok' => false, 'error' => 'Theme slug required']);
            return;
        }

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder([
                'provider' => $provider,
                'model' => $model,
            ]);
            $result = $builder->regenerateCss($slug, $instructions);
            Response::json($result);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/ai-theme-builder/update-brief — Update colors/fonts + regen CSS
     */
    public function updateBrief(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? [];
        $slug = trim($body['slug'] ?? '');
        $changes = $body['changes'] ?? [];
        $provider = trim($body['provider'] ?? '');
        $model = trim($body['model'] ?? '');

        if (empty($slug) || !preg_match('/^[a-z0-9-]+$/', $slug) || empty($changes)) {
            Response::json(['ok' => false, 'error' => 'Theme slug and changes required']);
            return;
        }

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder([
                'provider' => $provider,
                'model' => $model,
            ]);
            $result = $builder->updateBrief($slug, $changes);
            Response::json($result);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/ai-theme-builder/refine — AI-driven refinement via chat
     */
    public function refine(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? [];
        $slug = trim($body['slug'] ?? '');
        $instruction = trim($body['instruction'] ?? '');
        $provider = trim($body['provider'] ?? '');
        $model = trim($body['model'] ?? '');

        if (empty($slug) || !preg_match('/^[a-z0-9-]+$/', $slug) || empty($instruction)) {
            Response::json(['ok' => false, 'error' => 'Theme slug and instruction required']);
            return;
        }

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder([
                'provider' => $provider,
                'model' => $model,
            ]);
            $result = $builder->refine($slug, $instruction);
            Response::json($result);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/ai-theme-builder/export — Download theme as .zip
     */
    public function export(Request $request): void
    {
        $slug = trim($_GET['theme'] ?? '');
        if (empty($slug) || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            http_response_code(400);
            echo 'Invalid theme slug';
            return;
        }

        $themeDir = \CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir)) {
            http_response_code(404);
            echo 'Theme not found';
            return;
        }

        $zipPath = sys_get_temp_dir() . '/theme-' . $slug . '-' . time() . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            http_response_code(500);
            echo 'Failed to create zip';
            return;
        }

        $this->addDirToZip($zip, $themeDir, $slug);
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $slug . '.zip"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        @unlink($zipPath);
        exit;
    }

    private function addDirToZip(\ZipArchive $zip, string $dir, string $base): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file) {
            $relativePath = $base . '/' . substr($file->getPathname(), strlen($dir) + 1);
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($file->getPathname(), $relativePath);
            }
        }
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

    /* ═══════════════════════════════════════════════════════
       WIZARD API — Multi-step generation endpoints
       ═══════════════════════════════════════════════════════ */

    /**
     * POST /api/ai-theme-builder/wizard/layout-stream — SSE streaming layout generation
     * Uses brief from wizard Step 1 (not generate's own brief)
     */
    public function wizardLayoutStream(Request $request): void
    {
        // Suppress PHP warnings in SSE stream — they corrupt the event format
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);

        $body = $GLOBALS['_JSON_DATA'] ?? [];

        // SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        while (ob_get_level()) ob_end_flush();

        $sendSSE = function(string $event, array $data) {
            echo "event: {$event}\ndata: " . json_encode($data) . "\n\n";
            flush();
        };

        $brief = $body['brief'] ?? null;
        if (!$brief) {
            $sendSSE('error', ['error' => 'Brief is required. Generate a brief first (Step 1).']);
            exit;
        }

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder([
                'provider'   => $body['provider'] ?? '',
                'model'      => $body['model'] ?? '',
                'language'   => $body['language'] ?? 'English',
                'creativity' => $body['creativity'] ?? 'medium',
            ]);

            // Set progress callback for SSE
            $builder->setProgressCallback(function(string $event, array $data) use ($sendSSE) {
                $sendSSE($event, $data);
            });

            $result = $builder->generateLayoutOnly($body);
            $sendSSE('complete', $result);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $errorInfo = null;
            if (str_contains($msg, '|||AI_ERROR|||')) {
                [$msg, $errorJson] = explode('|||AI_ERROR|||', $msg, 2);
                $errorInfo = @json_decode($errorJson, true);
                $msg = trim($msg);
            }
            if (!$errorInfo && isset($builder)) {
                $errorInfo = $builder->getAIErrorInfo();
            }
            $errData = ['error' => $msg];
            if ($errorInfo) $errData['error_info'] = $errorInfo;
            $sendSSE('error', $errData);
        }
        exit;
    }

    /**
     * POST /api/ai-theme-builder/wizard/brief — Step 1: Generate brief only
     */

    /**
     * GET /api/ai-theme-builder/check-providers — Check health of all AI providers
     */
    public function checkProviders(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);

        try {
            $settingsPath = \CMS_ROOT . '/config/ai_settings.json';
            $settings = file_exists($settingsPath) ? @json_decode(file_get_contents($settingsPath), true) : [];
            $results = [];
            $providerNames = ['openai' => 'OpenAI', 'anthropic' => 'Anthropic', 'deepseek' => 'DeepSeek', 'google' => 'Google'];

            foreach (['openai', 'anthropic', 'deepseek', 'google'] as $provider) {
                $config = $settings['providers'][$provider] ?? [];
                if (empty($config['enabled']) || empty($config['api_key'])) {
                    $results[$provider] = ['status' => 'not_configured', 'message' => 'Not configured', 'icon' => '⚙️'];
                    continue;
                }

                // Quick connectivity check per provider
                try {
                    $result = $this->probeProviderDirect($provider, $config);
                    $results[$provider] = $result;
                } catch (\Throwable $e) {
                    $results[$provider] = ['status' => 'error', 'message' => $e->getMessage(), 'icon' => '❌'];
                }
            }

            Response::json(['ok' => true, 'providers' => $results]);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Direct provider probe — no JTB dependency
     */
    private function probeProviderDirect(string $provider, array $config): array
    {
        $apiKey = $config['api_key'] ?? '';
        $timeout = 10;

        switch ($provider) {
            case 'anthropic':
                $ch = curl_init('https://api.anthropic.com/v1/messages');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode(['model' => 'claude-sonnet-4-20250514', 'max_tokens' => 5, 'messages' => [['role' => 'user', 'content' => 'Hi']]]),
                    CURLOPT_HTTPHEADER => ['x-api-key: ' . $apiKey, 'anthropic-version: 2023-06-01', 'Content-Type: application/json'],
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 5,
                ]);
                break;

            case 'openai':
                $ch = curl_init('https://api.openai.com/v1/models');
                curl_setopt_array($ch, [
                    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey],
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 5,
                ]);
                break;

            case 'deepseek':
                $baseUrl = rtrim($config['base_url'] ?? 'https://api.deepseek.com', '/');
                $ch = curl_init($baseUrl . '/user/balance');
                curl_setopt_array($ch, [
                    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey],
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 5,
                ]);
                break;

            case 'google':
                $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 5,
                ]);
                break;

            default:
                return ['status' => 'unknown', 'message' => 'Unknown provider', 'icon' => '❓'];
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['status' => 'error', 'message' => 'Connection failed: ' . $curlError, 'icon' => '❌'];
        }

        // Parse response
        $data = @json_decode($response, true) ?: [];

        if ($httpCode === 200) {
            // Special: DeepSeek balance
            if ($provider === 'deepseek' && !empty($data['balance_infos'])) {
                foreach ($data['balance_infos'] as $bi) {
                    if (($bi['currency'] ?? '') === 'USD') {
                        $bal = (float)($bi['total_balance'] ?? 0);
                        $icon = $bal < 0.10 ? '🔴' : ($bal < 1.00 ? '🟡' : '🟢');
                        $st = $bal < 0.10 ? 'low_balance' : ($bal < 1.00 ? 'warning' : 'ok');
                        return ['status' => $st, 'balance' => $bal, 'message' => "Balance: \${$bal}", 'icon' => $icon];
                    }
                }
            }
            return ['status' => 'ok', 'message' => 'Connected', 'icon' => '🟢'];
        }

        $errorMsg = $data['error']['message'] ?? ($data['error'] ?? '');
        if (is_array($errorMsg)) $errorMsg = json_encode($errorMsg);
        $errorLower = strtolower((string)$errorMsg);

        if ($httpCode === 401 || $httpCode === 403 || str_contains($errorLower, 'invalid') || str_contains($errorLower, 'authentication')) {
            return ['status' => 'auth_error', 'message' => 'Invalid API key', 'icon' => '🔑'];
        }
        if ($httpCode === 402 || str_contains($errorLower, 'credit') || str_contains($errorLower, 'billing') || str_contains($errorLower, 'insufficient')) {
            return ['status' => 'no_credits', 'message' => 'No credits — top up required', 'icon' => '🔴'];
        }
        if ($httpCode === 429 || str_contains($errorLower, 'rate')) {
            return ['status' => 'ok', 'message' => 'Rate limited but working', 'icon' => '🟡'];
        }
        if (str_contains($errorLower, 'not_found') || str_contains($errorLower, 'model')) {
            return ['status' => 'ok', 'message' => 'Connected (model check needed)', 'icon' => '🟢'];
        }

        return ['status' => 'error', 'message' => $errorMsg ?: "HTTP {$httpCode}", 'icon' => '❌'];
    }

    public function wizardBrief(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);
        $data = $GLOBALS['_JSON_DATA'] ?? [];

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder([
                'provider'   => $data['provider'] ?? '',
                'model'      => $data['model'] ?? '',
                'language'   => $data['language'] ?? 'English',
                'creativity' => $data['creativity'] ?? 'medium',
            ]);

            $result = $builder->generateBriefOnly($data);
            Response::json($result);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/ai-theme-builder/wizard/layout — Step 3: Generate layout (header+footer+home+CSS)
     */
    public function wizardLayout(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);
        $data = $GLOBALS['_JSON_DATA'] ?? [];

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder([
                'provider'   => $data['provider'] ?? '',
                'model'      => $data['model'] ?? '',
                'language'   => $data['language'] ?? 'English',
                'creativity' => $data['creativity'] ?? 'medium',
            ]);

            $result = $builder->generateLayoutOnly($data);
            Response::json($result);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/ai-theme-builder/wizard/page — Step 4: Generate single sub-page
     */
    public function wizardPage(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);
        $data = $GLOBALS['_JSON_DATA'] ?? [];

        // If raw_content is provided, save directly without AI generation (Step 5 editor)
        if (!empty($data['raw_content'])) {
            $themeSlug = trim($data['slug'] ?? '');
            $pageType = trim($data['page_type'] ?? '');
            if (empty($themeSlug) || empty($pageType)) {
                Response::json(['ok' => false, 'error' => 'slug and page_type required']);
                return;
            }
            try {
                $db = \core\Database::connection();
                // Page slug in DB is "{theme_slug}-{page_type}", column is "theme_slug"
                $pageSlug = $themeSlug . '-' . $pageType;
                $stmt = $db->prepare("UPDATE pages SET content = ?, updated_at = NOW() WHERE slug = ? AND theme_slug = ?");
                $stmt->execute([$data['raw_content'], $pageSlug, $themeSlug]);
                if ($stmt->rowCount() === 0) {
                    // Fallback: try just page_type as slug
                    $stmt->execute([$data['raw_content'], $pageType, $themeSlug]);
                }
                Response::json(['ok' => true, 'saved' => true]);
            } catch (\Throwable $e) {
                Response::json(['ok' => false, 'error' => 'Save failed: ' . $e->getMessage()]);
            }
            return;
        }

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder([
                'provider'   => $data['provider'] ?? '',
                'model'      => $data['model'] ?? '',
                'language'   => $data['language'] ?? 'English',
                'creativity' => $data['creativity'] ?? 'medium',
            ]);

            $result = $builder->generateSubPage($data);
            Response::json($result);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/ai-theme-builder/wizard/finalize — Step 5: Seed content & finalize
     */
    public function wizardFinalize(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);
        $data = $GLOBALS['_JSON_DATA'] ?? [];

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder([
                'provider'   => $data['provider'] ?? '',
                'model'      => $data['model'] ?? '',
                'language'   => $data['language'] ?? 'English',
                'creativity' => $data['creativity'] ?? 'medium',
            ]);

            $result = $builder->finalizeTheme($data);
            Response::json($result);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/ai-theme-builder/wizard/upload-images — Upload user images
     */
    public function wizardUploadImages(Request $request): void
    {
        // CSRF validated by MVC middleware (X-CSRF-TOKEN header)

        $uploaded = [];
        $mediaDir = \CMS_ROOT . '/uploads/media/';
        if (!is_dir($mediaDir)) @mkdir($mediaDir, 0775, true);

        if (!empty($_FILES['images'])) {
            $files = $_FILES['images'];
            $count = is_array($files['name']) ? count($files['name']) : 1;

            for ($i = 0; $i < $count; $i++) {
                $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                $tmp  = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $mime = is_array($files['type']) ? $files['type'][$i] : $files['type'];
                $size = is_array($files['size']) ? $files['size'][$i] : $files['size'];

                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) continue;
                if ($size > 10 * 1024 * 1024) continue; // 10MB max

                $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'jpg';
                $filename = date('Ymd_His') . '_atb_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $mediaDir . $filename;

                if (move_uploaded_file($tmp, $destPath)) {
                    // Verify actual file type (MIME can be spoofed)
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $realMime = $finfo->file($destPath);
                    if (!in_array($realMime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
                        @unlink($destPath);
                        continue;
                    }
                    // Save to media table
                    try {
                        $pdo = \core\Database::connection();
                        $pdo->prepare("INSERT INTO media (filename, original_name, mime_type, size, path, folder, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'theme-builder', NOW(), NOW())")
                            ->execute([$filename, $name, $mime, $size, 'uploads/media/' . $filename]);
                    } catch (\Throwable $e) { /* non-critical */ }

                    $uploaded[] = [
                        'filename' => $filename,
                        'original_name' => $name,
                        'path' => '/uploads/media/' . $filename,
                        'mime' => $mime,
                        'size' => $size,
                    ];
                }
            }
        }

        Response::json([
            'ok' => true,
            'images' => $uploaded,
            'count' => count($uploaded),
        ]);
    }

    /* ═══════════════════════════════════════════════════════
       CONTENT-FIRST WIZARD — Content planning & generation
       ═══════════════════════════════════════════════════════ */

    /**
     * POST /api/ai-theme-builder/wizard/content-plan
     * Generate a content plan for ALL selected pages at once.
     */
    public function wizardContentPlan(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);
        $data = $GLOBALS['_JSON_DATA'] ?? [];

        $prompt   = trim($data['prompt'] ?? '');
        $industry = trim($data['industry'] ?? 'portfolio');
        $language = trim($data['language'] ?? 'English');
        $pages    = $data['pages'] ?? ['home'];
        $tone     = trim($data['tone'] ?? 'neutral');
        $provider = trim($data['provider'] ?? '');
        $model    = trim($data['model'] ?? '');

        if (empty($prompt)) {
            Response::json(['ok' => false, 'error' => 'Please describe your website']);
            return;
        }

        if (!is_array($pages) || empty($pages)) {
            Response::json(['ok' => false, 'error' => 'At least one page is required']);
            return;
        }

        // Sanitize page names
        $pages = array_values(array_filter(array_map(function($p) {
            return preg_replace('/[^a-z0-9-]/', '', strtolower(trim($p)));
        }, $pages)));

        if (empty($pages)) {
            Response::json(['ok' => false, 'error' => 'No valid pages specified']);
            return;
        }

        try {
            require_once \CMS_ROOT . '/core/ai_content.php';

            $pagesList = implode(', ', $pages);

            $systemPrompt = <<<SYSTEM
You are an expert website content strategist and SEO specialist. You create detailed, industry-specific content plans for multi-page websites. You always respond with valid JSON only, no markdown formatting, no code blocks.
SYSTEM;

            $userPrompt = <<<PROMPT
Create a comprehensive content plan for a {$industry} website.

Business description: {$prompt}
Language: {$language}
Tone: {$tone}
Pages to plan: {$pagesList}

For EACH page, generate a content plan with:
1. "title" — an appropriate page title (in {$language})
2. "keywords" — object with:
   - "primary": the main SEO keyword for this page (string)
   - "secondary": array of 3-5 secondary/long-tail keywords (string[])
3. "outline" — array of 3-6 content sections, each with:
   - "heading": section heading text (string)
   - "description": 1-2 sentence description of what this section should cover (string)
4. "meta_description" — SEO meta description, 150-160 characters (string)
5. "content_brief" — 2-3 sentence description of what the page should contain (string)
6. "word_count_target" — recommended word count as integer

IMPORTANT RULES:
- Make content plans industry-specific and relevant to: {$industry}
- Each page should have UNIQUE keywords — don't repeat the same primary keyword across pages
- The home page should target the broadest keyword, other pages more specific
- Outline headings should be specific and actionable, not generic
- Content briefs should reference the business description provided
- All text content must be in {$language}
- Tone should be {$tone} throughout

Return a single JSON object with page types as keys:
{{$pagesList}}

Example structure:
{
  "home": {
    "title": "...",
    "keywords": {"primary": "...", "secondary": ["...", "..."]},
    "outline": [{"heading": "...", "description": "..."}],
    "meta_description": "...",
    "content_brief": "...",
    "word_count_target": 800
  },
  "about": { ... }
}

Return ONLY the JSON object, no explanation.
PROMPT;

            $result = ai_universal_generate($provider, $model, $systemPrompt, $userPrompt, [
                'max_tokens' => 4000,
                'temperature' => 0.7,
            ]);

            if (empty($result['ok'])) {
                Response::json(['ok' => false, 'error' => $result['error'] ?? 'AI generation failed']);
                return;
            }

            $text = trim($result['content'] ?? '');

            // Clean markdown code blocks if present
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
            $text = preg_replace('/\s*```$/i', '', $text);
            $text = trim($text);

            $contentPlan = @json_decode($text, true);

            if (!is_array($contentPlan) || json_last_error() !== JSON_ERROR_NONE) {
                Response::json(['ok' => false, 'error' => 'AI returned invalid JSON. Please try again.']);
                return;
            }

            // Validate & normalize each page plan
            foreach ($pages as $page) {
                if (!isset($contentPlan[$page]) || !is_array($contentPlan[$page])) {
                    $contentPlan[$page] = [
                        'title' => ucfirst(str_replace('-', ' ', $page)),
                        'keywords' => ['primary' => $industry . ' ' . $page, 'secondary' => []],
                        'outline' => [['heading' => 'Overview', 'description' => 'Main content section']],
                        'meta_description' => '',
                        'content_brief' => '',
                        'word_count_target' => 600,
                    ];
                }

                $plan = &$contentPlan[$page];

                // Ensure required fields
                if (empty($plan['title'])) $plan['title'] = ucfirst(str_replace('-', ' ', $page));
                if (!isset($plan['keywords']) || !is_array($plan['keywords'])) {
                    $plan['keywords'] = ['primary' => '', 'secondary' => []];
                }
                if (!is_string($plan['keywords']['primary'] ?? null)) {
                    $plan['keywords']['primary'] = '';
                }
                if (!is_array($plan['keywords']['secondary'] ?? null)) {
                    $plan['keywords']['secondary'] = [];
                }
                if (!is_array($plan['outline'] ?? null) || empty($plan['outline'])) {
                    $plan['outline'] = [['heading' => 'Overview', 'description' => 'Main content']];
                }
                // Normalize outline items
                $plan['outline'] = array_values(array_map(function($item) {
                    return [
                        'heading' => is_string($item['heading'] ?? null) ? $item['heading'] : 'Section',
                        'description' => is_string($item['description'] ?? null) ? $item['description'] : '',
                    ];
                }, $plan['outline']));

                if (!is_string($plan['meta_description'] ?? null)) $plan['meta_description'] = '';
                if (!is_string($plan['content_brief'] ?? null)) $plan['content_brief'] = '';
                if (!is_int($plan['word_count_target'] ?? null)) {
                    $plan['word_count_target'] = max(300, min(5000, (int)($plan['word_count_target'] ?? 600)));
                }

                unset($plan);
            }

            Response::json([
                'ok' => true,
                'content_plan' => $contentPlan,
            ]);

        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => 'Content plan generation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * POST /api/ai-theme-builder/wizard/generate-content
     * Generate full HTML content for ONE page based on its content plan.
     */
    public function wizardGenerateContent(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);
        $data = $GLOBALS['_JSON_DATA'] ?? [];
        @file_put_contents('/tmp/aitb-content-debug.log', date('H:i:s') . " wizardGenerateContent called\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

        $pageType     = trim($data['page_type'] ?? '');
        $title        = trim($data['title'] ?? '');
        $contentBrief = trim($data['content_brief'] ?? '');
        $outline      = $data['outline'] ?? [];
        $keywords     = $data['keywords'] ?? [];
        $tone         = trim($data['tone'] ?? 'neutral');
        $industry     = trim($data['industry'] ?? '');
        $language     = trim($data['language'] ?? 'English');
        $businessInfo = $data['business_info'] ?? [];
        $provider     = trim($data['provider'] ?? '');
        $model        = trim($data['model'] ?? '');
        $wordTarget   = (int)($data['word_count_target'] ?? 800);

        if (empty($pageType)) {
            Response::json(['ok' => false, 'error' => 'page_type is required']);
            return;
        }
        if (empty($title)) {
            Response::json(['ok' => false, 'error' => 'title is required']);
            return;
        }

        // Normalize
        if (!is_array($outline)) $outline = [];
        if (!is_array($keywords)) $keywords = ['primary' => '', 'secondary' => []];
        if (!is_array($businessInfo)) $businessInfo = [];

        $primaryKw   = is_string($keywords['primary'] ?? null) ? $keywords['primary'] : '';
        $secondaryKw = is_array($keywords['secondary'] ?? null) ? implode(', ', $keywords['secondary']) : '';

        // Build outline text
        $outlineText = '';
        foreach ($outline as $i => $section) {
            $heading = is_string($section['heading'] ?? null) ? $section['heading'] : 'Section ' . ($i + 1);
            $desc = is_string($section['description'] ?? null) ? $section['description'] : '';
            $outlineText .= "- {$heading}: {$desc}\n";
        }

        // Business profile text — comprehensive
        $bizText = self::buildBusinessProfileText($businessInfo);

        try {
            require_once \CMS_ROOT . '/core/ai_content.php';

            $systemPrompt = <<<SYSTEM
You are an expert website copywriter specializing in creating engaging, SEO-optimized web page content. You write in clean semantic HTML. Never use markdown. Never wrap output in code blocks.
SYSTEM;

            $userPrompt = <<<PROMPT
Write the complete content for a "{$pageType}" page of a {$industry} website.

Page title: {$title}
Language: {$language}
Tone: {$tone}
Target word count: approximately {$wordTarget} words

Content brief: {$contentBrief}

Content outline (follow this structure):
{$outlineText}

Primary keyword: {$primaryKw}
Secondary keywords: {$secondaryKw}

PROMPT;

            if ($bizText) {
                $userPrompt .= <<<BIZ

═══ REAL BUSINESS DATA (from the owner — use this as PRIMARY content source) ═══
{$bizText}
═══════════════════════════════════════════════════════════════════════════════

CRITICAL: The above is REAL information from the actual business owner.
- Use their REAL business name, services, team members, testimonials, etc.
- Do NOT generate fake team names if real ones are provided
- Do NOT generate fake testimonials if real ones are provided
- Do NOT generate fake service names if real ones are provided
- Fill in gaps with reasonable copy, but always prefer real data over invented text
- Weave their unique selling points naturally into the content

BIZ;
            }

            $userPrompt .= <<<RULES

WRITING RULES:
1. Write ALL content in {$language}
2. Use semantic HTML: <h2> for main sections, <h3> for subsections, <p> for paragraphs
3. Use <ul>/<ol> with <li> for lists, <blockquote> for quotes where appropriate
4. Use <strong> and <em> for emphasis
5. Incorporate the primary keyword naturally 3-5 times throughout the content
6. Include secondary keywords where they fit naturally
7. Create a section for EACH heading in the outline
8. Make the content compelling, specific to the {$industry} industry
9. Match the {$tone} tone throughout
10. Do NOT include the page title as an H1 — start directly with H2 sections
11. Do NOT add <html>, <head>, <body> tags — just the inner content HTML
12. Do NOT include any meta tags or scripts
13. Return ONLY the HTML content, nothing else — no explanations, no preamble

Write the page content now:
RULES;

            $maxTokens = max(2000, min(8000, (int)($wordTarget * 2.5)));

            $result = ai_universal_generate($provider, $model, $systemPrompt, $userPrompt, [
                'max_tokens' => $maxTokens,
                'temperature' => 0.7,
            ]);

            if (empty($result['ok'])) {
                Response::json(['ok' => false, 'error' => $result['error'] ?? 'Content generation failed']);
                return;
            }

            $content = trim($result['content'] ?? '');

            // Clean up: remove any markdown code block wrappers
            $content = preg_replace('/^```(?:html)?\s*/i', '', $content);
            $content = preg_replace('/\s*```$/i', '', $content);
            $content = trim($content);

            // Remove any preamble text before the first HTML tag
            if (preg_match('/^[^<]+(<[hH][2-6]|<p|<div|<section|<article)/s', $content)) {
                $content = preg_replace('/^[^<]+(?=<)/s', '', $content);
            }

            $wordCount = str_word_count(strip_tags($content));

            // Generate meta description from content if not provided
            $metaDesc = '';
            $plainText = strip_tags($content);
            if (strlen($plainText) > 160) {
                $metaDesc = substr($plainText, 0, 157) . '...';
            } else {
                $metaDesc = $plainText;
            }

            Response::json([
                'ok' => true,
                'content' => $content,
                'word_count' => $wordCount,
                'meta_description' => $metaDesc,
            ]);

        } catch (\Throwable $e) {
            @file_put_contents('/tmp/aitb-content-debug.log', date('H:i:s') . " ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n", FILE_APPEND);
            Response::json(['ok' => false, 'error' => 'Content generation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * POST /api/ai-theme-builder/wizard/rewrite-content
     * Rewrite page content using AI rewrite tools.
     */
    public function wizardRewriteContent(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);
        $data = $GLOBALS['_JSON_DATA'] ?? [];

        $content  = trim($data['content'] ?? '');
        $mode     = trim($data['mode'] ?? 'paraphrase');
        $tone     = trim($data['tone'] ?? 'neutral');
        $keywords = $data['keywords'] ?? [];
        $provider = trim($data['provider'] ?? '');
        $model    = trim($data['model'] ?? '');

        if (empty($content)) {
            Response::json(['ok' => false, 'error' => 'Content is required']);
            return;
        }

        // Validate mode
        $validModes = ['paraphrase', 'summarize', 'expand', 'simplify', 'formalize', 'casual', 'seo', 'kids'];
        if (!in_array($mode, $validModes)) {
            $mode = 'paraphrase';
        }

        try {
            require_once \CMS_ROOT . '/core/ai_content_rewrite.php';

            $primaryKw = '';
            if (is_array($keywords) && is_string($keywords['primary'] ?? null)) {
                $primaryKw = $keywords['primary'];
            }

            $options = [
                'tone' => $tone,
                'keyword' => $primaryKw,
                'preserve_structure' => true,
                'provider' => $provider,
                'model' => $model,
            ];

            $result = ai_rewrite_content($content, $mode, $options);

            if (empty($result['ok'])) {
                Response::json(['ok' => false, 'error' => $result['error'] ?? 'Rewrite failed']);
                return;
            }

            $rewritten = $result['rewritten'] ?? '';
            $wordCount = str_word_count(strip_tags($rewritten));

            // Build a changes summary
            $origWords = $result['original_words'] ?? 0;
            $newWords = $result['new_words'] ?? 0;
            $changePct = $result['change_percent'] ?? 0;

            $summaryParts = [];
            if ($mode === 'seo' && $primaryKw) $summaryParts[] = "Optimized for \"{$primaryKw}\"";
            if ($tone !== 'neutral') $summaryParts[] = "Applied {$tone} tone";
            if ($changePct > 0) $summaryParts[] = "Expanded by {$changePct}%";
            elseif ($changePct < 0) $summaryParts[] = "Reduced by " . abs($changePct) . "%";
            $summaryParts[] = ucfirst($mode) . " mode applied";

            Response::json([
                'ok' => true,
                'content' => $rewritten,
                'word_count' => $wordCount,
                'changes_summary' => implode('. ', $summaryParts),
                'original_words' => $origWords,
                'new_words' => $newWords,
                'change_percent' => $changePct,
            ]);

        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => 'Rewrite failed: ' . $e->getMessage()]);
        }
    }

    /**
     * POST /api/ai-theme-builder/wizard/seo-check
     * Run SEO analysis on page content.
     */
    public function wizardSeoCheck(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);
        $data = $GLOBALS['_JSON_DATA'] ?? [];

        $content         = trim($data['content'] ?? '');
        $title           = trim($data['title'] ?? '');
        $metaDescription = trim($data['meta_description'] ?? '');
        $keywords        = $data['keywords'] ?? [];
        $urlSlug         = trim($data['url_slug'] ?? '');

        if (empty($content)) {
            Response::json(['ok' => false, 'error' => 'Content is required for SEO analysis']);
            return;
        }

        $primaryKw = '';
        $secondaryKw = '';
        if (is_array($keywords)) {
            if (is_string($keywords['primary'] ?? null)) {
                $primaryKw = $keywords['primary'];
            }
            if (is_array($keywords['secondary'] ?? null)) {
                $secondaryKw = implode(', ', $keywords['secondary']);
            }
        }

        if (empty($primaryKw)) {
            Response::json(['ok' => false, 'error' => 'Primary keyword is required for SEO analysis']);
            return;
        }

        try {
            require_once \CMS_ROOT . '/core/ai_seo_assistant.php';

            $spec = [
                'title'              => $title,
                'url'                => $urlSlug ? '/' . ltrim($urlSlug, '/') : '',
                'focus_keyword'      => $primaryKw,
                'secondary_keywords' => $secondaryKw,
                'content_html'       => $content,
                'content_type'       => 'landing_page',
                'language'           => 'en',
                'notes'              => $metaDescription ? 'Current meta description: ' . $metaDescription : '',
            ];

            $result = ai_seo_assistant_analyze($spec);

            if (empty($result['ok'])) {
                Response::json(['ok' => false, 'error' => $result['error'] ?? 'SEO analysis failed']);
                return;
            }

            $report = $result['report'] ?? [];

            // Extract key data for the frontend
            $score = (int)($report['health_score'] ?? 0);
            $issues = [];
            $recommendations = [];

            // Collect issues from on_page_checks
            $onPage = $report['on_page_checks'] ?? [];
            if (!empty($onPage['headings']['current_issues'])) {
                foreach ($onPage['headings']['current_issues'] as $issue) {
                    $issues[] = $issue;
                }
            }
            if (!empty($onPage['keyword_usage']['missing_variants'])) {
                foreach ($onPage['keyword_usage']['missing_variants'] as $variant) {
                    $issues[] = "Missing keyword variant: {$variant}";
                }
            }

            // Collect recommendations from quick_wins and content_ideas
            if (!empty($report['quick_wins'])) {
                foreach ($report['quick_wins'] as $win) {
                    $recommendations[] = $win;
                }
            }
            if (!empty($onPage['headings']['suggested_improvements'])) {
                foreach ($onPage['headings']['suggested_improvements'] as $suggestion) {
                    $recommendations[] = $suggestion;
                }
            }

            Response::json([
                'ok' => true,
                'score' => $score,
                'issues' => $issues,
                'recommendations' => $recommendations,
                'summary' => $report['summary'] ?? '',
                'meta_suggestions' => $onPage['meta_suggestions'] ?? null,
                'keyword_usage' => $onPage['keyword_usage'] ?? null,
                'readability' => $onPage['readability'] ?? null,
                'full_report' => $report,
            ]);

        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => 'SEO analysis failed: ' . $e->getMessage()]);
        }
    }

    /**
     * POST /api/ai-theme-builder/wizard/search-images — Search Pexels images
     */
    public function searchImages(Request $request): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE);

        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true) ?: [];
        $query = trim($body['query'] ?? '');
        $page = max(1, (int)($body['page'] ?? 1));
        $perPage = max(1, min(80, (int)($body['per_page'] ?? 15)));

        if (empty($query)) {
            Response::json(['ok' => false, 'error' => 'Search query is required']);
            return;
        }

        try {
            $pdo = \core\Database::connection();
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'pexels_api_key' LIMIT 1");
            $stmt->execute();
            $pexelsKey = $stmt->fetchColumn() ?: '';
        } catch (\Throwable $e) {
            $pexelsKey = '';
        }

        if (empty($pexelsKey) || strlen($pexelsKey) < 20) {
            Response::json(['ok' => false, 'error' => 'Pexels API key not configured']);
            return;
        }

        $params = http_build_query([
            'query' => $query,
            'per_page' => $perPage,
            'page' => $page,
            'orientation' => 'landscape',
            'size' => 'large',
        ]);

        $ch = curl_init('https://api.pexels.com/v1/search?' . $params);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: ' . $pexelsKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Response::json(['ok' => false, 'error' => 'Pexels connection failed: ' . $curlError]);
            return;
        }
        if ($httpCode !== 200) {
            Response::json(['ok' => false, 'error' => 'Pexels API error (HTTP ' . $httpCode . ')']);
            return;
        }

        $data = @json_decode($response, true);
        if (empty($data['photos'])) {
            Response::json(['ok' => true, 'images' => [], 'total' => 0, 'page' => $page]);
            return;
        }

        $images = [];
        foreach ($data['photos'] as $photo) {
            $images[] = [
                'id' => $photo['id'] ?? 0,
                'src' => $photo['src']['large'] ?? $photo['src']['original'] ?? '',
                'thumb' => $photo['src']['medium'] ?? $photo['src']['small'] ?? '',
                'alt' => $photo['alt'] ?? '',
                'photographer' => $photo['photographer'] ?? '',
                'photographer_url' => $photo['photographer_url'] ?? '',
                'pexels_url' => $photo['url'] ?? '',
            ];
        }

        Response::json([
            'ok' => true,
            'images' => $images,
            'total' => $data['total_results'] ?? count($images),
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * POST /api/ai-theme-builder/wizard/regenerate-section — Regenerate CSS for a single section
     */
    public function regenerateSection(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? [];
        $slug = trim($body['slug'] ?? '');
        $sectionId = trim($body['section_id'] ?? '');
        $instructions = trim($body['instructions'] ?? '');
        $provider = trim($body['provider'] ?? '');
        $model = trim($body['model'] ?? '');

        if (empty($slug) || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            Response::json(['ok' => false, 'error' => 'Theme slug required']);
            return;
        }

        if (empty($sectionId) || !preg_match('/^[a-z0-9_-]+$/', $sectionId)) {
            Response::json(['ok' => false, 'error' => 'Section ID required']);
            return;
        }

        try {
            require_once \CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder([
                'provider' => $provider,
                'model' => $model,
            ]);
            $result = $builder->regenerateSectionCss($slug, $sectionId, $instructions);
            Response::json($result);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Analyze a website URL for design inspiration (colors, style, mood, fonts).
     * Uses AI to interpret the fetched HTML/CSS.
     */
    public function analyzeInspiration(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        $url = $body['url'] ?? '';
        $provider = $body['provider'] ?? '';
        $model = $body['model'] ?? '';

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            Response::json(['ok' => false, 'error' => 'Valid URL required']);
            return;
        }

        try {
            // Fetch the page HTML (limited to 50KB to stay within token limits)
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 15,
                    'user_agent' => 'Mozilla/5.0 (compatible; JessieCMS/1.0)',
                    'follow_location' => true,
                    'max_redirects' => 3,
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);
            $html = @file_get_contents($url, false, $ctx);

            if ($html === false) {
                Response::json(['ok' => false, 'error' => 'Could not fetch URL — check if the website is accessible']);
                return;
            }

            // Extract useful design signals from HTML
            $extracted = '';

            // Get <style> blocks and inline styles (first 10KB)
            preg_match_all('/<style[^>]*>(.*?)<\/style>/si', $html, $styleMatches);
            $cssContent = implode("\n", $styleMatches[1] ?? []);
            if (strlen($cssContent) > 10000) $cssContent = substr($cssContent, 0, 10000);
            if ($cssContent) $extracted .= "CSS:\n" . $cssContent . "\n\n";

            // Get linked stylesheets (just the URLs)
            preg_match_all('/href=["\']([^"\']+\.css[^"\']*)["\']/', $html, $cssLinks);
            if (!empty($cssLinks[1])) {
                $extracted .= "Linked CSS files: " . implode(', ', array_slice($cssLinks[1], 0, 5)) . "\n\n";
            }

            // Get Google Fonts
            preg_match_all('/fonts\.googleapis\.com\/css[^"\']+/', $html, $fontLinks);
            if (!empty($fontLinks[0])) {
                $extracted .= "Google Fonts: " . implode(', ', $fontLinks[0]) . "\n\n";
            }

            // Get meta description and title
            preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $titleMatch);
            if (!empty($titleMatch[1])) $extracted .= "Title: " . trim(strip_tags($titleMatch[1])) . "\n";

            preg_match('/content=["\']([^"\']+)["\'].*?name=["\']description["\']|name=["\']description["\'].*?content=["\']([^"\']+)["\']/i', $html, $descMatch);
            $desc = $descMatch[1] ?? $descMatch[2] ?? '';
            if ($desc) $extracted .= "Description: " . $desc . "\n\n";

            // Get body classes (often reveal theme/style)
            preg_match('/<body[^>]*class=["\']([^"\']+)["\']/', $html, $bodyClass);
            if (!empty($bodyClass[1])) $extracted .= "Body classes: " . $bodyClass[1] . "\n\n";

            // Limit total extraction
            if (strlen($extracted) > 15000) $extracted = substr($extracted, 0, 15000);

            if (empty($extracted)) {
                $extracted = "Could not extract CSS from the page. URL: " . $url;
            }

            // Ask AI to analyze the design
            require_once CMS_ROOT . '/core/ai-theme-builder.php';
            $builder = new \AiThemeBuilder();

            $systemPrompt = <<<PROMPT
You are a web design analyst. Analyze the following CSS/HTML extracted from a website and identify its design system.

Return a JSON object with:
- "colors": array of 4-6 hex colors used (primary, secondary, accent, background, text, surface) — extract from CSS variables or frequently used values
- "style": one word (minimalist, corporate, playful, elegant, bold, modern, vintage, creative, editorial, luxury)
- "mood": one word (light, dark, warm, cool, vibrant, muted, professional, casual)  
- "fonts": string describing font families found (e.g. "Inter for body, Playfair Display for headings")
- "layout": brief description of layout style (e.g. "full-width sections with centered content, card-based feature blocks")
- "summary": 1-2 sentence summary of the overall design aesthetic

Website URL: {$url}

EXTRACTED DESIGN DATA:
{$extracted}

Return ONLY valid JSON.
PROMPT;

            $aiSettings = json_decode(@file_get_contents(CMS_ROOT . '/config/ai_settings.json') ?: '{}', true) ?: [];
            $selectedProvider = $provider ?: ($aiSettings['selected_provider'] ?? 'deepseek');
            $selectedModel = $model ?: ($aiSettings['selected_model'] ?? 'deepseek-v3');

            $result = $builder->aiQuery('Analyze this website design', [
                'system_prompt' => $systemPrompt,
                'max_tokens' => 1000,
                'temperature' => 0.3,
                'json_mode' => true,
                'provider' => $selectedProvider,
                'model' => $selectedModel,
            ]);

            if (!empty($result['ok'])) {
                $text = $result['text'] ?? '';
                // Extract JSON
                $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
                $text = preg_replace('/```\s*$/m', '', $text);
                $analysis = json_decode($text, true);

                if (!$analysis && preg_match('/\{[\s\S]*\}/s', $text, $jm)) {
                    $analysis = json_decode($jm[0], true);
                }

                if ($analysis) {
                    Response::json(['ok' => true, 'analysis' => $analysis]);
                } else {
                    Response::json(['ok' => false, 'error' => 'AI returned invalid analysis', 'raw' => substr($text, 0, 500)]);
                }
            } else {
                Response::json(['ok' => false, 'error' => $result['error'] ?? 'AI query failed']);
            }
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Build comprehensive business profile text from business_info array.
     * Used by content generation, layout generation, sub-page generation.
     */
    public static function buildBusinessProfileText(array $info): string
    {
        if (empty($info)) return '';

        $lines = [];

        // Core identity
        if (!empty($info['name']))        $lines[] = "Business name: {$info['name']}";
        if (!empty($info['description'])) $lines[] = "About: {$info['description']}";
        if (!empty($info['tagline']))     $lines[] = "Tagline: \"{$info['tagline']}\"";
        if (!empty($info['years']))       $lines[] = "Years in business: {$info['years']}";
        if (!empty($info['audience']))    $lines[] = "Target audience: {$info['audience']}";

        // Unique selling points
        if (!empty($info['usps'])) {
            $usps = array_filter(array_map('trim', explode("\n", $info['usps'])));
            if ($usps) {
                $lines[] = "Unique selling points:";
                foreach ($usps as $usp) $lines[] = "  • {$usp}";
            }
        }

        // Contact info
        $contact = [];
        if (!empty($info['phone']))   $contact[] = "Phone: {$info['phone']}";
        if (!empty($info['email']))   $contact[] = "Email: {$info['email']}";
        if (!empty($info['address'])) $contact[] = "Address: {$info['address']}";
        if (!empty($info['website'])) $contact[] = "Website: {$info['website']}";
        if ($contact) {
            $lines[] = "Contact: " . implode(' | ', $contact);
        }

        // Social media
        $social = $info['social'] ?? [];
        if (is_array($social) && !empty($social)) {
            $socialItems = [];
            foreach ($social as $platform => $url) {
                if (!empty($url)) $socialItems[] = ucfirst($platform) . ": {$url}";
            }
            if ($socialItems) $lines[] = "Social media: " . implode(', ', $socialItems);
        }

        // Services / Products
        $services = $info['services'] ?? [];
        if (is_array($services) && !empty($services)) {
            $lines[] = "Services/Products offered:";
            foreach ($services as $svc) {
                $name = $svc['name'] ?? '';
                $desc = $svc['description'] ?? '';
                if ($name) {
                    $lines[] = $desc ? "  • {$name} — {$desc}" : "  • {$name}";
                }
            }
        }

        // Team members
        $team = $info['team'] ?? [];
        if (is_array($team) && !empty($team)) {
            $lines[] = "Team members:";
            foreach ($team as $member) {
                $name = $member['name'] ?? '';
                $role = $member['role'] ?? '';
                $bio  = $member['bio'] ?? '';
                if ($name) {
                    $entry = "  • {$name}";
                    if ($role) $entry .= " — {$role}";
                    if ($bio) $entry .= ". {$bio}";
                    $lines[] = $entry;
                }
            }
        }

        // Testimonials
        $testimonials = $info['testimonials'] ?? [];
        if (is_array($testimonials) && !empty($testimonials)) {
            $lines[] = "Real customer testimonials (USE THESE instead of generating fake ones):";
            foreach ($testimonials as $t) {
                $quote   = $t['quote'] ?? '';
                $author  = $t['name'] ?? '';
                $company = $t['company'] ?? '';
                if ($quote) {
                    $attribution = $author;
                    if ($company) $attribution .= ", {$company}";
                    $lines[] = $attribution ? "  \"{$quote}\" — {$attribution}" : "  \"{$quote}\"";
                }
            }
        }

        // Opening hours
        $hours = $info['hours'] ?? [];
        if (is_array($hours) && !empty($hours)) {
            $lines[] = "Opening hours:";
            $dayOrder = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
            foreach ($dayOrder as $day) {
                if (isset($hours[$day])) {
                    $lines[] = "  " . ucfirst($day) . ": {$hours[$day]}";
                }
            }
        }

        // Industry-specific fields
        if (!empty($info['cuisine']))               $lines[] = "Cuisine type: {$info['cuisine']}";
        if (!empty($info['price_range']))            $lines[] = "Price range: {$info['price_range']}";
        if (!empty($info['menu_highlights'])) {
            $items = array_filter(array_map('trim', explode("\n", $info['menu_highlights'])));
            if ($items) { $lines[] = "Menu highlights: " . implode(', ', $items); }
        }
        if (!empty($info['reservations']))           $lines[] = "Reservations: {$info['reservations']}";
        if (!empty($info['seating']))                $lines[] = "Seating capacity: {$info['seating']}";
        if (!empty($info['specialties'])) {
            $items = array_filter(array_map('trim', explode("\n", $info['specialties'])));
            if ($items) { $lines[] = "Specialties: " . implode(', ', $items); }
        }
        if (!empty($info['insurance']))              $lines[] = "Insurance/NHS: {$info['insurance']}";
        if (!empty($info['emergency']))              $lines[] = "Emergency services: {$info['emergency']}";
        if (!empty($info['creative_specialisation'])) $lines[] = "Specialisation: {$info['creative_specialisation']}";
        if (!empty($info['notable_clients'])) {
            $items = array_filter(array_map('trim', explode("\n", $info['notable_clients'])));
            if ($items) { $lines[] = "Notable clients: " . implode(', ', $items); }
        }
        if (!empty($info['awards'])) {
            $items = array_filter(array_map('trim', explode("\n", $info['awards'])));
            if ($items) { $lines[] = "Awards: " . implode(', ', $items); }
        }
        if (!empty($info['practice_areas'])) {
            $items = array_filter(array_map('trim', explode("\n", $info['practice_areas'])));
            if ($items) { $lines[] = "Practice areas: " . implode(', ', $items); }
        }
        if (!empty($info['accreditations'])) {
            $items = array_filter(array_map('trim', explode("\n", $info['accreditations'])));
            if ($items) { $lines[] = "Accreditations: " . implode(', ', $items); }
        }
        if (!empty($info['free_consultation']))      $lines[] = "Free consultation: {$info['free_consultation']}";
        if (!empty($info['products'])) {
            $items = array_filter(array_map('trim', explode("\n", $info['products'])));
            if ($items) { $lines[] = "Products: " . implode(', ', $items); }
        }
        if (!empty($info['shipping']))               $lines[] = "Shipping: {$info['shipping']}";
        if (!empty($info['returns']))                 $lines[] = "Returns: {$info['returns']}";
        if (!empty($info['certifications'])) {
            $items = array_filter(array_map('trim', explode("\n", $info['certifications'])));
            if ($items) { $lines[] = "Certifications & Awards: " . implode(', ', $items); }
        }
        if (!empty($info['areas_served']))           $lines[] = "Areas served: {$info['areas_served']}";
        if (!empty($info['extra_notes']))            $lines[] = "Additional notes: {$info['extra_notes']}";

        return implode("\n", $lines);
    }
}
