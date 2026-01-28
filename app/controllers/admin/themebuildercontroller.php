<?php
declare(strict_types=1);
/**
 * Theme Builder 3.0 Controller
 * MVC Admin interface for Divi-style page builder
 * Integrates with /core/theme-builder/ core module system
 */

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

// Bootstrap Theme Builder 3.0 core
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 3));
}
require_once CMS_ROOT . '/core/theme-builder/init.php';
tb_init();

class ThemeBuilderController
{
    private \PDO $db;
    
    public function __construct()
    {
        $this->db = db();
        $this->ensureTableExists();
    }
    
    /**
     * Ensure tb_pages table exists
     */
    private function ensureTableExists(): void
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'tb_pages'");
        if (!$stmt->fetch()) {
            // Table doesn't exist, create it
            $this->db->exec("CREATE TABLE tb_pages (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_id INT UNSIGNED NULL COMMENT 'FK to pages table if editing existing page',
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                content_json LONGTEXT NOT NULL COMMENT 'JSON structure of sections/rows/columns/modules',
                status ENUM('draft', 'published') DEFAULT 'draft',
                template VARCHAR(100) DEFAULT 'default',
                created_by INT UNSIGNED NULL,
                updated_by INT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_page_id (page_id),
                INDEX idx_slug (slug),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }
    
    /**
     * Show list of pages with Theme Builder content
     */
    public function index(Request $request): void
    {
        // Get all TB pages
        $stmt = $this->db->query("
            SELECT 
                p.id,
                p.page_id,
                p.title,
                p.slug,
                p.status,
                p.is_homepage,
                p.template,
                p.created_at,
                p.updated_at,
                (SELECT COUNT(*) FROM tb_revisions r WHERE r.page_id = p.id) as revision_count
            FROM tb_pages p
            ORDER BY p.is_homepage DESC, p.updated_at DESC
        ");
        $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        render('admin/theme-builder/index', [
            'pages' => $pages,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }
    
    /**
     * Create new TB page
     */
    public function create(Request $request): void
    {
        render('admin/theme-builder/create', [
            'page' => null,
            'pageId' => 0,
            'contentJson' => '{"sections":[]}',
            'modulesJson' => json_encode(tb_get_all_modules(), JSON_UNESCAPED_UNICODE),
            'categoriesJson' => json_encode(tb_get_category_labels(), JSON_UNESCAPED_UNICODE)
        ]);
    }
    
    /**
     * Edit page in visual builder
     */
    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        
        // Try to get TB page
        $stmt = $this->db->prepare("SELECT * FROM tb_pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$page) {
            Session::flash('error', 'Page not found.');
            Response::redirect('/admin/theme-builder');
            return;
        }
        
        // Get revisions
        $revisions = tb_get_revisions($id, 10);
        
        // Load theme colors from active theme
        $themeColors = $this->loadThemeColors();
        
        render('admin/theme-builder/edit', [
            'page' => $page,
            'pageId' => $id,
            'pageSlug' => $page['slug'] ?? '',
            'pageStatus' => $page['status'] ?? 'draft',
            'contentJson' => $page['content_json'] ?? '{"sections":[]}',
            'modulesJson' => json_encode(tb_get_all_modules(), JSON_UNESCAPED_UNICODE),
            'categoriesJson' => json_encode(tb_get_category_labels(), JSON_UNESCAPED_UNICODE),
            'themeColorsJson' => json_encode($themeColors, JSON_UNESCAPED_UNICODE),
            'revisions' => $revisions
        ]);
    }
    
    /**
     * Edit existing regular page with Theme Builder
     */
    public function editPage(Request $request): void
    {
        $pageId = (int)$request->param('page_id');
        
        // Get the regular page
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$pageId]);
        $regularPage = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$regularPage) {
            Session::flash('error', 'Page not found.');
            Response::redirect('/admin/theme-builder');
            return;
        }
        
        // Check if TB page exists for this page
        $stmt2 = $this->db->prepare("SELECT * FROM tb_pages WHERE page_id = ?");
        $stmt2->execute([$pageId]);
        $tbPage = $stmt2->fetch(\PDO::FETCH_ASSOC);
        
        if (!$tbPage) {
            // Create TB page entry for this regular page
            $stmt3 = $this->db->prepare("
                INSERT INTO tb_pages (page_id, title, slug, content_json, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $userId = $_SESSION['admin_user_id'] ?? null;
            $stmt3->execute([
                $pageId,
                $regularPage['title'],
                $regularPage['slug'],
                '{"sections":[]}',
                $regularPage['status'],
                $userId
            ]);
            $tbPageId = (int)$this->db->lastInsertId();
        } else {
            $tbPageId = (int)$tbPage['id'];
        }
        
        Response::redirect("/admin/theme-builder/{$tbPageId}/edit");
    }
    
    /**
     * AJAX: Save page content (POST, CSRF validated)
     */
    public function save(Request $request): void
    {
        // DEBUG: Log entry to save method
        @file_put_contents(CMS_ROOT . '/logs/index_debug.log', date('Y-m-d H:i:s') . " TB_SAVE: Method called\n", FILE_APPEND);

        // Ensure no prior output
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        try {
            // Parse JSON input - use cached data if available (set by router CSRF middleware)
            $input = $GLOBALS['_JSON_DATA'] ?? null;
            error_log("TB_SAVE: GLOBALS[_JSON_DATA] = " . ($input ? 'SET' : 'NULL'));

            if (!$input) {
                $rawInput = file_get_contents('php://input');
                error_log("TB_SAVE: php://input length = " . strlen($rawInput));
                $input = json_decode($rawInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("TB_SAVE: JSON decode error: " . json_last_error_msg());
                }
            }

            // CSRF already validated by router middleware, but verify input exists
            if (!$input) {
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                return;
            }

            $pageId = (int)($input['page_id'] ?? 0);
            $content = $input['content'] ?? [];
            $title = trim($input['title'] ?? 'Untitled');
            $slug = trim($input['slug'] ?? '');
            $status = in_array($input['status'] ?? '', ['draft', 'published']) ? $input['status'] : 'draft';

            $userId = $_SESSION['admin_user_id'] ?? null;
            $revisionId = 0;

            // Ensure tb_revisions table exists
            $this->ensureRevisionsTableExists();

            // CRITICAL FIX: Use tb_encode_content() to ensure settings/design are objects not arrays
            $contentJson = tb_encode_content($content);

            if ($pageId > 0) {
                // Update existing
                $stmt = $this->db->prepare("
                    UPDATE tb_pages
                    SET title = ?, slug = ?, content_json = ?, status = ?, updated_by = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $title,
                    $slug ?: $this->generateSlug($title),
                    $contentJson,
                    $status,
                    $userId,
                    $pageId
                ]);

                // Create revision (ignore failures)
                try {
                    $revisionId = tb_create_revision($pageId, $content, $userId);
                } catch (\Throwable $e) {
                    error_log("Theme Builder: Failed to create revision: " . $e->getMessage());
                }
            } else {
                // Create new
                $stmt = $this->db->prepare("
                    INSERT INTO tb_pages (title, slug, content_json, status, created_by, updated_by)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $title,
                    $slug ?: $this->generateSlug($title),
                    $contentJson,
                    $status,
                    $userId,
                    $userId
                ]);
                $pageId = (int)$this->db->lastInsertId();

                // Create initial revision (ignore failures)
                try {
                    $revisionId = tb_create_revision($pageId, $content, $userId);
                } catch (\Throwable $e) {
                    error_log("Theme Builder: Failed to create initial revision: " . $e->getMessage());
                }
            }

            // Sync rendered HTML to pages table if this TB page is linked to a regular page
            try {
                $this->syncToPages($pageId, $content, $title, $status);
            } catch (\Throwable $e) {
                error_log("Theme Builder: Failed to sync to pages: " . $e->getMessage());
            }

            $response = [
                'success' => true,
                'page_id' => $pageId,
                'revision_id' => $revisionId,
                'message' => 'Page saved successfully'
            ];
            $json = json_encode($response);
            error_log("TB_SAVE: Success response length = " . strlen($json));
            echo $json;
        } catch (\Throwable $e) {
            error_log("TB_SAVE: Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $response = [
                'success' => false,
                'error' => 'Failed to save page: ' . $e->getMessage()
            ];
            $json = json_encode($response);
            error_log("TB_SAVE: Error response length = " . strlen($json));
            echo $json;
        }
    }

