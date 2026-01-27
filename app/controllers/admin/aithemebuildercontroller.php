<?php
declare(strict_types=1);
/**
 * AI Theme Builder 4.0 Controller
 * Unified tool for generating complete TB 3.0 website themes
 * Generates: Header Template + Page Layouts + Footer Template
 *
 * NO CLI, pure PHP 8.1+, FTP-only, require_once only
 * DO NOT add closing ?> tag
 */

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

// Bootstrap Theme Builder 3.0 core
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 3));
}

class AiThemeBuilderController
{
    private \PDO $db;
    private array $aiSettings = [];
    private array $imageSourcePriority = ['media', 'pexels', 'unsplash'];

    public function __construct()
    {
        require_once CMS_ROOT . '/core/theme-builder/init.php';
        require_once CMS_ROOT . '/core/theme-builder/ai-design-prompts.php';
        tb_init();
        $this->db = \core\Database::connection();

        // Load AI settings
        $aiSettingsPath = CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($aiSettingsPath)) {
            $this->aiSettings = json_decode(file_get_contents($aiSettingsPath), true) ?: [];
        }
    }

    /**
     * Step 1: Show 4-step wizard interface
     */
    public function index(Request $request): void
    {
        $modules = tb_get_all_modules();
        $categories = tb_get_category_labels();

        render('admin/ai-theme-builder/index', [
            'modules' => $modules,
            'categories' => $categories,
            'modulesJson' => json_encode($modules, JSON_UNESCAPED_UNICODE),
            'categoriesJson' => json_encode($categories, JSON_UNESCAPED_UNICODE),
            'aiConfigured' => $this->isAiConfigured(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * Step 2: AJAX - Generate complete theme (Header + Pages + Footer)
     */
    public function generate(Request $request): void
    {
        header('Content-Type: application/json');

        error_log('[AI-TB4] Generate called at: ' . date('Y-m-d H:i:s'));

        // Parse JSON input
        $input = $GLOBALS['_JSON_DATA'] ?? null;
        if (!$input) {
            $input = json_decode(file_get_contents('php://input'), true);
        }

        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            exit;
        }

        $brief = trim($input['brief'] ?? '');
        $businessName = trim($input['business_name'] ?? '');
        $industry = trim($input['industry'] ?? 'general');
        $pageCount = (int)($input['page_count'] ?? 4);
        $pageNames = $input['page_names'] ?? ['Home', 'About', 'Services', 'Contact'];
        $stylePreference = trim($input['style_preference'] ?? 'modern');
        $colorScheme = $input['color_scheme'] ?? 'auto';
        $selectedModel = trim($input['model'] ?? '');
        $autoFillImages = isset($input['auto_fill_images']) ? (bool)$input['auto_fill_images'] : true;
        $imageSources = $input['image_sources'] ?? ['media', 'pexels', 'unsplash'];

        if (empty($brief)) {
            echo json_encode(['success' => false, 'error' => 'Project brief is required']);
            exit;
        }

        if ($pageCount < 1 || $pageCount > 10) {
            $pageCount = 4;
        }

        // Validate AI settings
        if (!$this->isAiConfigured()) {
            echo json_encode(['success' => false, 'error' => 'AI provider not configured. Go to Settings > AI Configuration.']);
            exit;
        }

        try {
            $result = [
                'header' => null,
                'pages' => [],
                'footer' => null,
                'meta' => [
                    'business_name' => $businessName,
                    'industry' => $industry,
                    'style' => $stylePreference,
                    'generated_at' => date('Y-m-d H:i:s')
                ]
            ];

            // Generate Header Template
            error_log('[AI-TB4] Generating header...');
            $result['header'] = $this->generateHeaderLayout($brief, $businessName, $stylePreference, $selectedModel);

            // Generate Page Layouts
            error_log('[AI-TB4] Generating ' . $pageCount . ' pages...');
            $result['pages'] = $this->generatePagesLayout($brief, $businessName, $industry, $pageNames, $stylePreference, $selectedModel);

            // Generate Footer Template
            error_log('[AI-TB4] Generating footer...');
            $result['footer'] = $this->generateFooterLayout($brief, $businessName, $stylePreference, $selectedModel);

            // Auto-fill images if enabled
            if ($autoFillImages && !empty($imageSources)) {
                error_log('[AI-TB4] Auto-filling images...');
                $result = $this->fillAllImages($result, $imageSources);
            }

            error_log('[AI-TB4] Generation complete');

            echo json_encode([
                'success' => true,
                'theme' => $result
            ]);

        } catch (\Exception $e) {
            error_log('[AI-TB4] ERROR: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Generation failed: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Step 3: AJAX - Preview rendered component
     */
    public function preview(Request $request): void
    {
        header('Content-Type: application/json');

        $input = $GLOBALS['_JSON_DATA'] ?? null;
        if (!$input) {
            $input = json_decode(file_get_contents('php://input'), true);
        }

        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            return;
        }

        $type = $input['type'] ?? 'page'; // header, page, footer
        $data = $input['data'] ?? [];

        $content = $data['content'] ?? ['sections' => []];
        $renderedHtml = tb_render_page($content, ['preview_mode' => true]);
        $html = $this->wrapPreviewHtml($renderedHtml, $data['title'] ?? 'Preview', $type);

        echo json_encode([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * AJAX: Store preview data in session and return preview URL
     * Opens full preview in new tab using tb-preview.php template
     */
    public function fullPreview(Request $request): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            return;
        }
        
        $pageData = $input['page'] ?? null;
        $pageIndex = (int)($input['page_index'] ?? 0);
        $headerData = $input['header'] ?? null;
        $footerData = $input['footer'] ?? null;
        
        if (!$pageData) {
            echo json_encode(['success' => false, 'error' => 'No page data provided']);
            return;
        }
        
        // Build full page content with header + page + footer
        $sections = [];
        
        // Add header sections if provided
        if ($headerData && !empty($headerData['content']['sections'])) {
            foreach ($headerData['content']['sections'] as $section) {
                $sections[] = $section;
            }
        }
        
        // Add page sections
        if (!empty($pageData['content']['sections'])) {
            foreach ($pageData['content']['sections'] as $section) {
                $sections[] = $section;
            }
        }
        
        // Add footer sections if provided
        if ($footerData && !empty($footerData['content']['sections'])) {
            foreach ($footerData['content']['sections'] as $section) {
                $sections[] = $section;
            }
        }
        
        $content = ['sections' => $sections];
        $pageTitle = $pageData['title'] ?? 'AI Theme Preview';
        
        // Store in session for preview
        $sessionKey = 'ai_tb_preview_' . session_id();
        $_SESSION[$sessionKey] = [
            'content' => $content,
            'title' => $pageTitle,
            'timestamp' => time()
        ];
        
        echo json_encode([
            'success' => true,
            'preview_url' => '/preview/ai-theme?key=' . urlencode($sessionKey)
        ]);
    }

    /**
     * Step 4: AJAX - Deploy theme (save to Layout Library + TB Templates)
     */
    public function deploy(Request $request): void
    {
        header('Content-Type: application/json');

        // Handle both JSON body and FormData
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // Get generated data - could be 'theme' or 'generated_data'
        $themeData = null;
        if (isset($input['theme'])) {
            $themeData = $input['theme'];
        } elseif (isset($input['generated_data'])) {
            $themeData = json_decode($input['generated_data'], true);
        }

        if (!$themeData) {
            echo json_encode(['success' => false, 'error' => 'Invalid theme data']);
            return;
        }

        // Map form field names to internal variables
        $themeName = trim($input['project_name'] ?? $input['theme_name'] ?? 'Untitled Theme');
        $saveHeader = isset($input['save_to_templates']) || ($input['save_header'] ?? false);
        $saveFooter = isset($input['save_to_templates']) || ($input['save_footer'] ?? false);
        $savePages = isset($input['save_to_library']) || ($input['save_pages'] ?? false);
        $createPages = isset($input['create_pages']) || ($input['create_cms_pages'] ?? false);
        $activateHeader = (bool)($input['activate_header'] ?? false);
        $activateFooter = (bool)($input['activate_footer'] ?? false);
        $category = $input['website_type'] ?? $input['category'] ?? 'full-site';

        $userId = $_SESSION['admin_user_id'] ?? null;
        $results = ['header_id' => null, 'footer_id' => null, 'layout_id' => null];

        try {
            $this->db->beginTransaction();

            // Save Header to tb_templates
            if ($saveHeader && !empty($themeData['header'])) {
                $headerId = $this->saveTemplate('header', $themeName . ' Header', $themeData['header'], $activateHeader, $userId);
                $results['header_id'] = $headerId;
            }

            // Save Footer to tb_templates
            if ($saveFooter && !empty($themeData['footer'])) {
                $footerId = $this->saveTemplate('footer', $themeName . ' Footer', $themeData['footer'], $activateFooter, $userId);
                $results['footer_id'] = $footerId;
            }

            // Save Pages to tb_layout_library
            if ($savePages && !empty($themeData['pages'])) {
                $layoutId = $this->saveToLayoutLibrary($themeName, $themeData, $category, $userId);
                $results['layout_id'] = $layoutId;
            }

            // Create actual CMS pages in tb_pages
            if ($createPages && !empty($themeData['pages'])) {
                $results['pages'] = [];
                $isFirstPage = true;
                foreach ($themeData['pages'] as $page) {
                    $pageTitle = $page['title'] ?? $page['name'] ?? 'Untitled Page';
                    $pageSlug = $this->generateSlug($pageTitle);
                    $pageContent = $page['content'] ?? ['sections' => []];
                    
                    // Make slug unique
                    $baseSlug = $pageSlug;
                    $counter = 0;
                    while (true) {
                        $testSlug = $counter ? "{$baseSlug}-{$counter}" : $baseSlug;
                        $stmt = $this->db->prepare("SELECT id FROM tb_pages WHERE slug = ?");
                        $stmt->execute([$testSlug]);
                        if (!$stmt->fetch()) {
                            $pageSlug = $testSlug;
                            break;
                        }
                        $counter++;
                    }
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO tb_pages (title, slug, content_json, status, is_homepage, created_by, created_at, updated_at)
                        VALUES (?, ?, ?, 'published', ?, ?, NOW(), NOW())
                    ");
                    $stmt->execute([
                        $pageTitle,
                        $pageSlug,
                        json_encode($pageContent, JSON_UNESCAPED_UNICODE),
                        $isFirstPage ? 1 : 0,  // First page becomes homepage
                        $userId
                    ]);
                    
                    $pageId = (int)$this->db->lastInsertId();
                    $results['pages'][] = ['id' => $pageId, 'title' => $pageTitle, 'slug' => $pageSlug];
                    
                    // Only first page is homepage
                    if ($isFirstPage) {
                        // Unset other homepages
                        $this->db->exec("UPDATE tb_pages SET is_homepage = 0 WHERE id != " . $pageId);
                        $isFirstPage = false;
                    }
                }
            }

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Theme deployed successfully!',
                'results' => $results,
                'library_url' => '/admin/layout-library'
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'error' => 'Deploy failed: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX Endpoint: Generate Header Template
     */
    public function generateHeader(\Core\Request $request): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // Map form field names to internal variable names
        $brief = trim($input['description'] ?? $input['brief'] ?? '');
        $businessName = trim($input['brand_name'] ?? $input['business_name'] ?? '');
        $style = trim($input['design_style'] ?? $input['style_preference'] ?? 'modern');
        $model = trim($input['ai_model'] ?? 'gpt-5.2');
        
        if (empty($brief)) {
            echo json_encode(['success' => false, 'error' => 'Brief is required']);
            exit;
        }
        
        try {
            $layout = $this->generateHeaderLayout($brief, $businessName, $style, $model);
            echo json_encode(['success' => true, 'layout' => $layout]);
        } catch (\Exception $e) {
            error_log('[AI-TB4] Header generation error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Generate Header Template using AI
     */
    private function generateHeaderLayout(string $brief, string $businessName, string $style, string $model): array
    {
        $systemPrompt = <<<PROMPT
You are an ELITE website header designer. RESPOND WITH VALID JSON ONLY - no markdown, no code blocks.

Create a professional header with this EXACT structure:

{
  "name": "{BUSINESS_NAME} Header",
  "content": {
    "sections": [{
      "id": "header_section",
      "name": "Header",
      "design": {
        "background_color": "#1a1a2e",
        "padding_top": "15px",
        "padding_bottom": "15px"
      },
      "rows": [{
        "id": "header_row",
        "columns": [
          {
            "id": "col_logo",
            "width": "20%",
            "modules": [{
              "id": "mod_logo",
              "type": "logo",
              "content": {
                "text": "{BUSINESS_NAME}",
                "link_url": "/"
              },
              "design": {
                "text_color": "#ffffff",
                "font_size": "28px",
                "font_weight": "700"
              }
            }]
          },
          {
            "id": "col_nav",
            "width": "55%",
            "modules": [{
              "id": "mod_menu",
              "type": "menu",
              "content": {
                "items": [
                  {"label": "Home", "url": "/"},
                  {"label": "About", "url": "/about"},
                  {"label": "Services", "url": "/services"},
                  {"label": "Portfolio", "url": "/portfolio"},
                  {"label": "Contact", "url": "/contact"}
                ]
              },
              "design": {
                "text_color": "#e2e8f0",
                "font_size": "15px",
                "font_weight": "500"
              }
            }]
          },
          {
            "id": "col_cta",
            "width": "25%",
            "modules": [{
              "id": "mod_cta",
              "type": "button",
              "content": {
                "text": "Get Quote",
                "url": "/contact",
                "target": "_self"
              },
              "design": {
                "background_color": "#e94560",
                "text_color": "#ffffff",
                "border_radius": "6px"
              }
            }]
          }
        ]
      }]
    }]
  }
}

CRITICAL RULES:
1. Logo module: Use "content.text" with the business name - NOT "Site"
2. Menu module: Use "content.items" as array of objects with "label" and "url" keys
3. Button module: Use "content.text" for CTA text like "Get Quote", "Book Now", "Contact Us"
4. Customize colors based on design style
5. Return ONLY valid JSON - no explanations
PROMPT;

        $userPrompt = "Create header for: {$businessName}\nBusiness type: {$brief}\nDesign style: {$style}";

        $content = $this->callAI($systemPrompt, $userPrompt, $model);
        $header = json_decode($content, true);

        if (!$header || !isset($header['content'])) {
            $header = $this->getDefaultHeader($businessName);
        }

        return $header;
    }

    /**
     * AJAX Endpoint: Generate Footer Template
     */
    public function generateFooter(\Core\Request $request): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // Map form field names to internal variable names
        $brief = trim($input['description'] ?? $input['brief'] ?? '');
        $businessName = trim($input['brand_name'] ?? $input['business_name'] ?? '');
        $style = trim($input['design_style'] ?? $input['style_preference'] ?? 'modern');
        $model = trim($input['ai_model'] ?? 'gpt-5.2');
        
        if (empty($brief)) {
            echo json_encode(['success' => false, 'error' => 'Brief is required']);
            exit;
        }
        
        try {
            $layout = $this->generateFooterLayout($brief, $businessName, $style, $model);
            echo json_encode(['success' => true, 'layout' => $layout]);
        } catch (\Exception $e) {
            error_log('[AI-TB4] Footer generation error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Generate Footer Template using AI
     */
    private function generateFooterLayout(string $brief, string $businessName, string $style, string $model): array
    {
        $systemPrompt = <<<PROMPT
You are an ELITE website footer designer creating PREMIUM footers comparable to Divi/Elementor.
RESPOND WITH VALID JSON ONLY. No markdown, no code blocks.

Generate a professional website footer in TB 3.0 JSON format.

FOOTER REQUIREMENTS:
- Section 1: Main footer with 4 columns
  - Column 1 (30%): Logo + company description + social icons
  - Column 2 (20%): Quick Links (5 items)
  - Column 3 (20%): Services Links (4 items)
  - Column 4 (30%): Contact info + Newsletter form
- Section 2: Bottom bar with copyright

DESIGN SPECIFICATIONS:
- Main section: background_color "#0f172a", padding_top "80px", padding_bottom "60px"
- Logo text: text_color "#ffffff", font_size "28px", font_weight "700"
- Description: text_color "#94a3b8", font_size "15px", line_height "1.7"
- Headings: text_color "#ffffff", font_size "18px", font_weight "600", margin_bottom "20px"
- Links: text_color "#94a3b8", font_size "14px"
- Bottom bar: background_color "#0a0f1a", padding "20px", text_color "#64748b", font_size "14px"

AVAILABLE MODULES: text, heading, button, spacer, icon, social, social_follow, form, signup, logo

JSON STRUCTURE:
{
  "name": "Footer Name",
  "content": {
    "sections": [
      {
        "id": "footer_main",
        "name": "Footer Main",
        "design": {"background_color": "#0f172a", "padding_top": "80px", "padding_bottom": "60px"},
        "rows": [{
          "id": "footer_row",
          "columns": [...]
        }]
      },
      {
        "id": "footer_bottom",
        "name": "Copyright",
        "design": {"background_color": "#0a0f1a", "padding_top": "20px", "padding_bottom": "20px"},
        "rows": [...]
      }
    ]
  }
}
PROMPT;

        $year = date('Y');
        $userPrompt = "Create a professional footer for: {$businessName}\nBusiness: {$brief}\nStyle: {$style}\nInclude company info, quick links, service links, contact details, and copyright {$year}.";

        $content = $this->callAI($systemPrompt, $userPrompt, $model);
        $footer = json_decode($content, true);

        if (!$footer || !isset($footer['content'])) {
            $footer = $this->getDefaultFooter($businessName);
        }

        return $footer;
    }

    /**
     * AJAX Endpoint: Generate Page Layouts
     */
    public function generatePages(\Core\Request $request): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // DEBUG: Log raw input
        file_put_contents('/var/www/html/cms/logs/ai_tb_debug.log', date('Y-m-d H:i:s') . " RAW INPUT:\n" . print_r($input, true) . "\n", FILE_APPEND);
        
        // Map form field names to internal variable names
        $brief = trim($input['description'] ?? $input['brief'] ?? '');
        $businessName = trim($input['brand_name'] ?? $input['business_name'] ?? '');
        $industry = trim($input['website_type'] ?? $input['industry'] ?? 'general');
        $style = trim($input['design_style'] ?? $input['style_preference'] ?? 'modern');
        $model = trim($input['ai_model'] ?? 'gpt-5.2');
        
        // DEBUG: Log mapped values
        file_put_contents('/var/www/html/cms/logs/ai_tb_debug.log', "MAPPED: style={$style}, industry={$industry}, business={$businessName}\n", FILE_APPEND);
        
        // Handle pages from form (pages[]) or page_names
        $pageNames = $input['pages'] ?? $input['page_names'] ?? ['homepage', 'about', 'services', 'contact'];
        
        if (!is_array($pageNames)) {
            $pageNames = explode(',', $pageNames);
        }
        $pageNames = array_map('trim', $pageNames);
        $pageNames = array_map('ucfirst', $pageNames); // Capitalize for display
        
        if (empty($brief)) {
            echo json_encode(['success' => false, 'error' => 'Brief is required']);
            exit;
        }
        
        try {
            $layouts = $this->generatePagesLayout($brief, $businessName, $industry, $pageNames, $style, $model);
            
            // DEBUG: Log AI response
            file_put_contents('/var/www/html/cms/logs/ai_tb_debug.log', "\n\nAI RESPONSE (first page):\n" . json_encode($layouts[0] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
            
            echo json_encode(['success' => true, 'layouts' => $layouts]);
        } catch (\Exception $e) {
            error_log('[AI-TB4] Pages generation error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * AJAX Endpoint: Fetch and fill images for generated theme
     */
    public function fetchImages(\Core\Request $request): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // Get generated data
        $themeData = null;
        if (isset($input['theme'])) {
            $themeData = $input['theme'];
        } elseif (isset($input['generated_data'])) {
            $themeData = is_string($input['generated_data']) 
                ? json_decode($input['generated_data'], true) 
                : $input['generated_data'];
        }
        
        if (!$themeData) {
            echo json_encode(['success' => false, 'error' => 'No theme data provided']);
            exit;
        }
        
        // Get image source preference
        $imageSource = $input['image_source'] ?? 'unsplash';
        $sources = [$imageSource];
        if ($imageSource !== 'media') {
            $sources[] = 'media'; // Fallback to media library
        }
        
        try {
            error_log('[AI-TB4] Fetching images from: ' . implode(', ', $sources));
            
            // Fill images in all components
            $filledTheme = $this->fillAllImages($themeData, $sources);
            
            echo json_encode([
                'success' => true,
                'theme' => $filledTheme,
                'message' => 'Images fetched successfully'
            ]);
        } catch (\Exception $e) {
            error_log('[AI-TB4] Image fetch error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Generate Page Layouts using AI
     */
    private function generatePagesLayout(string $brief, string $businessName, string $industry, array $pageNames, string $style, string $model): array
    {
        $pageCount = count($pageNames);
        $pageListStr = implode(', ', $pageNames);
        $modulesJson = tb_get_modules_json();
        $moduleTypes = array_column($modulesJson, 'slug');
        $moduleList = implode(', ', $moduleTypes);

        // DEBUG: Log what we're passing to design prompt
        error_log("[AI-TB4] generatePagesLayout called with: style={$style}, industry={$industry}, business={$businessName}");

        // Design-first prompt - adds creative vision BEFORE technical specs
        $systemPrompt = tb_build_design_prompt($style, $industry, $businessName, $brief, $moduleTypes);
        
        // DEBUG: Log prompt to file
        file_put_contents('/var/www/html/cms/logs/ai_tb_debug.log', "\n\nSYSTEM PROMPT (first 2500 chars):\n" . substr($systemPrompt, 0, 2500) . "\n...\n", FILE_APPEND);
        
        // DEBUG: Log prompt length
        error_log("[AI-TB4] System prompt length: " . strlen($systemPrompt) . " chars");

        $userPrompt = <<<USERPROMPT
⚠️⚠️⚠️ CRITICAL: YOU MUST GENERATE EXACTLY {$pageCount} PAGES IN THE "pages" ARRAY! ⚠️⚠️⚠️

Generate ALL of these pages for {$businessName}: {$pageListStr}

DO NOT generate only 1 page! The "pages" array MUST contain EXACTLY {$pageCount} page objects.

BUSINESS DESCRIPTION: {$brief}

REQUIRED OUTPUT STRUCTURE:
{"pages": [<page1>, <page2>, <page3>, ...]} - MUST have {$pageCount} pages!

⚠️ CRITICAL: Each page MUST have a DIFFERENT layout structure. DO NOT repeat the same sections on every page!

PAGE-SPECIFIC REQUIREMENTS:

📌 HOMEPAGE (is_homepage: true) - Maximum visual impact, 5-7 sections:
   • Hero: Full-width with gradient/image, large headline, subtext, primary CTA button, ADD IMAGE MODULE with src="hero [industry] professional"
   • Social Proof: Counter stats (years, clients, projects) OR client logos
   • Features/Benefits: 3-4 blurbs with icons showcasing key advantages
   • About Preview: Image + short text, link to About page
   • Services Overview: 3-4 service cards with icons
   • Testimonials: 2-3 customer quotes
   • Final CTA: Bold call-to-action section

📌 ABOUT PAGE - Storytelling focus, 4-5 sections:
   • Page Header: Simple hero with page title, ADD IMAGE MODULE with src="team office professional"
   • Our Story: Large text block with company history/mission (text module), ADD IMAGE MODULE beside it
   • Values/Mission: 3 blurbs with icons (integrity, quality, innovation)
   • Team Section: Optional, heading + text about the team
   • CTA: Contact us section

📌 SERVICES PAGE - Service showcase, 4-5 sections:
   • Page Header: Services title with short description, ADD IMAGE MODULE
   • Services Grid: 4-6 blurbs with IMAGE MODULE above each, showing service visuals
   • Process/How It Works: 3-4 numbered steps (blurbs)
   • Pricing or FAQ: Optional text section
   • CTA: Request quote/Contact section

📌 CONTACT PAGE - Conversion focus, 3-4 sections:
   • Page Header: "Get In Touch" or similar
   • Contact Info: 3 blurbs (address/phone/email with icons)
   • Contact Form: form module OR text placeholder
   • Map/Location: Text about location or image placeholder

📌 PORTFOLIO/GALLERY PAGE - Visual showcase:
   • Page Header: Portfolio title with IMAGE MODULE
   • Gallery Grid: MUST USE gallery module with keywords like "portfolio [industry] work examples", image_count: 9
   • CTA: Hire us section

📌 BLOG/NEWS PAGE - Content listing:
   • Page Header: Blog/News title
   • Featured Post: Large image + text preview
   • Recent Posts: 3 post cards (blurbs with dates)
   • Newsletter CTA: Signup section

SECTION VARIETY RULES:
1. Homepage has MOST sections (5-7), other pages have fewer (3-5)
2. Only Homepage has testimonials and counter stats
3. About page focuses on text-heavy storytelling
4. Services page focuses on service cards grid
5. Contact page is shortest - focused on conversion
6. Each page header should have different background color

Generate specific, professional content for "{$businessName}" in the {$industry} industry.
Use realistic text, not placeholder "Lorem ipsum".
USERPROMPT;

        $content = $this->callAI($systemPrompt, $userPrompt, $model, 16000);

        // Clean response
        $content = trim($content);
        if (str_starts_with($content, '```json')) {
            $content = substr($content, 7);
        }
        if (str_starts_with($content, '```')) {
            $content = substr($content, 3);
        }
        if (str_ends_with($content, '```')) {
            $content = substr($content, 0, -3);
        }
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', trim($content));

        $result = json_decode($content, true);

        if (!$result || !isset($result['pages'])) {
            error_log('[AI-TB4] Invalid pages response, trying fallback parse');
            $result = json_decode($content, true, 512, JSON_INVALID_UTF8_IGNORE);
        }

        if (!$result || !isset($result['pages'])) {
            throw new \Exception('AI returned invalid page structure');
        }

        // DEBUG: Log how many pages AI returned
        error_log('[AI-TB4] AI returned ' . count($result['pages']) . ' pages');
        file_put_contents('/var/www/html/cms/logs/ai_tb_debug.log', "\n\nAI RETURNED " . count($result['pages']) . " PAGES\n", FILE_APPEND);

        // Validate modules
        foreach ($result['pages'] as &$page) {
            $page = $this->validatePage($page);
        }

        return $result['pages'];
    }

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
            return $this->callAnthropic($providerConfig, $systemPrompt, $userPrompt, $temperature, $maxTokens, $jsonMode);
        }

        return $this->callOpenAI($providerConfig, $systemPrompt, $userPrompt, $temperature, $maxTokens, $jsonMode);
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(array $config, string $systemPrompt, string $userPrompt, float $temperature, int $maxTokens, bool $jsonMode = true): string
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
            'max_tokens' => $maxTokens,
            'response_format' => $jsonMode ? ['type' => 'json_object'] : null
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(array_filter($data, fn($v) => $v !== null)),
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
    private function callAnthropic(array $config, string $systemPrompt, string $userPrompt, float $temperature, int $maxTokens, bool $jsonMode = true): string
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
            CURLOPT_POSTFIELDS => json_encode(array_filter($data, fn($v) => $v !== null)),
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

    /**
     * Save template to tb_templates table
     */
    private function saveTemplate(string $type, string $name, array $data, bool $activate, ?int $userId): int
    {
        // Deactivate existing if activating new
        if ($activate) {
            $stmt = $this->db->prepare("UPDATE tb_templates SET is_global = 0 WHERE type = ?");
            $stmt->execute([$type]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO tb_templates (name, type, content_json, is_global, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $name,
            $type,
            json_encode($data, JSON_UNESCAPED_UNICODE),
            $activate ? 1 : 0
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Save pages to tb_layout_library
     */
    private function saveToLayoutLibrary(string $name, array $theme, string $category, ?int $userId): int
    {
        $slug = $this->generateSlug($name);
        $baseSlug = $slug;
        $counter = 0;

        // Ensure unique slug
        while (true) {
            $testSlug = $counter ? "{$baseSlug}-{$counter}" : $baseSlug;
            $stmt = $this->db->prepare("SELECT id FROM tb_layout_library WHERE slug = ?");
            $stmt->execute([$testSlug]);
            if (!$stmt->fetch()) {
                $slug = $testSlug;
                break;
            }
            $counter++;
        }

        $pageCount = count($theme['pages'] ?? []);

        $stmt = $this->db->prepare("
            INSERT INTO tb_layout_library
            (name, slug, description, category, industry, style, page_count, content_json, is_ai_generated, ai_prompt, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, NOW())
        ");

        $stmt->execute([
            $name,
            $slug,
            'AI-generated theme with ' . $pageCount . ' pages',
            $category,
            $theme['meta']['industry'] ?? null,
            $theme['meta']['style'] ?? 'modern',
            $pageCount,
            json_encode(['pages' => $theme['pages']], JSON_UNESCAPED_UNICODE),
            'AI Theme Builder 4.0',
            $userId
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Validate page structure and module types
     */
    private function validatePage(array $page): array
    {
        if (!isset($page['content']['sections'])) {
            return $page;
        }

        foreach ($page['content']['sections'] as &$section) {
            if (!isset($section['rows'])) continue;

            foreach ($section['rows'] as &$row) {
                if (!isset($row['columns'])) continue;

                foreach ($row['columns'] as &$column) {
                    if (!isset($column['modules'])) continue;

                    // Filter invalid modules
                    $column['modules'] = array_values(array_filter($column['modules'], function($module) {
                        $type = $module['type'] ?? '';
                        return tb_module_exists($type);
                    }));
                }
            }
        }

        return $page;
    }

    /**
     * Fill all images in theme
     */
    private function fillAllImages(array $theme, array $sources): array
    {
        // Fill header images
        if (!empty($theme['header'])) {
            $theme['header'] = $this->fillComponentImages($theme['header'], $sources);
        }

        // Fill footer images
        if (!empty($theme['footer'])) {
            $theme['footer'] = $this->fillComponentImages($theme['footer'], $sources);
        }

        // Fill page images
        foreach ($theme['pages'] as &$page) {
            $page = $this->fillComponentImages($page, $sources);
        }

        return $theme;
    }

    /**
     * Fill images in a component (header, footer, or page)
     */
    private function fillComponentImages(array $component, array $sources): array
    {
        error_log('[AI-TB4 FILL] fillComponentImages called');
        if (!isset($component['content']['sections'])) {
            error_log('[AI-TB4 FILL] No sections in component');
            return $component;
        }
        error_log('[AI-TB4 FILL] Found ' . count($component['content']['sections']) . ' sections');

        foreach ($component['content']['sections'] as &$section) {
            // Section background
            if (isset($section['design']['background_image']) && $section['design']['background_image'] === 'auto') {
                $keywords = $section['image_keywords'] ?? $section['name'] ?? 'abstract background';
                $imageUrl = $this->findImage($keywords, $sources);
                if ($imageUrl) {
                    $section['design']['background_image'] = $imageUrl;
                }
            }

            if (!isset($section['rows'])) continue;

            foreach ($section['rows'] as &$row) {
                if (!isset($row['columns'])) continue;

                foreach ($row['columns'] as &$column) {
                    if (!isset($column['modules'])) continue;

                    foreach ($column['modules'] as &$module) {
                        // Image modules
                        if ($module['type'] === 'image') {
                            error_log('[AI-TB4 FILL] Found IMAGE module');
                            error_log('[AI-TB4 FILL] Module content: ' . json_encode($module['content']));
                            // Priority: keywords > src (contains search terms) > alt
                            $keywords = $module['content']['keywords']
                                ?? $module['content']['src']
                                ?? $module['content']['alt']
                                ?? '';
                            error_log('[AI-TB4 FILL] Using keywords: ' . $keywords);
                            if ($keywords && !str_starts_with($keywords, 'http') && !str_starts_with($keywords, '/')) {
                                $imageUrl = $this->findImage($keywords, $sources);
                                if ($imageUrl) {
                                    $module['content']['src'] = $imageUrl;
                                    $module['content']['url'] = $imageUrl;
                                }
                            }
                        }

                        // Gallery modules
                        if ($module['type'] === 'gallery') {
                            $keywords = $module['content']['keywords'] ?? 'professional';
                            $count = (int)($module['content']['image_count'] ?? 6);
                            $images = $this->findMultipleImages($keywords, $sources, $count);
                            if ($images) {
                                $module['content']['images'] = $images;
                            }
                        }
                    }
                }
            }
        }

        return $component;
    }

    /**
     * Find single image from sources
     */
    private function findImage(string $keywords, array $sources): ?string
    {
        error_log('[AI-TB4 FIND] findImage called with: ' . $keywords);
        $keywords = trim($keywords);
        if (empty($keywords)) {
            error_log('[AI-TB4 FIND] Empty keywords!');
            return null;
        }

        foreach ($sources as $source) {
            $url = match($source) {
                'media' => $this->searchMediaLibrary($keywords),
                'pexels' => $this->searchPexels($keywords),
                'unsplash' => $this->downloadUnsplashImage($keywords),
                default => null
            };

            if ($url) return $url;
        }

        return $this->downloadUnsplashImage($keywords);
    }

    /**
     * Find multiple images
     */
    private function findMultipleImages(string $keywords, array $sources, int $count = 6): array
    {
        $images = [];
        $keywords = trim($keywords);
        if (empty($keywords)) return $images;

        foreach ($sources as $source) {
            if (count($images) >= $count) break;

            $found = match($source) {
                'media' => $this->searchMediaLibraryMultiple($keywords, $count - count($images)),
                'pexels' => $this->searchPexelsMultiple($keywords, $count - count($images)),
                default => []
            };

            $images = array_merge($images, $found);
        }

        return array_slice($images, 0, $count);
    }

    /**
     * Search Media Library
     */
    private function searchMediaLibrary(string $keywords): ?string
    {
        $searchTerms = explode(' ', strtolower($keywords));
        $placeholders = array_fill(0, count($searchTerms), "(filename LIKE ? OR alt_text LIKE ? OR title LIKE ?)");
        $where = implode(' OR ', $placeholders);

        $params = [];
        foreach ($searchTerms as $term) {
            $like = "%{$term}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        try {
            $stmt = $this->db->prepare("SELECT filepath FROM media WHERE ({$where}) AND mime_type LIKE 'image/%' ORDER BY created_at DESC LIMIT 1");
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? '/uploads/' . ltrim($result['filepath'], '/') : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Search Media Library for multiple images
     */
    private function searchMediaLibraryMultiple(string $keywords, int $limit = 6): array
    {
        $searchTerms = explode(' ', strtolower($keywords));
        $placeholders = array_fill(0, count($searchTerms), "(filename LIKE ? OR alt_text LIKE ? OR title LIKE ?)");
        $where = implode(' OR ', $placeholders);

        $params = [];
        foreach ($searchTerms as $term) {
            $like = "%{$term}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        try {
            $stmt = $this->db->prepare("SELECT filepath, title, alt_text FROM media WHERE ({$where}) AND mime_type LIKE 'image/%' ORDER BY created_at DESC LIMIT {$limit}");
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    'src' => '/uploads/' . ltrim($row['filepath'], '/'),
                    'alt' => $row['alt_text'] ?: $row['title'] ?: ''
                ];
            }, $results);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Search Pexels
     */
    private function searchPexels(string $keywords): ?string
    {
        $pexelsKey = $this->getPexelsApiKey();
        if (!$pexelsKey) return null;

        $url = 'https://api.pexels.com/v1/search?' . http_build_query([
            'query' => $keywords,
            'per_page' => 1,
            'orientation' => 'landscape'
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: ' . $pexelsKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (!empty($data['photos'][0]['src']['large'])) {
            return $this->downloadAndSaveImage($data['photos'][0]['src']['large'], 'pexels');
        }

        return null;
    }

    /**
     * Search Pexels for multiple images
     */
    private function searchPexelsMultiple(string $keywords, int $limit = 6): array
    {
        $pexelsKey = $this->getPexelsApiKey();
        if (!$pexelsKey) return [];

        $url = 'https://api.pexels.com/v1/search?' . http_build_query([
            'query' => $keywords,
            'per_page' => $limit,
            'orientation' => 'landscape'
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: ' . $pexelsKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $images = [];

        if (!empty($data['photos'])) {
            foreach ($data['photos'] as $photo) {
                $remoteUrl = $photo['src']['large'] ?? $photo['src']['medium'];
                $localUrl = $this->downloadAndSaveImage($remoteUrl, 'pexels');
                $images[] = [
                    'src' => $localUrl,
                    'alt' => $photo['alt'] ?? ''
                ];
            }
        }

        return $images;
    }

    /**
     * Get Pexels API key
     */
    private function getPexelsApiKey(): ?string
    {
        $configPath = CMS_ROOT . '/config/api_keys.json';
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            if (!empty($config['pexels'])) {
                return $config['pexels'];
            }
        }

        return $this->aiSettings['pexels_api_key'] ?? null;
    }

    /**
     * Download Unsplash image and save locally
     */
    private function downloadUnsplashImage(string $keywords): string
    {
        $encodedKeywords = urlencode(trim($keywords));
        $url = "https://source.unsplash.com/1200x800/?" . $encodedKeywords;
        return $this->downloadAndSaveImage($url, "unsplash");
    }

    /**
     * Download and save image locally
     */
    private function downloadAndSaveImage(string $url, string $prefix = "ai"): string
    {
        if (empty($url)) return "";

        if (str_starts_with($url, "/uploads/") || str_starts_with($url, "/assets/")) {
            return $url;
        }

        try {
            $uploadDir = CMS_ROOT . "/public/uploads/media/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = "jpg";
            if (preg_match("/\.(jpe?g|png|gif|webp)/i", parse_url($url, PHP_URL_PATH), $m)) {
                $ext = strtolower($m[1]);
            }
            $filename = $prefix . "_" . date("Ymd_His") . "_" . substr(md5($url . microtime()), 0, 8) . "." . $ext;
            $filepath = $uploadDir . $filename;

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
            ]);

            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || empty($imageData) || strlen($imageData) < 1000) {
                return $url;
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
            if (!str_starts_with($mimeType, "image/")) {
                return $url;
            }

            if (file_put_contents($filepath, $imageData) === false) {
                return $url;
            }

            return "/uploads/media/" . $filename;

        } catch (\Exception $e) {
            return $url;
        }
    }

    /**
     * Wrap preview HTML with full document
     */
    private function wrapPreviewHtml(string $content, string $title, string $type = 'page'): string
    {
        $bgColor = ($type === 'header' || $type === 'footer') ? '#1a1a2e' : '#ffffff';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            line-height: 1.6;
            color: #333;
            background: {$bgColor};
            -webkit-font-smoothing: antialiased;
        }
        img { max-width: 100%; height: auto; }
        .tb-section { width: 100%; }
        .tb-section-inner { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .tb-row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
        .tb-column { padding: 15px; box-sizing: border-box; }
        .tb-module { margin-bottom: 20px; }
        .tb-module:last-child { margin-bottom: 0; }
        .tb-col-100 { width: 100%; }
        .tb-col-80 { width: 80%; }
        .tb-col-75 { width: 75%; }
        .tb-col-66 { width: 66.666%; }
        .tb-col-60 { width: 60%; }
        .tb-col-50 { width: 50%; }
        .tb-col-40 { width: 40%; }
        .tb-col-33 { width: 33.333%; }
        .tb-col-25 { width: 25%; }
        .tb-col-20 { width: 20%; }
        .tb-button { transition: all 0.3s ease; display: inline-block; cursor: pointer; }
        .tb-button:hover { transform: translateY(-2px); filter: brightness(1.1); }
        .tb-blurb { transition: transform 0.3s ease; }
        .tb-blurb:hover { transform: translateY(-5px); }
        @media (max-width: 768px) {
            .tb-column { width: 100% !important; }
        }
    </style>
</head>
<body>
    {$content}
</body>
</html>
HTML;
    }

    /**
     * Generate slug from name
     */
    private function generateSlug(string $title): string
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-') ?: 'theme-' . time();
    }

    /**
     * Check if AI is configured
     */
    private function isAiConfigured(): bool
    {
        $openaiKey = $this->aiSettings['providers']['openai']['api_key'] ?? '';
        $anthropicKey = $this->aiSettings['providers']['anthropic']['api_key'] ?? '';
        return !empty($openaiKey) || !empty($anthropicKey);
    }

    /**
     * Get default header structure
     */
    private function getDefaultHeader(string $businessName): array
    {
        return [
            'name' => $businessName . ' Header',
            'content' => [
                'sections' => [[
                    'id' => 'header_section',
                    'name' => 'Header',
                    'design' => [
                        'background_color' => '#1a1a2e',
                        'padding_top' => '20px',
                        'padding_bottom' => '20px'
                    ],
                    'rows' => [[
                        'id' => 'header_row',
                        'columns' => [
                            [
                                'id' => 'col_logo',
                                'width' => '25%',
                                'modules' => [[
                                    'id' => 'logo',
                                    'type' => 'heading',
                                    'content' => ['text' => $businessName, 'level' => 'h3'],
                                    'design' => ['text_color' => '#ffffff', 'font_size' => '24px', 'font_weight' => '700']
                                ]]
                            ],
                            [
                                'id' => 'col_nav',
                                'width' => '50%',
                                'modules' => [[
                                    'id' => 'nav',
                                    'type' => 'text',
                                    'content' => ['text' => '<a href="#" style="color:#e2e8f0;margin:0 15px;">Home</a><a href="#" style="color:#e2e8f0;margin:0 15px;">About</a><a href="#" style="color:#e2e8f0;margin:0 15px;">Services</a><a href="#" style="color:#e2e8f0;margin:0 15px;">Contact</a>'],
                                    'design' => ['text_align' => 'center']
                                ]]
                            ],
                            [
                                'id' => 'col_cta',
                                'width' => '25%',
                                'modules' => [[
                                    'id' => 'cta',
                                    'type' => 'button',
                                    'content' => ['text' => 'Get Started', 'url' => '#contact'],
                                    'design' => ['background_color' => '#e94560', 'text_color' => '#ffffff', 'padding' => '12px 28px', 'border_radius' => '6px', 'text_align' => 'right']
                                ]]
                            ]
                        ]
                    ]]
                ]]
            ]
        ];
    }

    /**
     * Get default footer structure
     */
    private function getDefaultFooter(string $businessName): array
    {
        $year = date('Y');
        return [
            'name' => $businessName . ' Footer',
            'content' => [
                'sections' => [
                    [
                        'id' => 'footer_main',
                        'name' => 'Footer',
                        'design' => [
                            'background_color' => '#0f172a',
                            'padding_top' => '80px',
                            'padding_bottom' => '60px'
                        ],
                        'rows' => [[
                            'id' => 'footer_row',
                            'columns' => [
                                [
                                    'id' => 'col_brand',
                                    'width' => '33%',
                                    'modules' => [
                                        ['id' => 'logo', 'type' => 'heading', 'content' => ['text' => $businessName, 'level' => 'h3'], 'design' => ['text_color' => '#ffffff', 'font_size' => '28px', 'font_weight' => '700']],
                                        ['id' => 'desc', 'type' => 'text', 'content' => ['text' => 'Professional solutions for your business needs.'], 'design' => ['text_color' => '#94a3b8', 'font_size' => '15px', 'margin_top' => '15px']]
                                    ]
                                ],
                                [
                                    'id' => 'col_links',
                                    'width' => '33%',
                                    'modules' => [
                                        ['id' => 'title', 'type' => 'heading', 'content' => ['text' => 'Quick Links', 'level' => 'h4'], 'design' => ['text_color' => '#ffffff', 'font_size' => '18px', 'font_weight' => '600']],
                                        ['id' => 'links', 'type' => 'text', 'content' => ['text' => '<a href="#" style="color:#94a3b8;display:block;margin:8px 0;">Home</a><a href="#" style="color:#94a3b8;display:block;margin:8px 0;">About</a><a href="#" style="color:#94a3b8;display:block;margin:8px 0;">Services</a><a href="#" style="color:#94a3b8;display:block;margin:8px 0;">Contact</a>'], 'design' => ['font_size' => '14px', 'margin_top' => '20px']]
                                    ]
                                ],
                                [
                                    'id' => 'col_contact',
                                    'width' => '33%',
                                    'modules' => [
                                        ['id' => 'title', 'type' => 'heading', 'content' => ['text' => 'Contact', 'level' => 'h4'], 'design' => ['text_color' => '#ffffff', 'font_size' => '18px', 'font_weight' => '600']],
                                        ['id' => 'info', 'type' => 'text', 'content' => ['text' => '<p style="color:#94a3b8;margin:8px 0;">📧 info@example.com</p><p style="color:#94a3b8;margin:8px 0;">📞 (555) 123-4567</p>'], 'design' => ['font_size' => '14px', 'margin_top' => '20px']]
                                    ]
                                ]
                            ]
                        ]]
                    ],
                    [
                        'id' => 'footer_bottom',
                        'name' => 'Copyright',
                        'design' => [
                            'background_color' => '#0a0f1a',
                            'padding_top' => '20px',
                            'padding_bottom' => '20px'
                        ],
                        'rows' => [[
                            'id' => 'copyright_row',
                            'columns' => [[
                                'id' => 'col_copy',
                                'width' => '100%',
                                'modules' => [[
                                    'id' => 'copy',
                                    'type' => 'text',
                                    'content' => ['text' => "© {$year} {$businessName}. All rights reserved."],
                                    'design' => ['text_color' => '#64748b', 'font_size' => '14px', 'text_align' => 'center']
                                ]]
                            ]]
                        ]]
                    ]
                ]
            ]
        ];
    }

    /**
     * AJAX: Save generated theme to session for preview in new tab
     * Returns URL to open preview
     */
    public function previewNewTab(Request $request): void
    {
        header('Content-Type: application/json');
        
        // Use cached JSON data (php://input already read by index.php for CSRF)
        $input = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        if (empty($input['pages']) || !is_array($input['pages'])) {
            echo json_encode(['success' => false, 'error' => 'No pages data provided']);
            return;
        }
        
        // Get first page content for preview (usually homepage)
        $firstPage = $input['pages'][0] ?? null;
        if (!$firstPage || empty($firstPage['content'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid page content']);
            return;
        }
        
        // Store in session for preview
        $previewKey = 'ai_tb_preview_' . time();
        $_SESSION[$previewKey] = [
            'header' => $input['header'] ?? null,
            'pages' => $input['pages'],
            'footer' => $input['footer'] ?? null,
            'project_name' => $input['project_name'] ?? 'AI Generated Theme',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Return URL with session key
        echo json_encode([
            'success' => true,
            'preview_url' => '/admin/ai-theme-builder/show-preview?key=' . $previewKey,
            'key' => $previewKey
        ]);
    }

    /**
     * Show preview page from session data
     * Renders full page with tb_render_page() like Theme Builder preview
     */
    public function showPreview(Request $request): void
    {
        $key = $_GET['key'] ?? '';
        $pageIndex = (int)($_GET['page'] ?? 0);
        
        if (empty($key) || !isset($_SESSION[$key])) {
            http_response_code(404);
            echo 'Preview not found or expired. Please generate again.';
            return;
        }
        
        $previewData = $_SESSION[$key];
        $pages = $previewData['pages'] ?? [];
        $header = $previewData['header'] ?? null;
        $footer = $previewData['footer'] ?? null;
        $projectName = $previewData['project_name'] ?? 'Preview';
        
        // DEBUG
        error_log('[AI-TB4 Preview] Session key: ' . $key);
        error_log('[AI-TB4 Preview] Header present: ' . ($header ? 'YES' : 'NO'));
        error_log('[AI-TB4 Preview] Header keys: ' . ($header ? implode(', ', array_keys($header)) : 'none'));
        error_log('[AI-TB4 Preview] Header content: ' . ($header && isset($header['content']) ? 'YES' : 'NO'));
        if ($header) {
            error_log('[AI-TB4 Preview] Header dump: ' . substr(json_encode($header), 0, 500));
        }
        
        if (empty($pages) || !isset($pages[$pageIndex])) {
            http_response_code(404);
            echo 'Page not found';
            return;
        }
        
        $currentPage = $pages[$pageIndex];
        $pageTitle = $currentPage['title'] ?? $currentPage['name'] ?? 'Preview';
        $content = $currentPage['content'] ?? ['sections' => []];
        
        // Render using Theme Builder renderer
        require_once CMS_ROOT . '/core/theme-builder/renderer.php';
        
        // Build full page: header + content + footer
        $headerHtml = '';
        $footerHtml = '';
        
        if ($header && !empty($header['content'])) {
            $headerHtml = tb_render_page($header['content'], ['preview_mode' => true]);
            error_log('[AI-TB4 Preview] Header HTML length: ' . strlen($headerHtml));
            error_log('[AI-TB4 Preview] Header HTML (first 500): ' . substr($headerHtml, 0, 500));
        } else {
            error_log('[AI-TB4 Preview] Header NOT rendered - missing content');
        }
        
        $contentHtml = tb_render_page($content, ['preview_mode' => true]);
        
        if ($footer && !empty($footer['content'])) {
            $footerHtml = tb_render_page($footer['content'], ['preview_mode' => true]);
        }
        
        // Output full preview page
        $this->renderPreviewPage($projectName, $pageTitle, $headerHtml, $contentHtml, $footerHtml, $pages, $pageIndex, $key);
    }

    /**
     * Render complete preview HTML page
     */
    private function renderPreviewPage(string $projectName, string $pageTitle, string $headerHtml, string $contentHtml, string $footerHtml, array $pages, int $currentIndex, string $key): void
    {
        $title = htmlspecialchars($pageTitle . ' - ' . $projectName);
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Preview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
            line-height: 1.6; 
            margin: 0; 
            padding: 0;
        }
        .ai-preview-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 8px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 9999;
            font-size: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .ai-preview-bar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .ai-preview-bar-title {
            font-weight: 600;
        }
        .ai-preview-bar-pages {
            display: flex;
            gap: 8px;
        }
        .ai-preview-bar-pages a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 4px 12px;
            border-radius: 4px;
            background: rgba(255,255,255,0.1);
            transition: all 0.2s;
        }
        .ai-preview-bar-pages a:hover {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }
        .ai-preview-bar-pages a.active {
            background: rgba(255,255,255,0.3);
            color: #fff;
            font-weight: 500;
        }
        .ai-preview-bar-close {
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s;
        }
        .ai-preview-bar-close:hover {
            background: rgba(255,255,255,0.3);
        }
        .ai-preview-content {
            margin-top: 50px;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="ai-preview-bar">
        <div class="ai-preview-bar-left">
            <span class="ai-preview-bar-title">🎨 AI Theme Preview: <?= htmlspecialchars($projectName) ?></span>
            <div class="ai-preview-bar-pages">
                <?php foreach ($pages as $idx => $page): ?>
                    <a href="?key=<?= $key ?>&page=<?= $idx ?>" class="<?= $idx === $currentIndex ? 'active' : '' ?>">
                        <?= htmlspecialchars($page['title'] ?? $page['name'] ?? 'Page ' . ($idx + 1)) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="ai-preview-bar-close" onclick="window.close()">✕ Close Preview</button>
    </div>
    <div class="ai-preview-content">
        <!-- DEBUG: headerHtml length = <?= strlen($headerHtml) ?> -->
        <?= $headerHtml ?>
        <?= $contentHtml ?>
        <?= $footerHtml ?>
    </div>
</body>
</html>
        <?php
        exit;
    }

    // ==================== HTML CONVERTER METHODS (TB 4.0) ====================
    
    /**
     * AJAX: Generate theme using HTML-first approach
     * AI generates free HTML/CSS → Converter transforms to TB JSON
     */
    public function generateWithHtml(\Core\Request $request): void
    {
        header('Content-Type: application/json');
        
        error_log('[AI-TB4-HTML] Generate with HTML called at: ' . date('Y-m-d H:i:s'));
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            exit;
        }
        
        $brief = trim($input['brief'] ?? '');
        $businessName = trim($input['business_name'] ?? 'My Business');
        $industry = trim($input['industry'] ?? 'general');
        $style = trim($input['style_preference'] ?? 'modern');
        $selectedModel = trim($input['model'] ?? '');
        $sections = $input['sections'] ?? ['hero', 'features', 'about', 'testimonials', 'cta', 'contact'];
        $pageNames = $input['page_names'] ?? ['Home', 'About', 'Services', 'Contact'];
        
        if (empty($brief)) {
            echo json_encode(['success' => false, 'error' => 'Project brief is required']);
            exit;
        }
        
        if (!$this->isAiConfigured()) {
            echo json_encode(['success' => false, 'error' => 'AI provider not configured']);
            exit;
        }
        
        try {
            // Load HTML Converter
            require_once CMS_ROOT . '/core/theme-builder/ai-html-prompts.php';
            require_once CMS_ROOT . '/core/theme-builder/html-converter/Converter.php';
            require_once CMS_ROOT . '/core/theme-builder/html-converter/StyleExtractor.php';
            require_once CMS_ROOT . '/core/theme-builder/html-converter/SectionDetector.php';
            require_once CMS_ROOT . '/core/theme-builder/html-converter/LayoutAnalyzer.php';
            require_once CMS_ROOT . '/core/theme-builder/html-converter/ElementMapper.php';
            
            $converter = new \Core\ThemeBuilder\HtmlConverter\Converter();
            
            $result = [
                'header' => null,
                'pages' => [],
                'footer' => null,
                'meta' => [
                    'business_name' => $businessName,
                    'industry' => $industry,
                    'style' => $style,
                    'method' => 'html-converter',
                    'generated_at' => date('Y-m-d H:i:s')
                ]
            ];
            
            // Generate Header via HTML
            error_log('[AI-TB4-HTML] Generating header...');
            $headerHtml = $this->generateHtmlSection('header', $brief, $businessName, $style, $pageNames, $selectedModel);
            if ($headerHtml) {
                $headerJson = $converter->convert($headerHtml);
                $result['header'] = [
                    'title' => $businessName . ' Header',
                    'content' => $headerJson,
                    'source_html' => $headerHtml
                ];
            }
            
            // Generate main page with sections
            error_log('[AI-TB4-HTML] Generating main page...');
            $pageHtml = $this->generateHtmlPage($brief, $businessName, $industry, $style, $sections, $selectedModel);
            if ($pageHtml) {
                $pageJson = $converter->convert($pageHtml);
                $result['pages'][] = [
                    'title' => 'Home',
                    'slug' => 'home',
                    'content' => $pageJson,
                    'source_html' => $pageHtml
                ];
            }
            
            // Generate additional pages
            foreach (array_slice($pageNames, 1) as $pageName) {
                error_log('[AI-TB4-HTML] Generating page: ' . $pageName);
                $pageSections = $this->getSectionsForPageHtml($pageName);
                $innerPageHtml = $this->generateHtmlPage(
                    $brief . "\n\nThis is the {$pageName} page.",
                    $businessName, $industry, $style, $pageSections, $selectedModel
                );
                
                if ($innerPageHtml) {
                    $innerPageJson = $converter->convert($innerPageHtml);
                    $result['pages'][] = [
                        'title' => $pageName,
                        'slug' => strtolower(str_replace(' ', '-', $pageName)),
                        'content' => $innerPageJson,
                        'source_html' => $innerPageHtml
                    ];
                }
            }
            
            // Generate Footer via HTML
            error_log('[AI-TB4-HTML] Generating footer...');
            $footerHtml = $this->generateHtmlSection('footer', $brief, $businessName, $style, $pageNames, $selectedModel);
            if ($footerHtml) {
                $footerJson = $converter->convert($footerHtml);
                $result['footer'] = [
                    'title' => $businessName . ' Footer',
                    'content' => $footerJson,
                    'source_html' => $footerHtml
                ];
            }
            
            error_log('[AI-TB4-HTML] Generation complete');
            
            echo json_encode(['success' => true, 'theme' => $result]);
            
        } catch (\Exception $e) {
            error_log('[AI-TB4-HTML] ERROR: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Generation failed: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Generate full HTML page via AI
     */
    private function generateHtmlPage(string $brief, string $businessName, string $industry, string $style, array $sections, string $model): string
    {
        $systemPrompt = tb_get_html_system_prompt();
        $userPrompt = tb_get_html_user_prompt($brief, $businessName, $industry, $style, $sections);
        
        $response = $this->callAI($systemPrompt, $userPrompt, $model, 8000, false);
        
        return tb_clean_html_response($response);
    }
    
    /**
     * Generate HTML section (header or footer)
     */
    private function generateHtmlSection(string $type, string $brief, string $businessName, string $style, array $navItems, string $model): string
    {
        $systemPrompt = tb_get_html_system_prompt();
        
        if ($type === 'header') {
            $userPrompt = tb_get_html_header_prompt($businessName, $style, $navItems);
        } else {
            $userPrompt = tb_get_html_footer_prompt($businessName, $style, $navItems);
        }
        
        $response = $this->callAI($systemPrompt, $userPrompt, $model, 4000, false);
        
        return tb_clean_html_response($response);
    }
    
    /**
     * Get appropriate sections for a page type (HTML method)
     */
    private function getSectionsForPageHtml(string $pageName): array
    {
        $pageType = strtolower($pageName);
        
        $pageSections = [
            'about' => ['hero', 'about', 'team', 'stats', 'cta'],
            'services' => ['hero', 'services', 'features', 'pricing', 'cta'],
            'contact' => ['hero', 'contact', 'faq'],
            'pricing' => ['hero', 'pricing', 'features', 'faq', 'cta'],
            'portfolio' => ['hero', 'gallery', 'testimonials', 'cta'],
            'blog' => ['hero', 'features', 'cta'],
            'faq' => ['hero', 'faq', 'contact', 'cta'],
        ];
        
        return $pageSections[$pageType] ?? ['hero', 'features', 'cta'];
    }
    
    /**
     * AJAX: Convert raw HTML to TB JSON (utility endpoint)
     */
    public function convertHtml(\Core\Request $request): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['html'])) {
            echo json_encode(['success' => false, 'error' => 'HTML content required']);
            exit;
        }
        
        try {
            require_once CMS_ROOT . '/core/theme-builder/html-converter/Converter.php';
            require_once CMS_ROOT . '/core/theme-builder/html-converter/StyleExtractor.php';
            require_once CMS_ROOT . '/core/theme-builder/html-converter/SectionDetector.php';
            require_once CMS_ROOT . '/core/theme-builder/html-converter/LayoutAnalyzer.php';
            require_once CMS_ROOT . '/core/theme-builder/html-converter/ElementMapper.php';
            
            $converter = new \Core\ThemeBuilder\HtmlConverter\Converter();
            $tbJson = $converter->convert($input['html']);
            
            echo json_encode(['success' => true, 'tb_json' => $tbJson]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        
        exit;
    }

}
