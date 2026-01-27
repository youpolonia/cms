<?php
declare(strict_types=1);
/**
 * AI Theme Builder 5.0 Controller
 *
 * Fully integrated with JTB (Jessie Theme Builder)
 * Uses JTB Parser for HTML to module conversion
 *
 * @package AiThemeBuilder
 * @version 5.0
 */

namespace App\Controllers\Admin;

class AiTb5Controller
{
    private \PDO $db;
    private array $aiSettings = [];
    private string $jtbPluginPath;

    public function __construct()
    {
        // Support both database access patterns (case-sensitive namespaces)
        if (class_exists('\core\Database')) {
            $this->db = \core\Database::connection();
        } elseif (class_exists('\Core\Database')) {
            $this->db = \Core\Database::getInstance()->getConnection();
        } else {
            throw new \Exception('Database class not found');
        }
        $this->jtbPluginPath = CMS_ROOT . '/plugins/jessie-theme-builder';
        $this->loadAiSettings();
    }

    /**
     * Load AI configuration
     */
    private function loadAiSettings(): void
    {
        $aiSettingsPath = CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($aiSettingsPath)) {
            $this->aiSettings = json_decode(file_get_contents($aiSettingsPath), true) ?: [];
        }
    }

    /**
     * Check if AI is configured
     */
    private function isAiConfigured(): bool
    {
        $provider = $this->aiSettings['default_provider'] ?? 'openai';
        $config = $this->aiSettings['providers'][$provider] ?? [];
        return !empty($config['api_key']);
    }

    /**
     * Main wizard view
     */
    public function index(): void
    {
        if (!$this->isAiConfigured()) {
            header('Location: /admin/ai-settings?error=ai_not_configured');
            exit;
        }

        // Get available AI models
        $models = $this->getAvailableModels();

        // Pass data to view
        $data = [
            'models' => $models,
            'defaultModel' => $this->aiSettings['providers']['openai']['default_model'] ?? 'gpt-5.2',
            'csrfToken' => $_SESSION['csrf_token'] ?? ''
        ];

        include CMS_ROOT . '/app/views/admin/ai-theme-builder-v5/index.php';
    }

    /**
     * Get available AI models
     */
    /**
     * Get available AI models
     */
    private function getAvailableModels(): array
    {
        $models = [];

        // OpenAI models - show if provider is enabled
        if ($this->aiSettings['providers']['openai']['enabled'] ?? false) {
            $openaiModels = $this->aiSettings['providers']['openai']['models'] ?? [];
            $hasKey = !empty($this->aiSettings['providers']['openai']['api_key']);
            foreach ($openaiModels as $id => $model) {
                if (!empty($model['legacy'])) continue;
                $models[] = [
                    'id' => $id,
                    'name' => $model['name'] ?? $id,
                    'provider' => 'openai',
                    'available' => $hasKey,
                    'recommended' => $model['recommended'] ?? false
                ];
            }
        }

        // Anthropic models - show if provider is enabled
        if ($this->aiSettings['providers']['anthropic']['enabled'] ?? false) {
            $anthropicModels = $this->aiSettings['providers']['anthropic']['models'] ?? [];
            $hasKey = !empty($this->aiSettings['providers']['anthropic']['api_key']);
            foreach ($anthropicModels as $id => $model) {
                if (!empty($model['legacy'])) continue;
                $models[] = [
                    'id' => $id,
                    'name' => $model['name'] ?? $id,
                    'provider' => 'anthropic',
                    'available' => $hasKey,
                    'recommended' => $model['recommended'] ?? false
                ];
            }
        }

        // Google/Gemini models
        if ($this->aiSettings['providers']['google']['enabled'] ?? false) {
            $googleModels = $this->aiSettings['providers']['google']['models'] ?? [];
            $hasKey = !empty($this->aiSettings['providers']['google']['api_key']);
            foreach ($googleModels as $id => $model) {
                $models[] = [
                    'id' => $id,
                    'name' => $model['name'] ?? $id,
                    'provider' => 'google',
                    'available' => $hasKey
                ];
            }
        }

        // DeepSeek models
        if ($this->aiSettings['providers']['deepseek']['enabled'] ?? false) {
            $deepseekModels = $this->aiSettings['providers']['deepseek']['models'] ?? [];
            $hasKey = !empty($this->aiSettings['providers']['deepseek']['api_key']);
            foreach ($deepseekModels as $id => $model) {
                $models[] = [
                    'id' => $id,
                    'name' => $model['name'] ?? $id,
                    'provider' => 'deepseek',
                    'available' => $hasKey
                ];
            }
        }

        // Sort: recommended first
        usort($models, function($a, $b) {
            if (($a['recommended'] ?? false) !== ($b['recommended'] ?? false)) {
                return ($b['recommended'] ?? false) ? 1 : -1;
            }
            return 0;
        });

        return $models;
    }

    // =====================================================
    // API ENDPOINTS
    // =====================================================

    /**
     * AJAX: Generate complete theme
     * POST /api/jtb/ai/generate
     */
    public function generate(): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                throw new \Exception('Invalid JSON input');
            }

            // Validate required fields
            $brief = trim($input['brief'] ?? '');
            if (empty($brief)) {
                throw new \Exception('Project brief is required');
            }

            $businessName = trim($input['business_name'] ?? 'My Business');
            $industry = trim($input['industry'] ?? 'general');
            $style = trim($input['style'] ?? 'modern');
            $model = trim($input['model'] ?? '');
            $pages = $input['pages'] ?? ['Home'];

            // Load prompts
            require_once CMS_ROOT . '/core/theme-builder/ai-html-prompts.php';

            // Initialize result
            $result = [
                'header' => null,
                'pages' => [],
                'footer' => null,
                'meta' => [
                    'project_name' => $input['project_name'] ?? 'untitled',
                    'business_name' => $businessName,
                    'industry' => $industry,
                    'style' => $style,
                    'ai_model' => $model,
                    'generated_at' => date('Y-m-d H:i:s')
                ]
            ];

            // Generate Header
            $headerHtml = $this->generateHeaderHtml($businessName, $style, $pages, $model);
            $result['header'] = $this->parseHtmlToJtb($headerHtml, $businessName . ' Header');

            // Generate each page
            foreach ($pages as $pageName) {
                $sections = $this->getSectionsForPage($pageName);
                $pageHtml = $this->generatePageHtml($brief, $businessName, $industry, $style, $sections, $pageName, $model);
                $result['pages'][] = $this->parseHtmlToJtb($pageHtml, $pageName, $this->slugify($pageName));
            }

            // Generate Footer
            $footerHtml = $this->generateFooterHtml($businessName, $style, $pages, $model);
            $result['footer'] = $this->parseHtmlToJtb($footerHtml, $businessName . ' Footer');

            echo json_encode(['success' => true, 'theme' => $result]);

        } catch (\Exception $e) {
            error_log('[AI-TB5] Generate error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * AJAX: Regenerate single component
     * POST /api/jtb/ai/regenerate
     */
    public function regenerate(): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $type = $input['type'] ?? '';
            $businessName = $input['business_name'] ?? 'My Business';
            $style = $input['style'] ?? 'modern';
            $model = $input['model'] ?? '';
            $pages = $input['pages'] ?? ['Home'];
            $brief = $input['brief'] ?? '';
            $pageIndex = $input['page_index'] ?? 0;

            require_once CMS_ROOT . '/core/theme-builder/ai-html-prompts.php';

            $result = null;

            if ($type === 'header') {
                $html = $this->generateHeaderHtml($businessName, $style, $pages, $model);
                $result = $this->parseHtmlToJtb($html, $businessName . ' Header');
            } elseif ($type === 'footer') {
                $html = $this->generateFooterHtml($businessName, $style, $pages, $model);
                $result = $this->parseHtmlToJtb($html, $businessName . ' Footer');
            } elseif ($type === 'page') {
                $pageName = $pages[$pageIndex] ?? 'Home';
                $sections = $this->getSectionsForPage($pageName);
                $html = $this->generatePageHtml($brief, $businessName, $input['industry'] ?? 'general', $style, $sections, $pageName, $model);
                $result = $this->parseHtmlToJtb($html, $pageName, $this->slugify($pageName));
            }

            if ($result) {
                echo json_encode(['success' => true, 'component' => $result]);
            } else {
                throw new \Exception('Unknown component type: ' . $type);
            }

        } catch (\Exception $e) {
            error_log('[AI-TB5] Regenerate error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * AJAX: Fetch images for placeholders
     * POST /api/jtb/ai/fetch-images
     */
    public function fetchImages(): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $theme = $input['theme'] ?? null;
            $source = $input['image_source'] ?? 'pexels';

            if (!$theme) {
                throw new \Exception('Theme data required');
            }

            // Process header
            if ($theme['header']) {
                $theme['header'] = $this->fillImagesInComponent($theme['header'], $source);
            }

            // Process pages
            foreach ($theme['pages'] as $i => $page) {
                $theme['pages'][$i] = $this->fillImagesInComponent($page, $source);
            }

            // Process footer
            if ($theme['footer']) {
                $theme['footer'] = $this->fillImagesInComponent($theme['footer'], $source);
            }

            echo json_encode(['success' => true, 'theme' => $theme]);

        } catch (\Exception $e) {
            error_log('[AI-TB5] Fetch images error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * AJAX: Deploy theme to CMS
     * POST /api/jtb/ai/deploy
     */
    public function deploy(): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $theme = $input['theme'] ?? null;
            $options = $input['options'] ?? [];

            if (!$theme) {
                throw new \Exception('Theme data required');
            }

            $deployed = [
                'templates' => [],
                'pages' => [],
                'library' => []
            ];

            // Save to JTB Templates (header/footer)
            if (!empty($options['save_to_templates'])) {
                if ($theme['header']) {
                    $id = $this->saveToJtbTemplates('header', $theme['header']);
                    $deployed['templates'][] = ['type' => 'header', 'id' => $id];
                }
                if ($theme['footer']) {
                    $id = $this->saveToJtbTemplates('footer', $theme['footer']);
                    $deployed['templates'][] = ['type' => 'footer', 'id' => $id];
                }
            }

            // Save pages to Layout Library
            if (!empty($options['save_to_library'])) {
                foreach ($theme['pages'] as $page) {
                    $id = $this->saveToLayoutLibrary($page);
                    $deployed['library'][] = ['title' => $page['title'], 'id' => $id];
                }
            }

            // Create actual CMS pages
            if (!empty($options['create_pages'])) {
                foreach ($theme['pages'] as $page) {
                    $id = $this->createCmsPage($page);
                    $deployed['pages'][] = ['title' => $page['title'], 'id' => $id];
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Theme deployed successfully',
                'deployed' => $deployed
            ]);

        } catch (\Exception $e) {
            error_log('[AI-TB5] Deploy error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // =====================================================
    // HTML GENERATION METHODS
    // =====================================================

    /**
     * Generate header HTML via AI
     */
    private function generateHeaderHtml(string $businessName, string $style, array $navItems, string $model): string
    {
        $systemPrompt = tb_get_html_system_prompt();
        $userPrompt = tb_get_html_header_prompt($businessName, $style, $navItems);

        $response = $this->callAI($systemPrompt, $userPrompt, $model, 4000, false);
        return tb_clean_html_response($response);
    }

    /**
     * Generate footer HTML via AI
     */
    private function generateFooterHtml(string $businessName, string $style, array $navItems, string $model): string
    {
        $systemPrompt = tb_get_html_system_prompt();
        $userPrompt = tb_get_html_footer_prompt($businessName, $style, $navItems);

        $response = $this->callAI($systemPrompt, $userPrompt, $model, 4000, false);
        return tb_clean_html_response($response);
    }

    /**
     * Generate page HTML via AI
     */
    private function generatePageHtml(string $brief, string $businessName, string $industry, string $style, array $sections, string $pageName, string $model): string
    {
        $systemPrompt = tb_get_html_system_prompt();

        $enhancedBrief = $brief;
        if ($pageName !== 'Home' && $pageName !== 'Homepage') {
            $enhancedBrief .= "\n\nThis is the {$pageName} page. Focus on content relevant to this page type.";
        }

        $userPrompt = tb_get_html_user_prompt($enhancedBrief, $businessName, $industry, $style, $sections);

        $response = $this->callAI($systemPrompt, $userPrompt, $model, 8000, false);
        return tb_clean_html_response($response);
    }

    /**
     * Get appropriate sections for a page type
     */
    private function getSectionsForPage(string $pageName): array
    {
        $pageSections = [
            'Home' => ['hero', 'features', 'about', 'testimonials', 'cta'],
            'Homepage' => ['hero', 'features', 'about', 'testimonials', 'cta'],
            'About' => ['hero', 'about', 'team', 'stats', 'cta'],
            'About Us' => ['hero', 'about', 'team', 'stats', 'cta'],
            'Services' => ['hero', 'services', 'features', 'pricing', 'cta'],
            'Contact' => ['hero', 'contact', 'faq'],
            'Pricing' => ['hero', 'pricing', 'features', 'faq', 'cta'],
            'Portfolio' => ['hero', 'gallery', 'testimonials', 'cta'],
            'Blog' => ['hero', 'features', 'cta'],
            'FAQ' => ['hero', 'faq', 'contact', 'cta'],
            'Team' => ['hero', 'team', 'about', 'cta'],
            'Testimonials' => ['hero', 'testimonials', 'stats', 'cta'],
        ];

        return $pageSections[$pageName] ?? ['hero', 'features', 'about', 'cta'];
    }

    // =====================================================
    // JTB PARSER INTEGRATION
    // =====================================================

    /**
     * Parse HTML to JTB structure using JTB Parser
     */
    private function parseHtmlToJtb(string $html, string $title, string $slug = ''): array
    {
        // Load JTB Parser
        $parserPath = $this->jtbPluginPath . '/includes/parser/class-jtb-html-parser.php';

        if (!file_exists($parserPath)) {
            throw new \Exception('JTB Parser not found');
        }

        // Load dependencies
        if (!defined('CMS_ROOT')) {
            define('CMS_ROOT', '/var/www/cms');
        }

        require_once $parserPath;

        $parser = new \JessieThemeBuilder\JTB_HTML_Parser();
        $result = $parser->parse($html);

        return [
            'title' => $title,
            'slug' => $slug,
            'source_html' => $html,
            'jtb_content' => $result['content'] ?? [],
            'css' => $result['css'] ?? '',
            'stats' => $result['stats'] ?? []
        ];
    }

    // =====================================================
    // AI API METHODS
    // =====================================================

    /**
     * Call AI API (OpenAI or Anthropic)
     */
    private function callAI(string $systemPrompt, string $userPrompt, string $selectedModel = '', int $maxTokens = 4000, bool $jsonMode = true): string
    {
        $provider = $this->aiSettings['default_provider'] ?? 'openai';
        $providerConfig = $this->aiSettings['providers'][$provider] ?? [];
        $temperature = $this->aiSettings['generation_defaults']['temperature'] ?? 0.7;

        // Override with selected model
        $useAnthropic = false;
        if ($selectedModel) {
            if (str_starts_with($selectedModel, 'claude-')) {
                $useAnthropic = true;
                $providerConfig = $this->aiSettings['providers']['anthropic'] ?? [];
            }
            $providerConfig['default_model'] = $selectedModel;
        }

        if ($useAnthropic && !empty($providerConfig['api_key'])) {
            return $this->callAnthropic($providerConfig, $systemPrompt, $userPrompt, $temperature, $maxTokens);
        }

        return $this->callOpenAI($providerConfig, $systemPrompt, $userPrompt, $temperature, $maxTokens);
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(array $config, string $systemPrompt, string $userPrompt, float $temperature, int $maxTokens): string
    {
        $model = $config['default_model'] ?? 'gpt-5.2';
        $apiKey = $config['api_key'] ?? '';

        if (empty($apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }

        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new \Exception('API request failed: ' . curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            throw new \Exception($result['error']['message'] ?? 'API error ' . $httpCode);
        }

        return trim($result['choices'][0]['message']['content'] ?? '');
    }

    /**
     * Call Anthropic API
     */
    private function callAnthropic(array $config, string $systemPrompt, string $userPrompt, float $temperature, int $maxTokens): string
    {
        $model = $config['default_model'] ?? 'claude-opus-4-5-20251101';
        $apiKey = $config['api_key'] ?? '';

        if (empty($apiKey)) {
            throw new \Exception('Anthropic API key not configured');
        }

        $data = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'system' => $systemPrompt
        ];

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new \Exception('API request failed: ' . curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            throw new \Exception($result['error']['message'] ?? 'API error ' . $httpCode);
        }

        return trim($result['content'][0]['text'] ?? '');
    }

    // =====================================================
    // IMAGE PROCESSING
    // =====================================================

    /**
     * Fill image placeholders in component
     */
    private function fillImagesInComponent(array $component, string $source): array
    {
        // Replace placeholders in source_html
        if (!empty($component['source_html'])) {
            $component['source_html'] = $this->replaceImagePlaceholders($component['source_html'], $source);
        }

        // Replace in JTB content
        if (!empty($component['jtb_content'])) {
            $component['jtb_content'] = $this->fillImagesInJtbContent($component['jtb_content'], $source);
        }

        return $component;
    }

    /**
     * Replace image placeholders in HTML
     */
    private function replaceImagePlaceholders(string $html, string $source): string
    {
        // Pattern: src="PLACEHOLDER:description"
        preg_match_all('/src="PLACEHOLDER:([^"]+)"/', $html, $matches);

        foreach ($matches[1] as $i => $description) {
            $imageUrl = $this->fetchImage($description, $source);
            if ($imageUrl) {
                $html = str_replace($matches[0][$i], 'src="' . $imageUrl . '"', $html);
            }
        }

        return $html;
    }

    /**
     * Fill images in JTB content recursively
     */
    private function fillImagesInJtbContent(array $content, string $source): array
    {
        foreach ($content as $i => $item) {
            // Check if this is an image module
            if (isset($item['type']) && $item['type'] === 'image') {
                if (isset($item['attrs']['src']) && strpos($item['attrs']['src'], 'PLACEHOLDER:') === 0) {
                    $description = substr($item['attrs']['src'], 12);
                    $imageUrl = $this->fetchImage($description, $source);
                    if ($imageUrl) {
                        $content[$i]['attrs']['src'] = $imageUrl;
                    }
                }
            }

            // Process children recursively
            if (isset($item['children']) && is_array($item['children'])) {
                $content[$i]['children'] = $this->fillImagesInJtbContent($item['children'], $source);
            }
        }

        return $content;
    }

    /**
     * Fetch image from external source
     */
    private function fetchImage(string $keywords, string $source): ?string
    {
        $keywords = trim($keywords);
        if (empty($keywords)) {
            return null;
        }

        if ($source === 'pexels') {
            return $this->fetchFromPexels($keywords);
        } elseif ($source === 'unsplash') {
            return $this->fetchFromUnsplash($keywords);
        }

        return null;
    }

    /**
     * Fetch image from Pexels
     */
    private function fetchFromPexels(string $keywords): ?string
    {
        $apiKey = $this->aiSettings['pexels_api_key'] ?? '';
        if (empty($apiKey)) {
            return null;
        }

        $url = 'https://api.pexels.com/v1/search?' . http_build_query([
            'query' => $keywords,
            'per_page' => 1,
            'orientation' => 'landscape'
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: ' . $apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['photos'][0]['src']['large'] ?? null;
    }

    /**
     * Fetch image from Unsplash
     */
    private function fetchFromUnsplash(string $keywords): ?string
    {
        $accessKey = $this->aiSettings['unsplash_access_key'] ?? '';
        if (empty($accessKey)) {
            return null;
        }

        $url = 'https://api.unsplash.com/search/photos?' . http_build_query([
            'query' => $keywords,
            'per_page' => 1,
            'orientation' => 'landscape'
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Client-ID ' . $accessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['results'][0]['urls']['regular'] ?? null;
    }

    // =====================================================
    // DEPLOYMENT METHODS
    // =====================================================

    /**
     * Save to JTB Templates (jtb_templates table)
     */
    private function saveToJtbTemplates(string $type, array $component): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO jtb_templates (name, type, content, conditions, is_active, priority, created_at)
            VALUES (?, ?, ?, NULL, 0, 0, NOW())
        ");

        $content = [
            'version' => '1.0',
            'content' => $component['jtb_content'],
            'source_html' => $component['source_html'] ?? ''
        ];

        $stmt->execute([
            $component['title'],
            $type,
            json_encode($content, JSON_UNESCAPED_UNICODE)
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Save to Layout Library (tb_layout_library table)
     */
    private function saveToLayoutLibrary(array $page): int
    {
        // Check if table exists
        $stmt = $this->db->query("SHOW TABLES LIKE 'tb_layout_library'");
        if ($stmt->rowCount() === 0) {
            // Create table if not exists
            $this->db->exec("
                CREATE TABLE tb_layout_library (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    slug VARCHAR(255),
                    category VARCHAR(100) DEFAULT 'page',
                    content JSON,
                    thumbnail VARCHAR(500),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }

        $stmt = $this->db->prepare("
            INSERT INTO tb_layout_library (name, slug, category, content_json, is_ai_generated, created_at)
            VALUES (?, ?, 'other', ?, 1, NOW())
        ");

        $content = [
            'version' => '1.0',
            'content' => $page['jtb_content'],
            'source_html' => $page['source_html'] ?? ''
        ];

        $uniqueSlug = ($page['slug'] ?: $this->slugify($page['title'])) . '-ai-' . substr(md5(microtime()), 0, 6);

        $stmt->execute([
            $page['title'],
            $uniqueSlug,
            json_encode($content, JSON_UNESCAPED_UNICODE)
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Create actual CMS page with JTB content
     */
    private function createCmsPage(array $page): int
    {
        // Create unique slug with suffix
        $baseSlug = $page['slug'] ?: $this->slugify($page['title']);
        $uniqueSlug = $baseSlug . '-ai-' . substr(md5(microtime()), 0, 6);

        // Create page in cms_pages table
        $stmt = $this->db->prepare("
            INSERT INTO pages (title, slug, content, status, created_at)
            VALUES (?, ?, '', 'draft', NOW())
        ");

        $stmt->execute([
            $page['title'],
            $uniqueSlug
        ]);

        $pageId = (int)$this->db->lastInsertId();

        // Save JTB content
        $stmt = $this->db->prepare("
            INSERT INTO jtb_pages (post_id, content, version, created_at)
            VALUES (?, ?, '1.0', NOW())
            ON DUPLICATE KEY UPDATE content = VALUES(content), updated_at = NOW()
        ");

        $content = [
            'version' => '1.0',
            'content' => $page['jtb_content']
        ];

        $stmt->execute([
            $pageId,
            json_encode($content, JSON_UNESCAPED_UNICODE)
        ]);

        return $pageId;
    }

    // =====================================================
    // UTILITY METHODS
    // =====================================================

    /**
     * Convert string to slug
     */
    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
}