    /**
     * Ensure tb_revisions table exists
     */
    private function ensureRevisionsTableExists(): void
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'tb_revisions'");
        if (!$stmt->fetch()) {
            $this->db->exec("CREATE TABLE tb_revisions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_id INT UNSIGNED NOT NULL,
                content_json LONGTEXT NOT NULL,
                user_id INT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_page_id (page_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }
    
    /**
     * AJAX: Get page content (GET)
     */
    public function getContent(Request $request): void
    {
        header('Content-Type: application/json');
        
        $pageId = (int)$request->param('id');
        $content = tb_get_page_content($pageId);
        
        if ($content) {
            echo json_encode([
                'success' => true,
                'content' => $content
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Page not found',
                'contentJson' => '{"sections":[]}'
            ]);
        }
    }
    
    /**
     * AJAX: Get available modules
     */
    public function getModules(Request $request): void
    {
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => true,
            'modules' => tb_get_modules_json(),
            'categoriesJson' => json_encode(tb_get_category_labels(), JSON_UNESCAPED_UNICODE)
        ]);
    }
    
    /**
     * AJAX: Get revision
     */
    public function getRevision(Request $request): void
    {
        header('Content-Type: application/json');
        
        $revisionId = (int)$request->param('revision_id');
        $revision = tb_get_revision($revisionId);
        
        if ($revision) {
            echo json_encode([
                'success' => true,
                'revision' => $revision
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Revision not found'
            ]);
        }
    }
    
    /**
     * AJAX: Restore revision (POST, CSRF validated)
     */
    public function restoreRevision(Request $request): void
    {
        header('Content-Type: application/json');

        // Parse JSON input - use cached data if available
        $input = $GLOBALS['_JSON_DATA'] ?? null;
        if (!$input) {
            $input = json_decode(file_get_contents('php://input'), true);
        }

        // CSRF already validated by router middleware
        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            return;
        }

        $revisionId = (int)($input['revision_id'] ?? 0);
        
        $revision = tb_get_revision($revisionId);
        if (!$revision) {
            echo json_encode(['success' => false, 'error' => 'Revision not found']);
            return;
        }
        
        $pageId = (int)$revision['page_id'];
        $content = json_decode($revision['content_json'], true);
        $userId = $_SESSION['admin_user_id'] ?? null;
        
        // Update page with revision content
        $stmt = $this->db->prepare("
            UPDATE tb_pages 
            SET content_json = ?, updated_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $revision['content_json'],
            $userId,
            $pageId
        ]);
        
        // Create new revision marking it as a restore
        tb_create_revision($pageId, $content, $userId);
        
        echo json_encode([
            'success' => true,
            'content' => $content,
            'message' => 'Revision restored successfully'
        ]);
    }
    
    /**
     * AJAX: Preview rendered HTML
     */
    public function preview(Request $request): void
    {
        header('Content-Type: application/json');

        // Parse JSON input - use cached data if available
        $input = $GLOBALS['_JSON_DATA'] ?? null;
        if (!$input) {
            $input = json_decode(file_get_contents('php://input'), true);
        }
        
        $content = $input['content'] ?? ['sections' => []];
        $pageId = (int)($input['page_id'] ?? 0);
        $templateId = (int)($input['template_id'] ?? 0);
        $templateType = $input['template_type'] ?? '';

        // Handle template preview (both existing and new templates)
        if ($templateId > 0 || !empty($templateType)) {
            $previewKey = $templateId > 0 ? 'tb_template_preview_' . $templateId : 'tb_template_preview_new';
            $_SESSION[$previewKey] = [
                'content' => $content,
                'template_type' => $templateType,
                'timestamp' => time()
            ];
            
            $previewUrl = $templateId > 0 
                ? '/preview/template/' . $templateId . '?session=1'
                : '/preview/template/new?session=1';
                
            echo json_encode([
                'success' => true,
                'preview_url' => $previewUrl
            ]);
            return;
        }

        // For existing pages, save preview content to session and return URL
        if ($pageId > 0) {
            // Get page slug
            $stmt = $this->db->prepare("SELECT slug FROM tb_pages WHERE id = ?");
            $stmt->execute([$pageId]);
            $page = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($page && !empty($page['slug'])) {
                // Save preview content to session
                $_SESSION['tb_preview_' . $pageId] = [
                    'content' => $content,
                    'timestamp' => time()
                ];
                
                // Return preview URL
                echo json_encode([
                    'success' => true,
                    'preview_url' => '/preview/tb/' . $pageId . '?session=1'
                ]);
                return;
            }
        }

        // Fallback for new pages or if slug not found - render HTML
        $html = tb_render_page($content, [
            'preview_mode' => true,
            'wrap_in_container' => true
        ]);

        echo json_encode([
            'success' => true,
            'html' => $html
        ]);
    }
    
    /**
     * Delete TB page
     */
    public function destroy(Request $request): void
    {
        csrf_validate_or_403();
        
        $id = (int)$request->param('id');
        
        // Delete revisions first
        $stmt = $this->db->prepare("DELETE FROM tb_revisions WHERE page_id = ?");
        $stmt->execute([$id]);
        
        // Delete page
        $stmt = $this->db->prepare("DELETE FROM tb_pages WHERE id = ?");
        $stmt->execute([$id]);
        
        Session::flash('success', 'Page deleted successfully.');
        Response::redirect('/admin/theme-builder');
    }
    
    /**
     * AJAX: Generate AI content for module fields (POST, CSRF validated)
     */
    public function generateContent(Request $request): void
    {
        header('Content-Type: application/json');

        // Parse JSON input - use cached data if available
        $input = $GLOBALS['_JSON_DATA'] ?? null;
        if (!$input) {
            $input = json_decode(file_get_contents('php://input'), true);
        }

        // CSRF already validated by router middleware
        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            return;
        }

        $type = $input['type'] ?? 'text';
        $context = trim($input['context'] ?? '');
        $pageTitle = trim($input['page_title'] ?? '');

        if (empty($context)) {
            echo json_encode(['success' => false, 'error' => 'Context is required']);
            return;
        }

        // Load AI settings
        $aiSettingsPath = \CMS_ROOT . '/config/ai_settings.json';
        if (!file_exists($aiSettingsPath)) {
            echo json_encode(['success' => false, 'error' => 'AI settings not configured']);
            return;
        }

        $aiSettings = json_decode(file_get_contents($aiSettingsPath), true);
        $provider = $aiSettings['default_provider'] ?? 'openai';
        $providerConfig = $aiSettings['providers'][$provider] ?? null;

        if (!$providerConfig || empty($providerConfig['api_key'])) {
            echo json_encode(['success' => false, 'error' => 'AI provider not configured']);
            return;
        }

        // Build prompt based on content type
        $prompts = [
            'heading' => "Generate a compelling, concise heading (max 10 words) for a website section about: {$context}. Page context: {$pageTitle}. Return only the heading text, no quotes or extra formatting.",
            'text' => "Write 2-3 engaging paragraphs for a website section about: {$context}. Page context: {$pageTitle}. Use professional but friendly tone. Return only the text.",
            'button' => "Generate a short, action-oriented button text (2-4 words) for: {$context}. Page context: {$pageTitle}. Return only the button text, no quotes.",
            'cta_title' => "Generate a powerful call-to-action title (5-10 words) for: {$context}. Page context: {$pageTitle}. Return only the title text.",
            'cta_subtitle' => "Generate a brief, persuasive subtitle (1-2 sentences) supporting this CTA: {$context}. Page context: {$pageTitle}. Return only the subtitle text.",
            'cta_button' => "Generate an action-oriented CTA button text (2-4 words) for: {$context}. Return only the button text.",
            'testimonial' => "Generate a realistic, positive customer testimonial quote (2-3 sentences) about: {$context}. Page context: {$pageTitle}. Make it specific and believable. Return only the quote text, no attribution.",
            'pricing_features' => "Generate 5 short feature bullet points for a pricing plan about: {$context}. Page context: {$pageTitle}. Return each feature on a new line, no bullets or numbers.",
            'quote' => "Generate an inspiring or thought-provoking quote about: {$context}. Page context: {$pageTitle}. Return only the quote text, no attribution.",
            'hero_title' => "Generate a powerful, attention-grabbing hero headline (5-10 words) for: {$context}. Page context: {$pageTitle}. Return only the headline text, no quotes.",
            'hero_subtitle' => "Generate a compelling hero subtitle (1-2 sentences) that supports the main headline about: {$context}. Page context: {$pageTitle}. Return only the subtitle text.",
            'hero_button' => "Generate a short, action-oriented CTA button text (2-4 words) for a hero section about: {$context}. Examples: 'Get Started', 'Learn More', 'Start Free Trial'. Return only the button text.",
            'blurb_title' => "Generate a concise feature or service title (3-6 words) for: {$context}. Page context: {$pageTitle}. Return only the title.",
            'blurb_text' => "Generate a brief, engaging description (2-3 sentences) for a feature or service about: {$context}. Page context: {$pageTitle}. Return only the description.",
            'toggle_title' => "Generate a clear FAQ or accordion question title (5-12 words) for: {$context}. Page context: {$pageTitle}. Return only the question.",
            'toggle_content' => "Generate a helpful, detailed answer (2-4 sentences) for a FAQ about: {$context}. Page context: {$pageTitle}. Return only the answer content.",
            'counter_title' => "Generate a short statistic label (2-5 words) for: {$context}. Examples: 'Happy Clients', 'Projects Completed', 'Years Experience'. Return only the label.",
            'bar_counters' => "Generate a skill or progress label (2-4 words) for: {$context}. Examples: 'Web Development', 'Customer Satisfaction'. Return only the label.",
        ];

        $prompt = $prompts[$type] ?? "Generate short, professional content about: {$context}. Page context: {$pageTitle}. Return only the content.";

        try {
            $content = $this->callAIProvider($provider, $providerConfig, $prompt, $aiSettings);

            // For pricing_features, split into array
            if ($type === 'pricing_features') {
                $features = array_filter(array_map('trim', explode("\n", $content)));
                echo json_encode(['success' => true, 'content' => $features, 'type' => 'array']);
            } else {
                echo json_encode(['success' => true, 'content' => $content, 'type' => 'string']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'AI generation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Call AI provider API
     */
    private function callAIProvider(string $provider, array $config, string $prompt, array $settings): string
    {
        $defaults = $settings['generation_defaults'] ?? [];
        $temperature = $defaults['temperature'] ?? 0.7;
        $maxTokens = $defaults['max_tokens'] ?? 500;

        switch ($provider) {
            case 'openai':
                return $this->callOpenAI($config, $prompt, $temperature, $maxTokens);
            case 'anthropic':
                return $this->callAnthropic($config, $prompt, $temperature, $maxTokens);
            default:
                return $this->callOpenAI($config, $prompt, $temperature, $maxTokens);
        }
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(array $config, string $prompt, float $temperature, int $maxTokens): string
    {
        $model = $config['default_model'] ?? 'gpt-5.2';

        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional website copywriter. Rules: 1) Output ONLY the requested content with no explanations, preambles, or meta-commentary. 2) Never wrap output in quotes. 3) Never say "Here is..." or similar phrases. 4) Be professional, engaging, and conversion-focused. 5) Match the exact format requested (heading, paragraph, button text, etc.).'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $config['api_key']
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new \Exception('API request failed: ' . curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            throw new \Exception($result['error']['message'] ?? 'API request failed');
        }

        return trim($result['choices'][0]['message']['content'] ?? '');
    }

    /**
     * Call Anthropic API
     */
    private function callAnthropic(array $config, string $prompt, float $temperature, int $maxTokens): string
    {
        $model = $config['default_model'] ?? 'claude-opus-4-5-20251101';

        $data = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'system' => 'You are a professional website copywriter. Rules: 1) Output ONLY the requested content with no explanations, preambles, or meta-commentary. 2) Never wrap output in quotes. 3) Never say "Here is..." or similar phrases. 4) Be professional, engaging, and conversion-focused. 5) Match the exact format requested (heading, paragraph, button text, etc.).'
        ];

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $config['api_key'],
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new \Exception('API request failed: ' . curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            throw new \Exception($result['error']['message'] ?? 'API request failed');
        }

        return trim($result['content'][0]['text'] ?? '');
    }

    /**
     * Sync rendered Theme Builder content to pages table
     * This ensures public pages display the TB content
     */
    private function syncToPages(int $tbPageId, array $contentData, string $title, string $status): void
    {
        // Get the TB page
        $stmt = $this->db->prepare("SELECT page_id, slug FROM tb_pages WHERE id = ?");
        $stmt->execute([$tbPageId]);
        $tbPage = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$tbPage) {
            return;
        }
        
        // Render the Theme Builder content to HTML
        require_once CMS_ROOT . "/core/theme-builder/renderer.php";
        $renderedHtml = tb_render_page($contentData, ["mode" => "published"]);
        
        if ($tbPage["page_id"]) {
            // Update existing page
            $regularPageId = (int)$tbPage["page_id"];
            $stmt = $this->db->prepare("
                UPDATE pages 
                SET content = ?, title = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$renderedHtml, $title, $status, $regularPageId]);
        } else {
            // Create new page in pages table
            $slug = $tbPage["slug"];
            $stmt = $this->db->prepare("
                INSERT INTO pages (slug, title, content, status, template, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'default', NOW(), NOW())
            ");
            $stmt->execute([$slug, $title, $renderedHtml, $status]);
            $newPageId = (int)$this->db->lastInsertId();
            
            // Link TB page to the new pages entry
            $stmt = $this->db->prepare("UPDATE tb_pages SET page_id = ? WHERE id = ?");
            $stmt->execute([$newPageId, $tbPageId]);
        }
    }

    /**
     * Generate slug from title
     */
    private function generateSlug(string $title): string
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-') ?: 'page-' . time();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // THEME TEMPLATES (Header, Footer, Archive, etc.)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * List all theme templates
     */
    public function templates(Request $request): void
    {
        tb_ensure_templates_table();

        $templates = tb_get_templates();
        $templateTypes = tb_get_template_types();

        // Group templates by type
        $grouped = [];
        foreach ($templateTypes as $type => $info) {
            $grouped[$type] = [
                'info' => $info,
                'items' => array_filter($templates, fn($t) => $t['type'] === $type)
            ];
        }

        render('admin/theme-builder/templates', [
            'grouped' => $grouped,
            'templateTypes' => $templateTypes,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * Create new template
     */
    public function createTemplate(Request $request): void
    {
        tb_ensure_templates_table();

        $type = $request->param('type') ?? 'header';
        $validTypes = array_keys(tb_get_template_types());

        if (!in_array($type, $validTypes)) {
            Session::flash('error', 'Invalid template type.');
            Response::redirect('/admin/theme-builder/templates');
            return;
        }

        $typeInfo = tb_get_template_types()[$type];

        render('admin/theme-builder/template-edit', [
            'tplRecord' => null,
            'templateId' => 0,
            'templateType' => $type,
            'typeInfo' => $typeInfo,
            'contentJson' => '{"sections":[]}',
            'modulesJson' => json_encode(tb_get_all_modules(), JSON_UNESCAPED_UNICODE),
            'categoriesJson' => json_encode(tb_get_category_labels(), JSON_UNESCAPED_UNICODE),
            'themeColorsJson' => json_encode($this->loadThemeColors(), JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * Edit existing template
     */
    public function editTemplate(Request $request): void
    {
        tb_ensure_templates_table();

        $id = (int)$request->param('id');
        $template = tb_get_template($id);

        if (!$template) {
            Session::flash('error', 'Template not found.');
            Response::redirect('/admin/theme-builder/templates');
            return;
        }

        $typeInfo = tb_get_template_types()[$template['type']] ?? [
            'label' => ucfirst($template['type']),
            'description' => '',
            'icon' => '📄'
        ];

        render('admin/theme-builder/template-edit', [
            'tplRecord' => $template,
            'templateId' => $id,
            'templateType' => $template['type'],
            'typeInfo' => $typeInfo,
            'contentJson' => $template['content_json'] ?? '{"sections":[]}',
            'modulesJson' => json_encode(tb_get_all_modules(), JSON_UNESCAPED_UNICODE),
            'categoriesJson' => json_encode(tb_get_category_labels(), JSON_UNESCAPED_UNICODE),
            'themeColorsJson' => json_encode($this->loadThemeColors(), JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * AJAX: Save template (POST, CSRF validated)
     */
    public function saveTemplate(Request $request): void
    {
        header('Content-Type: application/json');

        // Parse JSON input - use cached data if available (set by router CSRF middleware)
        $input = $GLOBALS['_JSON_DATA'] ?? null;
        if (!$input) {
            $input = json_decode(file_get_contents('php://input'), true);
        }

        // CSRF already validated by router middleware, but check again for safety
        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            return;
        }

        tb_ensure_templates_table();

        $templateId = (int)($input['template_id'] ?? 0);
        $userId = $_SESSION['admin_user_id'] ?? null;

        $data = [
            'type' => $input['type'] ?? 'header',
            'name' => trim($input['name'] ?? 'Untitled'),
            'content' => $input['content'] ?? ['sections' => []],
            'conditions' => $input['conditions'] ?? null,
            'priority' => (int)($input['priority'] ?? 0),
            'is_active' => (int)($input['is_active'] ?? 1),
            'created_by' => $userId,
            'updated_by' => $userId
        ];

        $id = tb_save_template($data, $templateId ?: null);

        echo json_encode([
            'success' => true,
            'template_id' => $id,
            'message' => $templateId ? 'Template updated successfully' : 'Template created successfully'
        ]);
    }

    /**
     * Delete template (POST, CSRF validated)
     */
    public function deleteTemplate(Request $request): void
    {
        csrf_validate_or_403();

        $id = (int)$request->param('id');

        if (tb_delete_template($id)) {
            Session::flash('success', 'Template deleted successfully.');
        } else {
            Session::flash('error', 'Failed to delete template.');
        }

        Response::redirect('/admin/theme-builder/templates');
    }

    /**
     * Toggle template active status (POST, CSRF validated)
     */
    public function toggleTemplate(Request $request): void
    {
        csrf_validate_or_403();

        $id = (int)$request->param('id');
        $newStatus = tb_toggle_template($id);

        Session::flash('success', 'Template ' . ($newStatus ? 'activated' : 'deactivated') . ' successfully.');
        Response::redirect('/admin/theme-builder/templates');
    }

    /**
     * Duplicate template (POST, CSRF validated)
     */
    public function duplicateTemplate(Request $request): void
    {
        csrf_validate_or_403();

        $id = (int)$request->param('id');
        $template = tb_get_template($id);

        if (!$template) {
            Session::flash('error', 'Template not found.');
            Response::redirect('/admin/theme-builder/templates');
            return;
        }

        $userId = $_SESSION['admin_user_id'] ?? null;

        $newId = tb_save_template([
            'type' => $template['type'],
            'name' => $template['name'] . ' (Copy)',
            'content_json' => $template['content_json'],
            'conditions' => $template['conditions'],
            'priority' => 0,
            'is_active' => 0,
            'created_by' => $userId,
            'updated_by' => $userId
        ]);

        Session::flash('success', 'Template duplicated successfully.');
        Response::redirect('/admin/theme-builder/templates/' . $newId . '/edit');
    }

    /**
     * Upload template from JSON file (POST, CSRF validated via router middleware)
     */
    public function uploadTemplate(Request $request): void
    {
        header('Content-Type: application/json');

        // CSRF validation via form POST or header (router middleware handles JSON CSRF)
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!csrf_validate($token)) {
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            return;
        }

        // Check file upload
        if (!isset($_FILES['template_file']) || $_FILES['template_file']['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
                UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            ];
            $error = $errorMessages[$_FILES['template_file']['error'] ?? 0] ?? 'Unknown upload error';
            echo json_encode(['success' => false, 'error' => $error]);
            return;
        }

        $file = $_FILES['template_file'];

        // Validate extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'json') {
            echo json_encode(['success' => false, 'error' => 'Only .json files are allowed']);
            return;
        }

        // Parse JSON
        $content = file_get_contents($file['tmp_name']);
        $templateData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]);
            return;
        }

        // Validate required fields
        if (empty($templateData['name'])) {
            echo json_encode(['success' => false, 'error' => 'Missing required field: name']);
            return;
        }

        // Validate type (must be valid template type)
        $validTypes = ['header', 'footer', 'archive', 'single', 'sidebar', '404'];
        $type = $templateData['type'] ?? '';
        if (!in_array($type, $validTypes, true)) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid or missing template type. Must be one of: ' . implode(', ', $validTypes)
            ]);
            return;
        }

        // Validate content structure
        if (!isset($templateData['content']) || !is_array($templateData['content'])) {
            echo json_encode(['success' => false, 'error' => 'Missing required field: content']);
            return;
        }

        if (!isset($templateData['content']['sections']) || !is_array($templateData['content']['sections'])) {
            echo json_encode(['success' => false, 'error' => 'Missing required field: content.sections']);
            return;
        }

        tb_ensure_templates_table();

        $userId = $_SESSION['admin_user_id'] ?? null;

        // Prepare template data for saving
        $saveData = [
            'type' => $type,
            'name' => trim($templateData['name']),
            'content' => $templateData['content'],
            'conditions' => $templateData['conditions'] ?? null,
            'priority' => (int)($templateData['priority'] ?? 0),
            'is_active' => 0, // Import as inactive by default
            'created_by' => $userId,
            'updated_by' => $userId
        ];

        try {
            $templateId = tb_save_template($saveData);

            echo json_encode([
                'success' => true,
                'template_id' => $templateId,
                'name' => $saveData['name'],
                'type' => $type,
                'message' => "Template '{$saveData['name']}' imported successfully as {$type}"
            ]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * Set page as homepage (POST, CSRF validated)
     */
    public function setHomepage(Request $request): void
    {
        csrf_validate_or_403();

        $id = (int)$request->param('id');

        // Verify page exists
        $stmt = $this->db->prepare("SELECT id, title FROM tb_pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$page) {
            Session::flash('error', 'Page not found.');
            Response::redirect('/admin/theme-builder');
            return;
        }

        // Clear all homepage flags first
        $this->db->exec("UPDATE tb_pages SET is_homepage = 0");

        // Set this page as homepage
        $stmt = $this->db->prepare("UPDATE tb_pages SET is_homepage = 1 WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', '"' . $page['title'] . '" is now set as homepage.');
        Response::redirect('/admin/theme-builder');
    }

    /**
     * Remove homepage flag (POST, CSRF validated)
     */
    public function removeHomepage(Request $request): void
    {
        csrf_validate_or_403();

        $id = (int)$request->param('id');

        $stmt = $this->db->prepare("UPDATE tb_pages SET is_homepage = 0 WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Homepage flag removed. Static homepage will be used.');
        Response::redirect('/admin/theme-builder');
    }

    /**
     * Bulk actions for pages (JSON API)
     */
    public function bulk(Request $request): void
    {
        header('Content-Type: application/json');

        // CSRF validation from header
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!csrf_validate($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'error' => 'No pages selected']);
            return;
        }

        // Sanitize IDs
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, fn($id) => $id > 0);

        if (empty($ids)) {
            echo json_encode(['success' => false, 'error' => 'Invalid page IDs']);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        try {
            switch ($action) {
                case 'delete':
                    // Delete revisions first
                    $stmt = $this->db->prepare("DELETE FROM tb_revisions WHERE page_id IN ($placeholders)");
                    $stmt->execute($ids);
                    
                    // Delete pages
                    $stmt = $this->db->prepare("DELETE FROM tb_pages WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    
                    $count = $stmt->rowCount();
                    echo json_encode(['success' => true, 'message' => "$count page(s) deleted", 'deleted' => $count]);
                    break;

                case 'publish':
                    $stmt = $this->db->prepare("UPDATE tb_pages SET status = 'published' WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    
                    $count = $stmt->rowCount();
                    echo json_encode(['success' => true, 'message' => "$count page(s) published", 'updated' => $count]);
                    break;

                case 'draft':
                    $stmt = $this->db->prepare("UPDATE tb_pages SET status = 'draft' WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    
                    $count = $stmt->rowCount();
                    echo json_encode(['success' => true, 'message' => "$count page(s) set to draft", 'updated' => $count]);
                    break;

                default:
                    echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Load theme colors from active theme's theme.json
     * Returns array of semantic color names with their hex values
     */
    private function loadThemeColors(): array
    {
        // Default theme colors
        $defaultColors = [
            'primary' => ['label' => 'Primary', 'value' => '#1e40af', 'var' => 'var(--color-primary)'],
            'secondary' => ['label' => 'Secondary', 'value' => '#3b82f6', 'var' => 'var(--color-secondary)'],
            'accent' => ['label' => 'Accent', 'value' => '#f59e0b', 'var' => 'var(--color-accent)'],
            'background' => ['label' => 'Background', 'value' => '#0f172a', 'var' => 'var(--color-background)'],
            'surface' => ['label' => 'Surface', 'value' => '#1e293b', 'var' => 'var(--color-surface)'],
            'text' => ['label' => 'Text', 'value' => '#f1f5f9', 'var' => 'var(--color-text)'],
            'text_muted' => ['label' => 'Text Muted', 'value' => '#a0a0b0', 'var' => 'var(--color-text-muted)'],
            'border' => ['label' => 'Border', 'value' => '#2d2d3a', 'var' => 'var(--color-border)'],
        ];
        
        // Get active theme
        $activeTheme = 'default';
        if (class_exists('SettingsModel')) {
            $activeTheme = \SettingsModel::getActiveTheme() ?: 'default';
        }
        
        // Load theme.json
        $themePath = \CMS_ROOT . '/themes/' . $activeTheme . '/theme.json';
        if (file_exists($themePath)) {
            $themeData = json_decode(file_get_contents($themePath), true);
            if (!empty($themeData['colors'])) {
                foreach ($themeData['colors'] as $key => $value) {
                    if (isset($defaultColors[$key])) {
                        $defaultColors[$key]['value'] = $value;
                    }
                }
            }
        }
        
        return $defaultColors;
    }
}
