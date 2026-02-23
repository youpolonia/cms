<?php
/**
 * AI Theme Builder — Pipeline (Pro)
 * 
 * 4-step pipeline:
 * 1. Design Brief → theme.json (expanded palette, homepage_sections)
 * 2. HTML Structure → header, footer, home sections (with data-ts)
 * 3. CSS Generation → style.css (production-grade, all templates)
 * 4. Assembly → write files to themes/{slug}/
 * 
 * Lessons from manual theme building applied:
 * - Extended color palette (surface-elevated, surface-card, text-dim, border-hover, etc.)
 * - Premium CSS with proper variables, animations, responsive
 * - Photo-forward card patterns (overlay + standard)
 * - Typography hierarchy (section-label → divider → title → desc)
 * - Theme Studio integration (theme_get, data-ts, generate_studio_css_overrides)
 * - Gallery template support
 */

require_once __DIR__ . '/ai-theme-templates.php';
require_once __DIR__ . '/header-patterns.php';
require_once __DIR__ . '/footer-patterns.php';
require_once __DIR__ . '/hero-patterns.php';
require_once __DIR__ . '/features-patterns.php';
require_once __DIR__ . '/about-patterns.php';
require_once __DIR__ . '/testimonials-patterns.php';
require_once __DIR__ . '/pricing-patterns.php';
require_once __DIR__ . '/cta-patterns.php';
require_once __DIR__ . '/faq-patterns.php';
require_once __DIR__ . '/stats-patterns.php';
require_once __DIR__ . '/clients-patterns.php';
require_once __DIR__ . '/gallery-patterns.php';
require_once __DIR__ . '/team-patterns.php';
require_once __DIR__ . '/blog-patterns.php';
require_once __DIR__ . '/contact-patterns.php';

class AiThemeBuilder
{
    private string $prompt = '';
    private string $industry = 'business';
    private string $style = 'modern';
    private string $mood = 'light';
    private string $provider = '';
    private string $model = '';
    private string $language = 'English';
    private string $tone = 'professional';
    private array $steps = [];
    private array $timings = [];
    private string $slug = '';
    private string $knowledgeBase = '';
    private string $existingThemesContext = '';
    private array $headerSettings = [];
    private array $footerSettings = [];
    private array $heroSettings = [];
    private array $selectedPages = [];
    private array $seededPages = [];
    private string $creativity = 'medium';
    private ?\Closure $onProgress = null;
    private array $headerPatternResult = [];
    private array $footerPatternResult = [];
    private array $heroPatternResult = [];
    private array $sectionPatternResults = [];
    private array $pexelsImages = [];

    public function __construct(array $options = [])
    {
        $this->provider = $options['provider'] ?? '';
        $this->model    = $options['model'] ?? '';
        $this->language = $options['language'] ?? 'English';
        $this->creativity = $options['creativity'] ?? 'medium';

        // Load knowledge base
        $kbPath = CMS_ROOT . '/core/ai-theme-knowledge.md';
        if (file_exists($kbPath)) {
            $this->knowledgeBase = file_get_contents($kbPath);
        }

        // Load CMS core AI functions (no JTB dependency)
        require_once CMS_ROOT . '/core/ai_content.php';

        // Verify at least one provider is configured
        $aiSettings = ai_config_load_full();
        $hasProvider = false;
        foreach ($aiSettings['providers'] ?? [] as $pConf) {
            if (!empty($pConf['enabled']) && !empty($pConf['api_key'])) {
                $hasProvider = true;
                break;
            }
        }
        if (!$hasProvider) {
            throw new \RuntimeException('No AI provider configured. Add API key in Settings → AI Configuration.');
        }
        // Default provider from config if not set by caller
        if (empty($this->provider)) {
            $this->provider = $aiSettings['default_provider'] ?? 'anthropic';
        }
    }

    /**
     * Extract a section from the knowledge base by heading number
     * e.g. getKB('2') returns "## 2. theme.json..." section
     */
    private function getKB(string ...$sections): string
    {
        if (empty($this->knowledgeBase)) return '';
        $result = [];
        foreach ($sections as $num) {
            if (preg_match('/^(## ' . preg_quote($num, '/') . '\..*?)(?=^## \d+\.|\z)/ms', $this->knowledgeBase, $m)) {
                $result[] = trim($m[1]);
            }
        }
        return implode("\n\n", $result);
    }

    /**
     * Set progress callback for streaming updates.
     * Callback signature: fn(string $event, array $data)
     */
    public function setProgressCallback(\Closure $cb): void
    {
        $this->onProgress = $cb;
    }

    private function emitProgress(string $event, array $data = []): void
    {
        if ($this->onProgress) {
            ($this->onProgress)($event, $data);
        }
    }

    /**
     * Run the full pipeline
     */
    public function generate(array $params = []): array
    {
        $this->prompt   = $params['prompt'] ?? '';
        $this->industry = $params['industry'] ?? 'portfolio';
        $this->style    = $params['style'] ?? 'minimalist';
        $this->mood     = $params['mood'] ?? 'light';
        $this->tone     = $params['tone'] ?? 'professional';

        if (empty($this->prompt)) {
            return ['ok' => false, 'error' => 'Prompt is required'];
        }

        // Scan existing themes so AI knows what NOT to copy
        $this->existingThemesContext = $this->buildExistingThemesContext();

        $modelUsed = $this->model ?: ($this->provider . ' default');

        try {
            // Step 1: Design Brief
            $t0 = microtime(true);
            $this->steps['brief'] = ['status' => 'running'];
            $this->emitProgress('step', ['step' => 1, 'status' => 'running', 'label' => 'Generating design brief...']);
            $brief = $this->step1_designBrief();
            $this->steps['brief'] = ['status' => 'done', 'data' => $brief];
            $this->timings['step1'] = (int)((microtime(true) - $t0) * 1000);
            $this->emitProgress('step', ['step' => 1, 'status' => 'done', 'timing' => $this->timings['step1'], 'name' => $brief['name'] ?? '']);

            // Step 2: HTML Structure
            $t0 = microtime(true);
            $this->steps['html'] = ['status' => 'running'];
            $this->emitProgress('step', ['step' => 2, 'status' => 'running', 'label' => 'Building HTML structure...']);
            $html = $this->step2_htmlStructure($brief);
            $this->steps['html'] = ['status' => 'done'];
            $this->timings['step2'] = (int)((microtime(true) - $t0) * 1000);
            $sectionsParsed = !empty($html['sections']) ? count($html['sections']) : 0;
            $this->emitProgress('step', ['step' => 2, 'status' => 'done', 'timing' => $this->timings['step2'], 'sections' => $sectionsParsed]);

            // Step 3: CSS Generation
            $t0 = microtime(true);
            $this->steps['css'] = ['status' => 'running'];
            $this->emitProgress('step', ['step' => 3, 'status' => 'running', 'label' => 'Generating CSS...']);
            $css = $this->step3_cssGeneration($brief, $html);
            $this->steps['css'] = ['status' => 'done'];
            $this->timings['step3'] = (int)((microtime(true) - $t0) * 1000);
            $this->emitProgress('step', ['step' => 3, 'status' => 'done', 'timing' => $this->timings['step3'], 'coverage' => $this->steps['css']['selector_coverage'] ?? null]);

            // Step 4: Assembly
            $t0 = microtime(true);
            $this->steps['assembly'] = ['status' => 'running'];
            $this->emitProgress('step', ['step' => 4, 'status' => 'running', 'label' => 'Assembling theme files...']);
            $slug = $this->step4_assembly($brief, $html, $css);
            $this->steps['assembly'] = ['status' => 'done'];
            $this->timings['step4'] = (int)((microtime(true) - $t0) * 1000);
            $this->emitProgress('step', ['step' => 4, 'status' => 'done', 'timing' => $this->timings['step4']]);

            $sectionCount = !empty($html['sections']) ? count($html['sections']) : 0;
            $colorCount = count($brief['colors'] ?? []);
            $fontCount = 0;
            if (!empty($brief['typography']['headingFont'])) $fontCount++;
            if (!empty($brief['typography']['fontFamily'])) $fontCount++;

            return [
                'ok' => true,
                'slug' => $slug,
                'theme_name' => $brief['name'] ?? ucfirst($slug),
                'steps' => $this->steps,
                'timings' => $this->timings,
                'model_used' => $modelUsed,
                'summary' => [
                    'sections' => max($sectionCount, 1),
                    'fonts' => $fontCount,
                    'colors' => $colorCount,
                ],
            ];
        } catch (\Throwable $e) {
            $failedStep = 1;
            if (isset($this->timings['step1'])) $failedStep = 2;
            if (isset($this->timings['step2'])) $failedStep = 3;
            if (isset($this->timings['step3'])) $failedStep = 4;

            $msg = $e->getMessage();
            $errorInfo = null;
            if (str_contains($msg, '|||AI_ERROR|||')) {
                [$msg, $errorJson] = explode('|||AI_ERROR|||', $msg, 2);
                $errorInfo = @json_decode($errorJson, true);
                $msg = trim($msg);
            }
            $result = [
                'ok' => false,
                'error' => $msg,
                'step' => $failedStep,
                'steps' => $this->steps,
                'timings' => $this->timings,
            ];
            if ($errorInfo) $result['error_info'] = $errorInfo;
            return $result;
        }
    }

    /* ═══════════════════════════════════════════════════════
       WIZARD API: Separate methods for multi-step wizard
       ═══════════════════════════════════════════════════════ */

    /**
     * Step 1 ONLY: Generate design brief (colors, fonts, style)
     * Returns brief for user to review/edit before continuing.
     */
    public function generateBriefOnly(array $params = []): array
    {
        $this->prompt   = $params['prompt'] ?? '';
        $this->industry = $params['industry'] ?? 'portfolio';
        $this->style    = $params['style'] ?? 'minimalist';
        $this->mood     = $params['mood'] ?? 'light';
        $this->tone     = $params['tone'] ?? 'professional';
        $this->language = $params['language'] ?? 'en';

        if (empty($this->prompt)) {
            return ['ok' => false, 'error' => 'Prompt is required'];
        }

        // Load existing themes so AI knows what NOT to duplicate
        $this->existingThemesContext = $this->buildExistingThemesContext();

        try {
            $brief = $this->step1_designBrief();
            return [
                'ok' => true,
                'brief' => $brief,
                'slug' => $brief['slug'] ?? preg_replace('/[^a-z0-9-]/', '', strtolower(str_replace(' ', '-', substr($this->prompt, 0, 30)))),
            ];
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $errorInfo = null;
            if (str_contains($msg, '|||AI_ERROR|||')) {
                [$msg, $errorJson] = explode('|||AI_ERROR|||', $msg, 2);
                $errorInfo = @json_decode($errorJson, true);
                $msg = trim($msg);
            }
            // Also try to get error info from AI Core
            if (!$errorInfo) {
                $errorInfo = null;
            }
            $result = ['ok' => false, 'error' => $msg];
            if ($errorInfo) $result['error_info'] = $errorInfo;
            return $result;
        }
    }

    /**
     * Steps 2+3: Generate layout (header + footer + homepage sections + CSS)
     * Requires brief from Step 1, page list from Step 2, and optional business info.
     */
    public function generateLayoutOnly(array $params = []): array
    {
        $this->prompt   = $params['prompt'] ?? '';
        $this->industry = $params['industry'] ?? 'portfolio';
        $this->style    = $params['style'] ?? 'minimalist';
        $this->mood     = $params['mood'] ?? 'light';
        $this->tone     = $params['tone'] ?? 'professional';
        $this->language = $params['language'] ?? 'en';

        $brief = $params['brief'] ?? null;
        $pages = $params['pages'] ?? ['home', 'about', 'services', 'gallery', 'blog', 'contact'];
        $this->selectedPages = $pages;
        $businessInfo = $params['business_info'] ?? [];
        $this->headerSettings = $params['header_settings'] ?? [];
        $this->footerSettings = $params['footer_settings'] ?? [];
        $this->heroSettings = $params['hero_settings'] ?? [];

        if (!$brief) {
            return ['ok' => false, 'error' => 'Brief is required. Run generateBriefOnly first.'];
        }

        // Set slug from brief (Step 1 normally sets this, but wizard passes brief directly)
        $this->slug = $brief['slug'] ?? $params['slug'] ?? preg_replace('/[^a-z0-9-]/', '', strtolower(str_replace(' ', '-', substr($this->prompt, 0, 30))));
        if (empty($this->slug)) {
            $this->slug = 'ai-theme-' . date('His');
        }

        // Inject page list and business info into brief for AI context
        $brief['site_pages'] = $pages;
        if (!empty($businessInfo)) {
            $brief['business_info'] = $businessInfo;
        }

        $this->existingThemesContext = $this->buildExistingThemesContext();

        // Use user-selected images if provided, otherwise auto-fetch from Pexels
        $selectedImages = $params['selected_images'] ?? [];
        $this->pexelsImages = [];
        if (!empty($selectedImages) && is_array($selectedImages)) {
            // User picked images in the wizard — use those directly
            foreach ($selectedImages as $img) {
                if (!empty($img['src'])) {
                    $this->pexelsImages[] = [
                        'src' => $img['src'],
                        'alt' => $img['alt'] ?? '',
                        'photographer' => $img['photographer'] ?? '',
                    ];
                }
            }
        }
        if (empty($this->pexelsImages)) {
            // Fallback: auto-fetch from Pexels
            try {
                $images = $this->fetchPexelsImages($this->industry, 12);
                if (!empty($images)) {
                    $this->pexelsImages = $images;
                }
            } catch (\Throwable $e) {
                // Non-critical — continue without stock images
            }
        }

        @file_put_contents('/tmp/aitb-assembly.log', date('H:i:s') . " generateLayoutOnly START slug={$this->slug} pexels=" . count($this->pexelsImages) . "\n", FILE_APPEND);
        try {
            // Step 1: HTML Structure
            $t0 = microtime(true);
            $this->emitProgress('step', ['step' => 1, 'status' => 'running', 'label' => 'Building HTML structure...']);
            $html = $this->step2_htmlStructure($brief);
            $sectionsParsed = !empty($html['sections']) ? count($html['sections']) : 0;
            @file_put_contents('/tmp/aitb-assembly.log', date('H:i:s') . " HTML done: sections=" . $sectionsParsed . " header_len=" . strlen($html['header_html'] ?? '') . " footer_len=" . strlen($html['footer_html'] ?? '') . "\n", FILE_APPEND);
            $this->emitProgress('step', ['step' => 1, 'status' => 'done', 'timing' => (int)((microtime(true) - $t0) * 1000), 'sections' => $sectionsParsed]);

            // Step 2: CSS Generation
            $t0 = microtime(true);
            $this->emitProgress('step', ['step' => 2, 'status' => 'running', 'label' => 'Generating CSS stylesheet...']);
            $css = $this->step3_cssGeneration($brief, $html);
            @file_put_contents('/tmp/aitb-assembly.log', date('H:i:s') . " CSS done: len=" . strlen($css) . "\n", FILE_APPEND);
            $this->emitProgress('step', ['step' => 2, 'status' => 'done', 'timing' => (int)((microtime(true) - $t0) * 1000)]);

            // Step 3: Assembly
            $t0 = microtime(true);
            $this->emitProgress('step', ['step' => 3, 'status' => 'running', 'label' => 'Assembling theme files...']);
            $slug = $this->step4_assembly($brief, $html, $css);
            @file_put_contents('/tmp/aitb-assembly.log', date('H:i:s') . " Assembly done: slug={$slug}\n", FILE_APPEND);
            $this->emitProgress('step', ['step' => 3, 'status' => 'done', 'timing' => (int)((microtime(true) - $t0) * 1000), 'slug' => $slug]);

            return [
                'ok' => true,
                'slug' => $slug,
                'brief' => $brief,
                'sections' => array_keys($html['sections'] ?? []),
                'css_lines' => substr_count($css, "\n") + 1,
                'seeded_pages' => $this->seededPages,
            ];
        } catch (\Throwable $e) {
            @file_put_contents('/tmp/aitb-assembly.log', date('H:i:s') . " ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n", FILE_APPEND);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Step 4: Generate a single sub-page design.
     * Creates an individual section file for a specific page type.
     */
    public function generateSubPage(array $params = []): array
    {
        $this->prompt   = $params['prompt'] ?? '';
        $this->industry = $params['industry'] ?? 'portfolio';
        $this->style    = $params['style'] ?? 'minimalist';
        $this->mood     = $params['mood'] ?? 'light';
        $this->language = $params['language'] ?? 'en';

        $brief = $params['brief'] ?? null;
        $slug = $params['slug'] ?? '';
        $pageType = $params['page_type'] ?? '';
        $businessInfo = $params['business_info'] ?? [];
        $userImages = $params['user_images'] ?? [];
        $pageContent = $params['page_content'] ?? '';  // HTML from Content Studio
        $pastedContent = $params['pasted_content'] ?? '';  // User's own text to format
        $contentPlan = $params['content_plan'] ?? [];   // Plan from Step 1
        $layoutStyle = $params['layout_style'] ?? 'auto';  // User-selected layout style hint

        if (!$brief || !$slug || !$pageType) {
            return ['ok' => false, 'error' => 'brief, slug, and page_type are required'];
        }

        // Validate pageType — prevent path traversal
        $pageType = preg_replace('/[^a-z0-9-]/', '', strtolower($pageType));
        if (empty($pageType) || strlen($pageType) > 30) {
            return ['ok' => false, 'error' => 'Invalid page type'];
        }

        $themeDir = CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir)) {
            return ['ok' => false, 'error' => 'Theme directory not found: ' . $slug];
        }

        // ═══════════════════════════════════════════════════════
        // DESIGN SYSTEM CONTEXT — Full theme awareness for sub-pages
        // ═══════════════════════════════════════════════════════

        // 1. Load the FULL existing CSS for deep analysis
        $existingCss = @file_get_contents($themeDir . '/assets/css/style.css') ?: '';

        // 2. Extract CSS variable values from :root — these ARE the design system
        $cssVarValues = '';
        $cssVarBlock = '';
        if (!empty($existingCss) && preg_match('/:root\s*\{([^}]+)\}/', $existingCss, $rootMatch)) {
            $cssVarBlock = trim($rootMatch[1]);
            $cssVarValues = "THEME DESIGN SYSTEM — CSS VARIABLE VALUES (your page MUST use these exclusively):\n:root {\n{$cssVarBlock}\n}";
        }

        // 3. Extract existing class naming patterns for consistency
        $cssClassPatterns = '';
        if (!empty($existingCss)) {
            preg_match_all('/\.([a-z][a-z0-9-]+)\s*[{,]/', $existingCss, $cssMatches);
            if (!empty($cssMatches[1])) {
                $uniqueClasses = array_values(array_unique($cssMatches[1]));
                // Group by prefix for better context
                $prefixes = [];
                foreach (array_slice($uniqueClasses, 0, 80) as $cls) {
                    $parts = explode('-', $cls, 2);
                    $prefixes[$parts[0]][] = $cls;
                }
                $patternLines = [];
                foreach ($prefixes as $prefix => $classes) {
                    $patternLines[] = "  {$prefix}-*: " . implode(', ', array_slice($classes, 0, 6));
                }
                $cssClassPatterns = "EXISTING CSS CLASS NAMING PATTERNS (follow this naming convention):\n" . implode("\n", $patternLines);
            }
        }

        // 4. Extract key CSS patterns from the homepage (card styles, section patterns, button styles)
        $designPatterns = '';
        if (!empty($existingCss)) {
            $patterns = [];

            // Extract card styling pattern
            if (preg_match('/\.[a-z-]*card[^{]*\{([^}]+)\}/i', $existingCss, $cardMatch)) {
                $patterns[] = "Card style reference: {" . trim(preg_replace('/\s+/', ' ', $cardMatch[1])) . "}";
            }

            // Extract button styling pattern
            if (preg_match('/\.btn-primary[^{]*\{([^}]+)\}/i', $existingCss, $btnMatch)) {
                $patterns[] = "Button style reference: {" . trim(preg_replace('/\s+/', ' ', $btnMatch[1])) . "}";
            }

            // Extract section header pattern
            if (preg_match('/\.section-header[^{]*\{([^}]+)\}/i', $existingCss, $secMatch)) {
                $patterns[] = "Section header reference: {" . trim(preg_replace('/\s+/', ' ', $secMatch[1])) . "}";
            }

            // Extract hero overlay pattern
            if (preg_match('/\.hero-overlay[^{]*\{([^}]+)\}/i', $existingCss, $overlayMatch)) {
                $patterns[] = "Hero overlay reference: {" . trim(preg_replace('/\s+/', ' ', $overlayMatch[1])) . "}";
            }

            // Extract border-radius pattern
            preg_match_all('/border-radius:\s*([^;]+);/', $existingCss, $radiusMatches);
            if (!empty($radiusMatches[1])) {
                $radii = array_unique(array_map('trim', $radiusMatches[1]));
                $patterns[] = "Border-radius values used: " . implode(', ', array_slice($radii, 0, 5));
            }

            // Extract transition pattern
            if (preg_match('/transition:\s*([^;]+);/', $existingCss, $transMatch)) {
                $patterns[] = "Transition pattern: " . trim($transMatch[1]);
            }

            // Extract box-shadow pattern
            if (preg_match('/box-shadow:\s*([^;]+);/', $existingCss, $shadowMatch)) {
                $patterns[] = "Box-shadow pattern: " . trim($shadowMatch[1]);
            }

            if (!empty($patterns)) {
                $designPatterns = "HOMEPAGE DESIGN PATTERNS (replicate these visual patterns for consistency):\n" . implode("\n", $patterns);
            }
        }

        // 5. Scan already-generated sub-pages for visual diversity context
        $existingPagesContext = '';
        try {
            $pdo = \core\Database::connection();
            $stmt = $pdo->prepare("SELECT slug, title FROM pages WHERE theme_slug = ? AND content IS NOT NULL AND LENGTH(content) > 500 AND slug != ?");
            $stmt->execute([$slug, $slug . '-' . $pageType]);
            $existingPages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($existingPages)) {
                $pageList = array_map(fn($p) => $p['title'] . ' (' . str_replace($slug . '-', '', $p['slug']) . ')', $existingPages);
                $existingPagesContext = "ALREADY GENERATED PAGES (make THIS page visually DISTINCT from these):\n- " . implode("\n- ", $pageList) . "\nUse DIFFERENT hero background style, DIFFERENT section layout patterns, DIFFERENT color emphasis.";
            }
        } catch (\Throwable $e) {
            // Non-critical — continue without context
        }

        // 6. Use selected images if provided, otherwise fetch from Pexels
        $selectedImages = $params['selected_images'] ?? [];
        $pexelsImages = [];
        $pexelsImageNote = '';
        if (!empty($selectedImages) && is_array($selectedImages)) {
            foreach ($selectedImages as $img) {
                if (!empty($img['src'])) {
                    $pexelsImages[] = [
                        'src' => $img['src'],
                        'alt' => $img['alt'] ?? '',
                        'photographer' => $img['photographer'] ?? '',
                    ];
                }
            }
        }
        if (empty($pexelsImages)) {
            try {
                $images = $this->fetchPexelsImages($this->industry, 8);
                if (!empty($images)) {
                    $pexelsImages = $images;
                }
            } catch (\Throwable $e) {
                // Pexels fetch failed — AI will use generic approach
            }
        }
        try {
            if (!empty($pexelsImages)) {
                $imgList = [];
                foreach (array_slice($pexelsImages, 0, 8) as $i => $img) {
                    $imgList[] = ($i + 1) . ". {$img['src']} — alt: \"{$img['alt']}\"";
                }
                $pexelsImageNote = "REAL PEXELS IMAGES (use THESE URLs for <img> tags — do NOT invent photo IDs):\n" . implode("\n", $imgList);
            }
        } catch (\Throwable $e) {
            // Image context build failed — non-critical
        }

        // 7. Homepage section HTML excerpts — show AI what the homepage looks like
        $homepageContext = '';
        $sectionFiles = glob($themeDir . '/sections/*.php');
        if (!empty($sectionFiles)) {
            $sectionSnippets = [];
            foreach (array_slice($sectionFiles, 0, 3) as $sFile) {
                $sName = basename($sFile, '.php');
                $sContent = @file_get_contents($sFile);
                if ($sContent) {
                    // Strip PHP tags and get just HTML structure hints
                    $stripped = preg_replace('/<\?php.*?\?>/s', '', $sContent);
                    $stripped = preg_replace('/\s+/', ' ', strip_tags($stripped, '<section><div><h1><h2><h3><p><span><a>'));
                    $stripped = trim(substr($stripped, 0, 300));
                    if ($stripped) {
                        $sectionSnippets[] = "  [{$sName}]: {$stripped}...";
                    }
                }
            }
            if (!empty($sectionSnippets)) {
                $homepageContext = "HOMEPAGE SECTION STRUCTURE (for visual consistency reference):\n" . implode("\n", $sectionSnippets);
            }
        }

        // KB sections relevant to page design
        $kbRef = $this->getKB('3', '4', '5', '10', '13', '17', '18');

        $langInstr = $this->languageInstruction();
        $briefJson = json_encode($brief, JSON_PRETTY_PRINT);
        $bizJson = !empty($businessInfo) ? self::buildBusinessProfileText($businessInfo) : '';
        $imagesNote = '';
        if (!empty($userImages)) {
            $imgPaths = array_map(fn($img) => $img['path'] ?? $img, $userImages);
            $imagesNote = "\nUSER IMAGES available (use in <img src=\"...\">): " . implode(', ', $imgPaths);
        }

        // Page-type specific instructions
        $pageInstructions = $this->getPageTypeInstructions($pageType, $layoutStyle);

        // Content from Content Studio (if available)
        $contentSection = '';
        if (!empty($pageContent)) {
            $contentSection = <<<CONTENT

EXISTING PAGE CONTENT (from Content Studio — use this EXACT text, wrap it in beautiful themed sections):
---
{$pageContent}
---
Your job is to DESIGN the visual layout around this content. Keep ALL the text from above, but wrap it in
properly styled <section> elements that match the theme's design system. Add hero banners, section backgrounds,
icons, cards, grids, etc. The content text is FINAL — don't rewrite it, just present it beautifully.
CONTENT;
        }

        // User-pasted real content (higher priority than Content Studio)
        if (!empty($pastedContent) && empty($pageContent)) {
            $contentSection = <<<PASTED

USER'S OWN CONTENT (real text provided by the business owner — use this as the PRIMARY content source):
---
{$pastedContent}
---
CRITICAL: This is REAL content from the actual business, not AI-generated placeholder.
- Structure this text into well-designed <section> elements matching the theme
- Add proper headings (h2, h3) to organize the content logically
- Wrap lists in <ul>/<li>, quotes in <blockquote>, etc.
- Add hero section with the page title
- You MAY lightly edit for grammar/flow but NEVER change the meaning or remove information
- Add visual elements (cards, grids, icons) to present the content attractively
- Fill gaps with brief connecting text if needed, but the user's text is the foundation
PASTED;
        }

        // Content plan context
        $planSection = '';
        if (!empty($contentPlan) && is_array($contentPlan)) {
            $planTitle = $contentPlan['title'] ?? ucfirst($pageType);
            $planBrief = $contentPlan['content_brief'] ?? '';
            $planKeywords = '';
            if (!empty($contentPlan['keywords']['primary'])) {
                $planKeywords = "Primary keyword: " . $contentPlan['keywords']['primary'];
            }
            $planSection = "\nCONTENT PLAN:\n- Title: {$planTitle}\n- Brief: {$planBrief}\n- {$planKeywords}\n";
        }

        // ═══════════════════════════════════════════════════════
        // HERO STYLE VARIATION — deterministic per page type
        // Each page type gets a DIFFERENT hero style to prevent monotony
        // ═══════════════════════════════════════════════════════
        $heroStyles = [
            'about'        => 'GRADIENT BACKGROUND — Use a diagonal gradient from var(--primary) to var(--secondary) with 0.85 opacity overlay. Centered text, no background image.',
            'services'     => 'SOLID COLOR WITH GEOMETRIC PATTERN — Use var(--surface-elevated) background with subtle CSS geometric shapes (circles, lines) as decoration. Left-aligned text with decorative side element.',
            'contact'      => 'SPLIT-SCREEN — Left half: solid var(--primary) with white text, right half: decorative icon grid or map placeholder. Compact height (50vh, not 100vh).',
            'pricing'      => 'MINIMAL BANNER — Narrow hero (40vh) with var(--surface) background, centered title, subtle bottom border or decorative underline.',
            'faq'          => 'WAVE TOP — var(--background) with a CSS clip-path wave at the bottom. Icon cluster (question marks) as decoration.',
            'portfolio'    => 'IMAGE MOSAIC BACKGROUND — Background built from CSS grid of colored rectangles (no image), overlay gradient. Bold centered title.',
            'team'         => 'ANGLED SLICE — Diagonal clip-path dividing primary color and background color. Title on the primary side.',
            'testimonials' => 'QUOTE MARK HERO — Large decorative quotation mark SVG in background, subtle gradient, centered italic title.',
            'gallery'      => 'MASONRY HINT — Hero with small thumbnail grid (3x2) blurred in background behind overlay, centered title.',
            'blog'         => 'EDITORIAL STRIP — Narrow hero (35vh) with strong typography, large serif title, thin top/bottom borders, newspaper-inspired.',
            'events'       => 'CALENDAR ACCENT — Left-aligned date-styled decorative element (day/month blocks), gradient right side, event-inspired visual.',
            'careers'      => 'PHOTO OVERLAY — Full-width with Pexels office/team image as background, dark gradient overlay, centered bold title.',
            'process'      => 'STEP INDICATOR — Hero with numbered circle (step 0) decoration, connecting line leading into the page content, gradient accent.',
            'partners'     => 'LOGO COLLAGE — Muted background with faded geometric shapes suggesting logos, centered professional title.',
        ];
        $heroStyleInstruction = $heroStyles[$pageType] ?? 'GRADIENT BACKGROUND — Use a diagonal gradient from var(--primary) to var(--secondary). Centered text.';

        // ═══════════════════════════════════════════════════════
        // BUILD THE PROMPT — with full Design System Context
        // ═══════════════════════════════════════════════════════

        $systemPrompt = <<<PROMPT
{$langInstr}You are a SENIOR frontend designer generating a PREMIUM sub-page for an existing website theme.
This page MUST look like it was designed by the SAME designer who built the homepage — consistent typography,
colors, spacing, card styles, and visual language.

This content will be stored in the database and rendered inside the theme's layout.php (which already provides
header, footer, Google Fonts, Font Awesome, and CSS variables via the theme's style.css).

═══════════════════════════════════════════════
DESIGN SYSTEM (from the existing theme — follow EXACTLY)
═══════════════════════════════════════════════

{$cssVarValues}

{$cssClassPatterns}

{$designPatterns}

{$homepageContext}

═══════════════════════════════════════════════
PAGE CONTEXT
═══════════════════════════════════════════════

PAGE TYPE: {$pageType}
DESIGN BRIEF:
{$briefJson}

═══ REAL BUSINESS DATA (from the owner) ═══
{$bizJson}
═══════════════════════════════════════════
CRITICAL: Use REAL data above (team names, services, testimonials, hours) instead of generating fake content.
If the owner provided services → use those EXACT names on the services page.
If the owner provided team → use those EXACT people on the about/team page.
If the owner provided testimonials → use those EXACT quotes.
Fill gaps with reasonable copy but ALWAYS prefer real data.
{$imagesNote}
{$contentSection}
{$planSection}

USER REQUEST: {$this->prompt}
INDUSTRY: {$this->industry} | STYLE: {$this->style} | MOOD: {$this->mood}

{$existingPagesContext}

{$pexelsImageNote}

═══════════════════════════════════════════════
PAGE-SPECIFIC DESIGN REQUIREMENTS
═══════════════════════════════════════════════

{$pageInstructions}

═══════════════════════════════════════════════
HERO SECTION STYLE (MANDATORY — this specific page uses this hero pattern)
═══════════════════════════════════════════════

{$heroStyleInstruction}

Include breadcrumb: Home > {$pageType} (use <nav class="{$pageType}-breadcrumb">)

═══════════════════════════════════════════════
CRITICAL OUTPUT FORMAT RULES
═══════════════════════════════════════════════

- Output PURE HTML + CSS only — absolutely NO PHP code, no PHP opening tags, no theme_get(), no esc(), no \$page variables
- Start with a <style> block containing ALL custom CSS for this page
- After the <style> block, output <section> elements with rich, themed content
- NO <!DOCTYPE>, NO <html>, NO <head>, NO <body>, NO <header>, NO <footer>
- layout.php already provides all of those — your output goes inside <main>
- NEVER use inline styles (style="...") on ANY element. Use semantic HTML with the theme's CSS classes. Use .container for width.
  All styling MUST go in the <style> block at the top. Inline style attributes will be stripped.

═══════════════════════════════════════════════
CSS RULES (CRITICAL — design system consistency)
═══════════════════════════════════════════════

VARIABLE USAGE — Use ONLY these CSS variables (they are defined in the theme's style.css):
  Colors: var(--primary), var(--secondary), var(--accent), var(--background), var(--surface),
          var(--surface-elevated), var(--text), var(--text-muted), var(--text-dim),
          var(--border), var(--border-hover), var(--primary-contrast)
  Typography: var(--heading-font), var(--body-font), var(--heading-weight), var(--line-height)
  Layout: var(--border-radius), var(--section-spacing), var(--transition-speed),
          var(--container-width), var(--radius-lg)
  Buttons: var(--button-padding-y), var(--button-padding-x), var(--button-border-radius),
           var(--button-font-weight)

NEVER use hardcoded colors (#hex values) — ALWAYS use var(--name). The only exception is rgba() wrappers
around CSS variable colors for opacity effects. For black/white overlays use rgba(0,0,0,X) or rgba(255,255,255,X).

CLASS NAMING — Prefix ALL custom classes with the page type to avoid CSS collisions:
  .{$pageType}-hero, .{$pageType}-section, .{$pageType}-card, .{$pageType}-grid, etc.

RESPONSIVE — Include breakpoints:
  @media (max-width: 1024px) {{ ... }}
  @media (max-width: 768px) {{ ... }}
  @media (max-width: 480px) {{ ... }}

LAYOUT — Use .container class (already defined in theme CSS) for content width:
  <div class="container"> wraps content inside each <section>

═══════════════════════════════════════════════
CONTENT RULES
═══════════════════════════════════════════════

- ALL text must be REAL, specific to the {$this->industry} industry — NOT "Lorem ipsum" or generic placeholders
- Include data-animate="fade-up" attributes on elements that should animate on scroll
- Use Font Awesome 7 icons (<i class="fas fa-..."></i> or <i class="far fa-..."></i> or <i class="fab fa-..."></i>)
  CRITICAL: Font family names are "Font Awesome 7 Free" (for fas/far) and "Font Awesome 7 Brands" (for fab). Do NOT use "Font Awesome 6".
- All images must have loading="lazy" decoding="async" and descriptive alt text
- Include proper section padding using var(--section-spacing) or calc(var(--section-spacing) * 1.2)
- End with a CTA section that uses var(--primary) background

═══════════════════════════════════════════════
QUALITY STANDARD (PREMIUM — this is what separates amateur from professional)
═══════════════════════════════════════════════

STRUCTURE: Output 400-700 lines of rich, production-quality HTML+CSS
- 4-6 distinct sections with VARIED layouts (grids, cards, full-width, split-screen, timelines)
- Beautiful visual hierarchy: section labels → section dividers → section titles → descriptions
- Card components with hover effects (translateY(-4px), shadow increase, border-color change)
- Icon circles (48-56px, bg rgba of primary with 0.1 opacity, border-radius 12px)

SPACING RHYTHM:
- Section padding: var(--section-spacing) vertical (120-160px — NEVER less than 80px)
- Card grid gap: 24-32px
- Heading margin-bottom: 16-24px, paragraph margin-bottom: 20-28px
- Hero content: generous padding (60px+ top/bottom)

DEPTH & POLISH:
- Cards: box-shadow 0 4px 24px rgba(0,0,0,0.06) resting, 0 12px 48px rgba(0,0,0,0.12) hover
- Subtle gradient backgrounds on alternating sections (not flat solid colors)
- Borders: rgba() with low opacity (0.08-0.15), not hard solid lines
- Smooth transitions: all interactive elements need transition: all var(--transition-speed) cubic-bezier(0.4,0,0.2,1)
- Focus styles: outline 2px solid var(--primary), outline-offset 2px

TYPOGRAPHY:
- Use clamp() for responsive sizes — NO fixed px for headings
- Section titles: clamp(2rem, 4vw, 3rem), font-family var(--heading-font)
- Hero title: clamp(2.5rem, 5vw, 4rem)
- Body text: font-family var(--body-font), line-height var(--line-height)
- Section labels: uppercase, letter-spacing 0.1em+, small font, color var(--primary)

ANTI-PATTERNS (NEVER DO):
- Never use hardcoded hex colors — ALWAYS use CSS variables
- Never have fixed px for font-sizes on headings — use clamp()
- Never leave images without loading="lazy"
- Never create a hero taller than 60vh on sub-pages (save 100vh for homepage only)
- Never repeat the same section layout twice — vary grids, splits, full-width vs contained
- Never leave cards without hover effects
- Never skip the .container wrapper inside sections

DO NOT output markdown backticks, JSON wrappers, or any explanation — output raw HTML+CSS only.
PROMPT;

        try {
            $result = $this->aiQuery("Generate the {$pageType} page as pure HTML+CSS", $this->queryOptions([
                'system_prompt' => $systemPrompt,
                'max_tokens' => 16000,
                'temperature' => $this->getCreativityTemp('subpage'),
            ]));

            if (empty($result['ok']) || empty($result['text'])) {
                throw new \RuntimeException('AI returned no output for ' . $pageType . ': ' . ($result['error'] ?? 'unknown'));
            }

            $htmlContent = $result['text'];
            $htmlContent = preg_replace('/^```(?:php|html|css)?\s*/m', '', $htmlContent);
            $htmlContent = preg_replace('/```\s*$/m', '', $htmlContent);
            $htmlContent = trim($htmlContent);

            // Strip any PHP tags that AI might have included despite instructions
            $htmlContent = preg_replace('/<\?(?:php)?.*?\?>/s', '', $htmlContent);
            // Also strip unclosed PHP opening tags (without closing tag)
            $htmlContent = preg_replace('/<\?(?:php)?\b[^?]*$/m', '', $htmlContent);

            // Strip inline style attributes — sub-pages must use <style> block only
            $htmlContent = preg_replace('/\s*style="[^"]*"/', '', $htmlContent);

            // Strip <script> tags — defense-in-depth against XSS from AI output
            $htmlContent = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $htmlContent);

            // Validate we got meaningful content
            if (strlen($htmlContent) < 200) {
                throw new \RuntimeException('AI generated insufficient content for ' . $pageType . ' (only ' . strlen($htmlContent) . ' bytes)');
            }

            // ═══════════════════════════════════════════════════════
            // POST-GENERATION VALIDATION & FIXES
            // ═══════════════════════════════════════════════════════

            // Fix 1: Ensure .container class is used (not raw divs)
            if (!str_contains($htmlContent, 'class="container"') && !str_contains($htmlContent, "class='container'")) {
                $htmlContent = preg_replace(
                    '/(<section[^>]*>)\s*(<div)(?![^>]*class=["\']container)/s',
                    '$1<div class="container">$2',
                    $htmlContent,
                    -1,
                    $containerFixes
                );
                if ($containerFixes > 0) {
                    $htmlContent = preg_replace(
                        '/(<\/div>\s*<\/section>)/',
                        '</div>$1',
                        $htmlContent,
                        $containerFixes
                    );
                }
            }

            // Fix 2: Ensure <style> block exists
            if (!str_contains($htmlContent, '<style')) {
                $htmlContent = "<style>\n/* Page: {$pageType} */\n</style>\n" . $htmlContent;
            }

            // Fix 3: Replace any hardcoded colors that AI might have snuck in
            // Common AI mistakes: using #fff, #000, #333, etc. instead of CSS variables
            $htmlContent = preg_replace('/(?<=[\s:;])#ffffff(?=[;\s}])/', 'var(--background)', $htmlContent);
            $htmlContent = preg_replace('/(?<=[\s:;])#fff(?=[;\s}])/', 'var(--background)', $htmlContent);
            $htmlContent = preg_replace('/(?<=[\s:;])#000000(?=[;\s}])/', 'var(--text)', $htmlContent);

            // Fix 4: Ensure data-animate attributes exist (at least on the first 3 sections)
            if (substr_count($htmlContent, 'data-animate') < 2) {
                // Add data-animate to sections that don't have it
                $htmlContent = preg_replace(
                    '/(<section\s+class="[^"]*")\s*>/s',
                    '$1 data-animate="fade-up">',
                    $htmlContent,
                    3 // Only first 3 sections
                );
            }

            // Fix 5: Validate sub-page hero is not 100vh (should be 40-60vh max)
            $htmlContent = preg_replace(
                '/min-height:\s*100vh/',
                'min-height: 50vh',
                $htmlContent,
                1 // Only the first occurrence (hero)
            );

            // Fix 6: Ensure all <img> tags have loading="lazy" and decoding="async"
            $htmlContent = preg_replace(
                '/<img(?![^>]*loading=)/',
                '<img loading="lazy" decoding="async"',
                $htmlContent
            );

            // Post-process: Remove truncated/incomplete <script> blocks
            // AI models (especially DeepSeek) often truncate output mid-script,
            // leaving unclosed <script> tags that eat all HTML after them (footer, main.js)
            // The theme's main.js already handles [data-animate] via IntersectionObserver
            if (preg_match('/<script\b[^>]*>(?!.*<\/script>)/si', $htmlContent)) {
                // Has an opening <script> without matching </script> — truncated
                $htmlContent = preg_replace('/<script\b[^>]*>(?!.*<\/script>).*$/si', '', $htmlContent);
                $htmlContent = rtrim($htmlContent) . "\n";
            }

            // Also strip complete inline <script> blocks — theme main.js handles animations
            // Sub-page scripts duplicate observer logic and can conflict
            $htmlContent = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $htmlContent);
            $htmlContent = rtrim($htmlContent) . "\n";

            // Fix fictional image paths — replace with Pexels URLs
            // AI generates /themes/slug/assets/gallery/photo.jpg etc. that don't exist
            if (!empty($pexelsImages)) {
                $pxUrls = array_column($pexelsImages, 'src');
                $pxIdx = 0;
                $htmlContent = preg_replace_callback(
                    '#src="(/themes/[^"]+/assets/[^"]+\.(?:jpg|jpeg|png|webp))"#i',
                    function ($m) use (&$pxIdx, $pxUrls) {
                        if (!empty($pxUrls[$pxIdx % count($pxUrls)])) {
                            return 'src="' . $pxUrls[$pxIdx++ % count($pxUrls)] . '"';
                        }
                        return $m[0];
                    },
                    $htmlContent
                );
                // Also fix background-image: url('/themes/...')
                $htmlContent = preg_replace_callback(
                    '#url\([\'"]?(/themes/[^\'")]+/assets/[^\'")]+\.(?:jpg|jpeg|png|webp))[\'"]?\)#i',
                    function ($m) use (&$pxIdx, $pxUrls) {
                        if (!empty($pxUrls[$pxIdx % count($pxUrls)])) {
                            return "url('" . $pxUrls[$pxIdx++ % count($pxUrls)] . "')";
                        }
                        return $m[0];
                    },
                    $htmlContent
                );
            }

            // Save content to database — UPDATE the page that was already created by seedContent
            $pdo = \core\Database::connection();
            $pageSlug = $slug . '-' . $pageType;

            // Try UPDATE first (page should exist from seedContent)
            $stmt = $pdo->prepare("UPDATE pages SET content = ?, template = 'page' WHERE slug = ? AND theme_slug = ?");
            $stmt->execute([$htmlContent, $pageSlug, $slug]);
            $updatedRows = $stmt->rowCount();

            // If UPDATE hit 0 rows — page doesn't exist yet, INSERT as fallback
            if ($updatedRows === 0) {
                $pageTitle = $params['page_title'] ?? ucfirst(str_replace('-', ' ', $pageType));
                $metaDesc = $contentPlan['meta_description'] ?? '';
                $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, template, status, theme_slug, meta_description, created_at, updated_at) VALUES (?, ?, ?, 'page', 'published', ?, ?, NOW(), NOW())");
                $stmt->execute([$pageTitle, $pageSlug, $htmlContent, $slug, $metaDesc]);
            }

            // Save content plan meta_description if available (UPDATE path)
            if ($updatedRows > 0 && !empty($contentPlan['meta_description'])) {
                $stmt = $pdo->prepare("UPDATE pages SET meta_description = ? WHERE slug = ? AND theme_slug = ?");
                $stmt->execute([$contentPlan['meta_description'], $pageSlug, $slug]);
            }

            // Log generation details
            $logData = date('H:i:s') . " SubPage generated: {$pageType} for {$slug}\n"
                . "  Content length: " . strlen($htmlContent) . " bytes\n"
                . "  Has <style>: " . (str_contains($htmlContent, '<style') ? 'yes' : 'NO') . "\n"
                . "  Has <section>: " . (str_contains($htmlContent, '<section') ? 'yes' : 'NO') . "\n"
                . "  Section count: " . substr_count($htmlContent, '<section') . "\n"
                . "  Has .container: " . (str_contains($htmlContent, 'container') ? 'yes' : 'NO') . "\n"
                . "  Has data-animate: " . (str_contains($htmlContent, 'data-animate') ? 'yes' : 'NO') . "\n"
                . "  DB action: " . ($updatedRows === 0 ? 'INSERT' : 'UPDATE') . "\n\n";
            @file_put_contents('/tmp/aitb-subpage.log', $logData, FILE_APPEND);

            return [
                'ok' => true,
                'slug' => $slug,
                'page_type' => $pageType,
                'content_length' => strlen($htmlContent),
                'stored_in' => 'database',
                'page_slug' => $pageSlug,
                'template' => 'page',
                'was_insert' => ($updatedRows === 0),
                'has_style_block' => str_contains($htmlContent, '<style'),
                'has_sections' => str_contains($htmlContent, '<section'),
                'section_count' => substr_count($htmlContent, '<section'),
                'has_container' => str_contains($htmlContent, 'container'),
                'has_animations' => str_contains($htmlContent, 'data-animate'),
                'has_content_studio' => !empty($pageContent),
                'pexels_images_provided' => count($pexelsImages),
                'existing_pages_context' => !empty($existingPagesContext),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Step 5: Finalize — seed all CMS content (menus, pages, galleries, articles)
     * Uses business info and user images from wizard state.
     */
    public function finalizeTheme(array $params = []): array
    {
        $this->prompt   = $params['prompt'] ?? '';
        $this->industry = $params['industry'] ?? 'portfolio';
        $this->language = $params['language'] ?? 'en';

        $brief = $params['brief'] ?? [];
        $slug = $params['slug'] ?? '';
        $businessInfo = $params['business_info'] ?? [];
        $userImages = $params['user_images'] ?? [];
        $selectedPages = $params['pages'] ?? [];
        $this->selectedPages = $selectedPages;

        if (!$slug) {
            return ['ok' => false, 'error' => 'slug is required'];
        }

        $this->slug = $slug;

        try {
            // Seed content with user-provided business info
            $this->seedContent($brief, $selectedPages);

            return [
                'ok' => true,
                'slug' => $slug,
                'seeded' => true,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get page-type specific AI instructions.
     */
    private function getPageTypeInstructions(string $pageType, string $layoutStyle = 'auto'): string
    {
        $instructions = [
            'about' => "ABOUT PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (40-50vh, NOT 100vh):
- Page title + breadcrumb navigation (Home > About)
- Use actual text, not PHP variables
- Subtle decorative element (gradient, pattern, or geometric shape)

SECTION 2 — OUR STORY (2-column split layout):
- LEFT: Large Pexels image (rounded corners, subtle shadow)
- RIGHT: Compelling narrative about this {$this->industry} business
- Include a quote or highlighted stat in a decorative box
- Layout: CSS Grid with grid-template-columns: 1fr 1fr; gap: 60px

SECTION 3 — MISSION & VALUES (card grid):
- 3-4 value cards in a responsive grid
- Each card: Font Awesome icon in a 56px circle (bg rgba of primary, 0.1 opacity) → title → description
- Cards with border, padding 32px, hover translateY(-4px)
- Background: var(--surface) for contrast

SECTION 4 — TIMELINE / MILESTONES (vertical timeline):
- 4-6 milestones with year, title, and description
- CSS timeline line (2px var(--primary)) connecting dots
- Alternating left-right on desktop, linear on mobile
- Each milestone with a date badge and icon

SECTION 5 — TEAM PREVIEW (optional, if team page not in nav):
- 3-4 team member cards with Pexels portrait photos
- Name, role, short bio, social icons (LinkedIn, Twitter)
- Photo with border-radius: 50% for circular crop OR rounded square

SECTION 6 — CTA:
- Full-width background with gradient (primary → secondary, low opacity)
- Strong heading + subtext + primary button
- Keep padding generous (80-100px vertical)

All text must tell THIS specific {$this->industry} business's story — real, compelling, unique.",

            'services' => "SERVICES PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (40-50vh):
- Page title + breadcrumb (Home > Services)
- Brief intro paragraph below title
- Optionally a subtle stats row (e.g., 500+ clients, 15 years, 24/7 support)

SECTION 2 — SERVICES OVERVIEW:
- Short intro paragraph about the company's expertise
- Decorative divider (line or dots)

SECTION 3 — SERVICE CARDS (main section):
- 4-8 cards in responsive grid (CSS Grid: repeat(auto-fill, minmax(300px, 1fr)))
- Each card: Font Awesome icon (large, in icon circle) → service title → description → 'Learn more' link
- Cards with border 1px solid var(--border), border-radius var(--radius-lg)
- Hover: translateY(-4px), box-shadow increase, border-color change
- Each service MUST be specific to {$this->industry} with 2-3 sentence descriptions

SECTION 4 — PROCESS STEPS (numbered timeline):
- 4-6 numbered steps showing how the service works
- Layout: horizontal steps on desktop (flexbox), vertical on mobile
- Each step: large number (styled decoratively) → title → short description
- Steps connected by a CSS line or arrow between them
- Background: var(--surface-elevated) for contrast

SECTION 5 — WHY CHOOSE US (split layout):
- 2-column: left = bullet points with checkmark icons, right = Pexels image
- Each bullet: <i class='fas fa-check-circle'></i> + benefit text
- 5-7 compelling reasons

SECTION 6 — CTA:
- Strong call to action: 'Get Started' or 'Request a Quote'
- Primary button + secondary outline button
- Full-width section with gradient background

Each service must be SPECIFIC to {$this->industry} — not generic 'Service 1, Service 2'.",

            'contact' => "CONTACT PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (compact, 35-45vh):
- Title + breadcrumb (Home > Contact)
- Friendly subheading ('We'd love to hear from you')

SECTION 2 — CONTACT INFO CARDS:
- 3-4 cards in a responsive grid (phone, email, address, hours)
- Each card: large Font Awesome icon → label → value
- Cards with var(--surface) background, centered content
- Icons in colored circles (48px, var(--primary) bg with low opacity)

SECTION 3 — CONTACT FORM (the main section):
- Full working HTML form with action='#' method='post'
- Fields: name (text), email (email), phone (tel), subject (select with 4-5 options), message (textarea)
- Submit button: styled with var(--primary) background, full width or centered
- Form styled in a card with padding 40px, subtle shadow
- 2-column layout for name/email, full-width for message
- Each input: border 1px solid var(--border), border-radius var(--border-radius), padding 14px
- Focus state: border-color var(--primary), box-shadow glow
- Labels above inputs, required asterisks on mandatory fields

SECTION 4 — MAP / LOCATION:
- Full-width div with var(--surface-elevated) background, min-height 300px
- 'Find Us' heading with address text
- Decorative map placeholder (could use a gradient or pattern background)
- Optional: Opening hours table

SECTION 5 — CTA:
- 'Prefer to call?' section with phone number
- Alternative contact methods (social media links)

Use business_info JSON for real contact details where available (phone, email, address).",

            'pricing' => "PRICING PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (compact, 35-45vh):
- Title + breadcrumb (Home > Pricing)
- Subheading: 'Simple, transparent pricing' or industry-appropriate

SECTION 2 — PRICING TOGGLE (optional):
- Monthly/Annual toggle switch (decorative only — just visual toggle)
- 'Save 20%' badge next to Annual

SECTION 3 — PRICING CARDS (main):
- 3 tiers in a row (CSS Grid or Flexbox, equal height)
- MIDDLE card: 'Popular' or 'Recommended' badge, slightly elevated (translateY(-8px)), primary border
- Each card: plan name → price (large) → period (/month) → feature list with check/x icons → CTA button
- Features: 6-8 items per plan, check = included (var(--primary)), x = not included (var(--text-muted))
- Buttons: middle card = btn-primary (filled), others = btn-outline
- Cards with generous padding (40px), border-radius var(--radius-lg)

SECTION 4 — FEATURE COMPARISON TABLE:
- Responsive table or grid comparing all 3 plans
- Feature rows with plan columns
- Check/x icons for each feature per plan
- Sticky header on scroll (optional)
- On mobile: stacked cards instead of table

SECTION 5 — FAQ about pricing:
- 4-6 FAQs using <details>/<summary> elements
- Questions: refund policy, payment methods, upgrades, custom plans
- Styled summary with arrow icon, open state styling

SECTION 6 — CTA:
- 'Not sure which plan?' or 'Need a custom solution?'
- Contact us button

Make pricing REALISTIC for {$this->industry} — real price ranges, real features.",

            'faq' => "FAQ PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (compact, 35-40vh):
- Title + breadcrumb (Home > FAQ)
- Subheading: 'Everything you need to know'
- Optional: search-style decorative input

SECTION 2 — FAQ CATEGORIES (filter bar):
- Horizontal button row: 'All', 'General', 'Services', 'Pricing', 'Support'
- Styled as pill buttons with active state (primary bg)
- Decorative only — visual categories

SECTION 3 — FAQ ITEMS (main content):
- 8-12 FAQ items using <details>/<summary> HTML5 elements
- Each item: styled summary with question text + chevron icon
- Summary: padding 20px, border-bottom 1px solid var(--border), cursor pointer
- Open state: chevron rotates 180deg, answer fades in
- Answer: padding 20px, font-size slightly smaller, var(--text-muted) color
- Group by category with section headings between groups

SECTION 4 — STILL HAVE QUESTIONS:
- Contact CTA card with phone, email, chat icons
- 'Can't find what you're looking for?' heading
- Primary button: 'Contact Us'
- Background: var(--surface-elevated)

Generate 8-12 REALISTIC FAQs with DETAILED answers specific to {$this->industry}.
Each answer should be 2-3 sentences minimum.",

            'portfolio' => "PORTFOLIO PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (40-50vh):
- Title + breadcrumb (Home > Portfolio)
- Brief intro about the work showcase

SECTION 2 — FILTER BAR:
- Category buttons: 'All', + 4-5 categories relevant to {$this->industry}
- Styled as pill/chip buttons with active state
- Horizontal scroll on mobile

SECTION 3 — PORTFOLIO GRID (main):
- 6-9 project cards in CSS Grid (repeat(auto-fill, minmax(350px, 1fr)))
- Each card: Pexels image (aspect-ratio 4/3, object-fit cover)
- Hover overlay: dark gradient from bottom, project title + category badge appear
- Subtle scale(1.02) on hover with transition
- Optional: masonry-style layout using grid-row-end: span 2 on featured items

SECTION 4 — FEATURED PROJECT:
- Full-width spotlight on one project
- 2-column: large image left, description right
- Client name, challenge, solution, results
- Testimonial quote from the client

SECTION 5 — CTA:
- 'Have a project in mind?' heading
- Buttons: 'Start a Project' + 'View All Work'

Use data-animate='fade-up' for staggered card entry animations.",

            'team' => "TEAM PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (40-50vh):
- Title + breadcrumb (Home > Our Team)
- Team philosophy or mission statement as subheading

SECTION 2 — LEADERSHIP:
- 2-3 leadership cards (larger, featured layout)
- Each: Pexels portrait photo (rounded or circular) + name + title + bio paragraph + social icons
- 2-column layout for featured members

SECTION 3 — TEAM GRID:
- 4-8 team member cards in responsive grid
- Each card: Pexels photo (consistent aspect ratio) → name → role → short bio
- Social icons on hover (LinkedIn, Twitter) — slide up from bottom
- Cards with subtle border, padding, hover elevation

SECTION 4 — CULTURE:
- 2-3 culture highlights (work environment, values, benefits)
- Photo + text alternating layout
- Icons for each benefit/value

SECTION 5 — JOIN US CTA:
- 'Want to join our team?' heading
- 'View Open Positions' button
- Background: gradient

Generate 4-8 team members with REALISTIC names appropriate for {$this->industry}.
Use diverse names reflecting a real team.",

            'testimonials' => "TESTIMONIALS PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (35-45vh):
- Title + breadcrumb (Home > Testimonials)
- Large decorative quotation mark (SVG or Font Awesome fa-quote-left, oversized, low opacity)

SECTION 2 — FEATURED TESTIMONIAL:
- Large blockquote with decorative quote marks
- Client photo (circular), name, role, company
- Star rating (5 filled stars with Font Awesome)
- Generous padding, var(--surface-elevated) background

SECTION 3 — TESTIMONIALS GRID:
- 6-8 testimonial cards in responsive grid (2-3 columns)
- Each card: quote text → star rating → author photo (small, circular) → name → role/company
- Vary card heights naturally (masonry feel)
- Cards with border, padding 32px, subtle shadow

SECTION 4 — STATS:
- Social proof numbers: '500+ Happy Clients', '4.9 Rating', '98% Recommend'
- Counters in a row with icons

SECTION 5 — CTA:
- 'Share Your Experience' heading
- Link to leave a review

Generate 6-8 REALISTIC testimonials specific to {$this->industry} with varied detail levels.",

            'gallery' => "GALLERY PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (35-40vh):
- Title + breadcrumb (Home > Gallery)
- Brief description

SECTION 2 — FILTER BAR:
- Category buttons relevant to {$this->industry}
- Pill-style, horizontal scroll on mobile

SECTION 3 — GALLERY GRID:
- 9-12 image cards in CSS Grid (auto-fill, varied sizes for masonry effect)
- Each image: Pexels photo, object-fit cover
- Hover: dark overlay with expand icon (<i class='fas fa-expand'></i>) and caption
- Some images span 2 columns (grid-column: span 2) for visual variety
- All images: border-radius var(--border-radius), cursor pointer

SECTION 4 — CTA:
- 'Want to see more?' or 'Book a session'
- Contact button

Use images relevant to {$this->industry}.",

            'blog' => "BLOG PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (35-40vh):
- Title + breadcrumb (Home > Blog)
- 'Latest insights and updates' subheading

SECTION 2 — FEATURED ARTICLE:
- Large card: Pexels image left (60%), text right (40%)
- Category badge, title (large), excerpt, author + date, 'Read More' link
- var(--surface-elevated) background

SECTION 3 — ARTICLES GRID with SIDEBAR:
- 2-column layout: articles (70%) + sidebar (30%)
- Articles: 3-6 cards in grid (each with image, category, title, excerpt, date)
- Sidebar: categories list, newsletter signup form (email + button), popular tags
- Cards with border, hover effects

SECTION 4 — NEWSLETTER CTA:
- Full-width: 'Stay Updated' heading, email input + subscribe button
- Background: var(--primary) with low opacity or gradient

Make blog article titles specific to {$this->industry}.",

            'events' => "EVENTS PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (35-45vh):
- Title + breadcrumb (Home > Events)
- Next event countdown or highlight

SECTION 2 — UPCOMING EVENTS:
- 4-6 event cards in a list layout (not grid)
- Each: date badge (day/month styled as calendar icon) → event title → time → location → description → 'Register' button
- Alternating backgrounds: white/surface
- Events specific to {$this->industry}

SECTION 3 — PAST EVENTS:
- Smaller cards, muted styling (opacity 0.7 or grayscale)
- 2-3 past events showing they happened

SECTION 4 — CTA:
- 'Never miss an event' + newsletter subscribe

Generate 4-6 REALISTIC upcoming events for {$this->industry}.",

            'careers' => "CAREERS PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (40-50vh):
- Title + breadcrumb (Home > Careers)
- 'Join our team' + Pexels office/team image background

SECTION 2 — WHY WORK WITH US:
- 4 benefit cards with icons: Culture, Growth, Perks, Impact
- Each card: icon → title → description
- Grid layout, cards with hover effects

SECTION 3 — OPEN POSITIONS:
- 3-5 job listing cards in list layout
- Each: title → department badge → location → type (Full-time/Part-time) → 'Apply' button
- Cards with border-left 4px solid var(--primary)
- Jobs realistic for {$this->industry}

SECTION 4 — APPLICATION CTA:
- 'Don't see your role?' heading
- 'Send us your CV' button + email address
- Background: var(--surface-elevated)

Generate 3-5 REALISTIC job openings for {$this->industry}.",

            'process' => "PROCESS PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (35-45vh):
- Title + breadcrumb (Home > Our Process)
- Brief intro about methodology

SECTION 2 — PROCESS STEPS (main):
- 4-6 numbered steps in timeline layout
- Each step: large number (decorative, var(--primary) color) → icon → title → detailed description
- Steps connected by CSS line (border-left 2px solid var(--primary) on vertical timeline)
- Alternating left/right content on desktop
- Each step with a small Pexels image thumbnail

SECTION 3 — WHAT MAKES US DIFFERENT:
- 3 differentiator cards
- Icon + title + description

SECTION 4 — CTA:
- 'Ready to get started?' + primary button

Make process steps specific to {$this->industry} workflow.",

            'partners' => "PARTNERS / CLIENTS PAGE — Generate these sections in pure HTML+CSS (NO PHP):

SECTION 1 — HERO (35-40vh):
- Title + breadcrumb (Home > Partners)
- 'Trusted by industry leaders'

SECTION 2 — LOGO GRID:
- 8-12 partner logos in a uniform grid (4-6 per row)
- Each: colored rectangle placeholder with company name text
- Grayscale on rest, full color on hover
- CSS filter: grayscale(100%) → grayscale(0%) transition

SECTION 3 — PARTNER TESTIMONIAL:
- Featured quote from a partner/client
- Large blockquote style with photo

SECTION 4 — BECOME A PARTNER:
- Benefits of partnership (3-4 bullet points)
- 'Apply for Partnership' button

SECTION 5 — CTA:
- Contact for partnership inquiries",

        ];

        $base = $instructions[$pageType] ?? "Generate a well-designed page for '{$pageType}' appropriate for a {$this->industry} website.
Output pure HTML+CSS only (NO PHP code). Include:
1. Page hero section (40-50vh) with title and breadcrumb navigation
2. 3-4 content sections with VARIED layouts (grids, splits, timelines, cards)
3. Add data-animate='fade-up' attributes on sections
4. Use Font Awesome icons and Pexels images where appropriate
5. Use CSS variables exclusively — no hardcoded colors
6. End with a CTA section with var(--primary) background gradient
7. All text must be REAL, specific to {$this->industry} and the '{$pageType}' topic
8. Include responsive breakpoints (@media 1024px, 768px, 480px)
9. Use .container class inside each section for content width
10. Cards with hover effects, icon circles, generous padding";

        // ── Layout Style Hints ──
        if ($layoutStyle && $layoutStyle !== 'auto') {
            $styleHints = $this->getLayoutStyleHint($pageType, $layoutStyle);
            if ($styleHints) {
                $base .= "\n\n🎨 USER-SELECTED LAYOUT STYLE: \"{$layoutStyle}\"\n{$styleHints}\nFollow this layout direction closely while maintaining the design system.";
            }
        }

        return $base;
    }

    /**
     * Layout style hints per page type — injected into AI prompt
     * These guide AI's layout decisions without rigid patterns
     */
    private function getLayoutStyleHint(string $pageType, string $style): string
    {
        $hints = [
            'about' => [
                'story'    => "Focus on storytelling: large narrative blocks, pull quotes in decorative boxes, a vertical timeline for company milestones. Start with a full-width story section (image + text split), then timeline, then values. Emotional, long-form feel.",
                'split'    => "Use split layouts throughout: image on one side, text on the other, alternating directions per section. 2-column CSS Grid (1fr 1fr). Large Pexels images with rounded corners. Clean, balanced composition.",
                'minimal'  => "Ultra-clean: generous whitespace (padding 120px+), max-width 720px for text blocks, no decorative elements. Let typography do the work. Single-column centered text with occasional full-bleed images.",
                'magazine' => "Editorial magazine feel: multi-column text blocks, drop caps, pull quotes with large serif text, image captions. Mix 2-column and 3-column grids. Background texture or subtle pattern.",
            ],
            'services' => [
                'cards'    => "Primary layout: responsive card grid (repeat(auto-fill, minmax(300px, 1fr))). Each card: large icon circle (64px), title, 2-3 sentence description, subtle hover lift. Cards with consistent height, generous padding (32px).",
                'detailed' => "Alternating rows: image left + text right, then flip. Each service gets a full-width section with large image (40% width), detailed description, bullet points, and a 'Learn more' link. Generous vertical spacing.",
                'compact'  => "Dense, efficient layout: icon-title-description rows in a 2-column grid. Small icons (32px), concise text. Maximize information density while staying readable. Good for businesses with many services.",
                'showcase' => "Each service as a hero-like showcase: full-width background image (dark overlay), service title in large white text, description below. Vertical scroll through services. Dramatic, visual-first approach.",
            ],
            'contact' => [
                'form'     => "Large, centered contact form as the main focus. Wide inputs, generous padding, clear labels. Form fields: Name, Email, Phone, Subject dropdown, Message textarea, Submit button. Below form: small info row (address, phone, email).",
                'split'    => "Two-column layout: LEFT = contact info cards (address, phone, email, hours), each with icon + text. RIGHT = contact form. Even 50/50 split with CSS Grid. Info side has subtle background color.",
                'map'      => "Top section: embedded map placeholder (styled div with map-like background). Below: 2-column layout with form on left, contact details on right. Include business hours section.",
                'cards'    => "Contact info as prominent cards: large icon, title (Call Us / Email Us / Visit Us / Hours), details. 4 cards in a grid. Below cards: simple contact form. Cards with hover effects and distinct colors.",
            ],
            'pricing' => [
                'columns'  => "Classic 2-3 column pricing comparison. Middle column elevated/highlighted as 'Popular'. Each column: plan name, price (large), feature list with checkmarks, CTA button. Include monthly/yearly toggle if possible.",
                'cards'    => "Elevated cards with shadows, rounded corners. Each card: colored top border (different per tier), plan name, price, features, button. Cards with hover translateY. Popular card with badge and different background.",
                'table'    => "Full-width comparison table with features on left and plan columns on right. Checkmarks/X for feature availability. Sticky header row. Alternating row backgrounds. CTA buttons at bottom of each column.",
                'minimal'  => "Clean, simple pricing. Each plan in a bordered box with ample whitespace. No decorative elements — just plan name, price, features list, and button. Rely on typography hierarchy for visual interest.",
            ],
            'gallery' => [
                'grid'     => "Clean uniform grid: CSS Grid with repeat(auto-fill, minmax(280px, 1fr)). All images same aspect ratio (4:3 or 1:1). Subtle gap (16px). Image hover: slight scale(1.03) + shadow increase. Optional category filter tabs.",
                'masonry'  => "Pinterest-style masonry: varying image heights, 3-4 columns. Use CSS columns or grid with auto-rows. Overlay on hover showing image title/category. Tight gaps (8-12px). Dense, visual-rich layout.",
                'carousel' => "Full-width image carousel/slider. Large images, navigation arrows on sides, dot indicators below. Current image nearly full viewport width. Smooth transitions. Optional thumbnail strip below.",
                'lightbox' => "Grid of thumbnails that open in a full-screen overlay when clicked. Grid: 4 columns, square thumbnails. Hover: zoom icon overlay. Lightbox: dark backdrop, large image, close button, prev/next arrows.",
            ],
            'portfolio' => [
                'grid'     => "Project cards in grid layout. Each card: project image (16:9), title, category tag, brief description. Hover: overlay with 'View Project' link. Filter tabs by category at top.",
                'case'     => "Featured case study layout: large hero image for each project, project title, client name, challenge/solution/results sections. One project at a time, vertical scroll. Detailed, in-depth feel.",
                'minimal'  => "Image-focused: large project images with minimal text. Clean grid, lots of whitespace. Project name as subtle overlay on hover. Let the work speak for itself.",
                'creative' => "Asymmetric, art-direction layout: varying image sizes, overlapping elements, creative use of whitespace. Some images full-bleed, others contained. Magazine-like editorial approach.",
            ],
            'blog' => [
                'grid'     => "3-column card grid. Each card: featured image (16:9), category badge, title, excerpt (2 lines), author + date. Cards with consistent height, hover shadow lift. Pagination at bottom.",
                'list'     => "Horizontal article rows: image on left (200px wide), title + excerpt + meta on right. Full-width rows with divider between. Clean, scannable, newspaper-like.",
                'magazine' => "Magazine layout: featured article (large, full-width) at top, then 2-column grid below. Mix article card sizes. Category labels as colored badges. Sidebar optional.",
                'minimal'  => "Simple list: title, date, excerpt. No images. Maximum readability. Large titles, generous line-height, subtle date styling. Clean dividers between posts.",
            ],
            'team' => [
                'grid'     => "Card grid: each card has photo (square or 4:5), name, role, short bio. 3-4 columns. Card hover: reveal social links (LinkedIn, Twitter). Clean, professional.",
                'circular' => "Circular photo crops (border-radius: 50%), centered layout. Name and role below each photo. 3-4 per row. Clean, personal feel. Optional: short quote from each member.",
                'detailed' => "Large cards with more content: bigger photo, name, role, 2-3 sentence bio, list of specialties, social links. 2 columns max. Good for small teams with important individual stories.",
                'creative' => "Interactive feel: hover to reveal bio/social overlay on photo. Or split layout: photo fills card, text appears on hover with sliding animation. Modern, engaging.",
            ],
            'faq' => [
                'accordion' => "Classic accordion: click question to expand answer. Chevron icon rotates on toggle. Questions in bold, answers with comfortable padding. Group by category with section headers if many questions.",
                'columns'   => "Two-column layout: questions/answers split across columns. Or: left column = category navigation, right = Q&A for selected category. Good for extensive FAQs.",
                'search'    => "Search bar prominently at top. Below: filtered FAQ list (accordion style). As user types, questions filter in real-time (or show placeholder for JS). Category chips/tags below search.",
                'tabs'      => "Category tabs at top (General, Billing, Technical, etc.). Each tab shows accordion Q&A for that category. Clean, organized, reduces overwhelm for large FAQ sets.",
            ],
            'testimonials' => [
                'cards'     => "Quote cards in 2-3 column grid. Each card: large quote marks, testimonial text, client photo (small circle), name, role/company. Cards with distinct border-left (primary color).",
                'slider'    => "One testimonial at a time, centered. Large quote text, client photo, name/company. Navigation dots or arrows. Auto-advance optional. Emphasis on the words, not decorative elements.",
                'wall'      => "Masonry-style quote wall: varying card sizes based on quote length. Dense, social-proof feel. Many testimonials visible at once. Subtle color variations between cards.",
                'minimal'   => "Large, centered single quote with generous whitespace. Cycle through quotes or show 2-3 below each other. Minimal decoration — let the words be powerful. Serif font for quotes.",
            ],
            'features' => [
                'grid'        => "Responsive grid of feature cards (3-4 columns). Each card: icon (64px circle with primary bg), feature name, 2-3 sentence description. Subtle hover lift effect. Cards with consistent height, generous padding (32px).",
                'alternating' => "Alternating rows: image on one side, feature description on the other, flip sides each row. 2-column CSS Grid (1fr 1fr). Each row is a distinct section with padding. Clean, balanced composition.",
                'showcase'    => "One hero/flagship feature displayed large at top with big visual and detailed description. Below: supporting features in a 3-column grid with smaller cards. Hierarchy shows importance.",
                'tabs'        => "Tab interface at top with category labels. Each tab reveals a feature group with its own content, icons, and visuals. Smooth transitions between tabs. Clean, organized, reduces overwhelm.",
            ],
        ];

        return $hints[$pageType][$style] ?? '';
    }

    /* ═══════════════════════════════════════════════════════
       BUSINESS PROFILE TEXT BUILDER
       ═══════════════════════════════════════════════════════ */

    /**
     * Build comprehensive human-readable business profile from business_info array.
     * Used in all AI prompts (content, layout, sub-pages) instead of raw JSON.
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

        // Contact
        $contact = [];
        if (!empty($info['phone']))   $contact[] = "Phone: {$info['phone']}";
        if (!empty($info['email']))   $contact[] = "Email: {$info['email']}";
        if (!empty($info['address'])) $contact[] = "Address: {$info['address']}";
        if ($contact) $lines[] = "Contact: " . implode(' | ', $contact);

        // Social
        $social = $info['social'] ?? [];
        if (is_array($social) && !empty($social)) {
            $items = [];
            foreach ($social as $p => $url) { if (!empty($url)) $items[] = ucfirst($p) . ": {$url}"; }
            if ($items) $lines[] = "Social: " . implode(', ', $items);
        }

        // Services
        $services = $info['services'] ?? [];
        if (is_array($services) && !empty($services)) {
            $lines[] = "Services/Products:";
            foreach ($services as $s) {
                $n = $s['name'] ?? ''; $d = $s['description'] ?? '';
                if ($n) $lines[] = $d ? "  • {$n} — {$d}" : "  • {$n}";
            }
        }

        // Team
        $team = $info['team'] ?? [];
        if (is_array($team) && !empty($team)) {
            $lines[] = "Team:";
            foreach ($team as $m) {
                $n = $m['name'] ?? ''; $r = $m['role'] ?? ''; $b = $m['bio'] ?? '';
                if ($n) { $e = "  • {$n}"; if ($r) $e .= " — {$r}"; if ($b) $e .= ". {$b}"; $lines[] = $e; }
            }
        }

        // Testimonials
        $test = $info['testimonials'] ?? [];
        if (is_array($test) && !empty($test)) {
            $lines[] = "Real testimonials (USE THESE, don't invent fake ones):";
            foreach ($test as $t) {
                $q = $t['quote'] ?? ''; $a = $t['name'] ?? ''; $c = $t['company'] ?? '';
                if ($q) { $attr = $a; if ($c) $attr .= ", {$c}"; $lines[] = $attr ? "  \"{$q}\" — {$attr}" : "  \"{$q}\""; }
            }
        }

        // Hours
        $hours = $info['hours'] ?? [];
        if (is_array($hours) && !empty($hours)) {
            $lines[] = "Opening hours:";
            foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $d) {
                if (isset($hours[$d])) $lines[] = "  " . ucfirst($d) . ": {$hours[$d]}";
            }
        }

        // Industry-specific
        if (!empty($info['cuisine']))      $lines[] = "Cuisine: {$info['cuisine']}";
        if (!empty($info['price_range']))   $lines[] = "Price range: {$info['price_range']}";
        if (!empty($info['menu_highlights'])) { $items = array_filter(array_map('trim', explode("\n", $info['menu_highlights']))); if ($items) $lines[] = "Menu highlights: " . implode(', ', $items); }
        if (!empty($info['reservations']))  $lines[] = "Reservations: {$info['reservations']}";
        if (!empty($info['seating']))       $lines[] = "Seating: {$info['seating']}";
        if (!empty($info['specialties']))   { $items = array_filter(array_map('trim', explode("\n", $info['specialties']))); if ($items) $lines[] = "Specialties: " . implode(', ', $items); }
        if (!empty($info['insurance']))     $lines[] = "Insurance/NHS: {$info['insurance']}";
        if (!empty($info['emergency']))     $lines[] = "Emergency: {$info['emergency']}";
        if (!empty($info['creative_specialisation'])) $lines[] = "Specialisation: {$info['creative_specialisation']}";
        if (!empty($info['notable_clients'])) { $items = array_filter(array_map('trim', explode("\n", $info['notable_clients']))); if ($items) $lines[] = "Notable clients: " . implode(', ', $items); }
        if (!empty($info['awards']))        { $items = array_filter(array_map('trim', explode("\n", $info['awards']))); if ($items) $lines[] = "Awards: " . implode(', ', $items); }
        if (!empty($info['practice_areas'])) { $items = array_filter(array_map('trim', explode("\n", $info['practice_areas']))); if ($items) $lines[] = "Practice areas: " . implode(', ', $items); }
        if (!empty($info['accreditations'])) { $items = array_filter(array_map('trim', explode("\n", $info['accreditations']))); if ($items) $lines[] = "Accreditations: " . implode(', ', $items); }
        if (!empty($info['free_consultation'])) $lines[] = "Free consultation: {$info['free_consultation']}";
        if (!empty($info['products']))      { $items = array_filter(array_map('trim', explode("\n", $info['products']))); if ($items) $lines[] = "Products: " . implode(', ', $items); }
        if (!empty($info['shipping']))      $lines[] = "Shipping: {$info['shipping']}";
        if (!empty($info['returns']))       $lines[] = "Returns: {$info['returns']}";
        if (!empty($info['certifications'])) { $items = array_filter(array_map('trim', explode("\n", $info['certifications']))); if ($items) $lines[] = "Certifications: " . implode(', ', $items); }
        if (!empty($info['areas_served']))  $lines[] = "Areas served: {$info['areas_served']}";
        if (!empty($info['extra_notes']))   $lines[] = "Extra notes: {$info['extra_notes']}";

        return implode("\n", $lines);
    }

    /* ═══════════════════════════════════════════════════════
       WIZARD HELPERS: Presets, Sections, Tone
       ═══════════════════════════════════════════════════════ */

    /**
     * Get style presets — ready-made starting points for quick generation.
     */
    public static function getStylePresets(): array
    {
        return [
            [
                'id' => 'dark-law',
                'name' => 'Dark Law Firm',
                'prompt' => 'Professional law firm specializing in corporate and family law',
                'industry' => 'law',
                'style' => 'corporate',
                'mood' => 'dark',
                'tone' => 'formal',
                'colors' => ['#1a1a2e', '#c9a227', '#e8e6e3'],
            ],
            [
                'id' => 'bright-saas',
                'name' => 'Modern SaaS',
                'prompt' => 'Cloud-based project management tool for remote teams',
                'industry' => 'saas',
                'style' => 'minimalist',
                'mood' => 'light',
                'tone' => 'friendly',
                'colors' => ['#ffffff', '#6366f1', '#1e293b'],
            ],
            [
                'id' => 'warm-restaurant',
                'name' => 'Cozy Restaurant',
                'prompt' => 'Family Italian restaurant with traditional recipes and warm atmosphere',
                'industry' => 'restaurant',
                'style' => 'elegant',
                'mood' => 'warm',
                'tone' => 'friendly',
                'colors' => ['#1a0f0a', '#d4a574', '#f5f0eb'],
            ],
            [
                'id' => 'bold-portfolio',
                'name' => 'Creative Portfolio',
                'prompt' => 'Freelance graphic designer and brand identity specialist',
                'industry' => 'portfolio',
                'style' => 'bold',
                'mood' => 'dark',
                'tone' => 'casual',
                'colors' => ['#0a0a0a', '#ff3366', '#ffffff'],
            ],
            [
                'id' => 'zen-spa',
                'name' => 'Zen Wellness',
                'prompt' => 'Luxury day spa offering massage, facials and holistic treatments',
                'industry' => 'spa',
                'style' => 'organic',
                'mood' => 'pastel',
                'tone' => 'luxurious',
                'colors' => ['#f5f0eb', '#7d9a7a', '#3d3d3d'],
            ],
            [
                'id' => 'tech-startup',
                'name' => 'Tech Startup',
                'prompt' => 'AI-powered analytics platform for e-commerce businesses',
                'industry' => 'startup',
                'style' => 'futuristic',
                'mood' => 'dark',
                'tone' => 'professional',
                'colors' => ['#0f172a', '#00f0ff', '#e2e8f0'],
            ],
            [
                'id' => 'clean-medical',
                'name' => 'Clean Medical',
                'prompt' => 'Modern dental clinic with cosmetic and general dentistry',
                'industry' => 'dental',
                'style' => 'minimalist',
                'mood' => 'cool',
                'tone' => 'professional',
                'colors' => ['#ffffff', '#0077b6', '#1e293b'],
            ],
            [
                'id' => 'construction-bold',
                'name' => 'Bold Construction',
                'prompt' => 'Professional paving and groundwork contractors',
                'industry' => 'construction',
                'style' => 'bold',
                'mood' => 'dark',
                'tone' => 'professional',
                'colors' => ['#1a1a2e', '#f59e0b', '#e2e8f0'],
            ],
            [
                'id' => 'artsy-photography',
                'name' => 'Artsy Photography',
                'prompt' => 'Fine art and wedding photographer with a moody editorial style',
                'industry' => 'photography',
                'style' => 'editorial',
                'mood' => 'monochrome',
                'tone' => 'casual',
                'colors' => ['#0a0a0a', '#ffffff', '#888888'],
            ],
            [
                'id' => 'ecom-fashion',
                'name' => 'Fashion Boutique',
                'prompt' => 'Online fashion boutique for contemporary women\'s clothing and accessories',
                'industry' => 'fashion',
                'style' => 'elegant',
                'mood' => 'luxury',
                'tone' => 'luxurious',
                'colors' => ['#1a1a1a', '#c9a227', '#f5f0eb'],
            ],
            [
                'id' => 'playful-childcare',
                'name' => 'Playful Nursery',
                'prompt' => 'A bright, cheerful nursery and childcare center for ages 0-5',
                'industry' => 'childcare',
                'style' => 'playful',
                'mood' => 'colorful',
                'tone' => 'friendly',
                'colors' => ['#ffffff', '#ff6b6b', '#4ecdc4'],
            ],
            [
                'id' => 'brutalist-agency',
                'name' => 'Brutalist Agency',
                'prompt' => 'Cutting-edge digital design agency with experimental aesthetic',
                'industry' => 'agency',
                'style' => 'brutalist',
                'mood' => 'dark',
                'tone' => 'witty',
                'colors' => ['#0a0a0a', '#ff0000', '#ffffff'],
            ],
            [
                'id' => 'retro-cafe',
                'name' => 'Retro Café',
                'prompt' => 'Vintage-inspired neighborhood café with 1950s diner aesthetic',
                'industry' => 'cafe',
                'style' => 'retro',
                'mood' => 'warm',
                'tone' => 'friendly',
                'colors' => ['#fdf6e3', '#c44536', '#2b2d42'],
            ],
            [
                'id' => 'artdeco-hotel',
                'name' => 'Art Deco Hotel',
                'prompt' => 'Luxury boutique hotel with 1920s Art Deco design and gold accents',
                'industry' => 'hotel',
                'style' => 'artdeco',
                'mood' => 'luxury',
                'tone' => 'luxurious',
                'colors' => ['#1a1a2e', '#d4af37', '#f5f0eb'],
            ],
            [
                'id' => 'glass-saas',
                'name' => 'Glassmorphism SaaS',
                'prompt' => 'Modern cloud platform with frosted glass UI and gradient backgrounds',
                'industry' => 'saas',
                'style' => 'glassmorphism',
                'mood' => 'cool',
                'tone' => 'professional',
                'colors' => ['#0f0c29', '#6c63ff', '#e0e0ff'],
            ],
            [
                'id' => 'neubrutalist-blog',
                'name' => 'Neubrutalist Blog',
                'prompt' => 'Personal tech blog with thick borders, quirky layouts, and bold colors',
                'industry' => 'blog',
                'style' => 'neubrutalism',
                'mood' => 'colorful',
                'tone' => 'casual',
                'colors' => ['#ffffff', '#ff5722', '#1a1a1a'],
            ],
            [
                'id' => 'geometric-arch',
                'name' => 'Geometric Architecture',
                'prompt' => 'Minimalist architecture firm with strong geometric shapes and clean lines',
                'industry' => 'architecture',
                'style' => 'geometric',
                'mood' => 'monochrome',
                'tone' => 'professional',
                'colors' => ['#ffffff', '#1a1a1a', '#e63946'],
            ],
            [
                'id' => 'neon-gaming',
                'name' => 'Neon Gaming',
                'prompt' => 'Esports team and gaming community with electric neon aesthetic',
                'industry' => 'gamedev',
                'style' => 'futuristic',
                'mood' => 'neon',
                'tone' => 'casual',
                'colors' => ['#0d0d0d', '#00ff88', '#ff00ff'],
            ],
            [
                'id' => 'earth-yoga',
                'name' => 'Earth Yoga',
                'prompt' => 'Nature-inspired yoga and wellness retreat in the countryside',
                'industry' => 'yoga',
                'style' => 'organic',
                'mood' => 'earth',
                'tone' => 'friendly',
                'colors' => ['#f5f0e8', '#5a7247', '#3d2c1e'],
            ],
            [
                'id' => 'pastel-bakery',
                'name' => 'Pastel Bakery',
                'prompt' => 'Charming artisan bakery and patisserie with French-inspired pastries',
                'industry' => 'bakery',
                'style' => 'elegant',
                'mood' => 'pastel',
                'tone' => 'friendly',
                'colors' => ['#fef9f4', '#e8a0bf', '#957fef'],
            ],
            [
                'id' => 'editorial-magazine',
                'name' => 'Editorial Magazine',
                'prompt' => 'Premium online lifestyle and culture magazine with bold typography',
                'industry' => 'magazine',
                'style' => 'editorial',
                'mood' => 'light',
                'tone' => 'professional',
                'colors' => ['#ffffff', '#1a1a1a', '#c1272d'],
            ],
            [
                'id' => 'warm-nonprofit',
                'name' => 'Warm Nonprofit',
                'prompt' => 'Community-focused charity helping underprivileged children access education',
                'industry' => 'nonprofit',
                'style' => 'organic',
                'mood' => 'warm',
                'tone' => 'friendly',
                'colors' => ['#fff8f0', '#e07a42', '#2d5f3e'],
            ],
        ];
    }

    /**
     * Get available homepage sections for the section picker.
     */
    public static function getAvailableSections(): array
    {
        return [
            ['id' => 'hero', 'name' => 'Hero', 'icon' => 'fas fa-star', 'desc' => 'Full-screen hero with headline, subtitle and CTA', 'required' => true, 'default' => true],
            ['id' => 'about', 'name' => 'About / Intro', 'icon' => 'fas fa-info-circle', 'desc' => 'Company introduction with image and story', 'required' => false, 'default' => true],
            ['id' => 'services', 'name' => 'Services', 'icon' => 'fas fa-concierge-bell', 'desc' => 'Service cards with icons and descriptions', 'required' => false, 'default' => false],
            ['id' => 'portfolio', 'name' => 'Portfolio / Projects', 'icon' => 'fas fa-th-large', 'desc' => 'Project showcase in grid or mosaic layout', 'required' => false, 'default' => false],
            ['id' => 'stats', 'name' => 'Stats / Numbers', 'icon' => 'fas fa-chart-bar', 'desc' => 'Key figures: years, clients, projects completed', 'required' => false, 'default' => false],
            ['id' => 'testimonials', 'name' => 'Testimonials', 'icon' => 'fas fa-quote-right', 'desc' => 'Client reviews and testimonial cards', 'required' => false, 'default' => false],
            ['id' => 'articles', 'name' => 'Recent Articles', 'icon' => 'fas fa-newspaper', 'desc' => 'Latest blog posts / news cards', 'required' => false, 'default' => true],
            ['id' => 'team', 'name' => 'Team Preview', 'icon' => 'fas fa-users', 'desc' => 'Key team members with photos and roles', 'required' => false, 'default' => false],
            ['id' => 'pricing', 'name' => 'Pricing Preview', 'icon' => 'fas fa-tags', 'desc' => 'Pricing tiers or starting-from prices', 'required' => false, 'default' => false],
            ['id' => 'partners', 'name' => 'Partners / Logos', 'icon' => 'fas fa-handshake', 'desc' => 'Client or partner logo carousel', 'required' => false, 'default' => false],
            ['id' => 'faq', 'name' => 'FAQ Preview', 'icon' => 'fas fa-question-circle', 'desc' => 'Top 3-5 frequently asked questions', 'required' => false, 'default' => false],
            ['id' => 'newsletter', 'name' => 'Newsletter Signup', 'icon' => 'fas fa-envelope', 'desc' => 'Email signup form with compelling copy', 'required' => false, 'default' => false],
            ['id' => 'cta', 'name' => 'Call to Action', 'icon' => 'fas fa-bullhorn', 'desc' => 'Full-width CTA section with background', 'required' => false, 'default' => true],
        ];
    }

    /**
     * Get available content tones.
     */
    public static function getContentTones(): array
    {
        return [
            ['id' => 'professional', 'name' => 'Professional', 'icon' => '💼', 'desc' => 'Formal, authoritative, trust-building'],
            ['id' => 'friendly', 'name' => 'Friendly', 'icon' => '😊', 'desc' => 'Warm, approachable, conversational'],
            ['id' => 'casual', 'name' => 'Casual', 'icon' => '🤙', 'desc' => 'Relaxed, informal, down-to-earth'],
            ['id' => 'formal', 'name' => 'Formal', 'icon' => '🎩', 'desc' => 'Dignified, traditional, ceremonial'],
            ['id' => 'witty', 'name' => 'Witty', 'icon' => '✨', 'desc' => 'Clever, playful, memorable'],
            ['id' => 'luxurious', 'name' => 'Luxurious', 'icon' => '👑', 'desc' => 'Premium, exclusive, aspirational'],
        ];
    }


    /**
     * Get the last AI error info (classified) from the AI Core.
     */
    public function getAIErrorInfo(): ?array
    {
        return null;
    }


    /**
     * AI query using core/ai_content.php — no JTB dependency.
     * @param string $prompt The user prompt
     * @param array $options Options: provider, model, system_prompt, max_tokens, temperature, json_mode
     * @return array {ok, text, json, error}
     */
    private function aiQuery(string $prompt, array $options = []): array
    {
        $provider = $options['provider'] ?? $this->provider;
        $model = $options['model'] ?? $this->model;
        $systemPrompt = $options['system_prompt'] ?? '';
        $maxTokens = $options['max_tokens'] ?? 4000;
        $temperature = $options['temperature'] ?? 0.7;

        if (!empty($options['max_tokens'])) {
            $maxTokens = min($maxTokens, $this->getModelOutputLimit());
        }

        $result = ai_universal_generate($provider, $model, $systemPrompt, $prompt, [
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ]);

        $text = $result['content'] ?? null;
        $json = null;

        if ($text && !empty($options['json_mode'])) {
            $jsonText = $text;
            if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $jsonText, $m)) {
                $jsonText = trim($m[1]);
            }
            $decoded = @json_decode($jsonText, true);
            if (is_array($decoded)) $json = $decoded;
        }

        return [
            'ok' => $result['ok'] ?? false,
            'text' => $text,
            'json' => $json,
            'error' => $result['error'] ?? null,
            'cached' => false,
            'tokens_used' => 0,
            'time_ms' => 0,
        ];
    }

    private function queryOptions(array $base = []): array
    {
        if (!empty($this->provider)) $base['provider'] = $this->provider;
        if (!empty($this->model)) $base['model'] = $this->model;

        // Cap max_tokens to model's output limit
        if (!empty($base['max_tokens'])) {
            $base['max_tokens'] = min($base['max_tokens'], $this->getModelOutputLimit());
        }

        return $base;
    }

    /**
     * Get the max output tokens for the current provider+model.
     * Read from ai_settings.json per-model config, with sensible per-provider fallbacks.
     */
    private function getModelOutputLimit(): int
    {
        // Per-provider output limits (API enforced, not context window)
        $providerDefaults = [
            'openai'    => 16000,  // most GPT models support 16k output
            'anthropic' => 32000,  // Claude supports up to 64k output, 32k is safe default
            'deepseek'  => 8000,   // DeepSeek API hard limit 8192
            'google'    => 8000,   // Gemini default output
            'huggingface' => 4000,
        ];

        $prov = $this->provider ?: $this->provider;
        $model = $this->model;

        // Try to read per-model max_output_tokens from config
        $settingsPath = CMS_ROOT . '/config/ai_settings.json';
        if ($model && file_exists($settingsPath)) {
            $settings = @json_decode(file_get_contents($settingsPath), true);
            $modelConf = $settings['providers'][$prov]['models'][$model] ?? [];
            if (!empty($modelConf['max_output_tokens'])) {
                return (int)$modelConf['max_output_tokens'];
            }
            // Some configs store it as output_limit
            if (!empty($modelConf['output_limit'])) {
                return (int)$modelConf['output_limit'];
            }
        }

        return $providerDefaults[$prov] ?? 8000;
    }

    /**
     * Get temperature for a step based on creativity setting.
     * low = precise/safe (good for complex prompts), medium = balanced, high = experimental
     */
    /**
     * Get explicit header class names for Step 3 CSS prompt.
     * Includes nav-list and nav-link which are inside PHP render_menu() calls.
     */
    private function getHeaderClassList(): string
    {
        $classes = $this->headerPatternResult['classes'] ?? [];
        // Also extract classes from render_menu() PHP calls in header HTML
        $html = $this->headerPatternResult['html'] ?? '';
        if (preg_match_all("/(?:'class'|'link_class')\s*=>\s*'([^']+)'/", $html, $m)) {
            foreach ($m[1] as $cls) {
                foreach (preg_split('/\s+/', $cls) as $c) {
                    if ($c) $classes[] = $c;
                }
            }
        }
        $classes = array_unique($classes);
        return implode(', ', array_map(fn($c) => '.' . $c, $classes));
    }

    /**
     * Get explicit footer class names for Step 3 CSS prompt.
     * Includes nav-list and nav-link which are inside PHP render_menu() calls.
     */
    private function getFooterClassList(): string
    {
        $classes = $this->footerPatternResult['classes'] ?? [];
        // Also extract classes from render_menu() PHP calls in footer HTML
        $html = $this->footerPatternResult['html'] ?? '';
        if (preg_match_all("/(?:'class'|'link_class')\s*=>\s*'([^']+)'/", $html, $m)) {
            foreach ($m[1] as $cls) {
                foreach (preg_split('/\s+/', $cls) as $c) {
                    if ($c) $classes[] = $c;
                }
            }
        }
        $classes = array_unique($classes);
        return implode(', ', array_map(fn($c) => '.' . $c, $classes));
    }

    /**
     * Get pattern-specific decorative guide block for header (appended to Step 3 header section).
     */
    private function getHeaderDecorativeGuideBlock(): string
    {
        $patternId = $this->headerPatternResult['pattern_id'] ?? '';
        if (!$patternId) return '';
        if (class_exists('\HeaderPatternRegistry') && method_exists('\HeaderPatternRegistry', 'getDecorativeGuide')) {
            $guide = \HeaderPatternRegistry::getDecorativeGuide($patternId);
            if ($guide) return "\n⚡ PATTERN-SPECIFIC visual approach for header \"{$patternId}\":\n{$guide}";
        }
        return '';
    }

    /**
     * Get pattern-specific decorative guide block for footer (appended to Step 3 footer section).
     */
    private function getFooterDecorativeGuideBlock(): string
    {
        $patternId = $this->footerPatternResult['pattern_id'] ?? '';
        if (!$patternId) return '';
        if (class_exists('\FooterPatternRegistry') && method_exists('\FooterPatternRegistry', 'getDecorativeGuide')) {
            $guide = \FooterPatternRegistry::getDecorativeGuide($patternId);
            if ($guide) return "\n⚡ PATTERN-SPECIFIC visual approach for footer \"{$patternId}\":\n{$guide}";
        }
        return '';
    }

    /**
     * Get explicit hero class names for Step 3 CSS prompt.
     * Uses prefixed classes from HeroPatternRegistry (e.g. .cp-hero, .cp-hero-headline).
     */
    private function getHeroClassList(): string
    {
        $classes = $this->heroPatternResult['classes'] ?? [];
        $classes = array_unique($classes);
        return implode(', ', array_map(fn($c) => '.' . $c, $classes));
    }

    /**
     * Get explicit class names for a section pattern (features, about, testimonials, etc.).
     */
    private function getSectionClassList(string $sectionId): string
    {
        $result = $this->sectionPatternResults[$sectionId] ?? null;
        if (!$result) return '';
        $classes = $result['classes'] ?? [];
        $classes = array_unique($classes);
        return implode(', ', array_map(fn($c) => '.' . $c, $classes));
    }

    /**
     * Build DECORATIVE CSS ONLY instruction blocks for ALL pattern-generated sections.
     * Mirrors the header/footer approach: tells AI which classes to style and how,
     * while forbidding structural CSS (which comes from pattern registries).
     */
    private function buildSectionCssGuide(): string
    {
        $blocks = [];

        // --- Hero ---
        $heroClasses = $this->getHeroClassList();
        $heroPatternId = $this->heroPatternResult['pattern_id'] ?? 'unknown';
        if ($heroClasses) {
            // Get pattern-specific decorative guide from HeroPatternRegistry
            $heroPatternGuide = '';
            if (class_exists('\HeroPatternRegistry') && method_exists('\HeroPatternRegistry', 'getDecorativeGuide')) {
                $heroPatternGuide = \HeroPatternRegistry::getDecorativeGuide($heroPatternId);
            }
            $heroSpecificBlock = $heroPatternGuide
                ? "\n\n⚡ PATTERN-SPECIFIC visual approach for \"{$heroPatternId}\":\n{$heroPatternGuide}"
                : '';

            $blocks[] = <<<GUIDE
HERO (DECORATIVE CSS ONLY — structural layout CSS is pre-built and injected separately):
The hero HTML uses pattern "{$heroPatternId}".
⚠️ EXACT hero CSS class names you MUST use (do NOT invent alternatives like .hero or .hero-title):
{$heroClasses}
⚠️ DO NOT write: position, display, flex, grid, width, height, max-width, min-height, padding, margin, overflow, z-index, order, gap, inset, align-items, justify-content for hero elements — these are handled by structural CSS.
⚠️ DO write decorative CSS for ALL hero classes listed above:
- Hero section: background-color (fallback), color
- Hero bg: background-size cover, background-position center (visual only)
- Hero overlay: background gradient (linear-gradient 135deg, rgba(bg,0.85) to rgba(bg,0.4)) — NEVER transparent!
- Hero headline: font-family var(--font-heading), font-size clamp(2.75rem, 6vw, 5rem), font-weight, color, letter-spacing, text-shadow
- Hero subtitle: color var(--text-muted), font-size clamp(1.0625rem, 2vw, 1.25rem), line-height, max-width 50ch
- Hero badge: background rgba(primary, 0.15), color var(--primary), border, border-radius 100px, text-transform uppercase, letter-spacing, font-size
- Hero buttons (btn + btn-primary): background var(--primary), color var(--primary-contrast), border-radius, font-weight, letter-spacing, hover effects (translateY, box-shadow)
- Hero buttons (btn-outline): border 2px solid, transparent background, hover fills
- Hero actions: gap styling only via CSS gap (not padding/margin hacks)
- Hero scroll indicator: color, animation (bounce), opacity
- Transitions on all interactive elements{$heroSpecificBlock}
GUIDE;
        }

        // --- Per-section guides ---
        $sectionGuides = [
            'features' => [
                'label' => 'Features/Services',
                'decorative' => <<<DEC
- Section background: background-color var(--background) or var(--surface), subtle gradient allowed
- Badge: background rgba(primary, 0.1), color var(--primary), border-radius 100px, text-transform uppercase
- Title: font-family var(--font-heading), color var(--text), font-weight 700
- Subtitle: color var(--text-muted)
- Feature icons: color var(--primary), background rgba(primary, 0.1), border-radius var(--radius)
- Feature item titles: font-weight 600, color var(--text)
- Feature item text: color var(--text-muted), line-height 1.7
- Cards (if present): background var(--surface-card), border 1px solid var(--border), border-radius var(--radius-lg), box-shadow
- Card hover: translateY(-4px), box-shadow enhancement, border-color change
- Timeline dots/lines (if present): background var(--primary), border-color
- Number badges (if present): color var(--primary), font-weight 700
- Transitions on all interactive/hoverable elements
DEC
            ],
            'services' => null, // alias → uses features guide
            'about' => [
                'label' => 'About',
                'decorative' => <<<DEC
- Section background: background-color var(--surface) or var(--background)
- Badge: background rgba(primary, 0.1), color var(--primary), border-radius, text-transform
- Title: font-family var(--font-heading), color var(--text)
- Description text: color var(--text-muted), line-height 1.8
- Image: border-radius var(--radius-lg), box-shadow, object-fit cover
- Stats numbers: font-family var(--font-heading), color var(--primary), font-size large
- Stats labels: color var(--text-muted), font-size small, text-transform uppercase
- Signature/author: font-style italic, color var(--text-muted)
- Accent decorations: background var(--primary), border-radius
- Links/buttons: color var(--primary), hover effects
DEC
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'decorative' => <<<DEC
- Section background: background-color var(--surface) or subtle gradient
- Badge/title/subtitle: same pattern as features (primary color, heading font)
- Quote text: font-size 1.125rem, line-height 1.8, color var(--text), font-style italic or normal
- Quote marks (decorative): color var(--primary), opacity 0.2, font-size 4rem+
- Stars: color #f59e0b (amber), font-size
- Avatar images: border-radius 50%, border 3px solid var(--primary) or var(--border), object-fit cover
- Author name: font-weight 600, color var(--text)
- Author role: color var(--text-muted), font-size small
- Cards: background var(--surface-card), border, border-radius, box-shadow
- Card hover: subtle lift or border-color change
- Active/center card: scale or shadow emphasis
- Navigation dots/arrows: color var(--primary), hover effects
DEC
            ],
            'cta' => [
                'label' => 'Call-to-Action',
                'decorative' => <<<DEC
- Section background: background gradient using primary color or var(--surface-elevated), or background-image
- Overlay (if present): gradient with rgba
- Badge: background rgba(white, 0.2) or rgba(primary, 0.15), color
- Title: color white or var(--text), font-family var(--font-heading), font-size large
- Subtitle: color rgba(white, 0.8) or var(--text-muted)
- CTA button: background white or var(--primary), color var(--primary) or white, border-radius, font-weight, letter-spacing
- CTA button hover: translateY(-2px), box-shadow glow
- Secondary button: border style, transparent bg, hover fills
- Decorative shapes/blobs: background rgba, border-radius, opacity
- Icon (if present): color, font-size large
DEC
            ],
            'gallery' => [
                'label' => 'Gallery/Portfolio',
                'decorative' => <<<DEC
- Section background: background-color var(--background) or var(--surface)
- Badge/title/subtitle: same heading pattern
- Gallery items/images: border-radius var(--radius), object-fit cover, box-shadow
- Image overlay on hover: background rgba(0,0,0,0.5) or gradient, opacity transition
- Image hover: transform scale(1.05), filter brightness
- Caption/title on hover: color white, font-weight 600, text-shadow
- Category/tag: color var(--text-muted) or var(--primary)
- Filter buttons (if present): background, border, border-radius, active state with var(--primary)
- Lightbox trigger icon: color white, font-size, background rgba
- Card variant: border, border-radius, box-shadow, hover lift
DEC
            ],
            'portfolio' => null, // alias → uses gallery guide
            'pricing' => [
                'label' => 'Pricing',
                'decorative' => <<<DEC
- Section background: background-color var(--background) or var(--surface)
- Badge/title/subtitle: same heading pattern
- Pricing cards: background var(--surface-card), border 1px solid var(--border), border-radius var(--radius-lg), box-shadow
- Popular/featured card: border-color var(--primary), box-shadow larger, optional background var(--primary) with white text
- Popular badge: background var(--primary), color white, border-radius, text-transform uppercase, font-size small
- Price amount: font-family var(--font-heading), font-size 3rem+, color var(--text) or var(--primary), font-weight 700
- Price period: font-size small, color var(--text-muted)
- Feature list items: color var(--text-muted), padding, border-bottom subtle
- Feature checkmarks: color var(--primary) or green
- Disabled features: opacity 0.4, text-decoration line-through
- Card button: full-width, background var(--primary), color white, border-radius, hover effects
- Card hover: translateY(-4px), box-shadow enhancement
- Toggle (monthly/yearly if present): background, border-radius, active indicator
DEC
            ],
            'faq' => [
                'label' => 'FAQ',
                'decorative' => <<<DEC
- Section background: background-color var(--surface) or var(--background)
- Badge/title/subtitle: same heading pattern
- FAQ items: background var(--surface-card), border 1px solid var(--border), border-radius var(--radius)
- FAQ question: font-weight 600, color var(--text), cursor pointer
- FAQ answer: color var(--text-muted), line-height 1.7
- Toggle icon: color var(--primary), transition transform 0.3s (rotate on open)
- Open/active state: border-color var(--primary) or background subtle change
- Hover: background subtle change, border-color
- Dividers between items: border-bottom or margin gap
DEC
            ],
            'stats' => [
                'label' => 'Stats/Numbers',
                'decorative' => <<<DEC
- Section background: background var(--primary) gradient or var(--surface-elevated), color contrast
- Stat numbers: font-family var(--font-heading), font-size clamp(2.5rem, 5vw, 4rem), font-weight 700, color white or var(--primary)
- Stat labels: color rgba(white, 0.8) or var(--text-muted), text-transform uppercase, letter-spacing, font-size small
- Stat icons (if present): color, font-size, opacity
- Dividers between stats: border-right or background separator
- Counter animation: transition or CSS counter-increment
DEC
            ],
            'clients' => [
                'label' => 'Clients/Partners',
                'decorative' => <<<DEC
- Section background: background-color var(--surface) or var(--background)
- Badge/title/subtitle: same heading pattern
- Logo images: filter grayscale(100%), opacity 0.5, transition
- Logo hover: filter grayscale(0%), opacity 1
- Logo container: background transparent or var(--surface-card), border subtle, border-radius, padding
- Tooltip (if present): background var(--text), color white, border-radius, font-size small
DEC
            ],
            'partners' => null, // alias → uses clients guide
            'team' => [
                'label' => 'Team',
                'decorative' => <<<DEC
- Section background: background-color var(--background) or var(--surface)
- Badge/title/subtitle: same heading pattern
- Team member image: border-radius (50% for circle or var(--radius-lg) for rounded), object-fit cover, box-shadow
- Image hover: filter brightness or overlay with social links
- Member name: font-weight 600, font-family var(--font-heading), color var(--text)
- Member role: color var(--primary) or var(--text-muted), font-size small, text-transform uppercase
- Member bio: color var(--text-muted), line-height 1.6
- Social icons: color var(--text-muted), hover color var(--primary), font-size, transition
- Card (if present): background var(--surface-card), border, border-radius, box-shadow, hover lift
DEC
            ],
            'blog' => [
                'label' => 'Blog/Articles',
                'decorative' => <<<DEC
- Section background: background-color var(--background) or var(--surface)
- Badge/title/subtitle: same heading pattern
- Article cards: background var(--surface-card), border 1px solid var(--border), border-radius var(--radius-lg), box-shadow, overflow hidden
- Card image: object-fit cover, transition transform on hover (scale 1.05)
- Card body: padding
- Article title: font-weight 600, color var(--text), hover color var(--primary)
- Article excerpt: color var(--text-muted), line-height 1.6
- Article meta (date, category): color var(--text-muted), font-size small
- Category badge: background rgba(primary, 0.1), color var(--primary), border-radius, font-size small
- Read more link: color var(--primary), font-weight 600, hover with arrow animation
- Card hover: translateY(-4px), box-shadow enhancement
DEC
            ],
            'articles' => null, // alias → uses blog guide
            'contact' => [
                'label' => 'Contact',
                'decorative' => <<<DEC
- Section background: background-color var(--surface) or var(--background)
- Badge/title/subtitle: same heading pattern
- Contact info items: color var(--text-muted)
- Info icons: color var(--primary), font-size 1.25rem, background rgba(primary, 0.1), border-radius
- Form inputs: background var(--surface), border 1px solid var(--border), border-radius var(--radius), color var(--text), font-family var(--body-font)
- Input focus: border-color var(--primary), box-shadow 0 0 0 3px rgba(primary, 0.15)
- Form labels: font-weight 600, color var(--text), font-size 0.875rem
- Submit button: background var(--primary), color white, border-radius, font-weight 600, hover effects
- Map container (if present): border-radius, box-shadow, border
- Social links: color var(--text-muted), hover color var(--primary)
DEC
            ],
            'newsletter' => null, // alias → uses cta guide
        ];

        // --- Section registry map for label lookups ---
        $sectionLabels = [
            'features' => 'Features/Services', 'services' => 'Features/Services',
            'about' => 'About', 'testimonials' => 'Testimonials',
            'cta' => 'Call-to-Action', 'newsletter' => 'Call-to-Action',
            'gallery' => 'Gallery/Portfolio', 'portfolio' => 'Gallery/Portfolio',
            'pricing' => 'Pricing', 'faq' => 'FAQ',
            'stats' => 'Stats/Numbers', 'clients' => 'Clients/Partners', 'partners' => 'Clients/Partners',
            'team' => 'Team', 'blog' => 'Blog/Articles', 'articles' => 'Blog/Articles',
            'contact' => 'Contact',
        ];

        // --- Registry class map for getDecorativeGuide() calls ---
        $registryMap = [
            'features' => 'FeaturesPatternRegistry', 'services' => 'FeaturesPatternRegistry',
            'about' => 'AboutPatternRegistry', 'testimonials' => 'TestimonialsPatternRegistry',
            'cta' => 'CTAPatternRegistry', 'newsletter' => 'CTAPatternRegistry',
            'gallery' => 'GalleryPatternRegistry', 'portfolio' => 'GalleryPatternRegistry',
            'pricing' => 'PricingPatternRegistry', 'faq' => 'FAQPatternRegistry',
            'stats' => 'StatsPatternRegistry', 'clients' => 'ClientsPatternRegistry', 'partners' => 'ClientsPatternRegistry',
            'team' => 'TeamPatternRegistry', 'blog' => 'BlogPatternRegistry', 'articles' => 'BlogPatternRegistry',
            'contact' => 'ContactPatternRegistry',
        ];

        foreach ($this->sectionPatternResults as $sectionId => $result) {
            $classList = $this->getSectionClassList($sectionId);
            if (!$classList) continue;

            $patternId = $result['pattern_id'] ?? 'unknown';
            $label = $sectionLabels[$sectionId] ?? ucfirst($sectionId);
            $sectionIdUpper = strtoupper($sectionId);

            // Get generic base guide from $sectionGuides
            $guide = $sectionGuides[$sectionId] ?? null;
            if ($guide === null) {
                $aliasMap = [
                    'services' => 'features', 'portfolio' => 'gallery',
                    'partners' => 'clients', 'articles' => 'blog', 'newsletter' => 'cta',
                ];
                $canonical = $aliasMap[$sectionId] ?? null;
                if ($canonical) $guide = $sectionGuides[$canonical] ?? null;
            }
            $baseDecorative = $guide['decorative'] ?? '';

            // Get pattern-specific decorative guide from registry
            $registryClass = $registryMap[$sectionId] ?? null;
            $patternSpecific = '';
            if ($registryClass && class_exists('\\' . $registryClass)) {
                $fqClass = '\\' . $registryClass;
                if (method_exists($fqClass, 'getDecorativeGuide')) {
                    $patternSpecific = $fqClass::getDecorativeGuide($patternId);
                }
            }

            // Combine base + pattern-specific
            $decorative = $baseDecorative;
            if ($patternSpecific) {
                $decorative .= "\n\n⚡ PATTERN-SPECIFIC visual approach for \"{$patternId}\":\n{$patternSpecific}";
            }

            $blocks[] = <<<GUIDE
{$sectionIdUpper} SECTION (DECORATIVE CSS ONLY — structural layout CSS is pre-built and injected separately):
The {$label} section uses pattern "{$patternId}".
⚠️ EXACT {$sectionId} CSS class names you MUST use (do NOT invent alternatives):
{$classList}
⚠️ DO NOT write: position, display, flex, grid, width, height, max-width, min-height, padding, margin, overflow, z-index, order, gap, grid-template-columns, align-items, justify-content, flex-direction, flex-wrap, text-align for {$sectionId} elements — these are handled by structural CSS.
⚠️ DO write decorative CSS for ALL {$sectionId} classes listed above:
{$decorative}
GUIDE;
        }

        return implode("\n\n", $blocks);
    }

    private function getCreativityTemp(string $step): float
    {
        $temps = [
            'low'    => ['html' => 0.6, 'css' => 0.3, 'subpage' => 0.5, 'refine' => 0.2],
            'medium' => ['html' => 0.85, 'css' => 0.5, 'subpage' => 0.7, 'refine' => 0.3],
            'high'   => ['html' => 1.0, 'css' => 0.7, 'subpage' => 0.85, 'refine' => 0.4],
        ];
        $level = $temps[$this->creativity] ?? $temps['medium'];
        return $level[$step] ?? 0.7;
    }

    /**
     * Generate a random variation seed to force AI to produce different palettes each time.
     */
    private function getVariationSeed(): string
    {
        // Hue families with specific ranges — forces different color territories
        $hueDirections = [
            'Use PRIMARY hue 0-20° (deep red/crimson family). Secondary in complementary 180-200° (teal/cyan).',
            'Use PRIMARY hue 25-45° (burnt orange/terracotta family). Secondary in 200-230° (steel blue).',
            'Use PRIMARY hue 50-70° (golden/amber family). Secondary in 230-260° (deep purple).',
            'Use PRIMARY hue 80-110° (lime/chartreuse family). Secondary in 280-310° (magenta).',
            'Use PRIMARY hue 120-150° (emerald/forest family). Secondary in 320-350° (rose/pink).',
            'Use PRIMARY hue 155-180° (teal/jade family). Secondary in 0-30° (coral/salmon).',
            'Use PRIMARY hue 185-210° (ocean blue/cerulean family). Secondary in 30-60° (gold/amber).',
            'Use PRIMARY hue 215-240° (royal blue/sapphire family). Secondary in 45-75° (bronze/copper).',
            'Use PRIMARY hue 245-270° (purple/violet family). Secondary in 60-90° (olive/moss).',
            'Use PRIMARY hue 275-300° (magenta/plum family). Secondary in 100-130° (sage/mint).',
            'Use PRIMARY hue 305-330° (raspberry/fuchsia family). Secondary in 130-160° (pine/jungle).',
            'Use PRIMARY hue 335-360° (rose/scarlet family). Secondary in 160-190° (aqua/seafoam).',
        ];

        // Specific font pairings AI should explore (not use exactly, but as direction)
        $fontDirections = [
            'Heading: a bold geometric display font (like Outfit, Sora, or Space Grotesk). Body: an elegant serif (like Spectral, Literata, or Lora).',
            'Heading: a classic serif with character (like Fraunces, Bodoni Moda, or DM Serif Display). Body: a clean modern sans (like Be Vietnam Pro, Figtree, or Albert Sans).',
            'Heading: a condensed/compressed sans (like Barlow Condensed, Oswald, or Archivo Narrow). Body: a readable serif (like Source Serif 4, Crimson Pro, or Vollkorn).',
            'Heading: a soft rounded sans (like Nunito, Quicksand, or Comfortaa). Body: a professional sans (like Work Sans, DM Sans, or Urbanist).',
            'Heading: a high-contrast serif (like EB Garamond, Cormorant Garamond, or Old Standard TT). Body: a humanist sans (like Red Hat Display, Manrope, or Plus Jakarta Sans).',
            'Heading: a strong slab serif (like Bitter, Roboto Slab, or Cardo). Body: a neutral sans (like Rubik, Jost, or Overpass).',
            'Heading: a stylish display font (like Playfair Display 2, Abril Fatface, or Righteous). Body: a crisp technical sans (like IBM Plex Sans, JetBrains Mono, or Source Sans 3).',
            'Heading: a warm humanist sans (like Mulish, Nunito Sans, or Raleway). Body: a readable transitional serif (like Merriweather, PT Serif, or Noto Serif).',
        ];

        // Visual mood — concrete CSS direction
        $moodDirections = [
            'Minimal whitespace: large padding (section-spacing 140px+), thin borders, subtle shadows, restrained color use.',
            'Bold & punchy: strong contrast, big headings (clamp 3-6rem), saturated primary, dark backgrounds with bright accents.',
            'Soft & organic: rounded corners (16px+), pastel tints, gentle gradients, warm undertones, flowing shapes.',
            'Sharp & technical: small border-radius (4px), monospace accents, high contrast, grid-based, precise spacing.',
            'Luxurious & refined: gold/champagne accents, deep dark surfaces, generous letter-spacing, subtle animations.',
            'Warm & editorial: cream/warm-white backgrounds, rich serif headings, generous line-height, magazine-like spacing.',
            'Dark & atmospheric: near-black bg (#0a-#15 range), glowing accents, backdrop-blur effects, dramatic hero.',
            'Colorful & energetic: vibrant primary + contrasting accent, playful hover effects, asymmetric layouts, bold buttons.',
            'Earthy & natural: brown/green/beige palette, organic textures, nature-inspired naming, warm feel.',
            'Monochrome & stark: grayscale base with ONE accent color, strong typography hierarchy, high contrast.',
        ];

        // Header layout — FORCES a specific structure (not just "be creative")
        $headerLayouts = [
            'HEADER LAYOUT: SPLIT NAVIGATION. Nav links split into two groups with large centered logo between them. Structure: <nav-left> | <LOGO big centered> | <nav-right> | <hamburger>. Logo should be prominent (2x normal size). No CTA button visible — use last nav link as CTA styled differently.',
            'HEADER LAYOUT: STACKED DUAL-BAR. Two distinct horizontal bars. TOP BAR: thin strip with phone, email, social icons on dark bg. MAIN BAR below: logo left + nav + CTA. The two bars must look visually distinct (different bg colors).',
            'HEADER LAYOUT: CENTERED LOGO TOWER. Logo/brand name VERY LARGE and centered on its own line. Below it: a thin nav bar with links centered + CTA at far right. Think magazine/editorial masthead. Logo text size: 2.5-3rem.',
            'HEADER LAYOUT: MINIMAL HAMBURGER-ONLY. On desktop: show ONLY logo (left) and hamburger button (right). NO visible nav links. ALL navigation in a full-screen overlay that opens on hamburger click. Overlay: dark bg, large centered nav links, close button.',
            'HEADER LAYOUT: LEFT-HEAVY BRANDED. Logo + tagline/description stacked vertically on the left (taking 40% width). Nav links + CTA packed compactly on the right. The left brand area should feel like a "nameplate".',
            'HEADER LAYOUT: TRANSPARENT FLOATING PILL. Header is a rounded pill-shaped container (border-radius: 100px) floating over the hero with margin from edges. Contains: logo, nav, CTA all compact inside the pill. Background: glassmorphism (backdrop-filter: blur + semi-transparent).',
            'HEADER LAYOUT: SIDEBAR VERTICAL NAV (desktop). On desktop: fixed vertical sidebar on the left (width: 240px) with logo on top, vertical nav links below, CTA at bottom, social icons. On mobile: convert to standard horizontal header with hamburger. Body content shifts right on desktop.',
            'HEADER LAYOUT: MEGA TOP-BAR. Three horizontal zones stacked: (1) thin announcement/promo bar, (2) logo + search + CTA + social, (3) full-width nav bar with links. Each zone has different bg shade. Total header height: ~140px.',
            'HEADER LAYOUT: ASYMMETRIC BLOCKS. Header split into two unequal blocks side by side. LEFT block (70%): colored/branded background with logo + nav links inside. RIGHT block (30%): contrasting bg with CTA button + phone number. Creates a two-tone visual effect.',
            'HEADER LAYOUT: BOTTOM NAVIGATION BAR. Logo bar at top (simple, thin). Main nav bar FIXED AT BOTTOM of viewport (like mobile app). Nav links spread evenly. CTA button in center, larger than other items. On mobile: same bottom bar but with icons instead of text.',
        ];

        $hue = $hueDirections[array_rand($hueDirections)];
        $font = $fontDirections[array_rand($fontDirections)];
        $mood = $moodDirections[array_rand($moodDirections)];

        // Header layout — use user's choice if set, otherwise random
        $headerLayoutMap = [
            'split-nav' => 0, 'stacked-dual' => 1, 'centered-tower' => 2,
            'minimal-hamburger' => 3, 'left-branded' => 4, 'floating-pill' => 5,
            'sidebar-vertical' => 6, 'mega-topbar' => 7, 'asymmetric-blocks' => 8,
            'bottom-nav' => 9,
        ];
        $userHeaderChoice = $this->headerSettings['layout'] ?? 'auto';
        if ($userHeaderChoice !== 'auto' && isset($headerLayoutMap[$userHeaderChoice])) {
            $headerLayout = $headerLayouts[$headerLayoutMap[$userHeaderChoice]];
        } else {
            $headerLayout = $headerLayouts[array_rand($headerLayouts)];
        }

        // Header behavior directive
        $behaviorMap = [
            'sticky' => 'HEADER BEHAVIOR: Sticky — position:sticky top:0, stays visible on scroll. Add .header-scrolled with solid bg + shadow.',
            'fixed-transparent' => 'HEADER BEHAVIOR: Fixed Transparent — position:fixed, transparent bg over hero. On scroll add .header-scrolled with solid bg + backdrop-filter:blur.',
            'hide-on-scroll' => 'HEADER BEHAVIOR: Hide on Scroll Down — position:fixed, transform:translateY(-100%) on scroll down, reappears on scroll up. JS adds .header-hidden class.',
            'static' => 'HEADER BEHAVIOR: Static — position:relative, scrolls with page. No sticky/fixed behavior needed.',
        ];
        $userBehavior = $this->headerSettings['behavior'] ?? 'sticky';
        $headerBehavior = $behaviorMap[$userBehavior] ?? $behaviorMap['sticky'];

        // Header content options
        $headerExtras = [];
        if (!empty($this->headerSettings['ctaText'])) $headerExtras[] = 'CTA button text: "' . $this->headerSettings['ctaText'] . '"';
        if (!empty($this->headerSettings['ctaLink'])) $headerExtras[] = 'CTA link: ' . $this->headerSettings['ctaLink'];
        if (!empty($this->headerSettings['showPhone'])) $headerExtras[] = 'Include phone number in header (from theme_get("header.phone"))';
        if (!empty($this->headerSettings['showSearch'])) $headerExtras[] = 'Include search icon/button in header';
        if (!empty($this->headerSettings['showSocial'])) $headerExtras[] = 'Include social media icons in header';
        if (!empty($this->headerSettings['topBar'])) $headerExtras[] = 'Include a top info bar above the main header (phone, email, social)';
        $headerExtrasStr = $headerExtras ? "\nHEADER EXTRAS: " . implode('. ', $headerExtras) . '.' : '';

        // Footer layout directive
        $footerLayouts = [
            'multi-column' => 'FOOTER LAYOUT: Multi-Column Grid — 3-4 columns: brand+desc, nav links, services, contact info.',
            'centered-minimal' => 'FOOTER LAYOUT: Centered Minimal — logo centered, single row of links, copyright. Clean and simple.',
            'big-brand' => 'FOOTER LAYOUT: Big Brand — large logo + long description on left (60%), compact links + contact on right.',
            'newsletter-focused' => 'FOOTER LAYOUT: Newsletter Focused — large email signup section at top, then brand + links below.',
            'split-dark-light' => 'FOOTER LAYOUT: Split Dark/Light — top half dark with brand+links, bottom half lighter with contact+copyright.',
            'magazine' => 'FOOTER LAYOUT: Magazine Style — multiple rows: featured links, categories, tags, about, then copyright bar.',
            'wave-divider' => 'FOOTER LAYOUT: Wave Divider — decorative SVG wave at top edge, then multi-column content below.',
            'compact' => 'FOOTER LAYOUT: Compact Single Line — logo, horizontal links, social icons, copyright all in one or two lines.',
        ];
        $userFooterChoice = $this->footerSettings['layout'] ?? 'auto';
        $footerLayout = ($userFooterChoice !== 'auto' && isset($footerLayouts[$userFooterChoice]))
            ? $footerLayouts[$userFooterChoice]
            : $footerLayouts[array_rand($footerLayouts)];

        // Footer content options
        $footerExtras = [];
        if (!empty($this->footerSettings['newsletter'])) $footerExtras[] = 'Include newsletter signup form';
        if (!empty($this->footerSettings['social'])) $footerExtras[] = 'Include social media icons';
        if (!empty($this->footerSettings['contact'])) $footerExtras[] = 'Include contact info (phone, email, address)';
        if (!empty($this->footerSettings['map'])) $footerExtras[] = 'Include a map/location section';
        if (!empty($this->footerSettings['hours'])) $footerExtras[] = 'Include opening hours';
        $footerExtrasStr = $footerExtras ? "\nFOOTER EXTRAS: " . implode('. ', $footerExtras) . '.' : '';

        return sprintf("COLOR: %s\nFONTS: %s\nMOOD: %s\n%s\n%s%s\n%s%s\nSEED: %s",
            $hue, $font, $mood, $headerLayout, $headerBehavior, $headerExtrasStr,
            $footerLayout, $footerExtrasStr, bin2hex(random_bytes(4))
        );
    }

    private function languageInstruction(): string
    {
        if ($this->language && strtolower($this->language) !== 'english') {
            return "IMPORTANT: Generate ALL text content (titles, descriptions, headings, paragraphs, button labels) in {$this->language}. Code (HTML tags, CSS, PHP) stays in English.\n\n";
        }
        return '';
    }

    /**
     * Scan existing themes to build "do NOT copy" context
     */
    private function buildExistingThemesContext(): string
    {
        $themesDir = CMS_ROOT . '/themes';
        if (!is_dir($themesDir)) return '';

        $existing = [];
        foreach (glob($themesDir . '/*/theme.json') as $jsonFile) {
            $data = @json_decode(file_get_contents($jsonFile), true);
            if (!$data) continue;
            $slug = basename(dirname($jsonFile));
            $colors = $data['colors'] ?? [];
            $typo = $data['typography'] ?? [];
            $sections = array_column($data['homepage_sections'] ?? [], 'id');
            // Only expose colors/fonts/sections — NOT names/descriptions (prevents AI copying content)
            $existing[] = sprintf(
                '- Theme %d: primary=%s, secondary=%s, bg=%s, fonts=%s/%s, sections=[%s]',
                count($existing) + 1,
                $colors['primary'] ?? '?',
                $colors['secondary'] ?? '?',
                $colors['background'] ?? '?',
                $typo['headingFont'] ?? '?',
                $typo['fontFamily'] ?? '?',
                implode(',', $sections)
            );
        }

        if (empty($existing)) return '';

        return "EXISTING THEMES (DO NOT duplicate these — use DIFFERENT colors, fonts, layout, sections):\n"
            . implode("\n", $existing) . "\n";
    }

    /* ═══════════════════════════════════════════════════════
       Step 1: Design Brief — Extended palette + sections
       ═══════════════════════════════════════════════════════ */
    private function step1_designBrief(): array
    {
        $langInstr = $this->languageInstruction();
        $kbSchema = $this->getKB('2', '14');
        $existing = $this->existingThemesContext;

        $systemPrompt = <<<PROMPT
{$langInstr}You are a professional web designer. Generate a design system (theme.json) for a website.

REFERENCE — follow this schema exactly:
{$kbSchema}

Return ONLY valid JSON matching the theme.json schema above, PLUS these additional keys:
- "slug": lowercase a-z and hyphens, max 30 chars
- "google_fonts_url": valid Google Fonts URL with both heading and body fonts

CONTEXT:
- Industry: {$this->industry}
- Style: {$this->style}
  minimalist=whitespace+clean, bold=strong contrasts+big type, elegant=serif+refined, playful=rounded+fun colors,
  corporate=structured+professional, brutalist=raw+stark+monospace, retro=vintage textures+nostalgic,
  futuristic=gradients+glassmorphism+neon, organic=curves+natural textures, artdeco=geometric+gold+luxury,
  glassmorphism=frosted glass+blur+transparency, neubrutalism=thick borders+bright fills+quirky,
  editorial=magazine-like typography+whitespace, geometric=bold shapes+patterns+angles
- Mood: {$this->mood}
  light=white/cream bg, dark=near-black bg, colorful=vibrant+saturated, monochrome=grayscale+single accent,
  warm=amber/orange/red tones, cool=blue/teal/cyan tones, pastel=soft muted colors, neon=electric bright on dark,
  earth=browns/greens/natural, luxury=dark+gold/champagne accents

{$existing}
UNIQUENESS — MOST IMPORTANT RULES:
- BANNED FONTS (overused — NEVER pick these): Inter, Open Sans, Roboto, Lato, Montserrat, Playfair Display, Poppins.
  There are 1500+ Google Fonts. Use them. Pick SURPRISING, FRESH combinations.
  Examples of great pairs: Fraunces + Be Vietnam Pro, Sora + Libre Baskerville, Space Grotesk + Crimson Pro,
  DM Serif Display + Manrope, Outfit + Spectral, Urbanist + Cardo, Red Hat Display + Literata,
  Plus Jakarta Sans + Old Standard TT, Figtree + Bodoni Moda, Albert Sans + Vollkorn.
  NEVER pair two fonts from the same family (e.g. Roboto + Roboto Slab).
- BANNED COLORS: Do NOT use generic #6366f1 (indigo), #3b82f6 (blue-500), #f97316 (orange-500), #8b5cf6 (violet).
  These are Tailwind defaults and look generic. Pick SPECIFIC, UNIQUE colors.
  Use HSL thinking: vary hue (0-360), saturation (40-90%), lightness (30-60% for primary).
  Examples: #2d6a4f (forest), #b5651d (burnt sienna), #5b4a8a (dusty purple), #c2185b (raspberry),
  #00838f (deep teal), #8d6e63 (warm taupe), #558b2f (olive), #ad1457 (magenta), #00695c (pine green).
- EVERY generation MUST look visually DISTINCT from all previous themes listed above.
  Different hue family, different font pairing, different mood.

RULES:
- homepage_sections: Pick 4-6 sections that MAKE SENSE for this industry. Use the guide below.
  hero is ALWAYS required. Then pick from the RECOMMENDED list for the industry.
  DO NOT add services/portfolio/pricing to a blog. DO NOT add menu/events to a SaaS.

INDUSTRY → RECOMMENDED SECTIONS (pick 4-6 from the list, hero always first):

  FOOD & HOSPITALITY:
  restaurant/cafe/bakery/foodtruck → hero, menu, gallery, about, testimonials, cta, events, map
  bar/nightclub            → hero, gallery, events, about, menu, cta, map
  catering                 → hero, services, gallery, menu, testimonials, cta, faq
  hotel/resort/bnb         → hero, gallery, features, testimonials, cta, map, faq
  winery/brewery           → hero, gallery, about, process, events, cta, map

  TECH & DIGITAL:
  saas/devtools/hosting    → hero, features, pricing, testimonials, faq, cta, stats, clients
  startup/app              → hero, features, stats, testimonials, pricing, cta, faq
  ai/ml                    → hero, features, stats, about, cta, articles, clients
  crypto/web3              → hero, features, stats, faq, cta, timeline
  cybersecurity            → hero, features, stats, testimonials, cta, clients, faq
  itsupport                → hero, services, pricing, testimonials, faq, cta, clients
  gamedev                  → hero, portfolio, gallery, about, team, cta

  CREATIVE & MEDIA:
  portfolio/design         → hero, portfolio, about, services, testimonials, cta, clients
  photography              → hero, gallery, about, portfolio, cta, testimonials
  videography/film/animation → hero, video, portfolio, about, testimonials, cta
  agency/marketing         → hero, services, portfolio, stats, testimonials, cta, clients, team
  music/band/dj            → hero, video, gallery, events, about, cta, newsletter
  art/gallery              → hero, gallery, about, events, cta, newsletter
  architecture             → hero, portfolio, about, services, team, cta, stats
  interior                 → hero, gallery, portfolio, about, services, testimonials, cta
  tattoo                   → hero, gallery, about, pricing, cta, faq, map

  CONTENT & PUBLISHING:
  blog/personal            → hero, articles, newsletter, about, categories, testimonials
  magazine/news            → hero, articles, newsletter, about, categories, testimonials
  podcast                  → hero, features, about, articles, newsletter, cta
  newsletter/substack      → hero, about, articles, newsletter, testimonials, cta
  author/writer            → hero, about, articles, gallery, testimonials, cta, newsletter
  influencer/creator       → hero, gallery, about, articles, newsletter, cta, stats

  COMMERCE & RETAIL:
  ecommerce/electronics    → hero, products-showcase, features, testimonials, cta, newsletter, stats, faq
  fashion/jewelry/beauty   → hero, products-showcase, gallery, features, testimonials, cta, newsletter
  furniture/homedecor      → hero, products-showcase, gallery, features, testimonials, cta, newsletter
  bookshop                 → hero, products-showcase, articles, features, testimonials, cta, newsletter
  grocery/organic          → hero, products-showcase, features, about, testimonials, cta, newsletter
  pets                     → hero, products-showcase, services, gallery, testimonials, cta, about
  florist                  → hero, products-showcase, gallery, services, about, testimonials, cta
  marketplace              → hero, products-showcase, features, stats, testimonials, faq, cta

  PROFESSIONAL SERVICES:
  law                      → hero, services, about, team, testimonials, faq, cta
  finance/insurance/accounting → hero, services, about, team, stats, testimonials, faq, cta
  consulting/coaching      → hero, services, about, testimonials, pricing, faq, cta
  recruiting               → hero, services, stats, testimonials, about, cta, clients
  translation              → hero, services, pricing, about, testimonials, cta, faq
  realestate/propertymanagement → hero, features, gallery, about, testimonials, cta, stats, map

  HEALTH & WELLNESS:
  medical/dental/pharmacy  → hero, services, team, about, testimonials, faq, cta
  veterinary               → hero, services, team, about, testimonials, cta, gallery
  therapy/mentalhealth/counseling → hero, about, services, testimonials, faq, cta
  spa/wellness             → hero, services, gallery, pricing, testimonials, cta, about
  fitness/gym              → hero, services, pricing, team, testimonials, cta, stats
  yoga/pilates             → hero, services, about, pricing, testimonials, cta, gallery
  nutrition/dietitian      → hero, services, about, testimonials, articles, cta, faq
  physiotherapy            → hero, services, team, about, testimonials, faq, cta

  EDUCATION & TRAINING:
  education/university     → hero, features, about, team, testimonials, faq, cta, articles
  onlinecourse/lms         → hero, features, pricing, testimonials, faq, cta, stats
  tutoring/language        → hero, services, pricing, about, testimonials, faq, cta
  driving                  → hero, services, pricing, faq, testimonials, cta, about
  childcare/nursery        → hero, about, gallery, services, testimonials, cta, faq
  library                  → hero, about, features, articles, events, cta
  training                 → hero, services, pricing, testimonials, stats, cta, clients

  CONSTRUCTION & TRADE:
  construction/builder     → hero, services, portfolio, about, testimonials, stats, cta
  plumbing/electrical/hvac/roofing → hero, services, about, testimonials, faq, cta, map
  painting/decorating      → hero, gallery, services, about, testimonials, cta
  landscaping/garden       → hero, gallery, services, about, testimonials, cta
  cleaning                 → hero, services, pricing, testimonials, faq, cta
  moving/removals          → hero, services, pricing, about, testimonials, faq, cta
  handyman                 → hero, services, about, testimonials, faq, cta, map
  solar/renewable          → hero, features, about, stats, testimonials, faq, cta

  AUTOMOTIVE & TRANSPORT:
  automotive/dealership    → hero, gallery, features, about, testimonials, cta
  mechanic/repair          → hero, services, about, testimonials, faq, cta, map
  carwash/detailing        → hero, services, pricing, gallery, testimonials, cta
  taxi/rideshare           → hero, features, about, pricing, cta, map
  trucking/logistics       → hero, services, about, stats, clients, cta
  motorcycle               → hero, gallery, features, about, cta
  boating/marine           → hero, gallery, services, about, cta, map

  EVENTS & ENTERTAINMENT:
  events/conference/festival → hero, gallery, timeline, about, testimonials, cta, map
  wedding/planner          → hero, gallery, services, testimonials, about, cta, faq
  party/entertainment      → hero, gallery, services, pricing, testimonials, cta
  venue/hall               → hero, gallery, features, pricing, faq, cta, map
  theater/performingarts   → hero, gallery, events, about, cta, newsletter
  cinema                   → hero, gallery, events, about, cta
  escaperoom               → hero, gallery, pricing, faq, testimonials, cta

  TRAVEL & LEISURE:
  travel/tourism           → hero, gallery, features, testimonials, about, cta, articles
  camping/outdoors         → hero, gallery, features, about, testimonials, cta, map
  skiing/diving/watersports → hero, gallery, features, pricing, about, cta, map
  golf/sportsclub          → hero, gallery, features, about, events, cta, map
  marina/yachtclub         → hero, gallery, features, about, events, cta, map

  COMMUNITY & NON-PROFIT:
  nonprofit/charity/volunteer → hero, about, stats, team, articles, cta, newsletter
  church/religious         → hero, about, events, articles, team, cta, newsletter
  political                → hero, about, stats, articles, cta, newsletter, events
  community/association    → hero, about, events, articles, team, cta, gallery

  GOVERNMENT & PUBLIC:
  government/municipal     → hero, services, about, articles, faq, cta
  police/emergency         → hero, about, services, articles, faq, cta
  military                 → hero, about, gallery, articles, stats, cta
  embassy                  → hero, about, services, articles, faq, cta

  OTHER:
  personal/resume/cv       → hero, about, portfolio, articles, cta, contact
  wiki/documentation       → hero, features, articles, faq, cta
  directory/listing        → hero, features, stats, about, cta
  landing                  → hero, features, testimonials, pricing, cta, faq
  comingsoon               → hero, newsletter, about, cta
  memorial/tribute         → hero, about, gallery, timeline, cta

  DEFAULT (unknown)        → hero, about, features, articles, cta

  If the user's prompt mentions specific sections (e.g. "with pricing page"), include them.
  Match the industry to the CLOSEST entry above. Do NOT default to services/portfolio for content-based sites.

- Colors: cohesive, accessible. Dark mood → light text (#e-f range), dark bg (#0-2 range). Light → opposite.
  ALWAYS generate SPECIFIC hex values — not generic framework defaults.
  Every color must feel INTENTIONAL and part of a cohesive palette.
- Fonts: pick from the FULL Google Fonts catalog. Avoid top-10 most popular fonts.
  Heading + body should be different families but complementary (serif+sans or display+sans).
- Create a UNIQUE name and slug. Be creative — not just "industry-style" pattern.

VARIATION DIRECTIVE (MANDATORY — follow this creative direction):
{$this->getVariationSeed()}
Use this seed to determine your hue family, font personality, and visual mood.
Generate something NOBODY has seen before. Surprise the user.
PROMPT;

        $result = $this->aiQuery($this->prompt, $this->queryOptions([
            'system_prompt' => $systemPrompt,
            'max_tokens' => 2500,
            'temperature' => $this->getCreativityTemp('html'),
            'json_mode' => true,
        ]));

        $json = $this->extractJson($result);
        if (!$json || empty($json['colors']) || empty($json['typography'])) {
            $rawText = substr($result['text'] ?? '(no text)', 0, 2000);
            $jsonKeys = $json ? implode(',', array_keys($json)) : 'NULL';
            @file_put_contents('/tmp/aitb-brief-fail.log', date('H:i:s') . " Brief parse FAILED\nKeys: {$jsonKeys}\nRaw: {$rawText}\n\n", FILE_APPEND);
            // Check if it was an AI provider error (not just bad JSON)
            $aiError = null;
            $errorDetail = $aiError ? json_encode($aiError) : '';
            throw new \RuntimeException('Step 1 failed: Invalid design brief from AI (keys: ' . $jsonKeys . ')' . ($errorDetail ? '|||AI_ERROR|||' . $errorDetail : ''));
        }

        $json['slug'] = preg_replace('/[^a-z0-9-]/', '', strtolower($json['slug'] ?? 'ai-theme'));
        if (empty($json['slug'])) $json['slug'] = 'ai-theme-' . date('His');
        $this->slug = $json['slug'];

        // Ensure homepage_sections exists with at least hero
        if (empty($json['homepage_sections'])) {
            $json['homepage_sections'] = [
                ['id' => 'hero', 'label' => 'Hero', 'icon' => '⭐', 'required' => true],
                ['id' => 'about', 'label' => 'About', 'icon' => '📖'],
                ['id' => 'pages', 'label' => 'Pages', 'icon' => '📋'],
                ['id' => 'articles', 'label' => 'Articles', 'icon' => '📰'],
                ['id' => 'cta', 'label' => 'CTA', 'icon' => '🎯'],
            ];
        }

        // Enforce: hero always first, CTA always last
        $sections = $json['homepage_sections'];
        $hero = array_filter($sections, fn($s) => ($s['id'] ?? '') === 'hero');
        $cta = array_filter($sections, fn($s) => ($s['id'] ?? '') === 'cta');
        $rest = array_filter($sections, fn($s) => !in_array($s['id'] ?? '', ['hero', 'cta']));
        $json['homepage_sections'] = array_values(array_merge($hero, $rest, $cta));

        return $json;
    }

    /* ═══════════════════════════════════════════════════════
       Step 2: HTML Structure — Delimiter-based (no JSON)
       ═══════════════════════════════════════════════════════ */
    private function step2_htmlStructure(array $brief): array
    {
        // ── Header Pattern (deterministic, no AI) ──
        $prefix = \HeaderPatternRegistry::generatePrefix($brief['theme_name'] ?? $this->slug ?: 'th');
        // Use user choice from wizard Design step if set, otherwise auto-select
        $userHeaderPick = $this->headerSettings['layout'] ?? 'auto';
        if ($userHeaderPick !== 'auto' && $userHeaderPick !== '') {
            $allHP = \HeaderPatternRegistry::getPatternList();
            $validHP = array_column($allHP, 'id');
            $patternId = in_array($userHeaderPick, $validHP) ? $userHeaderPick : \HeaderPatternRegistry::selectPattern($brief);
        } else {
            $patternId = \HeaderPatternRegistry::selectPattern($brief);
        }
        $this->headerPatternResult = \HeaderPatternRegistry::render($patternId, $prefix, $brief);
        $this->emitProgress('header_pattern', ['pattern' => $patternId, 'prefix' => $prefix]);

        // ── Footer Pattern (deterministic, no AI) ──
        $userFooterPick = $this->footerSettings['layout'] ?? 'auto';
        if ($userFooterPick !== 'auto' && $userFooterPick !== '') {
            $allFP = \FooterPatternRegistry::getPatternList();
            $validFP = array_column($allFP, 'id');
            $footerPatternId = in_array($userFooterPick, $validFP) ? $userFooterPick : \FooterPatternRegistry::selectPattern($brief);
        } else {
            $footerPatternId = \FooterPatternRegistry::selectPattern($brief);
        }
        $this->footerPatternResult = \FooterPatternRegistry::render($footerPatternId, $prefix, $brief);
        $this->emitProgress('footer_pattern', ['pattern' => $footerPatternId, 'prefix' => $prefix]);

        // ── Hero Pattern (deterministic, no AI) ──
        $userHeroPick = $this->heroSettings['layout'] ?? 'auto';
        if ($userHeroPick !== 'auto' && $userHeroPick !== '') {
            $allHeroP = \HeroPatternRegistry::getPatternList();
            $validHeroP = array_column($allHeroP, 'id');
            $heroPatternId = in_array($userHeroPick, $validHeroP) ? $userHeroPick : \HeroPatternRegistry::pickPattern($this->industry);
        } else {
            $heroPatternId = \HeroPatternRegistry::pickPattern($this->industry);
        }
        $this->heroPatternResult = \HeroPatternRegistry::render($heroPatternId, $prefix, $brief);
        $this->emitProgress('hero_pattern', ['pattern' => $heroPatternId, 'prefix' => $prefix]);

        // ── Section Patterns (deterministic, no AI) ──
        $sectionRegistryMap = [
            'features' => 'FeaturesPatternRegistry',
            'services' => 'FeaturesPatternRegistry',   // wizard alias → features
            'about' => 'AboutPatternRegistry',
            'testimonials' => 'TestimonialsPatternRegistry',
            'pricing' => 'PricingPatternRegistry',
            'cta' => 'CTAPatternRegistry',
            'faq' => 'FAQPatternRegistry',
            'stats' => 'StatsPatternRegistry',
            'clients' => 'ClientsPatternRegistry',
            'partners' => 'ClientsPatternRegistry',    // wizard alias → clients
            'gallery' => 'GalleryPatternRegistry',
            'portfolio' => 'GalleryPatternRegistry',   // wizard alias → gallery
            'team' => 'TeamPatternRegistry',
            'blog' => 'BlogPatternRegistry',
            'articles' => 'BlogPatternRegistry',        // wizard alias → blog
            'contact' => 'ContactPatternRegistry',
            'newsletter' => 'CTAPatternRegistry',       // wizard alias → cta
        ];
        $tempSectionIds = array_column($brief['homepage_sections'] ?? [], 'id');
        foreach ($tempSectionIds as $sectionId) {
            if ($sectionId === 'hero') continue; // already handled above
            $registryClass = $sectionRegistryMap[$sectionId] ?? null;
            if (!$registryClass || !class_exists('\\' . $registryClass)) continue;

            $registry = '\\' . $registryClass;
            $sectionPatternId = $registry::pickPattern($this->industry);
            $this->sectionPatternResults[$sectionId] = $registry::render($sectionPatternId, $prefix, $brief);
            $this->emitProgress('section_pattern', ['section' => $sectionId, 'pattern' => $sectionPatternId]);
        }

        $briefJson = json_encode($brief, JSON_PRETTY_PRINT);
        $langInstr = $this->languageInstruction();
        $sectionIds = array_column($brief['homepage_sections'] ?? [], 'id');
        $sectionsDesc = implode(', ', $sectionIds);

        // Header & Footer CSS classes for AI reference in CSS generation
        $headerClasses = implode(', ', array_map(fn($c) => '.' . $c, $this->headerPatternResult['classes'] ?? []));
        $footerClasses = implode(', ', array_map(fn($c) => '.' . $c, $this->footerPatternResult['classes'] ?? []));
        $heroClasses = implode(', ', array_map(fn($c) => '.' . $c, $this->heroPatternResult['classes'] ?? []));

        // All section pattern CSS classes for AI reference
        $allSectionClasses = [];
        foreach ($this->sectionPatternResults as $sid => $result) {
            $allSectionClasses[$sid] = implode(', ', array_map(fn($c) => '.' . $c, $result['classes'] ?? []));
        }

        // Build pre-built sections list for AI prompt (hero + all pattern-generated sections + products-showcase)
        $prebuiltSections = array_merge(['hero', 'products-showcase'], array_keys($this->sectionPatternResults));
        $prebuiltList = implode(', ', $prebuiltSections);
        $prebuiltDelimiters = implode(', ', array_map(fn($s) => "===SECTION:{$s}===", $prebuiltSections));

        // Build section classes block for AI to reference when writing CSS
        $sectionClassesLines = [];
        foreach ($this->sectionPatternResults as $sid => $result) {
            $cls = implode(', ', array_map(fn($c) => '.' . $c, $result['classes'] ?? []));
            $pid = $result['pattern_id'] ?? 'unknown';
            $sectionClassesLines[] = "{$sid} section uses these CSS classes (you MUST style them in Step 3): {$cls}\n{$sid} pattern: {$pid} — prefix: {$prefix}";
        }
        $sectionClassesBlock = implode("\n", $sectionClassesLines);

        // Load relevant KB sections (no header section needed — pattern handles it)
        $kbRef = $this->getKB('3', '4', '5', '7', '8', '8b', '10', '13', '17', '18');

        $existing = $this->existingThemesContext;

        $systemPrompt = <<<PROMPT
{$langInstr}You are an expert frontend developer generating PHP/HTML for a CMS theme.

DESIGN BRIEF:
{$briefJson}

VARIATION DIRECTIVE (MANDATORY — follow this EXACTLY for the header layout):
{$this->getVariationSeed()}

USER REQUEST: {$this->prompt}
INDUSTRY: {$this->industry} | STYLE: {$this->style} | TONE: {$this->tone}

{$existing}
KNOWLEDGE BASE — follow these structural patterns (IDs, classes, PHP functions) EXACTLY, but invent your OWN visual layout:
{$kbRef}

OUTPUT FORMAT — use these exact delimiters (each on its own line):
===SIDEBAR===
(sidebar HTML/PHP code for articles listing page)
{$this->buildSectionDelimiterBlock(array_diff($sectionIds, $prebuiltSections))}
===END===

⚠️ HEADER, FOOTER & PRE-BUILT SECTIONS — DO NOT generate HTML for any of these. They are handled by the pattern system.
Pre-built sections: {$prebuiltList}
Do NOT output these delimiters: {$prebuiltDelimiters}
Header uses these CSS classes (you MUST style them in Step 3): {$headerClasses}
Header pattern: {$patternId} — prefix: {$prefix}
Footer uses these CSS classes (you MUST style them in Step 3): {$footerClasses}
Footer pattern: {$footerPatternId} — prefix: {$prefix}
Hero uses these CSS classes (you MUST style them in Step 3): {$heroClasses}
Hero pattern: {$heroPatternId} — prefix: {$prefix}
{$sectionClassesBlock}
⚠️ You only need to generate the SIDEBAR content. All homepage sections listed above are pre-built.

TASK:
1. SIDEBAR — Generate a UNIQUE sidebar for the articles listing page between ===SIDEBAR=== delimiters.
   - This is an <aside> element placed next to the articles grid
   - MUST include categories widget: loop through \$categories array (inherited from parent scope)
     Each category has: slug, name, article_count
     Link format: /articles?category=<?= esc(\$cat['slug']) ?>
   - BE CREATIVE with additional widgets! Pick 2-3 from:
     * Search box (text input with search icon)
     * "About this blog" blurb with data-ts="sidebar.about"
     * Newsletter signup form (email input + button)
     * Tags cloud / popular tags
     * Social follow links (using theme_get('footer.facebook') etc.)
     * Recent articles placeholder (static "Latest Stories" heading)
     * Quote / tip of the day
   - Use theme_get() + data-ts for any editable text
   - Style classes: MUST use these CSS class names (they have pre-built CSS):
     * Wrapper: <aside class="atpl-sidebar"> (flex column, gap 24px)
     * Widget card: <div class="atpl-widget"> (background, border, padding, border-radius)
     * Widget title: <h4 class="atpl-widget-title"> (uppercase, flex with icon gap)
     * Category link: <a class="atpl-cat-link"> (flex, space-between, border-bottom)
     * Category count: <span class="atpl-cat-count"> (pill badge)
     * Search form: <form class="atpl-search-form"> + <input class="atpl-search-input"> + <button class="atpl-search-btn">
   - DO NOT invent custom sidebar class names — the above classes have CSS in the articles template
   - Be creative with WIDGET SELECTION and CONTENT, not class names

3. SECTIONS — Generate EACH section as a SEPARATE block between ===SECTION:id=== delimiters.
   - {$sectionsDesc}
   - Each section file starts with <?php variable assignments using theme_get()
   - ALL text: theme_get() + data-ts attributes
   - Add data-animate to animated elements
   - Use Font Awesome icons (section 13)

HERO SECTION ARCHITECTURE:
- .hero wrapping div with .hero-bg (background image), .hero-overlay (gradient), .hero-content
- OVERLAY MUST be a gradient: linear-gradient(135deg, rgba(bg,0.85) 0%, rgba(bg,0.4) 100%) — NEVER transparent, NEVER flat color
- Content: badge/label → headline → subtitle → CTA buttons → optional stats row
- data-ts bindings on all editable elements (section 5)

SECTION ARCHITECTURE:
- Alternate backgrounds: odd sections = --background, even sections = --surface or --surface-elevated
- Section padding: 120-160px vertical (use var(--section-spacing))
- Section header pattern: .section-label → .section-divider → .section-title → .section-desc (centered)
- Pages section: loop through \$pages array (inherited from parent scope)
- Articles section: loop through \$articles array (inherited from parent scope)
- Each section needs a DISTINCTIVE visual approach — not just "container > heading > grid > cards"

CRITICAL:
- Output raw PHP/HTML code between delimiters — NO JSON wrapping, NO markdown backticks
- Write real PHP: <?php ... ?> (no escaping needed — this is raw code output)
- Each section is a STANDALONE file — it gets \$pages, \$articles, \$themePath from parent scope
- NO inline styles except dynamic background images

CONTENT TONE: Write ALL text in a {$this->tone} tone.
- professional = formal, authoritative, trust-building
- friendly = warm, approachable, conversational
- casual = relaxed, informal, down-to-earth
- formal = dignified, traditional, ceremonial
- witty = clever, playful, memorable
- luxurious = premium, exclusive, aspirational

CONTENT — ALL text defaults MUST match the user's request:
- theme_get() default values must be RELEVANT to the industry and prompt
- Hero headline: compelling, specific to this business type, NOT generic
- Section labels, titles, descriptions: specific to {$this->industry}
- Footer tagline: specific to this business
- CTA text: specific to what this business offers
- NEVER copy text from existing themes — write ORIGINAL content for "{$this->prompt}"
- Example: if industry is "law firm" → "Protecting Your Rights Since 2005", NOT "23+ Years of Excellence"
- Example: if industry is "restaurant" → "Taste the Tradition", NOT "Professional services"

UNIQUENESS — THIS IS THE MOST IMPORTANT RULE:
- DO NOT copy or mimic existing themes listed above. Your layout MUST be visibly different.
- Vary the hero style: split-screen, video bg, animated text, minimal center, asymmetric, illustrated
- Vary card patterns: standard grid, masonry, overlapping, horizontal cards, list view, magazine layout
- Vary the page rhythm: alternating left-right, full-bleed sections, narrow centered, mixed widths
- Use creative section dividers: SVG waves, diagonal clips, gradient fades, geometric shapes
- Each section needs a DISTINCTIVE visual approach — not just "container > heading > grid > cards"
PROMPT;

        // Inject Pexels image URLs if available — prevents AI from inventing fake image paths
        if (!empty($this->pexelsImages)) {
            $imgLines = [];
            foreach (array_slice($this->pexelsImages, 0, 12) as $i => $img) {
                $imgLines[] = ($i + 1) . ". {$img['src']} — alt: \"{$img['alt']}\"";
            }
            $pexelsBlock = "\n\nREAL STOCK IMAGES — Use THESE URLs for ALL <img> src attributes. DO NOT invent image paths or filenames:\n" . implode("\n", $imgLines) . "\n\nFor gallery sections: use at least 6 of these images. For hero/about/other sections: pick the most relevant ones.\nIMPORTANT: Store image URLs in PHP variables at the top of each section file, e.g.:\n<?php \$images = ['" . implode("', '", array_column(array_slice($this->pexelsImages, 0, 6), 'src')) . "']; ?>\nThen use \$images[0], \$images[1], etc. in <img> tags.";
            $systemPrompt .= $pexelsBlock;
        }

        $result = $this->aiQuery("Generate the HTML structure", $this->queryOptions([
            'system_prompt' => $systemPrompt,
            'max_tokens' => 32000,
            'temperature' => $this->getCreativityTemp('html'),
        ]));

        if (empty($result['ok']) || empty($result['text'])) {
            throw new \RuntimeException('Step 2 failed: AI returned no output — ' . ($result['error'] ?? 'unknown'));
        }

        // Parse individual sections (HEADER comes from pattern, not AI)
        $sectionIds = array_column($brief['homepage_sections'] ?? [], 'id');
        $parsed = $this->extractSectionDelimited($result['text'], $sectionIds);

        // If we have pattern-generated sections, we don't strictly need AI-parsed sections
        // (AI only needs to provide sidebar and any non-pattern sections)
        $hasPatternSections = !empty($this->heroPatternResult['html']) || !empty($this->sectionPatternResults);
        if (empty($parsed['sections']) && !$hasPatternSections) {
            $debugPath = CMS_ROOT . '/storage/logs/aitb-step2-debug-' . date('His') . '.txt';
            @file_put_contents($debugPath, $result['text']);
            throw new \RuntimeException('Step 2 failed: No sections parsed. Raw saved to ' . $debugPath);
        }
        if (empty($parsed['sections'])) {
            $parsed['sections'] = [];
        }

        // Inject hero from pattern system (overrides any AI-generated hero)
        $parsed['sections']['hero'] = $this->heroPatternResult['html'];

        // Inject all section patterns (overrides any AI-generated sections)
        foreach ($this->sectionPatternResults as $sectionId => $result) {
            $parsed['sections'][$sectionId] = $result['html'];
        }

        return [
            'header_html'  => $this->headerPatternResult['html'],  // from pattern system
            'footer_html'  => $this->footerPatternResult['html'],  // from pattern system
            'sidebar_html' => $parsed['SIDEBAR'] ?? '',
            'sections'     => $parsed['sections'],
            'header_structural_css' => $this->headerPatternResult['structural_css'],
            'footer_structural_css' => $this->footerPatternResult['structural_css'],
            'hero_structural_css'   => $this->heroPatternResult['structural_css'],
            'header_pattern_id'     => $this->headerPatternResult['pattern_id'],
            'footer_pattern_id'     => $this->footerPatternResult['pattern_id'],
            'hero_pattern_id'       => $this->heroPatternResult['pattern_id'],
            'section_pattern_ids'   => array_map(fn($r) => $r['pattern_id'] ?? '', $this->sectionPatternResults),
        ];
    }

    /**
     * Parse delimiter-separated output: ===SECTION=== ... ===NEXT=== ... ===END===
     */
    private function extractDelimited(string $text, array $sections): array
    {
        $result = [];

        // Strip markdown code fences if AI wrapped everything
        $text = preg_replace('/^```(?:php|html)?\s*$/m', '', $text);

        for ($i = 0; $i < count($sections); $i++) {
            $start = $sections[$i];
            $end = $sections[$i + 1] ?? 'END';

            // Match ===SECTION=== ... up to ===NEXT=== (flexible whitespace)
            $pattern = '/===\s*' . preg_quote($start, '/') . '\s*===\s*\n(.*?)(?=\n===\s*' . preg_quote($end, '/') . '\s*===|\z)/s';
            if (preg_match($pattern, $text, $m)) {
                $result[$start] = trim($m[1]);
            }
        }

        return $result;
    }

    /**
     * Build section delimiter instruction string for the prompt.
     * For sections [hero, about, articles], outputs: "about===\n===SECTION:articles==="
     * (skips first — already in prompt — and END is appended separately)
     */
    /**
     * Build the full section delimiter block for the prompt.
     * Output: ===SECTION:hero===\n...\n===SECTION:about===\n...\n (one per section)
     */
    private function buildSectionDelimiterBlock(array $sectionIds): string
    {
        $lines = [];
        foreach ($sectionIds as $i => $id) {
            $lines[] = "===SECTION:{$id}===";
            if ($i === 0) {
                $lines[] = "(first section PHP/HTML — MUST start with theme_get variable assignments)";
            } else {
                $lines[] = "(section PHP/HTML code here)";
            }
        }
        return implode("\n", $lines);
    }

    /**
     * Parse response with ===HEADER===, ===FOOTER===, ===SECTION:id=== delimiters
     */
    private function extractSectionDelimited(string $text, array $sectionIds): array
    {
        $result = ['HEADER' => '', 'FOOTER' => '', 'SIDEBAR' => '', 'sections' => []];

        // Strip markdown code fences
        $text = preg_replace('/^```(?:php|html)?\s*$/m', '', $text);

        // Extract HEADER — stop at ===FOOTER=== or ===SECTION:xxx===
        if (preg_match('/===\s*HEADER\s*===\s*\n(.*?)(?=\n===\s*(?:FOOTER|SECTION)[:\s=])/s', $text, $m)) {
            $result['HEADER'] = trim($m[1]);
        }

        // Extract FOOTER — stop at ===SIDEBAR=== or ===SECTION:xxx=== or ===END===
        if (preg_match('/===\s*FOOTER\s*===\s*\n(.*?)(?=\n===\s*(?:SIDEBAR|SECTION|END)[:\s=])/s', $text, $m)) {
            $result['FOOTER'] = trim($m[1]);
        }

        // Extract SIDEBAR — stop at ===SECTION:xxx=== or ===END===
        if (preg_match('/===\s*SIDEBAR\s*===\s*\n(.*?)(?=\n===\s*(?:SECTION|END)[:\s=])/s', $text, $m)) {
            $result['SIDEBAR'] = trim($m[1]);
        }

        // Extract each section: ===SECTION:id===
        foreach ($sectionIds as $id) {
            $safeId = preg_quote($id, '/');
            // Match ===SECTION:id=== ... up to next ===SECTION: or ===END=== or end of string
            if (preg_match('/===\s*SECTION\s*:\s*' . $safeId . '\s*===\s*\n(.*?)(?=\n===\s*(?:SECTION|END)[:\s=]|\z)/s', $text, $m)) {
                $code = trim($m[1]);
                if ($code) $result['sections'][$id] = $code;
            }
        }

        // Fallback: if no sections parsed but we have ===HOME===, treat as monolithic
        if (empty($result['sections']) && preg_match('/===\s*HOME\s*===\s*\n(.*?)(?=\n===\s*END\s*===|\z)/s', $text, $m)) {
            $result['sections']['_monolithic'] = trim($m[1]);
        }

        return $result;
    }

    /* ═══════════════════════════════════════════════════════
       Step 3: CSS Generation — Knowledge-base driven
       ═══════════════════════════════════════════════════════ */
    private function step3_cssGeneration(array $brief, array $html): string
    {
        $briefJson = json_encode($brief, JSON_PRETTY_PRINT);
        // Concatenate all HTML (header + sections + footer) for CSS reference
        $allSectionsHtml = '';
        if (!empty($html['sections'])) {
            $allSectionsHtml = implode("\n", $html['sections']);
        } elseif (!empty($html['home_html'])) {
            $allSectionsHtml = $html['home_html']; // legacy monolithic fallback
        }
        $rawHtml = ($html['header_html'] ?? '') . "\n" . $allSectionsHtml . "\n" . ($html['sidebar_html'] ?? '') . "\n" . ($html['footer_html'] ?? '');
        // Extract unique CSS classes from HTML for explicit class list
        preg_match_all('/class="([^"]*)"/', $rawHtml, $classMatches);
        $allClasses = [];
        foreach ($classMatches[1] as $classStr) {
            foreach (preg_split('/\s+/', $classStr) as $cls) {
                $cls = trim($cls);
                if ($cls && !str_starts_with($cls, '<?')) $allClasses[$cls] = true;
            }
        }
        // Add layout.php fixed classes that AI-generated CSS must also cover
        $layoutClasses = [
            // Page templates (page.php, article.php, articles.php)
            'container', 'page-hero', 'page-hero-overlay', 'page-hero-title', 'page-breadcrumb',
            'breadcrumb-sep', 'page-content-section', 'container-narrow', 'prose',
            // Article templates
            'article-meta', 'article-category', 'article-date', 'article-author',
            'article-featured-img', 'article-body',
            // Articles list
            'articles-grid', 'article-card', 'article-card-img', 'article-card-body',
            'article-card-title', 'article-card-excerpt', 'article-card-meta',
        ];
        // NOTE: Footer classes are no longer hardcoded — AI generates unique footer HTML+CSS per theme
        foreach ($layoutClasses as $cls) {
            $allClasses[$cls] = true;
        }
        // Add header pattern classes (including nav-list/nav-link which are inside PHP render_menu() calls)
        if (!empty($this->headerPatternResult['classes'])) {
            foreach ($this->headerPatternResult['classes'] as $cls) {
                $allClasses[$cls] = true;
            }
        }
        // Add footer pattern classes (including nav-list/nav-link which are inside PHP render_menu() calls)
        if (!empty($this->footerPatternResult['classes'])) {
            foreach ($this->footerPatternResult['classes'] as $cls) {
                $allClasses[$cls] = true;
            }
        }
        // Add hero pattern classes
        if (!empty($this->heroPatternResult['classes'])) {
            foreach ($this->heroPatternResult['classes'] as $cls) {
                $allClasses[$cls] = true;
            }
        }
        // Add section pattern classes (features, about, testimonials, etc.)
        foreach ($this->sectionPatternResults as $sid => $result) {
            foreach ($result['classes'] ?? [] as $cls) {
                $allClasses[$cls] = true;
            }
        }
        // Also extract classes from PHP string arguments: ['class' => 'xxx', 'link_class' => 'xxx']
        if (preg_match_all("/(?:'class'|'link_class'|'menu_class')\s*=>\s*'([^']+)'/", $rawHtml, $phpClassMatches)) {
            foreach ($phpClassMatches[1] as $cls) {
                foreach (preg_split('/\s+/', $cls) as $c) {
                    if ($c) $allClasses[$c] = true;
                }
            }
        }
        $classList = implode(', ', array_map(fn($c) => '.' . $c, array_keys($allClasses)));
        // Clean HTML: keep structure tags, strip PHP
        $cleanHtml = strip_tags(
            $rawHtml,
            '<header><footer><section><div><nav><a><button><span><h1><h2><h3><h4><p><ul><li><img><i><form><input><blockquote><cite><main><article>'
        );

        // Load CSS requirements + quality standards from KB
        $kbCss = $this->getKB('11', '14', '17');

        // Strip PHP tags from HTML to reduce noise but KEEP all HTML attributes (especially class)
        $htmlForCss = preg_replace('/<\?php.*?\?>/s', '', $rawHtml);
        $htmlForCss = preg_replace('/\s+/', ' ', $htmlForCss); // compact whitespace
        // Truncate if too long (keep under 12k chars to leave room for CSS output)
        if (strlen($htmlForCss) > 12000) {
            $htmlForCss = substr($htmlForCss, 0, 12000) . "\n<!-- truncated -->";
        }

        $systemPrompt = <<<PROMPT
You are a senior CSS developer. Generate a COMPLETE, production-grade stylesheet.

DESIGN BRIEF:
{$briefJson}

FULL HTML STRUCTURE (with all class names preserved — PHP tags removed):
{$htmlForCss}

STYLE: {$this->style} | MOOD: {$this->mood}

CSS REQUIREMENTS — follow ALL rules from this reference:
{$kbCss}

MANDATORY CLASS LIST — you MUST write CSS rules for EVERY class below.
These are the EXACT classes used in the HTML. Do NOT rename them or invent alternatives:
{$classList}

Return ONLY CSS code. No markdown, no backticks, no explanation.

CRITICAL RULES:
- Use ONLY the class names from the HTML above — do NOT invent new class names
- EVERY class in the MANDATORY CLASS LIST must have CSS rules
- If HTML has .projects-mosaic, write CSS for .projects-mosaic (NOT .projects-grid)
- If HTML has .project-tile, write CSS for .project-tile (NOT .project-card)
- Zero class name mismatches between HTML and CSS

QUALITY RULES (PREMIUM 2025 STANDARDS):

⚠️ CRITICAL: ALL homepage sections (header, footer, hero, and content sections) use PRE-BUILT HTML patterns.
Their structural CSS (position, display, flex, grid, width, height, max-width, min-height, padding, margin, overflow, z-index, order, gap, align-items, justify-content, flex-direction, flex-wrap, text-align, grid-template-columns, inset) is INJECTED SEPARATELY at the end of style.css.
You MUST write ONLY decorative CSS (colors, fonts, backgrounds, borders, border-radius, box-shadow, opacity, transitions, transforms on hover, text-decoration, letter-spacing, line-height, font-size, font-weight, font-family, cursor, filter, backdrop-filter, text-shadow, animation, outline) for these sections.
If you write conflicting structural CSS, it will break the layout.

HEADER (DECORATIVE CSS ONLY — structural layout CSS is pre-built and injected separately):
The header HTML uses pattern "{$this->headerPatternResult['pattern_id']}".
⚠️ EXACT header CSS class names you MUST use (do NOT invent alternatives):
{$this->getHeaderClassList()}
⚠️ DO NOT write structural properties for header elements — they are handled by injected structural CSS.
⚠️ DO write decorative CSS for ALL header classes listed above:
- Background: var(--surface), gradient, or transparent with backdrop-filter
- .header-scrolled state: background change + box-shadow + optional border-bottom
- Brand text: color, font-family, font-weight, letter-spacing
- Nav links: color var(--text-muted), hover color var(--primary), ::after underline animation
- CTA button: background var(--primary), color var(--primary-contrast), border-radius (pill or rounded), hover effects (translateY, box-shadow glow)
- Burger spans: background-color currentColor
- Mobile nav (body.nav-open): background var(--surface), nav link colors, close button styling
  ⚠️ Use EXACTLY `body.nav-open` — NOT `body.menu-open` or any other class name!
- Body scroll lock: `body.nav-open { overflow: hidden; }` (use nav-open, NOT menu-open)
- Topbar (if present): subtle background, border-bottom, text colors
- Transitions and hover effects on all interactive elements
{$this->getHeaderDecorativeGuideBlock()}

FOOTER (DECORATIVE CSS ONLY — structural layout CSS is pre-built and injected separately):
The footer HTML uses pattern "{$this->footerPatternResult['pattern_id']}".
⚠️ EXACT footer CSS class names you MUST use (do NOT invent alternatives):
{$this->getFooterClassList()}
⚠️ DO NOT write structural properties for footer elements — they are handled by injected structural CSS.
⚠️ DO write decorative CSS for ALL footer classes listed above:
- Footer background: var(--surface-elevated) or var(--surface-card), subtle gradient
- Footer brand text: color, font-family, font-weight
- Footer links: color var(--text-muted), hover color var(--primary), underline on hover
- Footer social icons: background, color, border-radius, hover effects (transform, background)
- Footer titles: color, font-weight, text-transform, letter-spacing
- Footer contact info: color var(--text-muted), icon colors
- Footer copyright: opacity, font-size
- Newsletter form: input border, background, button styling
- Transitions and hover effects on all interactive elements
{$this->getFooterDecorativeGuideBlock()}

{$this->buildSectionCssGuide()}

TYPOGRAPHY:
- Use clamp() for responsive sizes — NO fixed px for headings
- Body: 16-17px, line-height 1.7-1.75
- Section titles: clamp(2rem, 4vw, 3.5rem)
- Hero title: clamp(2.75rem, 6vw, 5rem)
- Letter-spacing: -0.02em on large headings, 0.05-0.1em on labels/badges

BUTTONS:
- .btn-primary: padding 14px 32px, border-radius 100px (pill) OR var(--radius), primary bg
- .btn-outline: same padding, transparent bg, primary border, primary text
- Hover: translateY(-2px) + box-shadow

LAYOUT ESSENTIALS (CRITICAL — sub-pages depend on these):
- .container: max-width: var(--container-width, 1280px); margin: 0 auto; padding: 0 clamp(16px, 3vw, 40px); width: 100%
- .container-narrow: max-width: 800px (same padding/margin)
- .prose: line-height 1.8, good paragraph spacing, styled headings/lists/links inside .prose
  - .prose h2: margin-top 2em, font-family var(--heading-font)
  - .prose h3: margin-top 1.5em
  - .prose ul, .prose ol: padding-left 1.5em, margin-bottom 1em
  - .prose a: color var(--primary), text-decoration underline, hover opacity 0.8
  - .prose blockquote: border-left 4px solid var(--primary), padding-left 1.5em, font-style italic
  - .prose img: max-width 100%, border-radius var(--border-radius), margin 1.5em 0
- Sub-page content uses .container inside every <section> — it MUST be defined in CSS

SUB-PAGE TEMPLATE STYLES (CRITICAL — these classes are used by ALL generated sub-pages):
- Breadcrumb: styled nav with separator chevrons, var(--text-muted) links, current page bold
- Page hero (sub-page): 40-50vh (NOT 100vh), padding-top calc(var(--header-height, 80px) + 40px), centered text
- Section labels: uppercase, letter-spacing 0.1em, small font, var(--primary) color, margin-bottom 8px
- Section dividers: 40-60px width, 2px height, var(--primary), centered, margin-bottom 16px
- Icon circles: width 56px, height 56px, border-radius 12px, display flex, align-items center, justify-content center
  background: rgba of primary with 0.1 opacity, color var(--primary), font-size 1.5rem
- Timeline: vertical line with position absolute, border-left 2px solid var(--primary), dots at each node

FORM STYLES (for contact/booking pages generated by AI):
- input, textarea, select: width 100%, padding 14px 16px, border 1px solid var(--border), border-radius var(--border-radius)
  font-family var(--body-font), font-size 1rem, background var(--surface), color var(--text)
  transition: border-color var(--transition-speed), box-shadow var(--transition-speed)
- input:focus, textarea:focus, select:focus: border-color var(--primary), outline none, box-shadow 0 0 0 3px rgba(primary, 0.15)
- label: display block, margin-bottom 6px, font-weight 600, font-size 0.875rem
- textarea: min-height 150px, resize vertical
- form .form-group: margin-bottom 20px
- form .form-row: display grid, grid-template-columns 1fr 1fr, gap 20px (stack on mobile)
- button[type=submit]: as .btn-primary, cursor pointer

ANIMATION CLASSES:
- [data-animate]: opacity 0, transform translateY(20px), transition: opacity 0.6s ease, transform 0.6s ease
- [data-animate].animated: opacity 1, transform translateY(0)
- [data-animate="fade-up"]: default above
- [data-animate="fade-in"]: opacity 0 → 1 (no translate)
- [data-animate="scale-in"]: transform scale(0.95) → scale(1)

RESPONSIVE:
- Mobile-first: base styles for mobile, @media (min-width: 768px) for tablet, 1024px for desktop
- Use clamp() over media queries where possible
- Mobile: hamburger visible, nav hidden, footer stacked 1-col, hero padding reduced

PROFESSIONAL FINISH (CRITICAL — these separate amateur from professional):

CONTRAST & READABILITY:
- WCAG AA minimum: text on bg must have 4.5:1 contrast ratio
- Light text on dark: use #e2e8f0 to #f8fafc range (never pure white on dark)
- Dark text on light: use #1a202c to #2d3748 range (never pure black)
- Hero text over images: ALWAYS use overlay gradient + text-shadow: 0 2px 20px rgba(0,0,0,0.3)
- Buttons: primary text must contrast with primary bg (use white on saturated colors, dark on light pastels)
- Ghost/outline buttons: border 2px solid, text same as border, hover fills bg

SPACING & RHYTHM:
- Section padding: 100px to 160px vertical — NEVER less than 80px
- Card grid gap: 24-32px minimum
- Heading margin-bottom: 16-24px, paragraph margin-bottom: 20-28px
- Hero content: add generous padding (60px+ top/bottom within content area)
- Footer sections: 60-80px padding, not cramped

DEPTH & POLISH:
- Cards: box-shadow 0 4px 24px rgba(0,0,0,0.06) resting, 0 12px 48px rgba(0,0,0,0.12) on hover
- Subtle gradient backgrounds on alternating sections (not flat solid colors)
- Borders: use rgba() with low opacity (0.08-0.15), not hard solid lines
- Smooth transitions: all interactive elements need transition: all 0.3s cubic-bezier(0.4,0,0.2,1)
- Focus styles: outline 2px solid primary, outline-offset 2px (accessibility)

ANTI-PATTERNS (NEVER DO):
- Never use default blue (#0000ff) or link blue (#0066cc) — use theme primary
- Never leave unstyled scrollbars on custom containers
- Never use fixed px for font-size on body/headings — ALWAYS use clamp()
- Never have hero shorter than 100vh
- Never have white/bright text directly on an image without overlay
- Never use generic border-radius: 4px everywhere — vary by element (buttons: pill or 8px, cards: 12-16px, images: 8-12px)

HEADER OVERFLOW PROTECTION (MANDATORY):
- Header nav links: font-size max 0.875rem, padding max 0 14px
- Nav container: flex-shrink:1, overflow:hidden to prevent overlap with brand
- If brand is centered: nav containers get max-width:calc(50% - 80px)
- Mobile (<768px): nav links display:none, only hamburger+brand+CTA visible
- .articles-grid, .article-card, .gallery-layout-grid, .gallery-item: include fallback styles in theme CSS
- .page-hero: must have padding, background, text-align:center (used by articles/gallery/page templates)
- .container: max-width var(--container-width, 1200px), margin 0 auto, padding 0 20px

E-COMMERCE SHOP STYLES (INCLUDE for ALL industries — shop pages use these classes):
/* Shop Listing Page */
.shop-page { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
.shop-header { margin-bottom: 30px; }
.shop-title { font-size: clamp(1.75rem, 3vw, 2.5rem); font-family: var(--font-heading); }
.shop-description { color: var(--text-muted); }
.shop-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
.shop-filter-btn { padding: 6px 16px; border-radius: 20px; border: 1px solid var(--border, #e2e8f0); text-decoration: none; font-size: .85rem; background: var(--surface, #f1f5f9); color: var(--text-muted, #333); transition: all .2s; }
.shop-filter-btn:hover { border-color: var(--primary); color: var(--primary); }
.shop-filter-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; }
.product-card { border-radius: var(--radius-lg, 12px); overflow: hidden; border: 1px solid var(--border, #e2e8f0); background: var(--surface-card, #fff); transition: transform 0.2s, box-shadow 0.2s; }
.product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
.product-card-link { text-decoration: none; color: inherit; display: block; }
.product-card-image img { width: 100%; height: 200px; object-fit: cover; }
.product-card-placeholder { width: 100%; height: 200px; background: var(--surface, #f1f5f9); display: flex; align-items: center; justify-content: center; font-size: 3rem; }
.product-card-body { padding: 16px; }
.product-card-title { font-size: 1rem; font-weight: 600; margin: 0 0 8px; }
.product-card-description { font-size: .85rem; color: var(--text-muted); line-height: 1.4; margin: 0 0 12px; }
.product-card-price { font-weight: 700; color: var(--primary); }
.product-card-price .original { text-decoration: line-through; color: var(--text-muted); font-weight: 400; font-size: .85rem; }
.product-card-price .sale { color: var(--primary); }
.product-card-rating { margin-bottom: 8px; font-size: .8rem; display: flex; align-items: center; gap: 4px; }
.product-card-rating .stars { color: #f59e0b; }
.product-card-rating .count { color: var(--text-muted); }
.shop-wish-btn { position: absolute; top: 8px; right: 8px; z-index: 2; background: rgba(255,255,255,.85); border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.shop-pagination { display: flex; justify-content: center; gap: 8px; margin-top: 40px; }
.shop-pagination-link { padding: 8px 14px; border-radius: var(--radius, 6px); text-decoration: none; background: var(--surface, #f1f5f9); color: var(--text); }
.shop-pagination-link.active { background: var(--primary); color: #fff; }
/* Single Product Page */
.product-page { max-width: 1000px; margin: 0 auto; padding: 40px 20px; }
.product-back-link { color: var(--primary); text-decoration: none; font-size: .85rem; }
.product-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 10px; }
.product-gallery img, .product-image { width: 100%; border-radius: var(--radius-lg, 12px); }
.product-title { font-size: clamp(1.5rem, 3vw, 2rem); font-family: var(--font-heading); }
.product-price { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
.product-price .original { text-decoration: line-through; color: var(--text-muted); font-size: 1rem; font-weight: 400; }
.product-stock.in-stock { color: var(--success, #10b981); }
.product-stock.out-of-stock { color: var(--danger, #ef4444); }
.add-to-cart-btn { flex: 1; padding: 12px 24px; background: var(--primary); color: #fff; border: none; border-radius: var(--radius, 8px); font-size: 1rem; font-weight: 600; cursor: pointer; transition: all .2s; }
.add-to-cart-btn:hover { opacity: .9; transform: translateY(-1px); }
.product-wishlist-btn { padding: 12px 16px; border: 1px solid var(--border); border-radius: var(--radius, 8px); cursor: pointer; font-size: 1.2rem; background: var(--surface-card, #fff); transition: all .2s; }
.product-wishlist-btn.active { background: #fee2e2; }
.variant-swatches { margin-bottom: 20px; }
.variant-group { margin-bottom: 12px; }
.variant-group-label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: 6px; }
.variant-swatch { padding: 8px 16px; border: 2px solid var(--border, #e2e8f0); border-radius: var(--radius, 8px); background: var(--surface-card, #fff); cursor: pointer; transition: all .2s; }
.variant-swatch.active { border-color: var(--primary); background: var(--primary); color: #fff; }
.product-actions { display: flex; gap: 12px; align-items: center; margin-bottom: 30px; }
/* Reviews */
.reviews-section { margin-top: 60px; border-top: 2px solid var(--border, #e2e8f0); padding-top: 40px; }
.review-summary { display: grid; grid-template-columns: 200px 1fr; gap: 30px; margin-bottom: 30px; }
.review-stars { color: #f59e0b; }
.review-distribution { display: flex; flex-direction: column; gap: 6px; }
.review-bar { display: flex; align-items: center; gap: 8px; }
.review-bar-track { flex: 1; height: 10px; background: var(--border, #e2e8f0); border-radius: 5px; overflow: hidden; }
.review-bar-fill { height: 100%; background: #f59e0b; border-radius: 5px; }
.review-item { padding: 20px; background: var(--surface, #f8fafc); border-radius: var(--radius-lg, 12px); border: 1px solid var(--border); }
.review-form { padding: 24px; background: var(--surface, #f8fafc); border-radius: var(--radius-lg, 12px); border: 1px solid var(--border); margin-top: 20px; }
.star-picker span { cursor: pointer; font-size: 1.5rem; }
/* Cart */
.cart-page { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
.cart-table { border: 1px solid var(--border); border-radius: var(--radius-lg, 12px); overflow: hidden; margin-bottom: 30px; }
.cart-table table { width: 100%; border-collapse: collapse; }
.cart-item td { padding: 12px 16px; border-top: 1px solid var(--border); }
.cart-item-image { width: 50px; height: 50px; border-radius: 6px; object-fit: cover; }
.cart-summary { background: var(--surface, #f8fafc); border-radius: var(--radius-lg, 12px); padding: 24px; border: 1px solid var(--border); }
.cart-empty { text-align: center; padding: 60px 0; }
/* Checkout */
.checkout-page { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
.checkout-layout { display: grid; grid-template-columns: 1fr 340px; gap: 30px; align-items: start; }
.checkout-section { background: var(--surface-card, #fff); border: 1px solid var(--border); border-radius: var(--radius-lg, 12px); padding: 24px; margin-bottom: 20px; }
.checkout-section-title { font-size: 1.1rem; margin: 0 0 20px; font-family: var(--font-heading); }
.checkout-field input, .checkout-field select, .checkout-field textarea { width: 100%; padding: 10px 14px; border: 1px solid var(--border, #e2e8f0); border-radius: var(--radius, 8px); font-size: .9rem; box-sizing: border-box; }
.checkout-btn { width: 100%; padding: 14px; font-size: 1.1rem; background: var(--primary); color: #fff; border: none; border-radius: var(--radius, 8px); font-weight: 600; cursor: pointer; }
.checkout-summary { background: var(--surface, #f8fafc); border: 1px solid var(--border); border-radius: var(--radius-lg, 12px); padding: 24px; }
/* Thank You */
.thankyou-page { max-width: 700px; margin: 0 auto; padding: 60px 20px; text-align: center; }
.thankyou-icon { font-size: 4rem; margin-bottom: 16px; }
.thankyou-title { font-size: 2rem; font-family: var(--font-heading); }
.order-summary { background: var(--surface, #f8fafc); border: 1px solid var(--border); border-radius: var(--radius-lg, 12px); padding: 24px; text-align: left; }
.download-links { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-lg, 12px); padding: 24px; text-align: left; }
/* Responsive e-commerce */
@media (max-width: 768px) {
  .product-layout { grid-template-columns: 1fr; }
  .checkout-layout { grid-template-columns: 1fr; }
  .review-summary { grid-template-columns: 1fr; }
}

DARK MODE (MANDATORY — include after all other CSS):
Generate a dark mode override that adapts YOUR color palette to dark backgrounds.
Do NOT hardcode — derive dark values from the theme's actual colors.

@media (prefers-color-scheme: dark) {
  :root {
    /* Generate dark versions of bg, surface, text based on the theme's palette */
    /* Keep --primary, --secondary, --accent the same — they already have good contrast */
    /* Invert bg/text: light bg → dark bg (#1a1a2e-#1e1e30 range), dark text → light text (#e4e4e7 range) */
    /* Darken surfaces: use slightly lighter than bg (#2a2a3e range) */
    /* Adjust --text-muted to ~#a1a1aa range */
    /* Set --border to rgba(255,255,255,0.1) */
    /* Recalculate -rgb variables to match new hex values */
  }
}
[data-theme="dark"] {
  /* Same dark overrides as above — for manual toggle support */
}

MINIMUM 800 lines of production-ready CSS.
Create a UNIQUE visual identity matching {$this->style} + {$this->mood}
PROMPT;

        $result = $this->aiQuery("Generate the complete CSS", $this->queryOptions([
            'system_prompt' => $systemPrompt,
            'max_tokens' => 16000,
            'temperature' => $this->getCreativityTemp('css'),
        ]));

        if (empty($result['ok']) || empty($result['text'])) {
            throw new \RuntimeException('Step 3 failed: ' . ($result['error'] ?? 'No CSS generated'));
        }

        $css = $result['text'];
        $css = preg_replace('/^```(?:css)?\s*/m', '', $css);
        $css = preg_replace('/```\s*$/m', '', $css);
        $css = trim($css);

        if (strlen($css) < 500) {
            throw new \RuntimeException('Step 3 failed: CSS too short (' . strlen($css) . ' chars)');
        }

        // Validate required CSS selectors
        $this->validateCss($css);

        return $css;
    }

    /**
     * Repair broken CSS from AI output — fix unclosed brackets, parens, missing semicolons.
     * AI models (especially budget ones like DeepSeek) often truncate output mid-declaration.
     * This ensures structural CSS appended AFTER AI CSS is always parsed correctly.
     */
    private function repairBrokenCss(string $css): string
    {
        $openBraces = substr_count($css, '{');
        $closeBraces = substr_count($css, '}');
        
        // Find the last complete rule (ends with })
        $lastCloseBrace = strrpos($css, '}');
        if ($lastCloseBrace !== false && $openBraces > $closeBraces) {
            $afterLastClose = substr($css, $lastCloseBrace + 1);
            $afterTrimmed = trim($afterLastClose);
            
            if (!empty($afterTrimmed)) {
                // Fix unclosed parentheses in the trailing content
                $openParens = substr_count($afterTrimmed, '(');
                $closeParens = substr_count($afterTrimmed, ')');
                $parenFix = str_repeat(')', max(0, $openParens - $closeParens));
                
                if (!str_ends_with($afterTrimmed, ';') && !str_ends_with($afterTrimmed, '}')) {
                    $css .= $parenFix . ";\n";
                }
                
                $remaining = $openBraces - $closeBraces;
                if ($remaining > 0 && !str_ends_with(rtrim($css), '}')) {
                    $css .= str_repeat("}\n", $remaining);
                }
            }
        }
        
        // Re-count after fix
        $openBraces = substr_count($css, '{');
        $closeBraces = substr_count($css, '}');
        if ($openBraces > $closeBraces) {
            $css .= str_repeat("}\n", $openBraces - $closeBraces);
        }
        
        // Fix common AI truncation: rgba(var(--xxx-rgb), followed by newline instead of value
        $css = preg_replace(
            '/rgba\\(var\\(--[a-z-]+\\),\\s*\\n/',
            'rgba(var(--primary-rgb), 0.15);\n',
            $css
        );
        
        // Fix Font Awesome version mismatch: AI often outputs FA 5/6 families but CMS bundles FA 7
        $css = str_replace(
            ['Font Awesome 5 Free', 'Font Awesome 5 Brands', 'Font Awesome 6 Free', 'Font Awesome 6 Brands'],
            ['Font Awesome 7 Free', 'Font Awesome 7 Brands', 'Font Awesome 7 Free', 'Font Awesome 7 Brands'],
            $css
        );
        
        return $css;
    }

    /**
     * Strip structural CSS properties from AI-generated rules targeting header/footer elements.
     * The pattern system provides structural CSS (position, display, flex, height, overflow, etc.)
     * appended at the END of style.css. AI models (especially budget ones like DeepSeek) often
     * ignore prompt instructions and generate conflicting structural properties. This function
     * removes those properties from the AI CSS so the pattern structural CSS takes effect cleanly.
     */
    private function stripStructuralFromAiCss(string $css, string $slug): string
    {
        // Build prefix from slug (e.g. "velvet-crumb-bakery" → "vcb")
        $parts = explode('-', $slug);
        $prefix = '';
        foreach ($parts as $part) {
            if (!empty($part)) $prefix .= $part[0];
        }

        // Structural properties that ONLY the pattern system should set on header elements
        $structuralProps = [
            'position', 'display', 'flex-direction', 'flex-wrap', 'flex',
            'align-items', 'justify-content', 'width', 'max-width', 'min-width',
            'height', 'min-height', 'max-height', 'overflow', 'overflow-x', 'overflow-y',
            'z-index', 'order', 'gap', 'top', 'left', 'right', 'bottom',
            'padding', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right',
            'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
        ];

        // Header selectors to sanitize (using the theme prefix)
        $headerSelectors = [
            ".{$prefix}-header",
            ".{$prefix}-header-inner",
            ".{$prefix}-header--",  // any modifier like --brand-center
        ];

        // Footer selectors to sanitize
        $footerSelectors = [
            ".{$prefix}-footer",
            ".{$prefix}-footer-inner",
            ".{$prefix}-footer--",
        ];

        // Hero selectors to sanitize
        $heroSelectors = [
            ".{$prefix}-hero",
            ".{$prefix}-hero--",
            ".{$prefix}-hero-grid",
            ".{$prefix}-hero-bg",
            ".{$prefix}-hero-overlay",
            ".{$prefix}-hero-content",
            ".{$prefix}-hero-stats",
            ".{$prefix}-hero-visual",
            ".{$prefix}-hero-scroll",
            ".{$prefix}-hero-wave",
            ".{$prefix}-hero-gradient",
        ];

        // Section selectors to sanitize (all pattern-generated sections)
        $sectionSelectors = [];
        $sectionPrefixes = ['features', 'about', 'testimonials', 'pricing', 'cta', 'faq', 'stats', 'clients', 'gallery', 'team', 'blog', 'contact'];
        foreach ($sectionPrefixes as $secName) {
            $sectionSelectors[] = ".{$prefix}-{$secName}";
            $sectionSelectors[] = ".{$prefix}-{$secName}--";
            $sectionSelectors[] = ".{$prefix}-{$secName}-";
        }

        $allSelectors = array_merge($headerSelectors, $footerSelectors, $heroSelectors, $sectionSelectors);

        // Fully protected selectors — AI must NOT write ANY CSS for these (remove entire rule)
        $fullyProtected = [
            ".{$prefix}-hero-wave",
        ];

        // Process CSS: find rules targeting header/footer and strip structural properties
        $result = preg_replace_callback(
            '/([^{}]+)\{([^{}]+)\}/',
            function ($match) use ($allSelectors, $structuralProps, $fullyProtected) {
                $selector = trim($match[1]);
                $declarations = $match[2];

                // Fully protected: remove ENTIRE rule (wave, etc.)
                foreach ($fullyProtected as $fp) {
                    if (stripos($selector, $fp) !== false) {
                        return ''; // Remove completely
                    }
                }

                // Check if this rule targets a header/footer element
                $isStructural = false;
                foreach ($allSelectors as $sel) {
                    if (stripos($selector, $sel) !== false) {
                        $isStructural = true;
                        break;
                    }
                }

                if (!$isStructural) {
                    return $match[0]; // Leave non-header/footer rules untouched
                }

                // Strip structural properties, keep decorative ones
                $lines = array_filter(array_map('trim', explode(';', $declarations)));
                $kept = [];
                foreach ($lines as $line) {
                    if (empty($line)) continue;
                    $colonPos = strpos($line, ':');
                    if ($colonPos === false) continue;
                    $prop = strtolower(trim(substr($line, 0, $colonPos)));
                    // Remove vendor-prefix for matching
                    $propClean = preg_replace('/^-(?:webkit|moz|ms|o)-/', '', $prop);
                    if (!in_array($propClean, $structuralProps)) {
                        $kept[] = $line;
                    }
                }

                if (empty($kept)) {
                    return ''; // Remove empty rule entirely
                }

                return $selector . " {\n  " . implode(";\n  ", $kept) . ";\n}\n";
            },
            $css
        );

        return $result ?? $css;
    }

    /**
     * Validate CSS contains required selectors. Warns but doesn't fail.
     * Stores missing selectors in $this->steps for reporting.
     */
    private function validateCss(string $css): void
    {
        $required = [
            // Layout
            '.container', '.section', '.section-header', '.section-title',
            // Buttons
            '.btn', '.btn-primary', '.btn-outline',
            // Header
            '.site-header', '.header-scrolled', '.header-container', '.header-logo', '.header-nav',
            '.nav-links', '.nav-link', '.header-cta', '.mobile-toggle',
            '.nav-open',
            // Hero
            '.hero', '.hero-bg', '.hero-overlay', '.hero-content',
            // Footer (pattern-generated classes)
            // Note: Footer pattern classes added dynamically below
            // Page templates
            '.page-hero', '.page-content-section', '.prose',
            // Articles
            '.article-card', '.article-card-img',
            // Gallery
            '.gallery-section',
            // 404
            '.error-section', '.error-code',
            // Animations
            '[data-animate]', '.animated',
            // Responsive
            '@media',
        ];

        // Add footer pattern classes to required list
        if (!empty($this->footerPatternResult['classes'])) {
            foreach ($this->footerPatternResult['classes'] as $class) {
                $required[] = '.' . $class;
            }
        }

        $missing = [];
        foreach ($required as $sel) {
            // Check if selector exists (loosely — could be part of compound selector)
            $escaped = preg_quote($sel, '/');
            if (!preg_match('/' . $escaped . '/i', $css)) {
                $missing[] = $sel;
            }
        }

        $this->steps['css']['missing_selectors'] = $missing;
        $this->steps['css']['selector_coverage'] = round((1 - count($missing) / count($required)) * 100);

        // E-commerce selectors — check separately, don't affect coverage %
        $ecomRequired = [
            '.product-grid', '.product-card', '.product-card-body', '.product-card-price',
            '.product-layout', '.product-title', '.product-price',
            '.add-to-cart-btn', '.cart-page', '.checkout-page',
            '.shop-page', '.shop-filters', '.shop-filter-btn',
        ];
        $ecomMissing = [];
        foreach ($ecomRequired as $sel) {
            $escaped = preg_quote($sel, '/');
            if (!preg_match('/' . $escaped . '/i', $css)) {
                $ecomMissing[] = $sel;
            }
        }
        $this->steps['css']['ecom_missing_selectors'] = $ecomMissing;
        $this->steps['css']['ecom_selector_coverage'] = round((1 - count($ecomMissing) / count($ecomRequired)) * 100);
    }

    /* ═══════════════════════════════════════════════════════
       Step 4: Assembly — Write theme files
       ═══════════════════════════════════════════════════════ */
    private function step4_assembly(array $brief, array $html, string $css): string
    {
        $slug = $this->slug;
        $themeDir = CMS_ROOT . '/themes/' . $slug;
        @file_put_contents('/tmp/aitb-assembly.log', date('H:i:s') . " step4_assembly START slug={$slug} themeDir={$themeDir}\n", FILE_APPEND);
        @file_put_contents('/tmp/aitb-assembly.log', date('H:i:s') . " sections=" . implode(',', array_keys($html['sections'] ?? [])) . " css_len=" . strlen($css) . "\n", FILE_APPEND);

        // Check for existing non-AI theme (don't overwrite user themes)
        if (is_dir($themeDir) && file_exists($themeDir . '/theme.json')) {
            $existing = @json_decode(file_get_contents($themeDir . '/theme.json'), true);
            if ($existing && ($existing['author'] ?? '') !== 'AI Theme Builder') {
                throw new \RuntimeException("Theme '{$slug}' already exists and is not AI-generated. Choose a different name.");
            }
        }

        // Create directories
        foreach (['', '/sections', '/templates', '/assets/css', '/assets/js', '/content'] as $dir) {
            $path = $themeDir . $dir;
            if (!is_dir($path)) mkdir($path, 0755, true);
        }

        // Store the design brief in content/ so the directory is not empty
        file_put_contents($themeDir . '/content/brief.json', json_encode($brief, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Google Fonts link
        $fontsUrl = $brief['google_fonts_url'] ?? '';
        $fontsLink = '';
        if ($fontsUrl) {
            $fontsLink = '<link rel="preconnect" href="https://fonts.googleapis.com">'
                . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
                . '<link href="' . htmlspecialchars($fontsUrl, ENT_QUOTES) . '" rel="stylesheet">';
        }

        // 1. theme.json — with homepage_sections
        $themeJson = [
            'name' => $brief['name'] ?? ucfirst($slug),
            'description' => $brief['description'] ?? 'AI-generated theme',
            'version' => '1.0.0',
            'author' => 'AI Theme Builder',
            'supports' => [
                'theme-builder' => true,
                'custom-header' => true,
                'custom-footer' => true,
                'custom-colors' => true,
            ],
            'options' => [
                'show_header' => true,
                'show_footer' => true,
                'body_background' => $brief['colors']['background'] ?? '#ffffff',
                'preload_fonts' => true,
            ],
            'colors' => $brief['colors'] ?? [],
            'typography' => $brief['typography'] ?? [],
            'buttons' => $brief['buttons'] ?? [],
            'layout' => $brief['layout'] ?? [],
            'homepage_sections' => $brief['homepage_sections'] ?? [
                ['id' => 'hero', 'label' => 'Hero', 'icon' => '⭐', 'required' => true],
                ['id' => 'about', 'label' => 'About', 'icon' => '📖'],
                ['id' => 'pages', 'label' => 'Pages', 'icon' => '📋'],
                ['id' => 'articles', 'label' => 'Articles', 'icon' => '📰'],
                ['id' => 'cta', 'label' => 'CTA', 'icon' => '🎯'],
            ],
        ];
        file_put_contents($themeDir . '/theme.json', json_encode($themeJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 2. layout.php
        $footerHtml = $html['footer_html'] ?? '';  // Always provided by FooterPatternRegistry
        file_put_contents(
            $themeDir . '/layout.php',
            ai_theme_build_layout($fontsLink, $html['header_html'] ?? '', $footerHtml)
        );

        // 3. sections/ — individual section files
        if (!is_dir($themeDir . '/sections')) mkdir($themeDir . '/sections', 0755, true);
        $sectionIds = array_column($brief['homepage_sections'] ?? [], 'id');

        if (!empty($html['sections']) && !isset($html['sections']['_monolithic'])) {
            // Write each section as its own file
            foreach ($html['sections'] as $sectionId => $sectionCode) {
                // Ensure section starts with <?php if it has theme_get calls
                $code = $sectionCode;
                if (!str_starts_with(trim($code), '<?php') && str_contains($code, 'theme_get')) {
                    $code = "<?php\n// Section: {$sectionId}\n?>\n" . $code;
                }
                file_put_contents($themeDir . '/sections/' . $sectionId . '.php', $code);
            }
        } else {
            // Monolithic fallback: write as single file, split by <section markers if possible
            $mono = $html['sections']['_monolithic'] ?? $html['home_html'] ?? '';
            // Try to split by <section ... id="sectionId"> patterns
            $split = $this->splitMonolithicSections($mono, $sectionIds);
            if (count($split) > 1) {
                foreach ($split as $sid => $scode) {
                    file_put_contents($themeDir . '/sections/' . $sid . '.php', $scode);
                }
            } else {
                // Can't split — write as hero.php (entire home content)
                file_put_contents($themeDir . '/sections/hero.php', $mono);
            }
        }

        // 3b. Trim trailing junk from section files
        // AI sometimes appends extra closing tags after </section>.
        // We trim everything after the LAST </section> tag.
        foreach (glob($themeDir . '/sections/*.php') as $sectionFile) {
            $code = file_get_contents($sectionFile);

            // Find the position of the last </section> tag
            $lastSectionClose = strrpos($code, '</section>');
            if ($lastSectionClose !== false) {
                $trimmed = substr($code, 0, $lastSectionClose + strlen('</section>')) . "\n";
                if ($trimmed !== $code) {
                    file_put_contents($sectionFile, $trimmed);
                }
            }
        }

        // 3b2. Normalize prefixed container classes to .container / .container-narrow
        // AI sometimes prefixes container class (e.g. "lsa-container") but CSS only defines ".container"
        foreach (glob($themeDir . '/sections/*.php') as $sectionFile) {
            $code = file_get_contents($sectionFile);
            // Replace {prefix}-container-narrow → container-narrow (must check before -container)
            $fixed = preg_replace('/(?<=["\s])([a-z]+-container-narrow)(?=["\s])/i', 'container-narrow', $code);
            // Replace {prefix}-container → container (standalone class or first in list)
            $fixed = preg_replace('/(?<=["\s])([a-z]+-container)(?=["\s])/i', 'container', $fixed);
            if ($fixed !== $code) {
                file_put_contents($sectionFile, $fixed);
            }
        }

        // 3c. Validate section PHP syntax (dev only — uses php -l)
        foreach (glob($themeDir . '/sections/*.php') as $sectionFile) {
            $output = [];
            $returnCode = 0;
            @exec('php -l ' . escapeshellarg($sectionFile) . ' 2>&1', $output, $returnCode);
            if ($returnCode !== 0) {
                $this->steps['assembly']['syntax_errors'][] = basename($sectionFile) . ': ' . implode(' ', $output);
            }
        }

        // 3d-pre. Write products-showcase section for e-commerce themes
        $sectionIdsFlat = array_column($brief['homepage_sections'] ?? [], 'id');
        if (in_array('products-showcase', $sectionIdsFlat)) {
            $productsShowcasePath = $themeDir . '/sections/products-showcase.php';
            if (!file_exists($productsShowcasePath)) {
                file_put_contents($productsShowcasePath, $this->getProductsShowcaseTemplate());
            }
        }

        // 3d. Save section content defaults to theme_customizations DB
        // Extract theme_get('section.key', 'default') values from PHP files and persist them
        $this->saveSectionContentToDb($slug, $themeDir);

        // 3e. Fix broken image paths in section files
        // AI sometimes generates fictional paths like /themes/slug/assets/gallery/photo.jpg
        // Replace them with Pexels image URLs or placeholder
        $this->fixSectionImagePaths($themeDir);

        // 4. templates/home.php — Dynamic section loader
        file_put_contents($themeDir . '/templates/home.php', $this->generateHomePHP($sectionIds));

        // 5. templates/page.php
        file_put_contents($themeDir . '/templates/page.php', ai_theme_page_template());

        // 6. templates/article.php
        file_put_contents($themeDir . '/templates/article.php', ai_theme_article_template());

        // 7. templates/articles.php (with AI-generated sidebar)
        file_put_contents($themeDir . '/templates/articles.php', ai_theme_articles_template($html['sidebar_html'] ?? ''));

        // 8. templates/gallery.php
        file_put_contents($themeDir . '/templates/gallery.php', ai_theme_gallery_template());

        // 9. templates/404.php
        file_put_contents($themeDir . '/templates/404.php', ai_theme_404_template());

        // 10. assets/css/style.css — AI decorative CSS + structural header & footer CSS appended
        $css = $this->repairBrokenCss($css); // Fix AI truncation artifacts before appending structural
        $css = $this->stripStructuralFromAiCss($css, $slug); // Remove structural props AI shouldn't set
        $headerStructuralCss = $html['header_structural_css'] ?? ($this->headerPatternResult['structural_css'] ?? '');
        $footerStructuralCss = $html['footer_structural_css'] ?? ($this->footerPatternResult['structural_css'] ?? '');
        $heroStructuralCss = $html['hero_structural_css'] ?? ($this->heroPatternResult['structural_css'] ?? '');
        $sectionStructuralCss = '';
        foreach ($this->sectionPatternResults as $sid => $result) {
            $sectionStructuralCss .= "\n" . ($result['structural_css'] ?? '');
        }
        $finalCss = $css . "\n" . $heroStructuralCss . "\n" . $sectionStructuralCss . "\n" . $headerStructuralCss . "\n" . $footerStructuralCss;

        // ── Wave-fill fix: detect bg color of first section after hero and match wave to it ──
        $heroPatternId = $this->heroPatternResult['pattern_id'] ?? '';
        if ($heroPatternId === 'gradient-wave') {
            // Find first section after hero to determine its background
            $sections = $brief['homepage_sections'] ?? [];
            $firstAfterHero = null;
            $foundHero = false;
            foreach ($sections as $sec) {
                if (($sec['id'] ?? '') === 'hero') { $foundHero = true; continue; }
                if ($foundHero) { $firstAfterHero = $sec['id'] ?? ''; break; }
            }
            if ($firstAfterHero) {
                // Scan AI CSS for the first section's background
                $prefix = \HeaderPatternRegistry::generatePrefix($brief['name'] ?? $slug);
                $nextSectionClass = ".{$prefix}-{$firstAfterHero}";
                // Check if AI set a background on the next section
                if (preg_match('/\\' . preg_quote($nextSectionClass, '/') . '\s*\{[^}]*background\s*:\s*([^;]+)/i', $finalCss, $bgMatch)) {
                    $bgValue = trim($bgMatch[1]);
                    // Override wave fill to match next section background
                    $finalCss .= "\n/* Wave fill auto-matched to next section */\n.{$prefix}-hero-wave path { fill: {$bgValue} !important; }\n";
                }
            }
        }

        // 10b. CSS Coverage Validator — find HTML classes with no CSS rules, generate fallback
        $coverageResult = $this->validateAndFixCssCoverage($themeDir, $finalCss, $brief);
        if ($coverageResult['fallback_css']) {
            $finalCss .= "\n" . $coverageResult['fallback_css'];
            $this->steps['assembly']['css_coverage'] = $coverageResult['stats'];
        }

        // 10c. Add section CSS markers for per-section regeneration
        $assemblyPrefix = \HeaderPatternRegistry::generatePrefix($brief['name'] ?? $slug);
        $assemblySectionIds = array_column($brief['homepage_sections'] ?? [], 'id');
        // Add header and footer to marker list
        $allMarkerSections = array_merge(['header'], $assemblySectionIds, ['footer']);
        $finalCss = $this->addSectionCssMarkers($finalCss, $assemblyPrefix, $allMarkerSections);

        // Save readable version as style.dev.css, minified as style.css
        file_put_contents($themeDir . '/assets/css/style.dev.css', $finalCss);
        file_put_contents($themeDir . '/assets/css/style.css', self::minifyCss($finalCss));

        // 11. assets/js/main.js
        file_put_contents($themeDir . '/assets/js/main.js', ai_theme_main_js());

        // 12. Content seeding — demo pages, articles, images from Pexels
        try {
            $this->seedContent($brief, $this->selectedPages);
        } catch (\Throwable $e) {
            // Content seeding is non-critical — don't fail the pipeline
            $this->steps['assembly']['seeding_error'] = $e->getMessage();
        }

        // 13. Fix dead '#' button links → point to real sub-pages
        try {
            $this->seedButtonLinks($slug, $brief);
        } catch (\Throwable $e) {
            $this->steps['assembly']['link_seeding_error'] = $e->getMessage();
        }

        // 14. Generate theme preview thumbnail (SVG)
        $colors = $brief['colors'] ?? [];
        $thumbPrimary = $colors['primary'] ?? '#6366f1';
        $thumbSecondary = $colors['secondary'] ?? '#818cf8';
        $thumbBg = $colors['background'] ?? '#ffffff';
        $thumbText = $colors['text'] ?? '#1a1a2e';
        $thumbName = htmlspecialchars($brief['business_name'] ?? $brief['name'] ?? 'Theme', ENT_XML1);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">
  <rect width="400" height="300" fill="{$thumbBg}"/>
  <rect width="400" height="8" fill="{$thumbPrimary}"/>
  <rect y="8" width="400" height="52" fill="{$thumbBg}" stroke="{$thumbPrimary}" stroke-width="0" opacity="0.95"/>
  <circle cx="30" cy="34" r="12" fill="{$thumbPrimary}"/>
  <rect x="50" y="28" width="80" height="12" rx="2" fill="{$thumbText}" opacity="0.8"/>
  <rect x="260" y="24" width="70" height="20" rx="10" fill="{$thumbPrimary}"/>
  <rect y="60" width="400" height="140" fill="url(#grad)"/>
  <defs>
    <linearGradient id="grad" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$thumbPrimary}"/>
      <stop offset="100%" stop-color="{$thumbSecondary}"/>
    </linearGradient>
  </defs>
  <text x="200" y="120" text-anchor="middle" fill="white" font-size="20" font-weight="bold" font-family="system-ui">{$thumbName}</text>
  <text x="200" y="145" text-anchor="middle" fill="white" font-size="11" opacity="0.8" font-family="system-ui">AI-Generated Theme</text>
  <rect x="155" y="158" width="90" height="26" rx="13" fill="white" opacity="0.9"/>
  <text x="200" y="175" text-anchor="middle" fill="{$thumbPrimary}" font-size="10" font-weight="600" font-family="system-ui">Get Started</text>
  <rect y="200" width="133" height="100" fill="{$thumbBg}" stroke="{$thumbPrimary}" stroke-width="0"/>
  <rect x="133" y="200" width="134" height="100" fill="{$thumbBg}"/>
  <rect x="267" y="200" width="133" height="100" fill="{$thumbBg}"/>
  <rect x="20" y="215" width="93" height="8" rx="2" fill="{$thumbText}" opacity="0.15"/>
  <rect x="20" y="230" width="80" height="5" rx="1" fill="{$thumbText}" opacity="0.08"/>
  <rect x="20" y="240" width="90" height="5" rx="1" fill="{$thumbText}" opacity="0.08"/>
  <rect x="153" y="215" width="93" height="8" rx="2" fill="{$thumbText}" opacity="0.15"/>
  <rect x="153" y="230" width="80" height="5" rx="1" fill="{$thumbText}" opacity="0.08"/>
  <rect x="153" y="240" width="90" height="5" rx="1" fill="{$thumbText}" opacity="0.08"/>
  <rect x="287" y="215" width="93" height="8" rx="2" fill="{$thumbText}" opacity="0.15"/>
  <rect x="287" y="230" width="80" height="5" rx="1" fill="{$thumbText}" opacity="0.08"/>
  <rect x="287" y="240" width="90" height="5" rx="1" fill="{$thumbText}" opacity="0.08"/>
</svg>
SVG;

        file_put_contents($themeDir . '/thumbnail.svg', $svg);

        // Add thumbnail to theme.json
        $themeJsonData = @json_decode(file_get_contents($themeDir . '/theme.json'), true) ?: [];
        $themeJsonData['thumbnail'] = 'thumbnail.svg';
        file_put_contents($themeDir . '/theme.json', json_encode($themeJsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Set ownership
        $this->chownRecursive($themeDir);

        return $slug;
    }

    /**
     * Seed demo content: pages, articles, and Pexels images for the generated theme.
     */
    /**
     * Map industry-specific page slugs to standard page types.
     */
    private static array $slugToStandardType = [
        // Standard types (identity mapping)
        'about' => 'about', 'services' => 'services', 'contact' => 'contact',
        'pricing' => 'pricing', 'portfolio' => 'portfolio', 'team' => 'team',
        'faq' => 'faq', 'testimonials' => 'testimonials', 'gallery' => 'gallery',
        // Food & Hospitality
        'our-menu' => 'services', 'reservations' => 'contact', 'about-us' => 'about',
        // Hotel
        'rooms' => 'services', 'amenities' => 'about',
        // Tech
        'features' => 'services',
        // Law & Finance
        'practice-areas' => 'services', 'our-team' => 'team', 'consultations' => 'contact',
        // Medical
        'patient-info' => 'contact',
        // Construction
        'our-work' => 'portfolio', 'free-quote' => 'contact',
        // Creative
        'process' => 'services',
        // Education
        'courses' => 'services', 'enrollment' => 'contact', 'faculty' => 'team',
        // Fitness
        'classes' => 'services', 'membership' => 'pricing', 'trainers' => 'team',
        // Real Estate
        'listings' => 'services',
        // Events
        'packages' => 'pricing',
        // Travel
        'destinations' => 'services',
        // Ecommerce
        'shop' => 'services', 'returns' => 'contact',
        // Nonprofit
        'our-mission' => 'about', 'get-involved' => 'services',
        // Automotive
        'inventory' => 'portfolio',
        // Content/Publishing
        'archive' => 'services',
        // Spa & Wellness
        'treatments' => 'services',
        // Music/Art
        'events' => 'services',
        // Architecture
        'projects' => 'portfolio', 'approach' => 'services',
        // Tattoo
        'artists' => 'team', 'booking' => 'contact',
        // Directory
        'browse' => 'services', 'submit' => 'contact',
        // Memorial
        'tribute' => 'about', 'memories' => 'services', 'donations' => 'contact',
        // Government
        'news' => 'services',
    ];

    /**
     * Get content for a specific standard page type, using industry-specific content where available.
     */
    private function getPageContentForType(string $industry, string $pageType, string $themeName, string $lang): array
    {
        $allPages = $this->getIndustryPages($industry, $themeName, $lang);

        // Build lookup: standardType => page content (first match wins)
        $byType = [];
        foreach ($allPages as $page) {
            $originalSlug = $page['slug'];
            $standardType = self::$slugToStandardType[$originalSlug] ?? $originalSlug;
            if (!isset($byType[$standardType])) {
                $byType[$standardType] = $page;
            }
        }

        if (isset($byType[$pageType])) {
            $result = $byType[$pageType];
            $result['slug'] = $pageType; // Standardize slug
            return $result;
        }

        return $this->getGenericPageContent($pageType, $themeName);
    }

    /**
     * Get generic page content for any standard page type.
     */
    private function getGenericPageContent(string $pageType, string $themeName): array
    {
        $generic = [
            'about' => ['slug' => 'about', 'title' => 'About', 'excerpt' => "Learn more about {$themeName} and our team.", 'content' => "<p>Welcome to {$themeName}. We're passionate about what we do and committed to delivering excellence.</p><h2>Our Story</h2><p>What started as a simple idea has grown into something we're truly proud of. Every step of our journey has been guided by a commitment to our clients and our craft.</p><h2>Why Choose Us</h2><ul><li>Experienced, dedicated team</li><li>Client-focused approach</li><li>Proven track record</li><li>Transparent communication</li></ul>"],
            'services' => ['slug' => 'services', 'title' => 'Services', 'excerpt' => 'Explore our comprehensive range of professional services.', 'content' => '<p>We offer a comprehensive range of services tailored to your needs. Our approach combines industry expertise with a deep understanding of your unique requirements.</p><h2>Consultation</h2><p>Expert advice to help you achieve your goals.</p><h2>Custom Solutions</h2><p>Every client is different, and we design our solutions accordingly.</p><h2>Ongoing Support</h2><p>Our relationship doesn\'t end at delivery. We provide ongoing support and optimization.</p>'],
            'contact' => ['slug' => 'contact', 'title' => 'Contact', 'excerpt' => 'Get in touch — we\'d love to hear from you.', 'content' => '<p>We\'d love to hear from you. Whether you have a question, need a quote, or want to discuss a project, our team is ready to help.</p><h2>Contact Details</h2><ul><li>Email: hello@example.com</li><li>Phone: (555) 123-4567</li><li>Address: 123 Main Street, Suite 100</li></ul><h2>Office Hours</h2><p>Monday – Friday: 9:00 AM – 5:00 PM</p>'],
            'pricing' => ['slug' => 'pricing', 'title' => 'Pricing', 'excerpt' => 'Simple, transparent pricing for every need.', 'content' => '
<h2>Simple, Transparent Pricing</h2>
<p>Choose a plan that fits your needs. No hidden fees, no surprises — just straightforward value.</p>

<h3>Starter</h3>
<p><strong>$29/month</strong></p>
<ul><li>Core features included</li><li>Up to 5 users</li><li>Email support</li><li>Basic analytics</li><li>1 GB storage</li></ul>
<p>Perfect for individuals and small teams just getting started.</p>

<h3>Professional</h3>
<p><strong>$79/month</strong> — Most Popular</p>
<ul><li>Everything in Starter</li><li>Up to 25 users</li><li>Priority support</li><li>Advanced analytics & reporting</li><li>25 GB storage</li><li>Custom integrations</li></ul>
<p>Ideal for growing businesses that need more power and flexibility.</p>

<h3>Enterprise</h3>
<p><strong>Custom pricing</strong></p>
<ul><li>Everything in Professional</li><li>Unlimited users</li><li>Dedicated account manager</li><li>24/7 phone support</li><li>Unlimited storage</li><li>Custom development</li><li>SLA guarantee</li></ul>
<p>For organizations with complex requirements. <a href="/contact">Contact us</a> for a tailored quote.</p>

<h2>All Plans Include</h2>
<ul><li>Free 14-day trial — no credit card required</li><li>Secure, encrypted data storage</li><li>Regular updates and improvements</li><li>Comprehensive documentation</li><li>99.9% uptime guarantee</li></ul>

<h2>Frequently Asked Questions</h2>
<details><summary><strong>Can I change plans later?</strong></summary><p>Yes, you can upgrade or downgrade your plan at any time. Changes take effect at the start of your next billing cycle.</p></details>
<details><summary><strong>Is there a long-term contract?</strong></summary><p>No. All plans are month-to-month with no long-term commitment. You can cancel anytime.</p></details>'],
            'portfolio' => ['slug' => 'portfolio', 'title' => 'Portfolio', 'excerpt' => 'Browse our collection of work and recent projects.', 'content' => '
<h2>Our Work</h2>
<p>A curated selection of our projects showcasing range, quality, and attention to detail. Each project represents our commitment to excellence and client satisfaction.</p>

<h3>Featured Projects</h3>
<p>These projects represent some of our most impactful and creative work. From initial concept through final delivery, each one presented unique challenges that we were proud to solve.</p>

<h3>Project: Brand Reimagined</h3>
<p>A complete brand overhaul for a growing technology company, including visual identity, digital presence, and marketing materials. The result was a 40% increase in brand recognition within six months.</p>

<h3>Project: Digital Transformation</h3>
<p>End-to-end digital transformation for a traditional retail business. We designed and implemented a comprehensive online platform that increased their revenue by 65% in the first year.</p>

<h3>Project: Community Platform</h3>
<p>Built an engaging community platform that brought together over 10,000 members in its first quarter. Features include real-time collaboration, event management, and resource sharing.</p>

<h3>Project: Sustainable Solutions</h3>
<p>Developed an innovative approach for an environmental organization, combining cutting-edge technology with sustainable practices. The project received industry recognition for its creative approach.</p>

<h2>Our Approach</h2>
<p>Every project starts with understanding — your goals, your audience, and your vision. We combine strategic thinking with creative execution to deliver results that matter.</p>
<ul><li><strong>Discovery:</strong> Deep dive into your needs and objectives</li><li><strong>Strategy:</strong> Data-driven planning and creative direction</li><li><strong>Execution:</strong> Meticulous implementation with regular check-ins</li><li><strong>Delivery:</strong> Thorough testing and polished final product</li></ul>'],
            'team' => ['slug' => 'team', 'title' => 'Our Team', 'excerpt' => 'Meet the talented people behind our success.', 'content' => '
<h2>Meet Our Team</h2>
<p>Behind every great project is a team of dedicated professionals. We bring together diverse expertise, shared values, and a genuine passion for what we do.</p>

<h3>Leadership</h3>

<h4>Alex Morgan — Founder & CEO</h4>
<p>With over 15 years of industry experience, Alex founded the company with a vision to deliver exceptional quality while building lasting client relationships. Alex holds an MBA from a top business school and is a regular speaker at industry events.</p>

<h4>Jordan Lee — Chief Operations Officer</h4>
<p>Jordan oversees day-to-day operations and ensures every project runs smoothly from kickoff to delivery. Their background in project management and process optimization keeps our team performing at its best.</p>

<h3>Our Experts</h3>

<h4>Sam Rivera — Lead Designer</h4>
<p>Sam brings a unique blend of creative vision and technical precision to every project. With awards in both print and digital design, Sam ensures every deliverable is visually compelling and strategically effective.</p>

<h4>Casey Kim — Technical Lead</h4>
<p>Casey architects the technical foundations of our solutions. With deep expertise across modern technologies, Casey ensures our implementations are robust, scalable, and future-proof.</p>

<h4>Taylor Brooks — Client Success Manager</h4>
<p>Taylor is the primary point of contact for our clients, ensuring clear communication and satisfaction throughout every engagement. Their proactive approach anticipates needs before they arise.</p>

<h2>Our Values</h2>
<ul><li><strong>Excellence:</strong> We hold ourselves to the highest standards in everything we do</li><li><strong>Integrity:</strong> Honest communication and transparent practices, always</li><li><strong>Innovation:</strong> Continuously pushing boundaries to deliver better results</li><li><strong>Collaboration:</strong> Working together — with each other and with our clients</li></ul>

<h2>Join Our Team</h2>
<p>We\'re always looking for talented individuals who share our passion. <a href="/contact">Get in touch</a> to learn about current opportunities.</p>'],
            'faq' => ['slug' => 'faq', 'title' => 'FAQ', 'excerpt' => 'Answers to frequently asked questions.', 'content' => '
<h2>Frequently Asked Questions</h2>
<p>Find answers to common questions about our services, process, and policies below. If you don\'t see what you\'re looking for, please don\'t hesitate to <a href="/contact">contact us</a>.</p>

<h3>General Questions</h3>
<details><summary><strong>What services do you offer?</strong></summary><p>We offer a comprehensive range of services tailored to your specific needs. Visit our <a href="/services">Services page</a> for a detailed overview of everything we provide, including consultation, implementation, and ongoing support.</p></details>
<details><summary><strong>How do I get started?</strong></summary><p>Getting started is easy. You can reach out through our contact form, email us directly, or give us a call. We\'ll schedule an initial consultation to understand your needs and create a tailored plan.</p></details>
<details><summary><strong>What areas do you serve?</strong></summary><p>We serve clients locally and remotely. Our team is equipped to handle projects of any scale, regardless of location. Contact us to discuss your specific requirements.</p></details>

<h3>Pricing & Payment</h3>
<details><summary><strong>Do you offer free consultations?</strong></summary><p>Yes! We offer a complimentary initial consultation for all new clients. This allows us to understand your needs and provide you with an accurate quote before any commitment.</p></details>
<details><summary><strong>What payment methods do you accept?</strong></summary><p>We accept all major credit cards, bank transfers, and can arrange flexible payment plans for larger projects. We\'ll discuss payment terms during our initial consultation.</p></details>
<details><summary><strong>Are there any hidden fees?</strong></summary><p>Absolutely not. We believe in complete transparency. All costs are outlined clearly in our proposals, and we\'ll always discuss any potential additional expenses before proceeding.</p></details>

<h3>Process & Timeline</h3>
<details><summary><strong>How long does a typical project take?</strong></summary><p>Project timelines vary depending on scope and complexity. Small projects may take 1-2 weeks, while larger engagements can span several months. We\'ll provide a detailed timeline during the planning phase.</p></details>
<details><summary><strong>Can I make changes during the project?</strong></summary><p>Of course. We build flexibility into our process to accommodate evolving needs. We use an iterative approach with regular check-ins so you can provide feedback throughout.</p></details>'],
            'testimonials' => ['slug' => 'testimonials', 'title' => 'Testimonials', 'excerpt' => 'What our clients say.', 'content' => '
<h2>What Our Clients Say</h2>
<p>Don\'t just take our word for it — hear from the businesses and individuals we\'ve had the pleasure of working with.</p>

<blockquote>
<p>"Exceptional service from start to finish. The team went above and beyond our expectations, delivering results that truly transformed our business. Their attention to detail and commitment to quality is unmatched."</p>
<cite>— <strong>Sarah Johnson</strong>, CEO, Brightwave Solutions</cite>
</blockquote>

<blockquote>
<p>"Professional, responsive, and incredibly talented. They took the time to understand our unique challenges and crafted a solution that perfectly addressed our needs. We\'ve seen measurable improvements since working with them."</p>
<cite>— <strong>Michael Roberts</strong>, Director of Operations, TechForward Inc.</cite>
</blockquote>

<blockquote>
<p>"Working with this team has been a game-changer for our organization. Their expertise, combined with a genuine care for client success, makes them stand out in the industry. Highly recommended."</p>
<cite>— <strong>Emily Chen</strong>, Marketing Manager, GreenLeaf Partners</cite>
</blockquote>

<blockquote>
<p>"From the initial consultation to final delivery, every step was handled with professionalism and transparency. The results exceeded our expectations, and we continue to benefit from their ongoing support."</p>
<cite>— <strong>David Thompson</strong>, Founder, Nexus Ventures</cite>
</blockquote>

<blockquote>
<p>"I was impressed by how quickly they grasped our vision and turned it into reality. The quality of work, combined with their collaborative approach, made the entire process enjoyable and stress-free."</p>
<cite>— <strong>Rachel Martinez</strong>, Creative Director, Studio Aria</cite>
</blockquote>

<h2>Our Track Record</h2>
<ul>
<li><strong>500+</strong> projects completed</li>
<li><strong>98%</strong> client satisfaction rate</li>
<li><strong>150+</strong> active long-term clients</li>
<li><strong>10+</strong> years of industry experience</li>
</ul>'],
        ];

        $readableType = ucfirst(str_replace('-', ' ', $pageType));
        return $generic[$pageType] ?? [
            'slug' => $pageType,
            'title' => $readableType,
            'excerpt' => "Learn more about {$readableType} — we're here to help.",
            'content' => "<h2>{$readableType}</h2>
<p>Welcome to our {$readableType} page. Here you'll find everything you need to know about this area of our business.</p>

<h3>What We Offer</h3>
<p>We pride ourselves on delivering exceptional quality and value. Our team brings years of experience and a genuine commitment to client satisfaction.</p>

<h3>Why Choose Us</h3>
<ul>
<li><strong>Expert Team:</strong> Professionals dedicated to excellence</li>
<li><strong>Proven Results:</strong> A track record of satisfied clients</li>
<li><strong>Personal Touch:</strong> Every client receives individual attention</li>
<li><strong>Transparent Process:</strong> Clear communication at every step</li>
</ul>

<h3>Get Started</h3>
<p>Ready to learn more? <a href=\"/contact\">Contact us</a> today to discuss how we can help you achieve your goals. We'd love to hear from you.</p>",
        ];
    }

    /**
     * Seed demo content: pages, articles, and Pexels images for the generated theme.
     * Uses $selectedPages to seed ONLY the pages the user selected in the wizard.
     */
    private function seedContent(array $brief, array $selectedPages = []): void
    {
        try {
            $pdo = \core\Database::connection();
        } catch (\Throwable $e) {
            return;
        }

        $slug = $this->slug;
        $industry = $this->industry;
        $name = $brief['name'] ?? ucfirst($slug);
        $lang = $this->language;

        // Fetch Pexels images for this industry
        $images = $this->fetchPexelsImages($industry, 15);

        // ── PAGE SEEDING ──
        // Determine which pages to seed — filter out home/blog/gallery (handled by CMS routes)
        $pagesToSeed = !empty($selectedPages)
            ? array_values(array_filter($selectedPages, fn($p) => !in_array($p, ['home', 'blog', 'gallery'])))
            : ['about', 'services', 'contact'];

        // Store seeded pages list so generateLayoutOnly can return it
        $this->seededPages = $pagesToSeed;

        $pageIdx = 0;
        foreach ($pagesToSeed as $pageType) {
            $pageData = $this->getPageContentForType($industry, $pageType, $name, $lang);

            $img = $images[$pageIdx] ?? null;
            $featuredImage = $img ? ($img['src'] ?? null) : null;
            $pageIdx++;

            $dpTitle = $pageData['title'] ?? ucfirst($pageType);
            $dpContent = $pageData['content'] ?? '';
            $dpExcerpt = $pageData['excerpt'] ?? '';
            // Auto-generate meta_description from content if excerpt is empty
            if (empty($dpExcerpt) && !empty($dpContent)) {
                $dpExcerpt = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($dpContent))), 0, 160);
            }
            $pageSlug = $slug . '-' . $pageType;

            $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND theme_slug = ?");
            $stmt->execute([$pageSlug, $slug]);
            if ($stmt->fetch()) continue;

            // Template = 'page' (generic template — content comes from DB, not custom PHP files)
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, featured_image, template, status, theme_slug, meta_description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'published', ?, ?, NOW(), NOW())");
            $stmt->execute([$dpTitle, $pageSlug, $dpContent, $featuredImage, 'page', $slug, $dpExcerpt]);
        }

        // ── ARTICLE SEEDING ──
        $demoArticles = $this->getIndustryArticles($industry, $name, $lang);
        foreach ($demoArticles as $i => $da) {
            $img = $images[($pageIdx + $i) % max(count($images), 1)] ?? null;
            $featuredImage = $img ? ($img['src'] ?? null) : null;

            $daSlug = $da['slug'] ?? ('article-' . $i);
            $daTitle = $da['title'] ?? ucfirst($daSlug);
            $daExcerpt = $da['excerpt'] ?? '';
            $daContent = $da['content'] ?? '';
            // Auto-generate excerpt from content if empty
            if (empty($daExcerpt) && !empty($daContent)) {
                $daExcerpt = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($daContent))), 0, 160);
            }

            $artSlug = $slug . '-' . $daSlug;
            $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
            $stmt->execute([$artSlug]);
            if ($stmt->fetch()) continue;

            $stmt = $pdo->prepare("INSERT INTO articles (title, slug, excerpt, content, featured_image, status, theme_slug, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'published', ?, NOW(), NOW(), NOW())");
            $stmt->execute([$daTitle, $artSlug, $daExcerpt, $daContent, $featuredImage, $slug]);
        }

        // ── FOOTER CONTACT & SOCIAL ──
        // Use real business info from Business Profile if available, otherwise generate defaults
        $bizInfo = $brief['business_info'] ?? [];
        $bizSocial = $bizInfo['social'] ?? [];
        $themeName = $bizInfo['name'] ?? $brief['theme_name'] ?? $brief['name'] ?? 'My Business';
        $themeSlugClean = str_replace('-', '', $slug);
        $footerDefaults = [
            'footer.phone'       => $bizInfo['phone'] ?? '+1 (555) 234-5678',
            'footer.email'       => $bizInfo['email'] ?? ('hello@' . preg_replace('/[^a-z0-9]/', '', strtolower($themeName)) . '.com'),
            'footer.address'     => $bizInfo['address'] ?? '123 Main Street, Suite 100',
            'footer.facebook'    => $bizSocial['facebook'] ?? ('https://facebook.com/' . $themeSlugClean),
            'footer.instagram'   => $bizSocial['instagram'] ?? ('https://instagram.com/' . $themeSlugClean),
            'footer.twitter'     => $bizSocial['twitter'] ?? ('https://twitter.com/' . $themeSlugClean),
            'footer.linkedin'    => $bizSocial['linkedin'] ?? ('https://linkedin.com/company/' . $themeSlugClean),
        ];
        // Add YouTube and TikTok if provided
        if (!empty($bizSocial['youtube'])) $footerDefaults['footer.youtube'] = $bizSocial['youtube'];
        if (!empty($bizSocial['tiktok'])) $footerDefaults['footer.tiktok'] = $bizSocial['tiktok'];
        foreach ($footerDefaults as $dotKey => $val) {
            [$sec, $key] = explode('.', $dotKey, 2);
            $stmtC = $pdo->prepare("SELECT id FROM theme_customizations WHERE theme_slug = ? AND section = ? AND field_key = ?");
            $stmtC->execute([$slug, $sec, $key]);
            if (!$stmtC->fetch()) {
                $pdo->prepare("INSERT INTO theme_customizations (theme_slug, section, field_key, field_value, field_type) VALUES (?, ?, ?, ?, 'text')")
                    ->execute([$slug, $sec, $key, $val]);
            }
        }

        // ── IMAGE ASSIGNMENT ──
        $imageMap = [
            0 => ['hero', 'bg_image'],
            1 => ['about', 'image'],
            2 => ['about', 'bg_image'],
        ];

        // Hero patterns use different keys: bg_image (centered) vs image (split)
        // Save to BOTH so the image works regardless of pattern type
        if (!empty($images[0])) {
            $heroImgSrc = $images[0]['src'];
            $stmtChk = $pdo->prepare("SELECT id FROM theme_customizations WHERE theme_slug = ? AND section = 'hero' AND field_key = 'image'");
            $stmtChk->execute([$slug]);
            if ($stmtChk->fetch()) {
                $pdo->prepare("UPDATE theme_customizations SET field_value = ? WHERE theme_slug = ? AND section = 'hero' AND field_key = 'image'")->execute([$heroImgSrc, $slug]);
            } else {
                $pdo->prepare("INSERT INTO theme_customizations (theme_slug, section, field_key, field_value, field_type) VALUES (?, 'hero', 'image', ?, 'image')")->execute([$slug, $heroImgSrc]);
            }
        }

        $sectionIds = array_column($brief['homepage_sections'] ?? [], 'id');
        $extraIdx = 3;
        foreach (['services', 'projects', 'testimonials', 'cta', 'parallax'] as $sec) {
            if (in_array($sec, $sectionIds) && isset($images[$extraIdx])) {
                $imageMap[$extraIdx] = [$sec, 'bg_image'];
                $extraIdx++;
            }
        }

        foreach ($imageMap as $imgIdx => [$section, $fieldKey]) {
            if (empty($images[$imgIdx])) continue;
            $imgSrc = $images[$imgIdx]['src'];
            $stmt = $pdo->prepare("SELECT id FROM theme_customizations WHERE theme_slug = ? AND section = ? AND field_key = ?");
            $stmt->execute([$slug, $section, $fieldKey]);
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE theme_customizations SET field_value = ? WHERE theme_slug = ? AND section = ? AND field_key = ?")->execute([$imgSrc, $slug, $section, $fieldKey]);
            } else {
                $pdo->prepare("INSERT INTO theme_customizations (theme_slug, section, field_key, field_value, field_type) VALUES (?, ?, ?, ?, 'text')")->execute([$slug, $section, $fieldKey, $imgSrc]);
            }
        }

        // ═══════════════════════════════════════════
        // DYNAMIC MENU SEEDING — built from $selectedPages
        // ═══════════════════════════════════════════
        $headerMenuName = $name . ' Navigation';
        $footerMenuName = $name . ' Footer';

        $allSelectedPages = !empty($selectedPages) ? $selectedPages : ['home', 'about', 'services', 'gallery', 'blog', 'contact'];
        $headerMenuItems = [];
        $footerMenuItems = [];

        foreach ($allSelectedPages as $pageType) {
            if ($pageType === 'home') {
                $headerMenuItems[] = ['title' => 'Home', 'url' => '/'];
                $footerMenuItems[] = ['title' => 'Home', 'url' => '/'];
            } elseif ($pageType === 'blog') {
                $headerMenuItems[] = ['title' => 'Blog', 'url' => '/articles'];
            } elseif ($pageType === 'gallery') {
                $headerMenuItems[] = ['title' => 'Gallery', 'url' => '/gallery'];
            } else {
                $pageData = $this->getPageContentForType($industry, $pageType, $name, $lang);
                $menuTitle = $pageData['title'] ?? ucfirst($pageType);
                $url = '/page/' . $slug . '-' . $pageType;
                $headerMenuItems[] = ['title' => $menuTitle, 'url' => $url];
                $footerMenuItems[] = ['title' => $menuTitle, 'url' => $url];
            }
        }

        // Add Shop link for e-commerce industries
        $ecomIndustries = ['ecommerce', 'electronics', 'fashion', 'jewelry', 'beauty', 'furniture',
            'homedecor', 'bookshop', 'grocery', 'organic', 'pets', 'florist', 'marketplace'];
        $industryLower = strtolower(str_replace([' ', '-'], '', $industry));
        if (in_array($industryLower, $ecomIndustries)) {
            // Add Shop link right after Home (position 1) if not already present
            $hasShop = false;
            foreach ($headerMenuItems as $mi) {
                if ($mi['url'] === '/shop') { $hasShop = true; break; }
            }
            if (!$hasShop) {
                // Insert after first item (Home)
                array_splice($headerMenuItems, 1, 0, [['title' => 'Shop', 'url' => '/shop']]);
                $footerMenuItems[] = ['title' => 'Shop', 'url' => '/shop'];
            }
        }

        // Header menu — limit items to pattern's max_nav to prevent overflow
        $maxNav = $this->headerPatternResult['max_nav'] ?? 7;
        $headerMenuLimited = array_slice($headerMenuItems, 0, $maxNav);
        // Items that didn't fit in header still go to footer
        $headerOverflow = array_slice($headerMenuItems, $maxNav);
        foreach ($headerOverflow as $overflow) {
            if (!in_array($overflow['url'], array_column($footerMenuItems, 'url'))) {
                $footerMenuItems[] = $overflow;
            }
        }

        $stmt = $pdo->prepare("SELECT id FROM menus WHERE location = 'header' AND name = ?");
        $stmt->execute([$headerMenuName]);
        if (!$stmt->fetch()) {
            try {
                $pdo->prepare("INSERT INTO menus (name, slug, location, theme_slug, created_at, updated_at) VALUES (?, ?, 'header', ?, NOW(), NOW())")
                    ->execute([$headerMenuName, $slug . '-header', $slug]);
                $menuId = (int)$pdo->lastInsertId();
                $itemCheck = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE menu_id = ?");
                $itemCheck->execute([$menuId]);
                if ($itemCheck->fetchColumn() > 0) { throw new \RuntimeException('skip'); }
                $menuOrder = 0;
                foreach ($headerMenuLimited as $item) {
                    $pdo->prepare("INSERT INTO menu_items (menu_id, title, url, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())")
                        ->execute([$menuId, $item['title'], $item['url'], $menuOrder++]);
                }
            } catch (\Throwable $e) { /* skip */ }
        }

        // Footer menu
        $stmt = $pdo->prepare("SELECT id FROM menus WHERE location = 'footer' AND name = ?");
        $stmt->execute([$footerMenuName]);
        if (!$stmt->fetch()) {
            try {
                $pdo->prepare("INSERT INTO menus (name, slug, location, theme_slug, created_at, updated_at) VALUES (?, ?, 'footer', ?, NOW(), NOW())")
                    ->execute([$footerMenuName, $slug . '-footer', $slug]);
                $menuId = $pdo->lastInsertId();
                $menuOrder = 0;
                foreach ($footerMenuItems as $item) {
                    $pdo->prepare("INSERT INTO menu_items (menu_id, title, url, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())")
                        ->execute([$menuId, $item['title'], $item['url'], $menuOrder++]);
                }
            } catch (\Throwable $e) { /* skip */ }
        }

        // Footer-services menu
        $footerServicesName = $name . ' Services';
        $stmt = $pdo->prepare("SELECT id FROM menus WHERE location = 'footer-services' AND theme_slug = ?");
        $stmt->execute([$slug]);
        if (!$stmt->fetch()) {
            try {
                $pdo->prepare("INSERT INTO menus (name, slug, location, theme_slug, created_at, updated_at) VALUES (?, ?, 'footer-services', ?, NOW(), NOW())")
                    ->execute([$footerServicesName, $slug . '-footer-services', $slug]);
                $svcMenuId = $pdo->lastInsertId();
                $svcOrder = 0;
                $svcItems = $this->getIndustryServiceNames($this->industry);
                foreach ($svcItems as $svcLabel) {
                    $pdo->prepare("INSERT INTO menu_items (menu_id, title, url, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())")
                        ->execute([$svcMenuId, $svcLabel, '/page/' . $slug . '-services', $svcOrder++]);
                }
            } catch (\Throwable $e) { /* skip */ }
        }

        // === GALLERY SEEDING ===
        // Create a gallery for this theme with Pexels images
        $galleryName = $name . ' Gallery';
        $gallerySlug = $slug . '-gallery';
        $stmt = $pdo->prepare("SELECT id FROM galleries WHERE slug = ?");
        $stmt->execute([$gallerySlug]);
        if (!$stmt->fetch()) {
            try {
                $galleryImages = array_slice($images, 8, 6);
                if (!empty($galleryImages)) {
                    $pdo->prepare("INSERT INTO galleries (name, slug, description, is_public, display_template, theme, created_at, updated_at) VALUES (?, ?, ?, 1, 'masonry', ?, NOW(), NOW())")
                        ->execute([$galleryName, $gallerySlug, 'Photo gallery for ' . $name, $slug]);
                    $galleryId = $pdo->lastInsertId();
                    $sortOrder = 0;
                    foreach ($galleryImages as $gImg) {
                        $filename = 'gallery_' . $slug . '_' . $sortOrder . '.jpg';
                        $localPath = '/var/www/cms/uploads/media/' . $filename;
                        if (!file_exists($localPath)) {
                            $dlCtx = stream_context_create(['http' => ['timeout' => 10, 'max_redirects' => 3]]);
                            $imgData = @file_get_contents($gImg['src'], false, $dlCtx);
                            if ($imgData && strlen($imgData) < 10 * 1024 * 1024) @file_put_contents($localPath, $imgData);
                        }
                        if (file_exists($localPath)) {
                            $pdo->prepare("INSERT INTO gallery_images (gallery_id, filename, title, original_name, sort_order, created_at) VALUES (?, ?, ?, ?, ?, NOW())")
                                ->execute([$galleryId, $filename, $gImg['alt'] ?? $galleryName, $filename, $sortOrder++]);
                        }
                    }
                }
            } catch (\Throwable $e) { /* skip */ }
        }

        // === BRAND & CONTACT SEEDING ===
        $brandData = [
            'brand' => ['site_name' => $name],
            'footer' => ['description' => $brief['description'] ?? "Welcome to {$name}"],
        ];
        $bizInfo = $brief['business_info'] ?? [];
        if (!empty($bizInfo['phone']))   $brandData['footer']['phone']   = $bizInfo['phone'];
        if (!empty($bizInfo['email']))   $brandData['footer']['email']   = $bizInfo['email'];
        if (!empty($bizInfo['address'])) $brandData['footer']['address'] = $bizInfo['address'];
        if (!empty($bizInfo['social']['facebook']))  $brandData['footer']['facebook']  = $bizInfo['social']['facebook'];
        if (!empty($bizInfo['social']['instagram'])) $brandData['footer']['instagram'] = $bizInfo['social']['instagram'];

        if (!empty($brief['hero_headline']))  $brandData['hero'] = ['headline' => $brief['hero_headline']];
        if (!empty($brief['hero_subheadline'])) {
            $brandData['hero'] = $brandData['hero'] ?? [];
            $brandData['hero']['subheadline'] = $brief['hero_subheadline'];
        }

        foreach ($brandData as $section => $fields) {
            foreach ($fields as $fieldKey => $fieldValue) {
                if (empty($fieldValue)) continue;
                $stmt = $pdo->prepare("SELECT id FROM theme_customizations WHERE theme_slug = ? AND section = ? AND field_key = ?");
                $stmt->execute([$slug, $section, $fieldKey]);
                if ($stmt->fetch()) {
                    $pdo->prepare("UPDATE theme_customizations SET field_value = ? WHERE theme_slug = ? AND section = ? AND field_key = ?")
                        ->execute([$fieldValue, $slug, $section, $fieldKey]);
                } else {
                    $pdo->prepare("INSERT INTO theme_customizations (theme_slug, section, field_key, field_value, field_type) VALUES (?, ?, ?, ?, 'text')")
                        ->execute([$slug, $section, $fieldKey, $fieldValue]);
                }
            }
        }
    }


    /**
     * Fetch images from Pexels based on industry keywords.
     */
    private function fetchPexelsImages(string $industry, int $count = 8): array
    {
        // Industry-to-search mapping
        $searchTerms = [
            // Food & Hospitality
            'restaurant' => 'restaurant food dining',
            'cafe' => 'coffee cafe pastry',
            'bar' => 'cocktail bar nightlife',
            'bakery' => 'artisan bakery bread',
            'foodtruck' => 'food truck street food',
            'catering' => 'catering food event',
            'hotel' => 'luxury hotel room',
            'resort' => 'tropical resort pool',
            'winery' => 'winery vineyard wine',

            // Tech & Digital
            'saas' => 'technology workspace laptop',
            'startup' => 'startup team office',
            'ai' => 'artificial intelligence technology',
            'app' => 'mobile app smartphone',
            'crypto' => 'cryptocurrency blockchain digital',
            'cybersecurity' => 'cybersecurity network protection',
            'devtools' => 'developer coding workspace',
            'hosting' => 'server data center',
            'itsupport' => 'tech support helpdesk',
            'gamedev' => 'game development design',

            // Creative & Media
            'portfolio' => 'creative workspace design',
            'design' => 'graphic design studio',
            'photography' => 'photography camera landscape',
            'videography' => 'video production camera',
            'animation' => 'animation digital art',
            'agency' => 'creative agency team',
            'marketing' => 'digital marketing strategy',
            'music' => 'music concert performance',
            'film' => 'film production cinema',
            'art' => 'art gallery paintings',
            'architecture' => 'architecture building design',
            'interior' => 'interior design modern',
            'tattoo' => 'tattoo artist studio',

            // Content & Publishing
            'blog' => 'writing journal creative',
            'personal' => 'personal branding lifestyle',
            'magazine' => 'magazine editorial style',
            'news' => 'newspaper journalism media',
            'podcast' => 'podcast microphone studio',
            'newsletter' => 'email newsletter writing',
            'author' => 'author books library',
            'influencer' => 'influencer lifestyle social',

            // Commerce & Retail
            'ecommerce' => 'shopping products retail',
            'fashion' => 'fashion style clothing',
            'jewelry' => 'jewelry luxury accessories',
            'beauty' => 'beauty cosmetics skincare',
            'furniture' => 'modern furniture interior',
            'electronics' => 'electronics gadgets technology',
            'bookshop' => 'bookstore reading books',
            'grocery' => 'fresh groceries organic',
            'pets' => 'pets dogs cats',
            'florist' => 'flower arrangement bouquet',
            'marketplace' => 'online marketplace shopping',

            // Professional Services
            'law' => 'law office justice',
            'finance' => 'finance business investment',
            'consulting' => 'business consulting meeting',
            'accounting' => 'accounting financial documents',
            'insurance' => 'insurance protection family',
            'recruiting' => 'recruiting job interview',
            'translation' => 'translation languages global',
            'realestate' => 'real estate architecture house',
            'propertymanagement' => 'property management building',

            // Health & Wellness
            'medical' => 'medical healthcare doctor',
            'dental' => 'dental care smile',
            'fitness' => 'fitness gym workout',
            'yoga' => 'yoga meditation wellness',
            'spa' => 'spa wellness relaxation',
            'veterinary' => 'veterinary pets animals',
            'therapy' => 'therapy counseling session',
            'mentalhealth' => 'mental health mindfulness',
            'nutrition' => 'healthy food nutrition',
            'physiotherapy' => 'physiotherapy rehabilitation exercise',
            'pharmacy' => 'pharmacy medicine health',

            // Education & Training
            'education' => 'education learning school',
            'onlinecourse' => 'online learning education',
            'coaching' => 'coaching mentoring growth',
            'tutoring' => 'tutoring student learning',
            'language' => 'language learning classroom',
            'driving' => 'driving school car',
            'childcare' => 'childcare nursery children',
            'library' => 'library books reading',
            'training' => 'corporate training workshop',

            // Construction & Trade
            'construction' => 'construction building workers',
            'plumbing' => 'plumbing repair pipes',
            'electrical' => 'electrician wiring installation',
            'hvac' => 'heating ventilation air',
            'roofing' => 'roofing construction house',
            'painting' => 'house painting decorator',
            'landscaping' => 'landscaping garden outdoor',
            'cleaning' => 'cleaning service home',
            'moving' => 'moving boxes relocation',
            'handyman' => 'handyman home repair',
            'solar' => 'solar panels energy',

            // Automotive & Transport
            'automotive' => 'cars automotive luxury',
            'mechanic' => 'auto mechanic garage',
            'carwash' => 'car wash detailing',
            'taxi' => 'taxi cab transport',
            'trucking' => 'truck logistics freight',
            'motorcycle' => 'motorcycle riding road',
            'boating' => 'boat marina ocean',

            // Events & Entertainment
            'events' => 'event planning celebration',
            'wedding' => 'wedding celebration love',
            'party' => 'party celebration decorations',
            'venue' => 'event venue ballroom',
            'theater' => 'theater stage performance',
            'cinema' => 'cinema movie theater',
            'escape' => 'escape room adventure',
            'festival' => 'music festival outdoor',

            // Travel & Leisure
            'travel' => 'travel adventure destination',
            'tourism' => 'tourism sightseeing landmark',
            'camping' => 'camping outdoors nature',
            'skiing' => 'skiing snow mountain',
            'diving' => 'scuba diving ocean',
            'golf' => 'golf course green',
            'marina' => 'marina yacht harbor',

            // Community & Non-Profit
            'nonprofit' => 'community volunteer charity',
            'church' => 'church community faith',
            'volunteer' => 'volunteer community helping',
            'political' => 'political campaign rally',
            'community' => 'community neighborhood people',
            'association' => 'professional association meeting',

            // Government & Public
            'government' => 'government building civic',
            'police' => 'police law enforcement',
            'military' => 'military defense forces',
            'embassy' => 'embassy diplomatic building',

            // Other / Specialty
            'resume' => 'professional resume career',
            'wiki' => 'knowledge documentation library',
            'directory' => 'business directory listings',
            'landing' => 'startup product launch',
            'comingsoon' => 'coming soon launch',
            'memorial' => 'memorial tribute candles',
            'sports' => 'sports team athletics stadium',
            'saas-landing' => 'modern landing page product',
            'other' => 'professional business office',
        ];

        $query = $searchTerms[$industry] ?? $industry . ' business';

        try {
            // Standalone Pexels API call — no JTB dependency
            $pexelsKey = '';
            try {
                $db = \core\Database::connection();
                $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = 'pexels_api_key' LIMIT 1");
                $stmt->execute();
                $pexelsKey = $stmt->fetchColumn() ?: '';
            } catch (\Throwable $e) { /* no key */ }
            if (empty($pexelsKey) || strlen($pexelsKey) < 20) return [];

            $params = http_build_query([
                'query' => $query,
                'per_page' => $count,
                'page' => random_int(1, 5),
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
            curl_close($ch);
            if ($httpCode !== 200) return [];

            $data = @json_decode($response, true);
            if (empty($data['photos'])) return [];

            $images = [];
            foreach ($data['photos'] as $photo) {
                $images[] = [
                    'src' => $photo['src']['large'] ?? $photo['src']['original'] ?? '',
                    'alt' => $photo['alt'] ?? '',
                    'photographer' => $photo['photographer'] ?? '',
                ];
            }
            return $images;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get industry-appropriate demo pages.
     */
    private function getIndustryPages(string $industry, string $themeName, string $lang): array
    {
        // Food & Hospitality group
        $foodPages = [
            ['slug' => 'our-menu', 'title' => 'Our Menu', 'excerpt' => 'Explore our carefully curated selection of dishes and drinks.', 'content' => '<p>Discover our carefully crafted dishes, made with the finest seasonal ingredients sourced from local farms and trusted suppliers.</p><h2>Starters</h2><p>Our starters showcase locally sourced produce prepared with modern techniques. From crispy calamari to hand-pulled mozzarella, every appetizer sets the tone for a memorable meal.</p><h2>Main Courses</h2><p>From wood-fired specialties to ocean-fresh catches, each plate tells a story. Our chefs draw on decades of combined experience to deliver bold flavors with elegant presentation.</p><h2>Desserts</h2><p>End your meal with our artisanal desserts crafted by our pastry chef. Seasonal tarts, rich chocolate fondant, and house-made gelato await you.</p><ul><li>Vegetarian and vegan options available</li><li>Gluten-free dishes clearly marked</li><li>Kids menu available on request</li></ul>'],
            ['slug' => 'reservations', 'title' => 'Reservations', 'excerpt' => 'Reserve your table for an unforgettable dining experience.', 'content' => '<p>Join us for an unforgettable dining experience. Reserve your table today and let us take care of the rest.</p><p>We accept reservations for parties of all sizes. For groups of 8 or more, please contact us directly to discuss tailored menus and private dining options.</p><h2>Opening Hours</h2><ul><li>Monday – Thursday: 11:00 AM – 10:00 PM</li><li>Friday – Saturday: 11:00 AM – 11:00 PM</li><li>Sunday: 10:00 AM – 9:00 PM (Brunch from 10 AM)</li></ul><h2>Private Events</h2><p>Host your next celebration with us. Our private dining room seats up to 40 guests and can be customized with bespoke menus and decorations. Contact our events team to start planning.</p>'],
            ['slug' => 'about-us', 'title' => 'About Us', 'excerpt' => 'Learn about our story, our passion, and the team behind the kitchen.', 'content' => '<p>Our story began with a simple passion for great food and memorable experiences. What started as a family recipe has grown into a beloved dining destination.</p><p>Founded by a team of culinary enthusiasts, we bring together traditional techniques and modern innovation to create dishes that delight and inspire. Every ingredient is chosen with care, every plate assembled with pride.</p><h2>Our Philosophy</h2><p>We believe dining is about more than just food — it\'s about creating moments. From the warm welcome at the door to the last sip of espresso, every detail matters.</p><h2>The Team</h2><p>Our talented team of chefs, sommeliers, and front-of-house staff share a common goal: to make every visit exceptional. Together, we\'ve built a culture of hospitality that keeps guests coming back.</p>'],
        ];

        // Hotel & Resort group
        $hotelPages = [
            ['slug' => 'rooms', 'title' => 'Rooms & Suites', 'excerpt' => 'Discover our elegantly appointed rooms and luxury suites.', 'content' => '<p>Experience the perfect blend of comfort and sophistication in our thoughtfully designed accommodations. Each room features premium linens, modern amenities, and stunning views.</p><h2>Deluxe Room</h2><p>Our Deluxe Rooms offer a spacious retreat with king-size beds, marble bathrooms, and floor-to-ceiling windows. Ideal for couples and solo travelers seeking a refined experience.</p><h2>Executive Suite</h2><p>The Executive Suite provides a separate living area, walk-in wardrobe, and a private balcony. Perfect for extended stays and guests who appreciate extra space and luxury.</p><h2>Presidential Suite</h2><p>Our flagship accommodation features a grand living room, dining area, master bedroom with panoramic views, and a private terrace with hot tub. Available by reservation only.</p><ul><li>24-hour room service</li><li>Complimentary high-speed Wi-Fi</li><li>Pillow menu and turndown service</li><li>In-room safe and minibar</li></ul>'],
            ['slug' => 'amenities', 'title' => 'Amenities', 'excerpt' => 'Explore world-class facilities designed for your comfort.', 'content' => '<p>Our property features an exceptional range of amenities designed to enhance every aspect of your stay, from relaxation to recreation.</p><h2>Spa & Wellness</h2><p>Unwind in our full-service spa offering massages, facials, and holistic treatments. The heated indoor pool and steam room provide the perfect complement to your wellness journey.</p><h2>Dining</h2><p>Choose from three distinct dining venues: our signature fine-dining restaurant, a casual poolside grill, and an elegant lobby bar serving craft cocktails and afternoon tea.</p><h2>Business & Events</h2><p>Our state-of-the-art conference facilities accommodate up to 500 guests. Full AV equipment, dedicated event coordinators, and bespoke catering make us the ideal venue for any occasion.</p><h2>Recreation</h2><ul><li>Fitness center with personal trainers</li><li>Tennis and squash courts</li><li>Concierge and tour desk</li><li>Children\'s club and playground</li></ul>'],
            ['slug' => 'reservations', 'title' => 'Reservations', 'excerpt' => 'Book your stay and experience unforgettable hospitality.', 'content' => '<p>Planning your perfect getaway starts here. Browse available dates, compare room types, and secure the best rates by booking directly with us.</p><h2>Booking Benefits</h2><p>When you book direct, you enjoy exclusive perks including complimentary breakfast, late checkout (subject to availability), and loyalty points toward future stays.</p><h2>Cancellation Policy</h2><p>We understand plans change. Free cancellation is available up to 48 hours before your scheduled arrival. Prepaid rates offer significant savings but are non-refundable.</p><h2>Special Packages</h2><p>Explore our seasonal packages including romantic escapes, family adventures, and wellness retreats. Each package is curated to deliver exceptional value and unforgettable memories.</p>'],
        ];

        // Tech group (SaaS/Startup/AI/App/DevTools/Hosting)
        $techPages = [
            ['slug' => 'features', 'title' => 'Features', 'excerpt' => 'Discover powerful tools designed for modern teams.', 'content' => '<p>Powerful tools designed to streamline your workflow and boost productivity. Our platform brings together everything your team needs in one unified experience.</p><h2>Automation</h2><p>Automate repetitive tasks and focus on what matters most. Set up intelligent workflows that trigger actions based on events, schedules, or custom conditions — no coding required.</p><h2>Analytics & Insights</h2><p>Real-time dashboards and custom reports help you make data-informed decisions. Track KPIs, monitor trends, and share insights with your team through interactive visualizations.</p><h2>Seamless Integrations</h2><p>Connect with your favorite tools seamlessly. Our platform integrates with 200+ popular apps including Slack, Jira, GitHub, Salesforce, and Google Workspace.</p><ul><li>Role-based access control and SSO</li><li>API-first architecture for custom workflows</li><li>99.99% uptime SLA</li><li>SOC 2 Type II certified</li></ul>'],
            ['slug' => 'pricing', 'title' => 'Pricing', 'excerpt' => 'Simple, transparent pricing for teams of all sizes.', 'content' => '<p>Simple, transparent pricing with no hidden fees. Start free and scale as you grow — upgrade or downgrade at any time.</p><h2>Starter — Free</h2><p>Perfect for individuals and small teams getting started. Includes 3 projects, 1 GB storage, and community support. Free forever with no credit card required.</p><h2>Professional — $29/month</h2><p>Advanced features for growing businesses. Unlimited projects, 50 GB storage, priority support, advanced analytics, and team collaboration tools.</p><h2>Enterprise — Custom</h2><p>Custom solutions for large organizations. Dedicated account manager, custom integrations, SLA guarantees, on-premise deployment options, and volume licensing.</p><p>All plans include a 14-day free trial of Professional features. Need something custom? <a href="/contact">Contact our sales team</a>.</p>'],
            ['slug' => 'about', 'title' => 'About', 'excerpt' => 'Learn about our mission, team, and the story behind the product.', 'content' => '<p>We\'re building the future of work. Our mission is to help teams collaborate more effectively by removing friction from everyday workflows.</p><p>Founded in 2024, we\'ve grown from a small startup to a trusted platform serving thousands of businesses worldwide — from two-person agencies to Fortune 500 enterprises.</p><h2>Our Values</h2><ul><li><strong>Simplicity first:</strong> Complex problems deserve elegant solutions</li><li><strong>Customer obsession:</strong> Every feature starts with a real need</li><li><strong>Transparency:</strong> Open roadmap, honest pricing, clear communication</li></ul><h2>The Team</h2><p>Our diverse team of engineers, designers, and customer advocates is distributed across 12 countries. We\'re united by a shared belief that software should empower people, not overwhelm them.</p>'],
        ];

        // Law & Finance group
        $lawFinancePages = [
            ['slug' => 'practice-areas', 'title' => 'Practice Areas', 'excerpt' => 'Explore our areas of legal expertise and specialization.', 'content' => '<p>Our firm provides comprehensive legal services across a wide range of practice areas. With decades of combined experience, our attorneys deliver strategic counsel tailored to each client\'s unique situation.</p><h2>Corporate & Commercial Law</h2><p>From business formation to complex M&A transactions, we guide companies through every stage of growth. Our corporate team handles contracts, compliance, joint ventures, and regulatory matters.</p><h2>Family Law</h2><p>Sensitive family matters require compassionate yet effective representation. We handle divorce, custody, prenuptial agreements, and estate planning with discretion and care.</p><h2>Litigation & Dispute Resolution</h2><p>When disputes arise, our litigators pursue the best possible outcome through negotiation, mediation, or courtroom advocacy. We represent clients in civil, commercial, and employment disputes.</p><h2>Real Estate Law</h2><p>Our real estate team assists with property transactions, zoning issues, landlord-tenant disputes, and commercial leasing. We protect your interests at every step.</p>'],
            ['slug' => 'our-team', 'title' => 'Our Team', 'excerpt' => 'Meet the experienced professionals dedicated to your success.', 'content' => '<p>Behind every successful case is a team of dedicated professionals. Our attorneys combine deep expertise with a personal approach to deliver results that matter.</p><h2>Leadership</h2><p>Our managing partners bring over 40 years of combined experience to the firm. Their vision shapes our culture of excellence and client-first service.</p><h2>Associates & Counsel</h2><p>Our associates are graduates of top law schools with clerking experience at state and federal courts. Each member of our team is selected for their analytical rigor and commitment to client success.</p><h2>Support Staff</h2><p>Our paralegals, legal assistants, and administrative team ensure that every case runs smoothly. From document preparation to scheduling, they are the backbone of our operations.</p><p>We invest in continuous professional development, ensuring our team stays at the forefront of legal practice and industry trends.</p>'],
            ['slug' => 'consultations', 'title' => 'Book a Consultation', 'excerpt' => 'Schedule a confidential consultation with one of our attorneys.', 'content' => '<p>Taking the first step can be the hardest part. We offer confidential initial consultations to understand your situation and outline your options — with no obligation.</p><h2>What to Expect</h2><p>During your consultation, you\'ll meet with an experienced attorney who will listen to your concerns, assess the merits of your case, and recommend a course of action. We believe in clear, honest advice from the outset.</p><h2>How to Prepare</h2><ul><li>Gather any relevant documents or correspondence</li><li>Write down key dates and facts</li><li>Prepare a list of questions you\'d like answered</li><li>Bring identification and any existing legal agreements</li></ul><h2>Contact Us</h2><p>Call us at (555) 123-4567 or use our online booking form to schedule a time that works for you. Evening and weekend appointments are available upon request.</p>'],
        ];

        // Medical & Health group
        $medicalPages = [
            ['slug' => 'services', 'title' => 'Our Services', 'excerpt' => 'Comprehensive healthcare services for you and your family.', 'content' => '<p>We provide a full spectrum of healthcare services in a modern, patient-centered environment. Our board-certified practitioners use the latest techniques and technology to deliver exceptional care.</p><h2>Primary Care</h2><p>Regular check-ups, preventive screenings, and chronic disease management. Our primary care providers build long-term relationships with patients to support lifelong wellness.</p><h2>Specialist Consultations</h2><p>Access to specialists in cardiology, dermatology, orthopedics, and more. Our referral network ensures you receive expert care without unnecessary delays.</p><h2>Diagnostics & Lab</h2><p>On-site laboratory and diagnostic imaging for fast, accurate results. From blood work to X-rays, we minimize wait times so you can get answers quickly.</p><h2>Telehealth</h2><p>Convenient virtual consultations for follow-ups, prescription renewals, and non-emergency concerns. Connect with your provider from the comfort of your home.</p>'],
            ['slug' => 'our-team', 'title' => 'Our Team', 'excerpt' => 'Meet the dedicated healthcare professionals who care for you.', 'content' => '<p>Our team of physicians, nurses, and support staff is united by a shared commitment to compassionate, evidence-based care. Every member brings specialized expertise and genuine dedication to patient wellbeing.</p><h2>Physicians</h2><p>Our doctors are board-certified with advanced training from leading medical institutions. They participate in ongoing education to stay current with the latest medical advances and treatment protocols.</p><h2>Nursing Staff</h2><p>Our registered nurses and nurse practitioners provide hands-on care, patient education, and emotional support. They are often the first point of contact and play a vital role in your care journey.</p><h2>Administrative Team</h2><p>From scheduling to insurance coordination, our administrative staff ensures your experience is smooth and stress-free. We handle the paperwork so you can focus on your health.</p>'],
            ['slug' => 'patient-info', 'title' => 'Patient Information', 'excerpt' => 'Everything you need to know before, during, and after your visit.', 'content' => '<p>We want your visit to be as smooth as possible. Here\'s everything you need to know about appointments, insurance, and what to expect during your care.</p><h2>New Patients</h2><p>Welcome! Please arrive 15 minutes early to complete registration forms. Bring your photo ID, insurance card, and a list of current medications. You can also download forms from our patient portal.</p><h2>Insurance & Billing</h2><p>We accept most major insurance plans. Our billing team is available to answer questions about coverage, co-pays, and payment options. We offer flexible payment plans for uninsured patients.</p><h2>Office Hours</h2><ul><li>Monday – Friday: 8:00 AM – 6:00 PM</li><li>Saturday: 9:00 AM – 1:00 PM</li><li>Sunday: Closed (Emergency line available)</li></ul><h2>Patient Portal</h2><p>Access your medical records, request prescription refills, view lab results, and message your care team through our secure online patient portal — available 24/7.</p>'],
        ];

        // Construction & Trades group
        $constructionPages = [
            ['slug' => 'our-work', 'title' => 'Our Work', 'excerpt' => 'Browse our portfolio of completed projects and craftsmanship.', 'content' => '<p>With hundreds of completed projects across residential and commercial sectors, our portfolio speaks for itself. Every job reflects our commitment to quality craftsmanship and attention to detail.</p><h2>Residential Projects</h2><p>From new builds to complete renovations, we transform houses into dream homes. Our residential portfolio includes kitchen and bathroom remodels, extensions, loft conversions, and whole-house renovations.</p><h2>Commercial Projects</h2><p>We deliver commercial spaces that work as hard as you do. Our commercial portfolio includes office fit-outs, retail spaces, restaurant build-outs, and warehouse conversions.</p><h2>Recent Highlights</h2><ul><li>Victorian townhouse restoration — full structural renovation preserving period features</li><li>Modern office complex — 50,000 sq ft commercial development delivered on time and under budget</li><li>Luxury apartment block — 24 units with premium finishes and smart home technology</li></ul><p>Each project is managed by a dedicated site supervisor who ensures quality, safety, and clear communication throughout.</p>'],
            ['slug' => 'services', 'title' => 'Services', 'excerpt' => 'Full-service construction, renovation, and maintenance solutions.', 'content' => '<p>We offer a comprehensive range of construction and building services, from initial design consultation through to final handover and aftercare.</p><h2>New Construction</h2><p>Ground-up construction for residential and commercial properties. We manage every phase: site preparation, foundations, framing, mechanical, electrical, plumbing, finishing, and landscaping.</p><h2>Renovations & Remodeling</h2><p>Breathe new life into existing spaces. Our renovation team handles structural changes, modernization, energy efficiency upgrades, and cosmetic refreshes with minimal disruption.</p><h2>Maintenance & Repairs</h2><p>Ongoing maintenance contracts keep your property in peak condition. We offer scheduled inspections, emergency repairs, and preventive maintenance programs.</p><h2>Why Choose Us</h2><ul><li>Fully licensed and insured</li><li>Transparent quoting — no hidden costs</li><li>Dedicated project manager for every job</li><li>Clean-up and waste disposal included</li></ul>'],
            ['slug' => 'free-quote', 'title' => 'Get a Free Quote', 'excerpt' => 'Request a no-obligation estimate for your next project.', 'content' => '<p>Ready to start your project? We provide detailed, no-obligation quotes so you can plan with confidence. Our estimates are transparent, competitive, and always delivered promptly.</p><h2>How It Works</h2><p>Tell us about your project using the form below or call us directly. We\'ll schedule a site visit (for larger projects), assess your requirements, and provide a detailed written quotation within 48 hours.</p><h2>What We Need From You</h2><ul><li>Description of the work required</li><li>Any existing plans or drawings (if available)</li><li>Preferred timeline and budget range</li><li>Site access details and any special requirements</li></ul><h2>Contact Information</h2><p>Phone: (555) 123-4567<br>Email: quotes@example.com<br>We respond to all enquiries within one business day.</p>'],
        ];

        // Creative group (Portfolio/Design/Photography/Videography/Animation)
        $creativePages = [
            ['slug' => 'portfolio', 'title' => 'Portfolio', 'excerpt' => 'Explore a selection of our best creative work and projects.', 'content' => '<p>A curated selection of projects that showcase our creative range, technical skill, and passion for visual storytelling. Each piece represents a unique collaboration with our clients.</p><h2>Brand Identity</h2><p>From logo design to comprehensive brand systems, we help businesses establish a visual identity that resonates with their audience and stands the test of time.</p><h2>Digital & Web</h2><p>Websites, apps, and digital experiences that combine beautiful design with intuitive functionality. We focus on user experience, performance, and accessibility.</p><h2>Print & Editorial</h2><p>Brochures, packaging, editorial layouts, and environmental graphics that make a tangible impact. We bring the same attention to detail to physical media as we do to digital.</p><p>Each project in our portfolio involved close collaboration with clients to understand their vision, audience, and objectives before a single pixel was placed.</p>'],
            ['slug' => 'process', 'title' => 'Our Process', 'excerpt' => 'Discover how we turn ideas into impactful creative work.', 'content' => '<p>Great design doesn\'t happen by accident. Our process is designed to deliver consistent quality while keeping you involved and informed at every stage.</p><h2>1. Discovery</h2><p>We start by listening. Through detailed briefs, research, and conversations, we build a deep understanding of your brand, your audience, and your goals.</p><h2>2. Concept Development</h2><p>Armed with insights, we explore multiple creative directions. Mood boards, sketches, and wireframes help us refine the concept before committing to production.</p><h2>3. Design & Production</h2><p>With an approved direction, we craft every element with precision. Regular check-ins ensure the work stays aligned with your vision.</p><h2>4. Delivery & Support</h2><p>We deliver production-ready files with comprehensive guidelines. Post-launch, we\'re here for revisions, updates, and ongoing creative support.</p>'],
            ['slug' => 'about', 'title' => 'About', 'excerpt' => 'The story, philosophy, and people behind our creative studio.', 'content' => '<p>We are a creative studio driven by curiosity, craft, and the belief that good design transforms businesses. Founded by designers who care deeply about their work and its impact.</p><p>Our approach blends strategic thinking with artistic sensibility. We don\'t just make things look beautiful — we make them work beautifully.</p><h2>What We Believe</h2><ul><li><strong>Design with purpose:</strong> Every choice has a reason</li><li><strong>Collaboration over ego:</strong> The best ideas come from partnership</li><li><strong>Quality takes time:</strong> We never rush the work that matters</li></ul><h2>Awards & Recognition</h2><p>Our work has been recognized by Awwwards, CSS Design Awards, Communication Arts, and featured in leading design publications. But our greatest reward is seeing clients succeed.</p>'],
        ];

        // Education group
        $educationPages = [
            ['slug' => 'courses', 'title' => 'Courses', 'excerpt' => 'Browse our full catalog of programs and learning opportunities.', 'content' => '<p>Explore our diverse catalog of courses designed to equip you with practical skills and deep knowledge. Whether you\'re starting out or leveling up, we have a program for you.</p><h2>Professional Certifications</h2><p>Industry-recognized certifications that boost your career. Our programs are developed in partnership with leading organizations and taught by experienced practitioners.</p><h2>Skill-Based Workshops</h2><p>Intensive, hands-on workshops focused on specific skills. From data analysis to creative writing, these short-format courses deliver maximum impact in minimal time.</p><h2>Degree Programs</h2><p>Comprehensive multi-semester programs that provide a thorough foundation in your chosen field. Flexible scheduling options accommodate working professionals.</p><ul><li>Online, hybrid, and in-person formats</li><li>Small class sizes for personalized attention</li><li>Career services and placement support</li><li>Flexible payment plans and financial aid</li></ul>'],
            ['slug' => 'enrollment', 'title' => 'Enrollment', 'excerpt' => 'Apply now and begin your learning journey with us.', 'content' => '<p>Ready to take the next step? Our enrollment process is straightforward and our admissions team is here to help you every step of the way.</p><h2>How to Apply</h2><p>Complete the online application form, submit required documents, and schedule an optional assessment or interview. Most applicants receive a decision within 5 business days.</p><h2>Requirements</h2><ul><li>Completed application form</li><li>Transcript or proof of prior education</li><li>Personal statement or portfolio (program-dependent)</li><li>Two letters of recommendation (degree programs)</li></ul><h2>Tuition & Financial Aid</h2><p>We believe education should be accessible. We offer merit-based scholarships, need-based grants, employer tuition assistance, and flexible payment plans to fit your budget.</p><h2>Key Dates</h2><p>Fall enrollment: Applications open March 1. Spring enrollment: Applications open October 1. Rolling admissions available for select workshops and certificate programs.</p>'],
            ['slug' => 'faculty', 'title' => 'Faculty', 'excerpt' => 'Meet the instructors and mentors who guide your learning.', 'content' => '<p>Our faculty members are more than teachers — they are active professionals, researchers, and thought leaders who bring real-world experience into every classroom.</p><h2>Academic Excellence</h2><p>Our instructors hold advanced degrees from top universities and maintain active research agendas. They publish in peer-reviewed journals and present at international conferences.</p><h2>Industry Experience</h2><p>Many of our faculty members have worked at leading companies and organizations before transitioning to education. This dual perspective enriches the learning experience with practical insights.</p><h2>Mentorship Approach</h2><p>Small class sizes and open-door policies mean you get genuine mentorship, not just lectures. Our faculty invest in your success through personalized feedback, office hours, and career guidance.</p>'],
        ];

        // Fitness & Wellness group
        $fitnessPages = [
            ['slug' => 'classes', 'title' => 'Classes', 'excerpt' => 'Find the perfect class to match your fitness goals.', 'content' => '<p>From high-intensity interval training to mindful yoga flows, our class schedule offers something for every fitness level and goal. All classes are led by certified instructors.</p><h2>Strength & Conditioning</h2><p>Build functional strength and improve your athletic performance. Our strength classes use free weights, kettlebells, and bodyweight exercises in structured, progressive programs.</p><h2>Cardio & HIIT</h2><p>Elevate your heart rate and burn calories with our high-energy cardio classes. Options include spin cycling, boxing-inspired workouts, and metabolic conditioning circuits.</p><h2>Mind & Body</h2><p>Restore balance with yoga, Pilates, and meditation classes. These sessions focus on flexibility, core strength, breathing techniques, and mental clarity.</p><h2>Schedule</h2><ul><li>Classes run 7 days a week from 6 AM to 9 PM</li><li>Book online or through our mobile app</li><li>Drop-in rates available for non-members</li><li>First class is always free</li></ul>'],
            ['slug' => 'membership', 'title' => 'Membership', 'excerpt' => 'Choose a membership plan that fits your lifestyle.', 'content' => '<p>Invest in yourself with a membership that gives you unlimited access to our facilities, classes, and community. No long-term contracts — stay because you love it.</p><h2>Monthly Membership — $49/mo</h2><p>Full access to the gym floor, locker rooms, and group classes. Includes a complimentary fitness assessment and one personal training session per month.</p><h2>Premium Membership — $89/mo</h2><p>Everything in Monthly plus priority class booking, guest passes, sauna and recovery suite access, and quarterly progress reviews with a coach.</p><h2>Student / Concession — $35/mo</h2><p>All the benefits of Monthly membership at a reduced rate for students, seniors, and military personnel (valid ID required).</p><h2>Corporate Plans</h2><p>Exclusive rates for companies enrolling 5 or more team members. Includes wellness workshops, team fitness challenges, and dedicated account management.</p>'],
            ['slug' => 'trainers', 'title' => 'Our Trainers', 'excerpt' => 'Meet the certified professionals who will guide your fitness journey.', 'content' => '<p>Our trainers are more than coaches — they are motivators, educators, and partners in your fitness journey. Each brings unique expertise and a genuine passion for helping people achieve their goals.</p><h2>Certifications & Expertise</h2><p>All trainers hold nationally recognized certifications (NASM, ACE, ISSA, or equivalent) and maintain CPR/AED certification. Many hold additional specializations in areas like sports nutrition, injury rehabilitation, and pre/post-natal fitness.</p><h2>Personal Training</h2><p>One-on-one sessions tailored to your goals, schedule, and fitness level. Your trainer develops a customized program and adjusts it as you progress.</p><h2>Small Group Training</h2><p>Train with 3-5 people for a semi-private experience that combines the attention of personal training with the energy and motivation of group fitness — at a fraction of the cost.</p>'],
        ];

        // Real Estate group
        $realestatePages = [
            ['slug' => 'listings', 'title' => 'Properties', 'excerpt' => 'Browse our current property listings and find your perfect home.', 'content' => '<p>Explore our curated selection of residential and commercial properties. From starter homes to luxury estates, our listings span every price point and style.</p><h2>Featured Properties</h2><p>Our featured listings represent the best value and most desirable properties currently on the market. Updated weekly, these homes are hand-picked by our agents for their exceptional qualities.</p><h2>Search by Type</h2><ul><li><strong>Houses:</strong> Detached, semi-detached, and townhouses</li><li><strong>Apartments:</strong> Studio to penthouse</li><li><strong>Commercial:</strong> Office, retail, and mixed-use spaces</li><li><strong>Land:</strong> Development plots and agricultural land</li></ul><h2>Market Insights</h2><p>Stay informed with our quarterly market reports covering pricing trends, inventory levels, and neighborhood analyses. Knowledge is power in real estate.</p>'],
            ['slug' => 'about', 'title' => 'About Our Agency', 'excerpt' => 'Learn about our real estate expertise and commitment to clients.', 'content' => '<p>With deep roots in the local community and a track record of successful transactions, we are your trusted partner in property. Our agents combine market expertise with a personal touch.</p><h2>Our Approach</h2><p>We take the time to understand your needs, budget, and lifestyle before showing you a single property. This consultative approach saves you time and leads to better outcomes.</p><h2>By the Numbers</h2><ul><li>500+ successful transactions</li><li>15+ years in the market</li><li>98% client satisfaction rate</li><li>Average 21 days from listing to offer</li></ul><h2>Community Commitment</h2><p>We\'re more than a real estate office — we\'re neighbors. Our team sponsors local events, volunteers with community organizations, and reinvests in the neighborhoods we serve.</p>'],
            ['slug' => 'contact', 'title' => 'Contact Us', 'excerpt' => 'Get in touch to start your property journey today.', 'content' => '<p>Whether you\'re buying, selling, or just have questions about the market, our team is ready to help. Reach out today for a no-obligation conversation.</p><h2>Visit Our Office</h2><p>123 High Street, Suite 200<br>Open Monday–Friday 9 AM – 6 PM, Saturday 10 AM – 3 PM</p><h2>Get in Touch</h2><ul><li>Phone: (555) 123-4567</li><li>Email: info@example.com</li><li>WhatsApp: Available for international buyers</li></ul><h2>Free Property Valuation</h2><p>Thinking of selling? We provide complimentary market valuations for homeowners. Our agents will visit your property, assess comparable sales, and deliver an honest, data-backed estimate of its value.</p>'],
        ];

        // Events & Wedding group
        $eventsPages = [
            ['slug' => 'services', 'title' => 'Our Services', 'excerpt' => 'Full-service event planning from concept to celebration.', 'content' => '<p>From intimate gatherings to grand celebrations, we create events that leave lasting impressions. Our full-service approach means you can relax while we handle every detail.</p><h2>Full Event Planning</h2><p>Comprehensive planning from initial concept through execution. We manage venues, vendors, logistics, timelines, and décor so you can enjoy every moment.</p><h2>Day-of Coordination</h2><p>Already planned your event? Our coordination service ensures everything runs smoothly on the big day. We manage the timeline, direct vendors, and troubleshoot so you don\'t have to.</p><h2>Design & Styling</h2><p>Transform any space into something extraordinary. Our design team creates cohesive visual themes with custom florals, lighting, linens, and décor elements.</p><ul><li>Corporate events and galas</li><li>Weddings and engagement parties</li><li>Milestone celebrations</li><li>Product launches and brand activations</li></ul>'],
            ['slug' => 'portfolio', 'title' => 'Portfolio', 'excerpt' => 'Browse our gallery of beautifully executed events.', 'content' => '<p>Every event tells a story. Browse our portfolio to see the range of celebrations, corporate events, and private gatherings we\'ve had the privilege of bringing to life.</p><h2>Weddings</h2><p>From rustic barn celebrations to black-tie ballroom affairs, our wedding portfolio showcases the diversity of love stories we\'ve helped tell through design and planning.</p><h2>Corporate Events</h2><p>Annual conferences, product launches, awards dinners, and team retreats. Our corporate portfolio demonstrates our ability to translate brand values into memorable experiences.</p><h2>Private Celebrations</h2><p>Birthday milestones, anniversary dinners, baby showers, and holiday parties. These personal celebrations showcase our talent for creating warm, intimate atmospheres.</p><p>Each event in our portfolio was a true collaboration with our clients — their vision, our expertise, unforgettable results.</p>'],
            ['slug' => 'packages', 'title' => 'Packages & Pricing', 'excerpt' => 'Transparent pricing to fit events of every size and budget.', 'content' => '<p>We offer structured packages as well as à la carte services, so you can choose the level of support that suits your event and budget.</p><h2>Essential Package</h2><p>Day-of coordination with a dedicated event manager, vendor confirmation, timeline management, and on-site supervision. Ideal for couples and hosts who have planned the details but want professional execution.</p><h2>Premium Package</h2><p>Full planning service including venue search, vendor sourcing, design concept, budget management, and unlimited consultations. Our most popular option.</p><h2>Bespoke Package</h2><p>For large-scale or destination events requiring custom solutions. Includes everything in Premium plus travel coordination, multi-day event management, and dedicated design team.</p><h2>À La Carte</h2><ul><li>Venue sourcing and negotiation</li><li>Floral design only</li><li>Invitation and stationery design</li><li>Entertainment booking</li></ul>'],
        ];

        // Travel & Tourism group
        $travelPages = [
            ['slug' => 'destinations', 'title' => 'Destinations', 'excerpt' => 'Discover breathtaking destinations curated by our travel experts.', 'content' => '<p>From sun-drenched coastlines to mountain retreats, our curated destinations offer unforgettable experiences for every type of traveler.</p><h2>Europe</h2><p>Historic cities, Mediterranean beaches, Alpine villages, and world-class cuisine. Our European packages cover classic destinations and hidden gems alike.</p><h2>Asia & Pacific</h2><p>Ancient temples, tropical islands, vibrant street food scenes, and spiritual retreats. Explore the diversity of Asia with our expertly planned itineraries.</p><h2>Americas</h2><p>From the Grand Canyon to Patagonia, the Americas offer extraordinary natural wonders, cultural richness, and adventure opportunities at every turn.</p><h2>Africa & Middle East</h2><p>Safari adventures, desert experiences, ancient civilizations, and pristine beaches. Our Africa and Middle East packages deliver once-in-a-lifetime moments.</p>'],
            ['slug' => 'packages', 'title' => 'Travel Packages', 'excerpt' => 'All-inclusive travel packages designed for unforgettable journeys.', 'content' => '<p>Our travel packages take the stress out of planning while maximizing your experience. Each itinerary is crafted by destination specialists and includes hand-picked accommodations.</p><h2>Adventure Package</h2><p>For thrill-seekers: trekking, diving, rafting, and wildlife encounters. Includes expert guides, quality equipment, and accommodations near activity sites.</p><h2>Cultural Immersion</h2><p>Go beyond sightseeing with cooking classes, artisan workshops, home-stays, and guided heritage walks. These packages connect you with local culture in meaningful ways.</p><h2>Luxury Escape</h2><p>Five-star hotels, private transfers, exclusive dining, and VIP access. Our luxury packages deliver uncompromising comfort and personalized service throughout.</p><ul><li>All packages include travel insurance</li><li>24/7 support during your trip</li><li>Flexible booking and cancellation policies</li><li>Custom itinerary options available</li></ul>'],
            ['slug' => 'about', 'title' => 'About Us', 'excerpt' => 'Learn about our passion for travel and commitment to experiences.', 'content' => '<p>We believe travel has the power to transform perspectives, build connections, and create memories that last a lifetime. That\'s why we pour our passion into every itinerary we create.</p><h2>Our Story</h2><p>Founded by avid travelers who were frustrated with cookie-cutter packages, we set out to build a travel company that values authenticity, sustainability, and genuine human connection.</p><h2>Why Travel With Us</h2><ul><li><strong>Local expertise:</strong> Destination specialists who\'ve lived in the regions they plan</li><li><strong>Sustainable tourism:</strong> Partnerships with eco-conscious operators and community projects</li><li><strong>Flexibility:</strong> Every itinerary can be customized to your interests and pace</li></ul><h2>Awards & Affiliations</h2><p>Proud members of ASTA and recipients of multiple travel industry awards for service excellence and sustainable tourism practices.</p>'],
        ];

        // Ecommerce group
        $ecommercePages = [
            ['slug' => 'shop', 'title' => 'Shop', 'excerpt' => 'Browse our curated collection of products.', 'content' => '<p>Discover our carefully curated selection of products, chosen for quality, design, and value. From everyday essentials to special finds, there\'s something for everyone.</p><h2>New Arrivals</h2><p>Be the first to shop our latest additions. New products are added weekly, sourced from independent makers and trusted brands.</p><h2>Best Sellers</h2><p>See what our community loves most. Our best sellers are top-rated by customers and represent exceptional quality and value.</p><h2>Gift Guide</h2><p>Finding the perfect gift is easy with our curated collections organized by occasion, recipient, and price range. Gift wrapping available at checkout.</p><ul><li>Free shipping on orders over $75</li><li>30-day hassle-free returns</li><li>Secure checkout with multiple payment options</li><li>Loyalty rewards on every purchase</li></ul>'],
            ['slug' => 'about', 'title' => 'Our Story', 'excerpt' => 'The story behind our brand and what drives us.', 'content' => '<p>Every product in our store has a story, and so do we. What started as a passion project has grown into a beloved destination for discerning shoppers who value quality and authenticity.</p><h2>Our Mission</h2><p>We exist to connect people with products that enhance their lives. We prioritize sustainable sourcing, ethical manufacturing, and building long-term relationships with our makers.</p><h2>Quality Promise</h2><p>Every product is personally tested and vetted before it reaches our shelves. We stand behind everything we sell with a satisfaction guarantee.</p><h2>Community</h2><p>Our customers aren\'t just shoppers — they\'re part of a community that values conscious consumption, great design, and supporting small businesses.</p>'],
            ['slug' => 'returns', 'title' => 'Returns & Shipping', 'excerpt' => 'Our policies for shipping, returns, and exchanges.', 'content' => '<p>We want you to love every purchase. If something isn\'t right, our hassle-free return policy makes it easy to exchange or get a refund.</p><h2>Shipping</h2><ul><li>Standard shipping (5-7 business days): $5.99</li><li>Express shipping (2-3 business days): $12.99</li><li>Free shipping on orders over $75</li><li>International shipping available to 40+ countries</li></ul><h2>Returns & Exchanges</h2><p>Not happy with your order? Return any unused item within 30 days for a full refund or exchange. Simply initiate a return through your account or contact our support team.</p><h2>Damaged or Defective Items</h2><p>If your item arrives damaged or defective, contact us within 48 hours with photos and we\'ll send a replacement immediately at no additional cost.</p>'],
        ];

        // Nonprofit & Community group
        $nonprofitPages = [
            ['slug' => 'our-mission', 'title' => 'Our Mission', 'excerpt' => 'Learn about the cause that drives everything we do.', 'content' => '<p>We are dedicated to creating lasting, positive change in our community. Every program we run, every dollar raised, and every volunteer hour contributed moves us closer to a world where everyone has the opportunity to thrive.</p><h2>What We Do</h2><p>Our programs address the root causes of inequality through education, economic empowerment, and community building. We work directly with underserved populations to deliver measurable impact.</p><h2>Our Impact</h2><ul><li>10,000+ individuals served annually</li><li>85% of program participants report improved outcomes</li><li>$2M+ in community grants distributed</li><li>50+ community partnerships</li></ul><h2>Transparency</h2><p>We believe in full transparency. Our annual reports, financial statements, and impact metrics are available to the public. We are proud to maintain a 4-star rating on Charity Navigator.</p>'],
            ['slug' => 'get-involved', 'title' => 'Get Involved', 'excerpt' => 'Discover ways to volunteer, donate, and make a difference.', 'content' => '<p>Change happens when people come together. Whether you can give time, talent, or treasure, there\'s a meaningful way for you to contribute to our mission.</p><h2>Volunteer</h2><p>Join our team of dedicated volunteers who power our programs. Opportunities range from weekly mentoring sessions to one-time event support. No experience necessary — just a willingness to help.</p><h2>Donate</h2><p>Your financial contribution directly funds our programs. Every dollar counts: $25 provides school supplies for one child, $100 funds a week of job training, $500 sponsors a family\'s housing assistance.</p><h2>Corporate Partnerships</h2><p>We partner with businesses that share our values. Corporate partnerships include employee volunteer days, matching gift programs, cause-related marketing, and event sponsorships.</p><h2>Fundraise</h2><p>Start your own fundraising campaign on our platform. Host an event, run a marathon, or celebrate a birthday — all while raising money for a cause that matters.</p>'],
            ['slug' => 'about', 'title' => 'About Us', 'excerpt' => 'Our story, values, and the team behind the mission.', 'content' => '<p>Founded in 2010 by a group of community leaders who saw a need and decided to act, we have grown from a small grassroots initiative into a recognized nonprofit serving thousands of people each year.</p><h2>Our Story</h2><p>What began as weekend tutoring sessions in a church basement has evolved into a multi-program organization with a dedicated staff, hundreds of volunteers, and partnerships with schools, businesses, and government agencies.</p><h2>Our Values</h2><ul><li><strong>Compassion:</strong> We lead with empathy in every interaction</li><li><strong>Integrity:</strong> We are accountable stewards of every resource</li><li><strong>Collaboration:</strong> We believe in the power of working together</li><li><strong>Impact:</strong> We measure success by the lives we change</li></ul><h2>Leadership</h2><p>Our board of directors and executive team bring diverse expertise in education, social work, finance, and nonprofit management. Together, they guide our strategy and ensure we remain true to our mission.</p>'],
        ];

        // Automotive group
        $automotivePages = [
            ['slug' => 'inventory', 'title' => 'Our Inventory', 'excerpt' => 'Browse our selection of new and pre-owned vehicles.', 'content' => '<p>Explore our extensive inventory of quality vehicles. Every car on our lot has been inspected, certified, and priced competitively to give you the best value.</p><h2>New Vehicles</h2><p>The latest models with full manufacturer warranties, cutting-edge technology, and the newest safety features. Browse by make, model, or body style to find your perfect match.</p><h2>Pre-Owned & Certified</h2><p>Our certified pre-owned vehicles undergo a rigorous multi-point inspection and come with extended warranty coverage. Quality you can trust at prices that make sense.</p><h2>Special Offers</h2><p>Check our current promotions including low-interest financing, trade-in bonuses, and seasonal deals. We update our specials weekly to bring you the best value.</p><ul><li>Competitive financing from 2.9% APR</li><li>Trade-in valuations same day</li><li>Home delivery available</li><li>Full vehicle history reports</li></ul>'],
            ['slug' => 'services', 'title' => 'Service Center', 'excerpt' => 'Expert maintenance and repair services for all makes and models.', 'content' => '<p>Our factory-trained technicians use genuine parts and state-of-the-art equipment to keep your vehicle running at its best. Quality service at competitive prices.</p><h2>Routine Maintenance</h2><p>Oil changes, tire rotations, brake inspections, and multi-point checks. Stay on top of maintenance with our scheduled service reminders and online booking.</p><h2>Repairs & Diagnostics</h2><p>From engine diagnostics to transmission repair, our team handles everything. We provide detailed estimates before beginning any work and keep you informed throughout.</p><h2>Collision Center</h2><p>Body work, paint repair, and frame straightening. We work with all major insurance companies and offer loaner vehicles while yours is being repaired.</p>'],
            ['slug' => 'about', 'title' => 'About Us', 'excerpt' => 'A trusted name in automotive sales and service.', 'content' => '<p>For over two decades, we\'ve been helping drivers find the right vehicle at the right price. Our reputation is built on honesty, fair dealing, and genuine care for our customers.</p><h2>Our Promise</h2><p>No pressure, no gimmicks. We believe the best salesmanship is simply listening to what you need and matching you with the right vehicle. Our transparent pricing means the price you see is the price you pay.</p><h2>Community Roots</h2><p>We\'re proud to be locally owned and operated. Our team lives, works, and raises families in this community, and we\'re committed to giving back through sponsorships, charity drives, and local partnerships.</p><h2>Recognition</h2><ul><li>Dealer of the Year — 5 consecutive years</li><li>A+ BBB rating</li><li>Google 4.8★ average from 1,200+ reviews</li></ul>'],
        ];

        // Content & Publishing group (blog/personal/magazine/news/podcast/newsletter/author/influencer)
        $contentPages = [
            ['slug' => 'about', 'title' => 'About the Author', 'excerpt' => 'The story behind the words — who writes this and why.', 'content' => '<p>Every publication starts with a voice, a perspective, and a story worth telling. This is mine.</p><p>I\'ve been writing professionally for over a decade, covering topics that sit at the intersection of technology, culture, and everyday life. What began as a personal journal has evolved into a platform read by thousands of people each month.</p><h2>My Background</h2><p>With a background in journalism and a lifelong curiosity about how the world works, I approach every piece with research rigor and a conversational tone. I\'ve contributed to major publications and spoken at industry conferences, but this space remains my creative home.</p><h2>What You\'ll Find Here</h2><ul><li><strong>In-depth features:</strong> Long-form explorations of topics that matter</li><li><strong>Opinion pieces:</strong> Honest takes on current events and trends</li><li><strong>Guides & tutorials:</strong> Practical how-tos based on real experience</li><li><strong>Interviews:</strong> Conversations with interesting people</li></ul><h2>Get in Touch</h2><p>I love hearing from readers. Whether you have a story tip, a question, or just want to say hello — my inbox is always open. You can also find me on social media or subscribe to the newsletter for weekly updates.</p>'],
            ['slug' => 'archive', 'title' => 'Archive', 'excerpt' => 'Browse the complete collection of published articles and essays.', 'content' => '<p>Welcome to the archive — a complete index of everything published on this site, organized chronologically and by topic for easy browsing.</p><h2>Browse by Category</h2><p>Whether you\'re interested in technology deep-dives, cultural commentary, creative writing, or practical guides, use the category filters to find exactly what you\'re looking for.</p><h2>Most Popular</h2><p>Looking for a starting point? Our most-read pieces represent the best of what this publication has to offer — the stories that resonated most with readers and sparked the most conversation.</p><h2>Newsletter Archive</h2><p>Missed a newsletter edition? Every issue is archived here so you can catch up on past dispatches, curated links, and exclusive insights that don\'t appear anywhere else.</p>'],
            ['slug' => 'contact', 'title' => 'Contact & Collaborate', 'excerpt' => 'Reach out for collaborations, guest posts, sponsorships, or just to say hi.', 'content' => '<p>This publication thrives on connection. Whether you\'re a fellow writer, a brand seeking partnership, or a reader with something to share, I\'d love to hear from you.</p><h2>For Readers</h2><p>Questions, corrections, story tips, or just want to start a conversation? Drop me an email at hello@example.com. I read every message and respond to as many as I can.</p><h2>For Collaborators</h2><p>I\'m open to guest posts, podcast appearances, speaking engagements, and cross-promotions with aligned publications. Send your pitch with a brief bio and relevant links.</p><h2>For Sponsors</h2><p>This publication reaches an engaged audience of professionals and curious minds. If your brand aligns with our values, let\'s explore sponsorship opportunities that feel authentic, not intrusive.</p><ul><li>Email: hello@example.com</li><li>Twitter/X: @example</li><li>Response time: 1-3 business days</li></ul>'],
        ];

        // Spa & Wellness group (spa/yoga — split from fitness for unique content)
        $spaWellnessPages = [
            ['slug' => 'treatments', 'title' => 'Our Treatments', 'excerpt' => 'A full menu of relaxation, rejuvenation, and holistic wellness treatments.', 'content' => '<p>Step into a world of tranquility and restoration. Our treatment menu is designed to nurture body, mind, and spirit through time-honoured techniques and modern wellness innovations.</p><h2>Massage Therapy</h2><p>From deep tissue and Swedish to hot stone and aromatherapy, our licensed massage therapists customize every session to address your specific needs — whether that\'s stress relief, pain management, or pure relaxation.</p><h2>Facial & Skin Care</h2><p>Our clinical-grade facials combine advanced skincare technology with luxurious organic products. Options include anti-aging treatments, hydrating facials, LED light therapy, and customized peels for every skin type.</p><h2>Body Treatments</h2><p>Indulge in full-body wraps, salt scrubs, hydrotherapy, and detox rituals that leave your skin glowing and your mind at ease. Each treatment uses hand-selected botanical ingredients.</p><h2>Holistic Wellness</h2><ul><li>Reiki energy healing</li><li>Reflexology</li><li>Meditation and breathwork sessions</li><li>Couples\' treatment suites available</li></ul>'],
            ['slug' => 'packages', 'title' => 'Spa Packages', 'excerpt' => 'Curated packages combining multiple treatments for the ultimate experience.', 'content' => '<p>Our spa packages combine complementary treatments into seamless experiences that maximize relaxation and value. Perfect for self-care days, celebrations, and gifts.</p><h2>Serenity Escape — 2 Hours</h2><p>Full-body massage, express facial, and scalp treatment. Includes herbal tea, robe, and access to our thermal suite. The perfect introduction to our spa.</p><h2>Total Renewal — Half Day</h2><p>Body scrub, wrap treatment, signature facial, massage of your choice, and a healthy lunch from our wellness kitchen. Our most popular package.</p><h2>Ultimate Retreat — Full Day</h2><p>A full day of pampering including all treatments in Total Renewal plus manicure, pedicure, hair treatment, and a three-course wellness lunch. Arrive stressed, leave transformed.</p><h2>Gift Vouchers</h2><p>Give the gift of relaxation. Our beautifully presented gift vouchers are available for any treatment, package, or custom amount. Order online for instant delivery or visit us for a physical card.</p>'],
            ['slug' => 'about', 'title' => 'About Our Sanctuary', 'excerpt' => 'The philosophy, space, and people behind our wellness practice.', 'content' => '<p>More than a spa — we are a sanctuary for those seeking respite from the pace of modern life. Our philosophy centres on the belief that true wellness encompasses body, mind, and spirit in equal measure.</p><h2>Our Space</h2><p>Designed by wellness architects, our facility features natural materials, soft lighting, flowing water features, and temperature-controlled treatment rooms. Every sensory detail has been considered to promote deep relaxation from the moment you arrive.</p><h2>Our Practitioners</h2><p>Our team of therapists, estheticians, and wellness practitioners are among the most qualified in the region. Each holds advanced certifications and participates in continuous education to stay at the forefront of their disciplines.</p><h2>Our Commitment</h2><ul><li><strong>Clean beauty:</strong> We use only paraben-free, cruelty-free, sustainable products</li><li><strong>Personalization:</strong> Every treatment begins with a consultation</li><li><strong>Sustainability:</strong> Eco-friendly operations from energy to water to waste</li><li><strong>Inclusivity:</strong> A welcoming space for everyone, regardless of age, gender, or body type</li></ul>'],
        ];

        // Music, Film & Art group (split from creative for unique content)
        $musicArtPages = [
            ['slug' => 'portfolio', 'title' => 'Our Work', 'excerpt' => 'Explore a curated showcase of our creative projects and productions.', 'content' => '<p>Our body of work spans genres, mediums, and moods — united by a commitment to craft, emotion, and authentic expression. Browse our selected projects below.</p><h2>Music & Audio</h2><p>From studio albums and live recordings to film scores and commercial jingles, our music portfolio showcases the breadth of our sonic capabilities. Every project begins with understanding the emotion we need to evoke.</p><h2>Visual & Film</h2><p>Short films, music videos, documentaries, and art installations that push creative boundaries. Our visual work has been featured at independent festivals, galleries, and digital platforms worldwide.</p><h2>Live Performances</h2><p>There\'s nothing quite like the energy of a live show. Our performance portfolio captures the atmosphere of concerts, gallery openings, theater productions, and immersive events.</p><h2>Collaborations</h2><p>Some of our best work happens when creative minds collide. We\'ve collaborated with brands, fellow artists, arts councils, and cultural institutions to create projects that transcend individual disciplines.</p>'],
            ['slug' => 'events', 'title' => 'Events & Shows', 'excerpt' => 'Upcoming performances, exhibitions, releases, and appearances.', 'content' => '<p>Stay connected with our latest events, from intimate gallery openings and acoustic sessions to major performances and album launches. Here\'s what\'s coming up.</p><h2>Upcoming Events</h2><p>Check back regularly for our latest schedule. We announce new events as they\'re confirmed, often with early-access tickets for our mailing list subscribers.</p><h2>Past Events</h2><p>Missed a show? Our past events archive includes photos, videos, set lists, and reviews from previous performances and exhibitions, so you can experience the highlights even if you weren\'t there.</p><h2>Private Bookings</h2><p>Available for private events, corporate entertainment, gallery commissions, and custom projects. Whether you need a solo acoustic set, a full band, or a curated art installation, we bring creativity and professionalism to every booking.</p><ul><li>Corporate events and brand activations</li><li>Private parties and celebrations</li><li>Gallery and museum installations</li><li>Festival and conference appearances</li></ul>'],
            ['slug' => 'about', 'title' => 'About', 'excerpt' => 'The story, influences, and creative philosophy behind what we do.', 'content' => '<p>Art doesn\'t exist in a vacuum. It\'s shaped by experiences, influences, struggles, and the relentless desire to express something that words alone cannot capture.</p><h2>The Story</h2><p>What started in a cramped studio with second-hand equipment has grown into a creative practice recognized for its authenticity and emotional depth. The journey hasn\'t been linear — it\'s been messy, exhilarating, and entirely worth it.</p><h2>Influences & Philosophy</h2><p>We draw inspiration from everywhere: street art in Tokyo, jazz clubs in New Orleans, brutalist architecture in London, and the quiet beauty of rural landscapes. Our creative philosophy is simple — make work that moves people.</p><h2>Press & Recognition</h2><ul><li>Featured in CreativeBoom, It\'s Nice That, and Dazed</li><li>Official selection at three international film festivals</li><li>Arts Council funded project in 2024</li><li>Collaborative residency at the Institute of Contemporary Art</li></ul>'],
        ];

        // Architecture & Interior group (split from creative)
        $architecturePages = [
            ['slug' => 'projects', 'title' => 'Projects', 'excerpt' => 'Award-winning architectural and interior design projects.', 'content' => '<p>Our portfolio represents decades of collaborative design thinking — from concept sketches to completed spaces that transform how people live, work, and interact.</p><h2>Residential</h2><p>Homes that reflect the people who inhabit them. Our residential projects range from compact urban apartments to expansive countryside estates, each designed with meticulous attention to light, flow, and material honesty.</p><h2>Commercial & Hospitality</h2><p>Workplaces, hotels, restaurants, and retail spaces designed to enhance human experience and business performance. We approach commercial projects with the same care and creativity we bring to homes.</p><h2>Public & Cultural</h2><p>Libraries, galleries, community centres, and civic buildings that serve the public good. These projects carry a special responsibility — to create spaces that belong to everyone and stand the test of time.</p><h2>Awards</h2><ul><li>RIBA National Award — Riverside Cultural Centre</li><li>AIA Honor Award — Elm Street Residences</li><li>World Architecture Festival shortlist — The Canopy Pavilion</li></ul>'],
            ['slug' => 'approach', 'title' => 'Our Approach', 'excerpt' => 'How we design — from first conversation to built reality.', 'content' => '<p>Great architecture begins with listening. Before we draw a single line, we invest deeply in understanding the site, the brief, the budget, and most importantly — the people who will use the space.</p><h2>1. Discovery & Brief</h2><p>We begin with conversations, site visits, and research. Understanding context — physical, cultural, environmental — is the foundation of every design decision that follows.</p><h2>2. Concept Design</h2><p>Ideas take shape through sketches, physical models, and 3D visualizations. We present multiple directions, inviting feedback and collaboration before committing to a design path.</p><h2>3. Technical Design & Planning</h2><p>Detailed drawings, structural engineering, planning applications, and material specifications. This phase turns creative vision into buildable reality, navigating regulations and practical constraints.</p><h2>4. Construction & Delivery</h2><p>We remain involved through construction, making regular site visits to ensure the design intent is faithfully executed. The details matter — we don\'t leave them to chance.</p>'],
            ['slug' => 'about', 'title' => 'About the Studio', 'excerpt' => 'Our story, values, and the people behind the designs.', 'content' => '<p>Founded on the belief that thoughtful design improves lives, our studio brings together architects, interior designers, and landscape architects in a collaborative practice that values craft over trend.</p><h2>Our Values</h2><ul><li><strong>Context-driven:</strong> Every project responds to its specific place and purpose</li><li><strong>Sustainability:</strong> Environmental responsibility is embedded in every design decision</li><li><strong>Craft:</strong> We celebrate material honesty and construction quality</li><li><strong>Collaboration:</strong> The best buildings emerge from genuine partnership</li></ul><h2>The Team</h2><p>Our team of 15 includes architects, interior designers, a dedicated sustainability consultant, and project managers. We\'re united by a shared passion for design that is both beautiful and meaningful.</p><h2>Recognition</h2><p>Our work has been published in Dezeen, ArchDaily, Wallpaper*, and Architectural Digest. We are members of the Royal Institute of British Architects and the American Institute of Architects.</p>'],
        ];

        // Tattoo & Body Art group (split from creative)
        $tattooPages = [
            ['slug' => 'gallery', 'title' => 'Gallery', 'excerpt' => 'Browse our portfolio of custom tattoos, cover-ups, and body art.', 'content' => '<p>Every piece in our gallery represents a collaboration between artist and client — a permanent mark that tells a personal story. Browse our work by style and artist below.</p><h2>Custom Designs</h2><p>Original artwork created specifically for you. Our artists work from your ideas, references, and stories to design one-of-a-kind pieces that you\'ll be proud to wear forever.</p><h2>Cover-Ups & Reworks</h2><p>Unhappy with an existing tattoo? Our cover-up specialists can transform old, faded, or unwanted tattoos into beautiful new artwork. We handle even the most challenging cover-up projects.</p><h2>Styles We Specialize In</h2><ul><li><strong>Black & Grey Realism:</strong> Photorealistic portraits, nature, and fine detail work</li><li><strong>Traditional & Neo-Traditional:</strong> Bold lines, rich colours, timeless designs</li><li><strong>Japanese:</strong> Full sleeves, back pieces, and traditional motifs</li><li><strong>Geometric & Dotwork:</strong> Precision patterns and sacred geometry</li><li><strong>Watercolour:</strong> Flowing colour work with painterly effects</li></ul>'],
            ['slug' => 'artists', 'title' => 'Our Artists', 'excerpt' => 'Meet the talented artists behind our studio.', 'content' => '<p>Our studio is home to a carefully selected team of tattoo artists, each bringing their own specialization and artistic vision. Together, they cover virtually every style and technique.</p><h2>How We Work</h2><p>Every tattoo begins with a consultation. You\'ll be matched with the artist whose style best fits your vision. They\'ll work with you through design development — discussing placement, size, flow, and details — before any ink touches skin.</p><h2>Guest Artists</h2><p>We regularly host acclaimed guest artists from around the world for limited residencies. Follow us on social media to stay updated on guest spots and booking windows — they fill up fast.</p><h2>Apprentice Program</h2><p>We\'re committed to nurturing the next generation of tattoo artists. Our apprentice program provides structured training under experienced mentors, ensuring high standards are maintained and passed on.</p>'],
            ['slug' => 'booking', 'title' => 'Book a Session', 'excerpt' => 'How to book your tattoo — consultation, deposits, and what to expect.', 'content' => '<p>Ready to get inked? Here\'s everything you need to know about booking your session with us, from initial enquiry to aftercare.</p><h2>How to Book</h2><p>Fill out our booking form with your idea, preferred style, desired placement, approximate size, and any reference images. We\'ll review your request and match you with the most suitable artist within 48 hours.</p><h2>Deposits & Pricing</h2><p>A non-refundable deposit (deducted from your final price) secures your appointment. Pricing varies by size, detail, and placement — your artist will provide a clear estimate during your consultation.</p><h2>What to Expect</h2><ul><li>Consultation: In-person or video call to finalize design</li><li>Design approval: You\'ll see and approve the design before your appointment</li><li>Session day: Arrive well-rested, hydrated, and fed</li><li>Aftercare: Detailed instructions provided; healing takes 2-4 weeks</li></ul><h2>Studio Policies</h2><p>We maintain the highest hygiene standards. All equipment is single-use or autoclave-sterilized. We are fully licensed and health-department inspected. Minimum age: 18 with valid ID.</p>'],
        ];

        // Landing / Coming Soon / Other specialty group
        $specialtyPages = [
            ['slug' => 'about', 'title' => 'About This Project', 'excerpt' => 'Learn about what we\'re building and why it matters.', 'content' => '<p>Great things take time. We\'re building something we believe will make a real difference, and we want you to be part of the journey from the very beginning.</p><h2>The Vision</h2><p>We identified a gap that needed filling — a problem experienced by millions of people every day that existing solutions fail to address properly. Our approach is different because we started by listening.</p><h2>What\'s Coming</h2><p>We\'re not ready to reveal everything just yet, but here\'s what we can share: our product combines cutting-edge technology with human-centred design to deliver an experience that\'s powerful yet intuitive.</p><h2>The Team</h2><p>Our founding team brings together expertise in engineering, design, and domain knowledge gained from years of working on the front lines of this problem. We\'re backed by investors who share our long-term vision.</p><ul><li>Founded in 2025 by industry veterans</li><li>Currently in private beta</li><li>Public launch scheduled for Q3</li><li>Join the waitlist for early access</li></ul>'],
            ['slug' => 'features', 'title' => 'Features & Details', 'excerpt' => 'A closer look at what makes this unique.', 'content' => '<p>Every feature we build starts with a real user need. We don\'t add complexity for the sake of it — we solve problems elegantly and let you get back to what matters.</p><h2>Core Platform</h2><p>The foundation of everything. A fast, reliable, secure platform built with modern architecture that scales from individual users to enterprise deployments without breaking a sweat.</p><h2>Smart Workflows</h2><p>Intelligent automation that learns from your patterns and suggests optimizations. Reduce manual work, eliminate errors, and free your team to focus on high-value activities.</p><h2>Insights & Analytics</h2><p>Real-time dashboards, custom reports, and trend analysis that turn raw data into actionable intelligence. Know what\'s working, what isn\'t, and what to do next.</p><h2>Security & Compliance</h2><ul><li>End-to-end encryption</li><li>SOC 2 Type II certified</li><li>GDPR and CCPA compliant</li><li>Regular third-party security audits</li></ul>'],
            ['slug' => 'contact', 'title' => 'Get in Touch', 'excerpt' => 'Questions, partnerships, or early access — we want to hear from you.', 'content' => '<p>We\'re still early, which means every conversation matters. Whether you\'re a potential user, investor, partner, or just curious — we\'d love to connect.</p><h2>For Early Access</h2><p>Join our waitlist to be among the first to experience the platform. Early adopters receive founding-member pricing and direct input into our product roadmap.</p><h2>For Partnerships</h2><p>We\'re actively seeking strategic partners in complementary spaces. If you see an opportunity for collaboration, let\'s explore it together.</p><h2>General Enquiries</h2><ul><li>Email: hello@example.com</li><li>Twitter/X: @example</li><li>LinkedIn: /company/example</li></ul><p>We respond to all enquiries within one business day. For media requests, please email press@example.com with your publication and deadline.</p>'],
        ];

        // Resume / Wiki / Directory group
        $directoryPages = [
            ['slug' => 'browse', 'title' => 'Browse', 'excerpt' => 'Explore the full directory of entries, resources, and listings.', 'content' => '<p>Welcome to our comprehensive directory. Browse by category, search by keyword, or explore featured entries to find exactly what you\'re looking for.</p><h2>Categories</h2><p>Our directory is organized into intuitive categories that make navigation effortless. Each listing includes detailed descriptions, contact information, ratings, and verified reviews.</p><h2>Featured Listings</h2><p>Our featured entries are highlighted for their exceptional quality, community engagement, or recent notable achievements. Featured status is based on merit and user feedback, never paid placement.</p><h2>How to Use This Resource</h2><ul><li><strong>Search:</strong> Use the search bar for specific queries</li><li><strong>Filter:</strong> Narrow results by category, location, rating, or date</li><li><strong>Save:</strong> Bookmark listings for quick access later</li><li><strong>Contribute:</strong> Submit your own listing or suggest edits to existing ones</li></ul>'],
            ['slug' => 'about', 'title' => 'About', 'excerpt' => 'The mission behind this resource and how it serves the community.', 'content' => '<p>This resource exists because information should be accessible, accurate, and organized. We built this platform to serve as a reliable reference point for our community.</p><h2>Our Mission</h2><p>To create the most comprehensive, accurate, and user-friendly resource in our space. Every entry is verified, every review is moderated, and every update is tracked to maintain quality and trust.</p><h2>How It Works</h2><p>Our platform combines community contributions with editorial oversight. Users can submit and update listings, which are reviewed by our editorial team for accuracy and completeness before publication.</p><h2>By the Numbers</h2><ul><li>5,000+ verified listings</li><li>50,000+ monthly visitors</li><li>98% accuracy rate (independently audited)</li><li>Updated daily by our editorial team</li></ul>'],
            ['slug' => 'submit', 'title' => 'Submit a Listing', 'excerpt' => 'Add your entry to our directory or suggest an update.', 'content' => '<p>Our directory grows and improves through community contribution. If you\'d like to add a new listing or update an existing one, here\'s how to get started.</p><h2>Submission Guidelines</h2><p>Please provide accurate, complete information. Listings that include descriptions, contact details, and supporting links are approved faster and rank higher in our directory.</p><h2>What We Accept</h2><ul><li>New listings with verifiable information</li><li>Updates or corrections to existing entries</li><li>Photos and supporting media</li><li>User reviews with constructive feedback</li></ul><h2>Review Process</h2><p>All submissions are reviewed within 3 business days. We verify the information provided and may contact you for clarification. Once approved, your listing goes live and is indexed for search.</p><h2>Contact</h2><p>Questions about submissions? Email submissions@example.com or use the contact form below.</p>'],
        ];

        // Memorial / Tribute group
        $memorialPages = [
            ['slug' => 'tribute', 'title' => 'A Life Remembered', 'excerpt' => 'Celebrating a remarkable life and the memories that endure.', 'content' => '<p>Some lives leave an imprint so deep that no amount of time can diminish their impact. This page is dedicated to honoring and preserving the memory of someone who touched countless hearts.</p><h2>A Beautiful Life</h2><p>Born with an innate warmth that drew people in, they lived with purpose, generosity, and an infectious joy that brightened every room they entered. Their legacy lives on in the lives they changed.</p><h2>Milestones & Memories</h2><p>From early years filled with curiosity and laughter to a career marked by dedication and achievement, every chapter of their life was lived fully and authentically.</p><h2>In Their Own Words</h2><p><em>"The measure of a life is not its length, but the love it generates and the difference it makes."</em></p><p>These words guided everything they did — from raising a family with boundless love to serving the community with quiet humility.</p>'],
            ['slug' => 'memories', 'title' => 'Share a Memory', 'excerpt' => 'Contribute your stories, photos, and messages of remembrance.', 'content' => '<p>Memories are the threads that keep us connected to those we\'ve lost. We invite friends, family, and all who were touched by their life to share their stories here.</p><h2>Share Your Story</h2><p>Whether it\'s a funny anecdote, a moment of kindness, or a lesson they taught you — every memory matters. Use the form below to submit your contribution, along with any photos you\'d like to share.</p><h2>Photo Gallery</h2><p>Browse the growing collection of photos shared by family and friends. From childhood snapshots to recent celebrations, these images capture the spirit of a life well-lived.</p><h2>Guest Book</h2><p>Leave a message of comfort, gratitude, or remembrance. Your words provide solace to the family and create a lasting testament to the lives they touched.</p>'],
            ['slug' => 'donations', 'title' => 'Memorial Donations', 'excerpt' => 'Honor their memory through a charitable contribution.', 'content' => '<p>In lieu of flowers, the family welcomes donations to causes that were close to their heart. Every contribution helps continue the work they believed in.</p><h2>Supported Charities</h2><p>The following organizations reflect the values and passions that defined their life. Donations in their name support ongoing work in education, community development, and healthcare access.</p><h2>How to Donate</h2><ul><li>Online donations can be made through the links provided for each charity</li><li>Cheques can be sent to the family address with "Memorial Fund" in the memo</li><li>Workplace matching gifts are welcome and can double your impact</li></ul><h2>Thank You</h2><p>The family is deeply grateful for every expression of love and generosity. A formal acknowledgment will be sent to all donors. Your kindness during this time means more than words can express.</p>'],
        ];

        // Government & Public group
        $governmentPages = [
            ['slug' => 'services', 'title' => 'Services', 'excerpt' => 'Access public services, permits, and citizen resources.', 'content' => '<p>We are committed to providing efficient, accessible services to all residents. Find information about permits, licenses, public programs, and civic resources below.</p><h2>Permits & Licenses</h2><p>Apply for building permits, business licenses, parking permits, and event authorizations. Many applications can now be submitted and tracked online through our citizen portal.</p><h2>Public Programs</h2><p>Access information about community programs including recreation activities, senior services, youth development, public health initiatives, and housing assistance.</p><h2>Payments & Records</h2><p>Pay property taxes, utility bills, and fines online. Request copies of vital records, property documents, and public records through our secure portal.</p><ul><li>Online services available 24/7</li><li>In-person hours: Monday–Friday, 8:30 AM – 4:30 PM</li><li>Translation services available upon request</li></ul>'],
            ['slug' => 'about', 'title' => 'About', 'excerpt' => 'Learn about our mission to serve the public interest.', 'content' => '<p>Serving the public interest with integrity, transparency, and dedication. Our organization is committed to improving the quality of life for every member of our community.</p><h2>Our Mission</h2><p>To provide responsive, efficient, and equitable services that meet the needs of all residents. We are accountable to the public we serve and strive for excellence in everything we do.</p><h2>Leadership</h2><p>Our leadership team is committed to open government and citizen engagement. Regular town halls, public comment periods, and advisory boards ensure community voices are heard.</p><h2>Contact</h2><p>Main Office: 100 Civic Center Drive<br>Phone: (555) 000-1234<br>Emergency Services: 911</p>'],
            ['slug' => 'news', 'title' => 'News & Announcements', 'excerpt' => 'Stay informed with the latest public notices and community news.', 'content' => '<p>Stay informed about community developments, policy changes, and upcoming events. We are committed to keeping citizens updated with timely, accurate information.</p><h2>Public Notices</h2><p>Official announcements regarding zoning changes, public hearings, road closures, and emergency alerts. Subscribe to our notification system for real-time updates.</p><h2>Community Calendar</h2><p>View upcoming public meetings, community events, volunteer opportunities, and seasonal programs organized by department.</p><h2>Press Releases</h2><p>Read official statements and press releases from our communications office. Media inquiries should be directed to our press office at press@example.gov.</p>'],
        ];

        // Map industries to their page templates
        $templates = [
            // Food & Hospitality
            'restaurant' => $foodPages,
            'cafe' => $foodPages,
            'bar' => $foodPages,
            'bakery' => $foodPages,
            'foodtruck' => $foodPages,
            'catering' => $foodPages,
            'winery' => $foodPages,

            // Hotels
            'hotel' => $hotelPages,
            'resort' => $hotelPages,

            // Tech
            'saas' => $techPages,
            'startup' => $techPages,
            'ai' => $techPages,
            'app' => $techPages,
            'crypto' => $techPages,
            'cybersecurity' => $techPages,
            'devtools' => $techPages,
            'hosting' => $techPages,
            'itsupport' => $techPages,
            'gamedev' => $techPages,

            // Law & Finance
            'law' => $lawFinancePages,
            'finance' => $lawFinancePages,
            'consulting' => $lawFinancePages,
            'accounting' => $lawFinancePages,
            'insurance' => $lawFinancePages,
            'recruiting' => $lawFinancePages,
            'translation' => $lawFinancePages,

            // Medical & Health
            'medical' => $medicalPages,
            'dental' => $medicalPages,
            'veterinary' => $medicalPages,
            'therapy' => $medicalPages,
            'mentalhealth' => $medicalPages,
            'nutrition' => $medicalPages,
            'physiotherapy' => $medicalPages,
            'pharmacy' => $medicalPages,

            // Construction & Trades
            'construction' => $constructionPages,
            'plumbing' => $constructionPages,
            'electrical' => $constructionPages,
            'hvac' => $constructionPages,
            'roofing' => $constructionPages,
            'painting' => $constructionPages,
            'landscaping' => $constructionPages,
            'cleaning' => $constructionPages,
            'moving' => $constructionPages,
            'handyman' => $constructionPages,
            'solar' => $constructionPages,

            // Creative & Portfolio
            'portfolio' => $creativePages,
            'design' => $creativePages,
            'photography' => $creativePages,
            'videography' => $creativePages,
            'animation' => $creativePages,
            'agency' => $creativePages,
            'marketing' => $creativePages,

            // Music, Film & Art
            'music' => $musicArtPages,
            'film' => $musicArtPages,
            'art' => $musicArtPages,

            // Architecture & Interior
            'architecture' => $architecturePages,
            'interior' => $architecturePages,

            // Tattoo & Body Art
            'tattoo' => $tattooPages,

            // Content & Publishing
            'blog' => $contentPages,
            'personal' => $contentPages,
            'magazine' => $contentPages,
            'news' => $contentPages,
            'podcast' => $contentPages,
            'newsletter' => $contentPages,
            'author' => $contentPages,
            'influencer' => $contentPages,

            // Education
            'education' => $educationPages,
            'onlinecourse' => $educationPages,
            'coaching' => $educationPages,
            'tutoring' => $educationPages,
            'language' => $educationPages,
            'driving' => $educationPages,
            'childcare' => $educationPages,
            'library' => $educationPages,
            'training' => $educationPages,

            // Fitness
            'fitness' => $fitnessPages,

            // Spa & Wellness
            'yoga' => $spaWellnessPages,
            'spa' => $spaWellnessPages,

            // Real Estate
            'realestate' => $realestatePages,
            'propertymanagement' => $realestatePages,

            // Events
            'events' => $eventsPages,
            'wedding' => $eventsPages,
            'party' => $eventsPages,
            'venue' => $eventsPages,
            'theater' => $eventsPages,
            'cinema' => $eventsPages,
            'escape' => $eventsPages,
            'festival' => $eventsPages,

            // Travel
            'travel' => $travelPages,
            'tourism' => $travelPages,
            'camping' => $travelPages,
            'skiing' => $travelPages,
            'diving' => $travelPages,
            'golf' => $travelPages,
            'marina' => $travelPages,

            // Ecommerce & Retail
            'ecommerce' => $ecommercePages,
            'fashion' => $ecommercePages,
            'jewelry' => $ecommercePages,
            'beauty' => $ecommercePages,
            'furniture' => $ecommercePages,
            'electronics' => $ecommercePages,
            'bookshop' => $ecommercePages,
            'grocery' => $ecommercePages,
            'pets' => $ecommercePages,
            'florist' => $ecommercePages,
            'marketplace' => $ecommercePages,

            // Nonprofit & Community
            'nonprofit' => $nonprofitPages,
            'church' => $nonprofitPages,
            'volunteer' => $nonprofitPages,
            'political' => $nonprofitPages,
            'community' => $nonprofitPages,
            'association' => $nonprofitPages,

            // Automotive
            'automotive' => $automotivePages,
            'mechanic' => $automotivePages,
            'carwash' => $automotivePages,
            'taxi' => $automotivePages,
            'trucking' => $automotivePages,
            'motorcycle' => $automotivePages,
            'boating' => $automotivePages,

            // Sports & Landing
            'sports' => $fitnessPages,
            'saas-landing' => $techPages,

            // Government
            'government' => $governmentPages,
            'police' => $governmentPages,
            'military' => $governmentPages,
            'embassy' => $governmentPages,

            // Directory / Wiki / Resume
            'resume' => $directoryPages,
            'wiki' => $directoryPages,
            'directory' => $directoryPages,

            // Landing / Coming Soon / Other
            'landing' => $specialtyPages,
            'comingsoon' => $specialtyPages,
            'other' => $specialtyPages,

            // Memorial
            'memorial' => $memorialPages,
        ];

        // Generic pages for industries without specific templates
        $generic = [
            ['slug' => 'about', 'title' => 'About', 'excerpt' => "Learn more about {$themeName} and our team.", 'content' => "<p>Welcome to {$themeName}. We're passionate about what we do and committed to delivering excellence in everything we undertake.</p><p>Our team brings together years of experience and a shared vision for quality and innovation. We believe in building lasting relationships through trust, transparency, and exceptional service.</p><h2>Our Story</h2><p>What started as a simple idea has grown into something we\'re truly proud of. Every step of our journey has been guided by a commitment to our clients and our craft.</p><h2>Why Choose Us</h2><ul><li>Experienced, dedicated team</li><li>Client-focused approach</li><li>Proven track record of results</li><li>Transparent communication</li></ul>"],
            ['slug' => 'services', 'title' => 'Services', 'excerpt' => 'Explore our comprehensive range of professional services.', 'content' => '<p>We offer a comprehensive range of services tailored to your needs. Our approach combines industry expertise with a deep understanding of your unique requirements.</p><h2>Consultation</h2><p>Expert advice to help you achieve your goals. We start every engagement with a thorough understanding of your situation before recommending solutions.</p><h2>Custom Solutions</h2><p>Every client is different, and we design our solutions accordingly. From strategy to implementation, we deliver work that\'s tailored to your specific challenges and objectives.</p><h2>Ongoing Support</h2><p>Our relationship doesn\'t end at delivery. We provide ongoing support, maintenance, and optimization to ensure continued success.</p>'],
            ['slug' => 'contact', 'title' => 'Contact', 'excerpt' => 'Get in touch with our team — we\'d love to hear from you.', 'content' => '<p>We\'d love to hear from you. Whether you have a question, need a quote, or want to discuss a project, our team is ready to help.</p><h2>Contact Details</h2><ul><li>Email: hello@example.com</li><li>Phone: (555) 123-4567</li><li>Address: 123 Main Street, Suite 100</li></ul><h2>Office Hours</h2><p>Monday – Friday: 9:00 AM – 5:00 PM<br>Saturday: By appointment<br>Sunday: Closed</p><h2>Send Us a Message</h2><p>Use our contact form to reach out and we\'ll get back to you within 24 hours. For urgent matters, please call us directly.</p>'],
        ];

        return $templates[$industry] ?? $generic;
    }

    /**
     * Get industry-appropriate demo articles.
     */
    private function getIndustryArticles(string $industry, string $themeName, string $lang): array
    {
        // Food & Hospitality articles
        $foodArticles = [
            ['slug' => 'seasonal-menu-update', 'title' => 'Our New Seasonal Menu is Here', 'excerpt' => 'Discover the fresh flavors of the season with our updated menu.', 'content' => '<p>We\'re excited to unveil our latest seasonal menu, featuring locally sourced ingredients at their peak freshness. Every dish has been carefully developed to highlight the natural flavors of the season.</p><p>Chef has crafted new dishes that celebrate the best of what this season has to offer, from heirloom tomato salads to slow-braised lamb with root vegetables.</p>'],
            ['slug' => 'wine-pairing-evening', 'title' => 'Wine Pairing Evening This Friday', 'excerpt' => 'Join us for an exclusive evening of fine wines and curated dishes.', 'content' => '<p>Experience the art of wine pairing with our sommelier-guided evening. Each course is thoughtfully matched with wines from select vineyards across three continents.</p><p>Limited to 30 guests, this intimate event includes a five-course tasting menu with paired wines, expert commentary, and a take-home bottle of the evening\'s favourite.</p>'],
            ['slug' => 'chef-spotlight', 'title' => 'Meet Our Head Chef', 'excerpt' => 'Learn about the creative mind behind our kitchen.', 'content' => '<p>With over 15 years of culinary experience spanning Michelin-starred kitchens in London, Paris, and Tokyo, our head chef brings a global perspective to every dish.</p><p>In this interview, discover the inspirations, techniques, and philosophy that drive our kitchen\'s creative direction — from farm-to-table principles to the art of plating.</p>'],
            ['slug' => 'cooking-class-announcement', 'title' => 'Cooking Classes Now Open', 'excerpt' => 'Learn to cook restaurant-quality meals at home.', 'content' => '<p>We\'re thrilled to announce our new cooking class series. Join our chefs for hands-on sessions covering knife skills, sauce making, pastry fundamentals, and regional cuisines.</p><p>Classes run every Saturday morning and are suitable for all skill levels. Each session includes recipes, ingredients, and a meal you\'ll cook and enjoy together.</p>'],
        ];

        // Hotel & Resort articles
        $hotelArticles = [
            ['slug' => 'luxury-suite-unveiling', 'title' => 'Introducing Our Redesigned Luxury Suites', 'excerpt' => 'Step inside our newly renovated premium accommodations.', 'content' => '<p>After months of meticulous renovation, we\'re proud to unveil our redesigned luxury suites. Every detail has been reimagined to deliver the ultimate in comfort, style, and modern convenience.</p><p>The new suites feature bespoke furnishings, Italian marble bathrooms, smart room controls, and panoramic views that take your breath away.</p>'],
            ['slug' => 'wellness-retreat-launch', 'title' => 'New Wellness Retreat Program Launches This Spring', 'excerpt' => 'Rejuvenate mind and body with our curated wellness experiences.', 'content' => '<p>Our new wellness retreat program combines spa treatments, guided meditation, nutritional cuisine, and fitness activities into a transformative multi-day experience.</p><p>Designed in partnership with leading wellness practitioners, each retreat is tailored to help you disconnect from daily stress and reconnect with yourself.</p>'],
            ['slug' => 'sustainable-hospitality', 'title' => 'Our Commitment to Sustainable Hospitality', 'excerpt' => 'How we\'re reducing our environmental footprint without compromising luxury.', 'content' => '<p>Sustainability and luxury are not mutually exclusive. We\'ve implemented comprehensive green initiatives — from solar energy and water recycling to locally sourced amenities and zero single-use plastics.</p><p>Our sustainability program has earned us Green Key certification and recognition from the Global Sustainable Tourism Council. Here\'s how we\'re making a difference.</p>'],
            ['slug' => 'local-experiences-guide', 'title' => 'A Local\'s Guide to Hidden Gems Near Us', 'excerpt' => 'Discover the best local attractions, restaurants, and experiences curated by our concierge.', 'content' => '<p>Our concierge team has compiled their favourite local experiences — from secret beaches and family-run trattorias to art galleries and hiking trails that most tourists never discover.</p><p>Whether you\'re here for a weekend or a week, this insider guide will help you experience the destination like a true local.</p>'],
        ];

        // Tech articles (SaaS/Startup/AI/etc.)
        $techArticles = [
            ['slug' => 'product-update-q1', 'title' => 'Product Update: What\'s New This Quarter', 'excerpt' => 'A roundup of new features, improvements, and fixes from the past quarter.', 'content' => '<p>This quarter has been our most productive yet. We\'ve shipped 15 new features, resolved over 200 issues, and laid the groundwork for some exciting capabilities coming later this year.</p><p>Highlights include our new real-time collaboration engine, redesigned analytics dashboard, and expanded API with webhook support for custom integrations.</p>'],
            ['slug' => 'scaling-remote-teams', 'title' => 'How We Scaled Our Remote Team to 100+ People', 'excerpt' => 'Lessons learned from building a distributed team across 12 countries.', 'content' => '<p>Growing from 5 co-founders in a garage to 100+ team members across 12 countries wasn\'t easy. In this post, we share the tools, processes, and cultural principles that made it work.</p><p>From async communication norms to our unique approach to onboarding, these are the building blocks of our remote-first culture.</p>'],
            ['slug' => 'security-best-practices', 'title' => '10 Security Best Practices for Modern Teams', 'excerpt' => 'Protect your organization with these essential security measures.', 'content' => '<p>Cyber threats are evolving faster than ever. Whether you\'re a startup or an enterprise, these 10 security practices will significantly reduce your attack surface and protect your data.</p><p>From zero-trust architecture and MFA enforcement to regular penetration testing and incident response planning, this guide covers the fundamentals every team needs.</p>'],
            ['slug' => 'customer-story-spotlight', 'title' => 'Customer Story: How Acme Corp Saved 40 Hours Per Week', 'excerpt' => 'A real-world look at how our platform transforms workflows.', 'content' => '<p>When Acme Corp came to us, their team was drowning in manual processes. Spreadsheet handoffs, email chains, and disconnected tools were costing them 40+ hours per week in wasted effort.</p><p>Within three months of implementation, they\'d automated their core workflows, reduced errors by 90%, and freed their team to focus on strategic work that actually moves the needle.</p>'],
        ];

        // Law & Finance articles
        $lawFinanceArticles = [
            ['slug' => 'understanding-business-formation', 'title' => 'Choosing the Right Business Structure', 'excerpt' => 'LLC, Corporation, or Partnership? Understanding your options.', 'content' => '<p>Selecting the right business structure is one of the most important decisions you\'ll make as an entrepreneur. The choice affects your taxes, liability, fundraising ability, and day-to-day operations.</p><p>In this guide, we break down the pros and cons of LLCs, S-Corps, C-Corps, and partnerships to help you make an informed decision.</p>'],
            ['slug' => 'tax-planning-strategies', 'title' => '5 Tax Planning Strategies for Small Businesses', 'excerpt' => 'Maximize deductions and minimize your tax burden legally.', 'content' => '<p>Smart tax planning can save your business thousands of dollars each year. The key is proactive strategy — not last-minute scrambling during filing season.</p><p>From retirement account contributions and equipment depreciation to qualified business income deductions and estimated tax optimization, these five strategies are essential for every small business owner.</p>'],
            ['slug' => 'estate-planning-basics', 'title' => 'Estate Planning: Why It Matters at Every Age', 'excerpt' => 'Protecting your family\'s future starts with a solid estate plan.', 'content' => '<p>Estate planning isn\'t just for the wealthy or the elderly. Having a will, power of attorney, and healthcare directive protects your family and ensures your wishes are honored regardless of what happens.</p><p>We walk through the essential documents everyone should have, common mistakes to avoid, and when it\'s time to update your existing plan.</p>'],
            ['slug' => 'regulatory-compliance-update', 'title' => 'Key Regulatory Changes to Watch This Year', 'excerpt' => 'Stay ahead of compliance requirements with our annual regulatory roundup.', 'content' => '<p>The regulatory landscape shifts every year, and 2025 brings several significant changes that could affect your business. From data privacy regulations to employment law updates, staying compliant is essential.</p><p>Our team has summarized the most impactful changes and what they mean for businesses in our sector. Contact us if you have questions about how these changes apply to your situation.</p>'],
        ];

        // Medical & Health articles
        $medicalArticles = [
            ['slug' => 'preventive-care-importance', 'title' => 'Why Preventive Care is Your Best Investment', 'excerpt' => 'Regular screenings and check-ups can catch problems before they become serious.', 'content' => '<p>Preventive care is the cornerstone of good health. Regular screenings, vaccinations, and check-ups can detect potential issues early — when they\'re most treatable and least costly.</p><p>In this article, we outline the recommended screening schedule by age group and explain why each test matters for your long-term health.</p>'],
            ['slug' => 'mental-health-awareness', 'title' => 'Breaking the Stigma: Mental Health in the Workplace', 'excerpt' => 'Understanding and supporting mental wellness for employees and employers.', 'content' => '<p>One in four adults experiences mental health challenges each year, yet stigma continues to prevent many from seeking help. Creating supportive workplace environments is essential for employee wellbeing and productivity.</p><p>We share practical strategies for employers and employees to foster open conversations, recognize warning signs, and access appropriate support resources.</p>'],
            ['slug' => 'nutrition-and-immunity', 'title' => 'Boost Your Immune System Through Nutrition', 'excerpt' => 'Science-backed dietary choices that support your body\'s natural defenses.', 'content' => '<p>Your immune system is your body\'s frontline defense, and what you eat plays a crucial role in how well it functions. A balanced diet rich in specific nutrients can significantly enhance your immune response.</p><p>From vitamin C and zinc to probiotics and omega-3 fatty acids, we break down the key nutrients your immune system needs and the best food sources for each.</p>'],
            ['slug' => 'telehealth-guide', 'title' => 'Getting the Most Out of Your Telehealth Visit', 'excerpt' => 'Tips for a productive virtual consultation with your healthcare provider.', 'content' => '<p>Telehealth has transformed how we access healthcare, making it more convenient and accessible than ever. But virtual visits work differently from in-person appointments.</p><p>Here\'s how to prepare, what to expect, and how to make sure you get the most value from your next telehealth consultation — from setting up your technology to organizing your health questions.</p>'],
        ];

        // Construction & Trades articles
        $constructionArticles = [
            ['slug' => 'home-renovation-planning', 'title' => 'Planning Your Home Renovation: A Complete Guide', 'excerpt' => 'Essential steps to ensure your renovation stays on time and on budget.', 'content' => '<p>A successful renovation starts long before the first hammer swings. Proper planning is the difference between a dream project and a costly nightmare.</p><p>We cover everything from setting realistic budgets and choosing the right contractor to understanding permits, creating timelines, and managing the inevitable surprises that come with any renovation project.</p>'],
            ['slug' => 'energy-efficiency-upgrades', 'title' => '7 Energy Efficiency Upgrades That Pay for Themselves', 'excerpt' => 'Smart home improvements that reduce bills and increase property value.', 'content' => '<p>Energy efficiency upgrades don\'t just reduce your carbon footprint — they put money back in your pocket. Many improvements pay for themselves within 3-5 years through lower utility bills alone.</p><p>From insulation and double-glazing to smart thermostats and LED lighting, these seven upgrades deliver the best return on investment for homeowners.</p>'],
            ['slug' => 'choosing-right-contractor', 'title' => 'How to Choose the Right Contractor for Your Project', 'excerpt' => 'Red flags to avoid and green flags to look for when hiring.', 'content' => '<p>Choosing the right contractor can make or break your project. Unfortunately, the industry has its share of unreliable operators who leave homeowners with unfinished work and unexpected costs.</p><p>Learn how to vet contractors properly — from checking licenses and insurance to reading contracts carefully and recognizing the warning signs of an unreliable operator.</p>'],
            ['slug' => 'project-completion-showcase', 'title' => 'Project Spotlight: Victorian Terrace Transformation', 'excerpt' => 'Before-and-after look at a complete structural renovation.', 'content' => '<p>This Victorian terrace was in dire need of attention — damp walls, sagging floors, outdated wiring, and a kitchen from the 1970s. The owners wanted to modernize while preserving the period character.</p><p>Over 16 weeks, our team completed a full structural renovation including underpinning, rewiring, replumbing, and a rear extension — all while preserving original cornices, fireplaces, and Victorian tile floors.</p>'],
        ];

        // Creative articles (Portfolio/Design/Photography/etc.)
        $creativeArticles = [
            ['slug' => 'design-trends-this-year', 'title' => 'Design Trends Shaping the Creative Industry', 'excerpt' => 'The visual trends, tools, and approaches defining design in 2025.', 'content' => '<p>The creative landscape never stands still. From AI-assisted workflows to the resurgence of hand-crafted aesthetics, this year\'s trends reflect a fascinating tension between technology and humanity.</p><p>We explore the key visual trends, emerging tools, and philosophical shifts that are shaping how designers, photographers, and creatives approach their work this year.</p>'],
            ['slug' => 'client-collaboration-tips', 'title' => 'The Art of Client Collaboration', 'excerpt' => 'How to build productive creative partnerships that lead to great work.', 'content' => '<p>The best creative work happens when client and creative are true partners. But getting there requires trust, clear communication, and a shared understanding of what success looks like.</p><p>From writing better briefs to giving constructive feedback and managing revisions, these strategies will help you build more productive creative relationships.</p>'],
            ['slug' => 'behind-the-project', 'title' => 'Behind the Project: Brand Identity for Lumos Coffee', 'excerpt' => 'A deep dive into our creative process for a recent brand project.', 'content' => '<p>When Lumos Coffee approached us to create their brand identity from scratch, we knew we had something special. A brand built around ethically sourced beans, community gathering, and the warmth of morning light.</p><p>Follow our journey from initial discovery workshops through moodboarding, concept development, typography selection, color exploration, and the final brand system that now adorns their cafes.</p>'],
            ['slug' => 'portfolio-building-guide', 'title' => 'Building a Portfolio That Gets You Hired', 'excerpt' => 'Practical advice for curating work that attracts the right clients.', 'content' => '<p>Your portfolio is your most powerful marketing tool. Yet many creatives undermine their talent with poorly organized, unfocused portfolios that fail to tell a compelling story.</p><p>We share practical advice on selecting your strongest work, writing effective case studies, designing a portfolio that\'s easy to navigate, and positioning yourself for the clients and projects you actually want.</p>'],
        ];

        // Education articles
        $educationArticles = [
            ['slug' => 'future-of-learning', 'title' => 'The Future of Learning: Trends in Education', 'excerpt' => 'How technology and pedagogy are evolving to create better learning outcomes.', 'content' => '<p>Education is undergoing a fundamental transformation. From personalized learning pathways powered by AI to immersive virtual classrooms, the way we teach and learn is changing rapidly.</p><p>We examine the most impactful trends in education technology, pedagogical research, and institutional innovation that are shaping the next decade of learning.</p>'],
            ['slug' => 'study-tips-success', 'title' => '10 Evidence-Based Study Techniques That Actually Work', 'excerpt' => 'Ditch the highlighters — science says these methods are far more effective.', 'content' => '<p>Most popular study techniques — highlighting, re-reading, cramming — are among the least effective according to cognitive science research. The methods that actually work are often counterintuitive.</p><p>From spaced repetition and retrieval practice to interleaving and elaborative interrogation, these 10 techniques are backed by decades of research and proven to dramatically improve retention and understanding.</p>'],
            ['slug' => 'alumni-success-story', 'title' => 'Alumni Spotlight: From Student to Industry Leader', 'excerpt' => 'How one graduate turned their education into a thriving career.', 'content' => '<p>When Maria enrolled in our program three years ago, she was making a career change from retail management to data science. Today, she leads a team of analysts at a Fortune 500 company.</p><p>In this candid interview, Maria shares the challenges of career transition, how her coursework prepared her for real-world data problems, and the advice she\'d give to current students.</p>'],
            ['slug' => 'new-programs-announcement', 'title' => 'Announcing New Programs for the Coming Semester', 'excerpt' => 'Expanding our offerings to meet the demands of a changing job market.', 'content' => '<p>We\'re excited to announce four new programs launching next semester, developed in response to industry demand and student feedback. Each program is designed with employment outcomes in mind.</p><p>New offerings include Sustainable Business Management, Applied AI & Machine Learning, UX Research & Design, and Healthcare Administration. Early enrollment is now open with limited spots available.</p>'],
        ];

        // Fitness articles
        $fitnessArticles = [
            ['slug' => 'beginner-workout-guide', 'title' => 'The Complete Beginner\'s Guide to Getting Fit', 'excerpt' => 'Starting your fitness journey? Here\'s everything you need to know.', 'content' => '<p>Starting a fitness journey can feel overwhelming — so many exercises, programs, and conflicting advice. But getting fit doesn\'t have to be complicated.</p><p>This beginner\'s guide covers the fundamentals: how to structure your first workouts, the importance of progressive overload, nutrition basics, recovery, and how to stay motivated past the first month.</p>'],
            ['slug' => 'nutrition-for-performance', 'title' => 'Fueling Your Workouts: Nutrition for Peak Performance', 'excerpt' => 'What to eat before, during, and after exercise for optimal results.', 'content' => '<p>Your nutrition directly impacts your training quality and results. Whether you\'re building muscle, improving endurance, or losing fat, what you eat matters as much as how you train.</p><p>We break down pre-workout fueling, hydration strategies, post-workout recovery nutrition, and how to structure your daily intake around your training schedule for maximum results.</p>'],
            ['slug' => 'injury-prevention-tips', 'title' => '5 Injury Prevention Strategies Every Athlete Should Know', 'excerpt' => 'Stay healthy and train consistently with these proven strategies.', 'content' => '<p>The fastest way to lose progress is to get injured. Yet many athletes skip the basic practices that keep them healthy and on track.</p><p>From proper warm-up protocols and mobility work to smart programming, sleep optimization, and knowing when to push versus when to rest — these five strategies will help you train consistently and stay injury-free.</p>'],
            ['slug' => 'member-transformation', 'title' => 'Member Spotlight: James\'s 12-Month Transformation', 'excerpt' => 'How one member changed his life through consistent training and support.', 'content' => '<p>When James walked through our doors a year ago, he hadn\'t exercised in over a decade. Today, he\'s 30 pounds lighter, infinitely stronger, and says the gym has transformed not just his body but his entire outlook on life.</p><p>In his own words, James shares the ups and downs of his journey, the role of his trainer and community, and why he believes it\'s never too late to start.</p>'],
        ];

        // Real Estate articles
        $realestateArticles = [
            ['slug' => 'first-time-buyer-guide', 'title' => 'First-Time Buyer\'s Guide: From Search to Keys', 'excerpt' => 'Everything you need to know about buying your first property.', 'content' => '<p>Buying your first home is one of life\'s biggest milestones — and one of its most complex transactions. This guide walks you through every step, from mortgage pre-approval to closing day.</p><p>We cover budgeting, choosing the right mortgage, making competitive offers, navigating inspections, understanding closing costs, and the common pitfalls first-time buyers should avoid.</p>'],
            ['slug' => 'market-update-quarterly', 'title' => 'Q1 Market Report: Prices, Trends & Predictions', 'excerpt' => 'Our quarterly analysis of local property market conditions.', 'content' => '<p>The property market continues to evolve, and staying informed is crucial whether you\'re buying, selling, or investing. Our Q1 report analyzes pricing trends, inventory levels, and market dynamics.</p><p>Key findings include a 3.2% year-over-year price increase, declining average days on market, and growing demand for energy-efficient homes — signaling a competitive spring market ahead.</p>'],
            ['slug' => 'home-staging-tips', 'title' => 'Home Staging Secrets That Sell Properties Faster', 'excerpt' => 'Professional staging tips that maximize your property\'s appeal and value.', 'content' => '<p>Staged homes sell 73% faster and for up to 6% more than unstaged properties, according to industry data. Yet many sellers overlook this powerful marketing tool.</p><p>Our staging experts share their top tips: decluttering strategically, neutralizing bold design choices, optimizing lighting, creating lifestyle vignettes, and the small investments that deliver outsized returns.</p>'],
            ['slug' => 'investment-property-guide', 'title' => 'Investing in Rental Property: A Practical Guide', 'excerpt' => 'What to know before buying your first investment property.', 'content' => '<p>Rental property can be an excellent wealth-building vehicle, but it\'s not the passive income dream many expect. Successful property investment requires careful analysis, realistic expectations, and hands-on management.</p><p>We cover location analysis, cash flow calculations, financing options, tenant management, tax implications, and the most common mistakes new property investors make.</p>'],
        ];

        // Events & Wedding articles
        $eventsArticles = [
            ['slug' => 'event-planning-timeline', 'title' => 'The Ultimate Event Planning Timeline', 'excerpt' => 'A month-by-month checklist for planning the perfect event.', 'content' => '<p>Whether you\'re planning a wedding, corporate gala, or milestone celebration, a clear timeline is your most valuable tool. Start planning too late and you\'ll face limited vendor availability and rushed decisions.</p><p>Our month-by-month checklist covers everything from 12 months out (venue and date selection) to the week before (final confirmations) and the day itself (timeline execution).</p>'],
            ['slug' => 'trending-event-themes', 'title' => 'Top Event Themes and Trends for This Season', 'excerpt' => 'Fresh inspiration for your next celebration or corporate event.', 'content' => '<p>Event design evolves constantly, influenced by art, fashion, technology, and cultural shifts. This season brings a fascinating mix of maximalism, sustainability, and immersive experiences.</p><p>From living walls and sustainable décor to interactive food stations and projection mapping, these are the trends that will define the most memorable events of the year.</p>'],
            ['slug' => 'vendor-selection-guide', 'title' => 'How to Choose the Right Vendors for Your Event', 'excerpt' => 'Expert advice on finding and vetting the best event vendors.', 'content' => '<p>Your vendors can make or break your event. The photographer, caterer, florist, and entertainment you choose will directly shape your guests\' experience and memories.</p><p>We share our insider tips for finding quality vendors, evaluating portfolios, negotiating contracts, and building a vendor team that works together seamlessly.</p>'],
            ['slug' => 'real-event-showcase', 'title' => 'Event Showcase: A Midsummer Garden Gala', 'excerpt' => 'Behind the scenes of a stunning outdoor celebration for 200 guests.', 'content' => '<p>When the Morrison Foundation asked us to create an outdoor summer gala for 200 guests, they had one request: make it magical. Challenge accepted.</p><p>From hand-illustrated invitations and a living flower arch entrance to a twilight dinner under string lights and a surprise fireworks finale, this event was one for the books. Here\'s how we brought it to life.</p>'],
        ];

        // Travel articles
        $travelArticles = [
            ['slug' => 'hidden-gem-destinations', 'title' => '10 Hidden Gem Destinations for Adventurous Travelers', 'excerpt' => 'Skip the tourist traps and discover these incredible lesser-known places.', 'content' => '<p>The most rewarding travel experiences often happen off the beaten path. While popular destinations have their charm, there\'s something magical about discovering a place that feels undiscovered.</p><p>From the dramatic fjords of the Faroe Islands to the ancient rock churches of Lalibela, Ethiopia, these 10 destinations offer extraordinary experiences without the crowds.</p>'],
            ['slug' => 'packing-like-a-pro', 'title' => 'The Art of Packing: Travel Light, Travel Right', 'excerpt' => 'Expert packing strategies for any trip length or destination.', 'content' => '<p>Overpacking is the most common travel mistake — and the most easily avoided. Whether you\'re gone for a weekend or a month, the right packing strategy makes everything smoother.</p><p>We share our tried-and-tested system for building a versatile travel wardrobe, organizing electronics and documents, choosing the right luggage, and the one packing rule that changed everything.</p>'],
            ['slug' => 'sustainable-travel-guide', 'title' => 'How to Travel More Sustainably in 2025', 'excerpt' => 'Practical ways to reduce your travel footprint without sacrificing experience.', 'content' => '<p>Travel broadens the mind, but it also impacts the planet. The good news is that sustainable travel doesn\'t mean sacrificing comfort or experience — it means making more thoughtful choices.</p><p>From choosing eco-certified operators and offsetting flights to supporting local economies and reducing single-use waste, these practical strategies help you explore the world more responsibly.</p>'],
            ['slug' => 'travel-photography-tips', 'title' => 'Capture Better Travel Photos: Tips From a Pro', 'excerpt' => 'Simple techniques to elevate your travel photography from snapshots to stories.', 'content' => '<p>Great travel photos aren\'t about having the best camera — they\'re about seeing the world with intention and knowing a few fundamental techniques.</p><p>Professional travel photographer Alex Chen shares his top tips: shooting during golden hour, finding foreground interest, capturing authentic moments, composing for storytelling, and the editing workflow that brings it all together.</p>'],
        ];

        // Ecommerce articles
        $ecommerceArticles = [
            ['slug' => 'new-arrivals-guide', 'title' => 'New Arrivals: Our Top Picks for This Season', 'excerpt' => 'First look at the newest additions to our collection.', 'content' => '<p>We\'re constantly curating new products that meet our standards for quality, design, and value. This season\'s arrivals include some of our most exciting finds yet.</p><p>From artisan-crafted home goods and sustainable fashion staples to innovative tech accessories and unique gift items, here\'s a sneak peek at what\'s just landed in our store.</p>'],
            ['slug' => 'gift-guide-seasonal', 'title' => 'The Ultimate Gift Guide for Every Budget', 'excerpt' => 'Thoughtful gift ideas organized by price range and recipient.', 'content' => '<p>Finding the perfect gift shouldn\'t be stressful. We\'ve done the hard work for you, curating our best products into a gift guide organized by budget, from under $25 to luxury splurges.</p><p>Whether you\'re shopping for a foodie, a tech enthusiast, a homebody, or someone who has everything, you\'ll find something special that shows you really thought about it.</p>'],
            ['slug' => 'sustainability-commitment', 'title' => 'Our Commitment to Sustainable Commerce', 'excerpt' => 'How we\'re building a more responsible retail business.', 'content' => '<p>We believe commerce and sustainability can — and must — coexist. Every decision we make, from product sourcing to packaging, considers its environmental and social impact.</p><p>This post details our sustainability journey: our sourcing criteria, packaging innovations, carbon offset program, and the goals we\'ve set for the years ahead. Transparency is part of our promise to you.</p>'],
            ['slug' => 'customer-favorites', 'title' => 'Customer Favorites: Most-Loved Products of the Year', 'excerpt' => 'The products our community rated highest and purchased most.', 'content' => '<p>Every year, we compile data from sales, reviews, and customer feedback to identify the products our community loves most. These aren\'t just best sellers — they\'re the items that earn five-star reviews and repeat purchases.</p><p>From our top-rated everyday essentials to the surprise hit that nobody saw coming, these are the products that defined our year.</p>'],
        ];

        // Nonprofit articles
        $nonprofitArticles = [
            ['slug' => 'annual-impact-report', 'title' => 'Annual Impact Report: A Year of Making a Difference', 'excerpt' => 'A transparent look at our programs, finances, and community impact.', 'content' => '<p>Transparency and accountability are core to our mission. Our annual impact report provides a comprehensive overview of where your donations go and the difference they make.</p><p>This year, we served over 10,000 individuals, launched two new programs, expanded to three additional communities, and maintained a 92-cent-on-the-dollar program spending ratio.</p>'],
            ['slug' => 'volunteer-spotlight', 'title' => 'Volunteer Spotlight: Meet the People Behind Our Mission', 'excerpt' => 'Celebrating the volunteers who make our work possible.', 'content' => '<p>Behind every program, event, and success story is a dedicated team of volunteers who give their time, skills, and hearts to our cause. This month, we spotlight three incredible volunteers.</p><p>From a retired teacher who tutors 15 students weekly to a corporate team that renovated our community center, these stories remind us why we do what we do.</p>'],
            ['slug' => 'fundraising-gala-recap', 'title' => 'Gala Recap: Record-Breaking Night of Generosity', 'excerpt' => 'Our annual fundraising gala exceeded all expectations.', 'content' => '<p>Last Saturday\'s annual gala was an evening to remember. Over 300 guests gathered to celebrate our mission, enjoy a spectacular program, and contribute to the community we all believe in.</p><p>Together, we raised a record-breaking $450,000 — enough to fund our flagship mentoring program for an entire year. A heartfelt thank you to every donor, sponsor, and attendee.</p>'],
            ['slug' => 'program-expansion-news', 'title' => 'Expanding Our Reach: New Programs Launching This Fall', 'excerpt' => 'Exciting new initiatives designed to serve more community members.', 'content' => '<p>Thanks to the generosity of our supporters, we\'re excited to announce three new programs launching this fall: a youth leadership academy, a financial literacy workshop series, and a community health initiative.</p><p>Each program was developed based on extensive community needs assessments and designed with measurable outcomes in mind. Registration opens next month.</p>'],
        ];

        // Automotive articles
        $automotiveArticles = [
            ['slug' => 'car-maintenance-checklist', 'title' => 'Seasonal Car Maintenance Checklist', 'excerpt' => 'Keep your vehicle running smoothly with these essential maintenance tasks.', 'content' => '<p>Regular maintenance is the key to vehicle longevity, safety, and performance. Yet many car owners overlook simple tasks that can prevent costly repairs down the road.</p><p>Our seasonal checklist covers tire condition and pressure, fluid levels, brake inspection, battery health, wiper blades, lighting, and the often-forgotten cabin air filter. Print it out and keep it in your glove box.</p>'],
            ['slug' => 'electric-vehicle-guide', 'title' => 'Thinking About Going Electric? Here\'s What You Need to Know', 'excerpt' => 'An honest look at EV ownership: costs, charging, range, and more.', 'content' => '<p>Electric vehicles have gone mainstream, but many buyers still have questions about range anxiety, charging infrastructure, true ownership costs, and whether an EV fits their lifestyle.</p><p>We provide an honest, balanced overview of EV ownership — including the real-world range you can expect, home charging setup costs, maintenance savings, and how to evaluate whether now is the right time to make the switch.</p>'],
            ['slug' => 'new-model-review', 'title' => 'First Drive: The All-New 2025 Model Range', 'excerpt' => 'Our team takes the latest models for a spin and shares their verdicts.', 'content' => '<p>The 2025 model year brings significant updates across the lineup. Our team spent a week with each new model to give you an honest assessment of what\'s improved, what\'s changed, and what stands out.</p><p>From the redesigned compact SUV with its vastly improved interior to the flagship sedan\'s new hybrid powertrain, here are our first impressions and recommendations.</p>'],
            ['slug' => 'buying-vs-leasing', 'title' => 'Buying vs. Leasing: Which Is Right for You?', 'excerpt' => 'A practical comparison to help you make the best financial decision.', 'content' => '<p>The buy-vs-lease debate is one of the most common questions we hear. The right answer depends on your driving habits, financial situation, and how long you like to keep your vehicles.</p><p>We break down the true costs of each option over 3 and 5 year periods, discuss the hidden costs many people miss, and provide a simple framework to help you decide which path makes sense for you.</p>'],
        ];

        // Government articles
        $governmentArticles = [
            ['slug' => 'community-development-plan', 'title' => 'Community Development Plan: Building a Better Future', 'excerpt' => 'Our vision for community growth and improvements over the next decade.', 'content' => '<p>After extensive public consultation, we\'re pleased to share our comprehensive Community Development Plan. This 10-year roadmap addresses infrastructure, housing, green spaces, and economic development.</p><p>Key initiatives include a new community recreation center, expanded public transit routes, affordable housing development, and the revitalization of the downtown commercial district.</p>'],
            ['slug' => 'public-safety-update', 'title' => 'Public Safety Update: New Programs and Initiatives', 'excerpt' => 'How we\'re working to keep our community safe and connected.', 'content' => '<p>Public safety is our top priority. This update outlines new initiatives including expanded community policing, upgraded emergency response systems, and enhanced neighborhood watch programs.</p><p>We\'re also launching a new anonymous tip line, expanding youth diversion programs, and investing in mental health crisis response teams to provide appropriate support when it\'s needed most.</p>'],
            ['slug' => 'budget-transparency-report', 'title' => 'Annual Budget Report: Where Your Tax Dollars Go', 'excerpt' => 'A transparent breakdown of public spending and priorities.', 'content' => '<p>We believe citizens deserve to know exactly how their tax dollars are spent. This annual budget report provides a detailed breakdown of revenue sources, expenditures, and the outcomes our investments deliver.</p><p>Major allocations include education (35%), infrastructure maintenance (22%), public safety (18%), health and social services (15%), and parks and recreation (10%).</p>'],
            ['slug' => 'upcoming-events-calendar', 'title' => 'Community Events: What\'s Happening This Season', 'excerpt' => 'Mark your calendar for these upcoming community gatherings and activities.', 'content' => '<p>A vibrant community calendar is a sign of a healthy community. This season brings an exciting lineup of events for residents of all ages.</p><p>Highlights include the annual Founders\' Day celebration, summer concert series in the park, a community health fair, farmers\' market launch, and the popular Kids\' Science Festival. Most events are free and open to all residents.</p>'],
        ];

        // Content & Publishing articles (blog/personal/magazine/news/podcast/newsletter/author/influencer)
        $contentArticles = [
            ['slug' => 'writing-process-revealed', 'title' => 'My Writing Process: From Idea to Published Piece', 'excerpt' => 'A transparent look at how articles go from spark of inspiration to final draft.', 'content' => '<p>People often ask how I write — where ideas come from, how I structure long pieces, and what the editing process looks like. Here\'s a full breakdown of my workflow, from first note to hit publish.</p><p>Every piece starts in a simple notes app. I capture fragments — a statistic, a quote, an observation — without trying to organize them. Once I have enough material around a theme, the real writing begins: outlining, drafting, rewriting, and the ruthless editing that turns good writing into great writing.</p>'],
            ['slug' => 'content-creation-tools', 'title' => 'The Tools I Use to Create Content in 2025', 'excerpt' => 'My essential toolkit for writing, editing, publishing, and growing an audience.', 'content' => '<p>After years of testing every tool on the market, I\'ve settled on a lean stack that handles everything from drafting and editing to newsletter distribution and analytics.</p><p>The core: a distraction-free writing app, a grammar checker that actually understands style, a scheduling tool for social distribution, and an analytics dashboard that shows what resonates. I\'ll walk you through each one and explain why it earned a place in my workflow.</p>'],
            ['slug' => 'building-audience-from-scratch', 'title' => 'How I Built an Audience From Zero', 'excerpt' => 'The strategies, mistakes, and breakthroughs that grew my readership.', 'content' => '<p>When I published my first piece, exactly three people read it — and two of them were family. Building a genuine audience took patience, consistency, and more than a few pivots along the way.</p><p>This post covers the three growth phases I experienced: the silent early months, the tipping point that changed everything, and the sustainable habits that keep readers coming back. No shortcuts, no hacks — just honest work that compounds over time.</p>'],
            ['slug' => 'favourite-reads-this-month', 'title' => 'What I\'ve Been Reading: Monthly Roundup', 'excerpt' => 'Books, articles, and ideas that caught my attention recently.', 'content' => '<p>One of the best ways I\'ve found to become a better writer is to be a voracious reader. Each month, I share the books, articles, podcasts, and essays that made me think differently.</p><p>This month\'s picks include a fascinating deep-dive into the psychology of decision-making, a beautifully written memoir about small-town life, an investigative piece on the future of work, and a podcast episode on creative burnout that felt uncomfortably relatable.</p>'],
        ];

        // Spa & Wellness articles (yoga/spa — distinct from fitness)
        $spaWellnessArticles = [
            ['slug' => 'benefits-of-regular-massage', 'title' => 'The Science-Backed Benefits of Regular Massage Therapy', 'excerpt' => 'Why massage is more than a luxury — it\'s an investment in your health.', 'content' => '<p>Massage therapy has moved well beyond the realm of indulgence. A growing body of clinical research demonstrates measurable benefits for pain management, stress reduction, immune function, and mental health.</p><p>In this article, we explore the evidence behind different massage modalities and help you understand which type might benefit your specific needs — whether you\'re managing chronic pain, recovering from injury, or simply investing in preventive wellness.</p>'],
            ['slug' => 'seasonal-selfcare-rituals', 'title' => 'Seasonal Self-Care: Adapting Your Wellness Routine', 'excerpt' => 'How to align your self-care practices with the rhythms of each season.', 'content' => '<p>Just as nature cycles through seasons of growth, abundance, harvest, and rest, our bodies and minds benefit from adapting our wellness routines to match these natural rhythms.</p><p>We share seasonal wellness rituals from Ayurvedic and traditional practices: warming and grounding treatments for winter, detoxifying and energizing for spring, cooling and hydrating for summer, and nourishing and restoring for autumn.</p>'],
            ['slug' => 'mindfulness-meditation-guide', 'title' => 'A Beginner\'s Guide to Mindfulness Meditation', 'excerpt' => 'Simple practices you can start today for lasting calm and clarity.', 'content' => '<p>Mindfulness meditation has been practiced for thousands of years, but modern neuroscience has given us new reasons to take it seriously. Regular practice physically changes brain structure in ways that improve focus, emotional regulation, and resilience.</p><p>This guide covers the absolute basics: how to sit, how to breathe, what to do when your mind wanders (hint: it\'s supposed to), and how to build a sustainable practice starting with just five minutes a day.</p>'],
            ['slug' => 'new-treatment-spotlight', 'title' => 'Treatment Spotlight: Introducing Crystal Sound Healing', 'excerpt' => 'Experience the profound relaxation of vibrational therapy with crystal singing bowls.', 'content' => '<p>We\'re excited to introduce crystal sound healing to our treatment menu. This ancient practice uses precisely tuned crystal singing bowls to create resonant frequencies that promote deep relaxation and energetic balance.</p><p>During a session, you\'ll lie comfortably while our certified practitioner plays bowls tuned to specific frequencies. Many clients report a profound sense of calm, improved sleep, and reduced anxiety after just one session.</p>'],
        ];

        // Music, Film & Art articles
        $musicArtArticles = [
            ['slug' => 'creative-process-behind-latest-work', 'title' => 'Behind the Work: Creative Process for Our Latest Project', 'excerpt' => 'An intimate look at how our newest project came to life.', 'content' => '<p>Every creative project has a story behind the story. Our latest work began as a rough sketch on a napkin during a late-night conversation about what art means in an age of artificial intelligence.</p><p>From that spark, we spent three months in development — experimenting with mediums, scrapping ideas, collaborating with unexpected partners, and finally arriving at something that feels honest, urgent, and entirely our own.</p>'],
            ['slug' => 'artist-influences-playlist', 'title' => 'Influences & Inspirations: A Curated Playlist', 'excerpt' => 'The artists, albums, and works that shaped our creative identity.', 'content' => '<p>No artist creates in a vacuum. Our work is shaped by a lifetime of influences — the music that stopped us in our tracks, the films that changed how we see the world, the artists who gave us permission to be ourselves.</p><p>This curated list goes beyond "top 10 favourites" — it\'s a map of the creative DNA that runs through everything we make. From underground hip-hop to Tarkovsky films to street art in São Paulo, these are the sources we keep returning to.</p>'],
            ['slug' => 'upcoming-exhibition-preview', 'title' => 'Preview: What to Expect at Our Upcoming Show', 'excerpt' => 'A sneak peek at the themes, pieces, and experiences we\'re preparing.', 'content' => '<p>We\'re putting the finishing touches on our most ambitious show to date, and we want to give you a glimpse of what\'s coming. Without spoiling too much, here\'s what to expect.</p><p>The show explores the tension between permanence and impermanence — physical and digital, memory and forgetting. It features new work across multiple mediums, an interactive installation, and a limited-edition print series available exclusively at the opening.</p>'],
            ['slug' => 'state-of-independent-arts', 'title' => 'The State of Independent Art: Challenges and Opportunities', 'excerpt' => 'Honest reflections on being an independent artist in today\'s creative economy.', 'content' => '<p>Being an independent artist has never been easier to start and never been harder to sustain. Platforms give us unprecedented access to audiences, but the economics of streaming, algorithm dependency, and content saturation create real challenges.</p><p>In this honest reflection, we discuss what\'s working, what\'s broken, and the community-driven models that give us hope for a more equitable creative economy.</p>'],
        ];

        // Architecture & Interior articles
        $architectureArticles = [
            ['slug' => 'sustainable-design-principles', 'title' => 'Designing for Tomorrow: Sustainable Architecture in Practice', 'excerpt' => 'How we integrate environmental responsibility into every project.', 'content' => '<p>Sustainable design is no longer optional — it\'s a fundamental responsibility of every architect and designer. But sustainability means more than solar panels and recycled materials; it\'s about designing buildings that respect their context and serve their communities for generations.</p><p>We explore our approach to passive design strategies, material selection, lifecycle analysis, and how we balance environmental ambition with practical constraints and budgets.</p>'],
            ['slug' => 'adaptive-reuse-case-study', 'title' => 'Adaptive Reuse: Giving Old Buildings New Purpose', 'excerpt' => 'Why preserving and repurposing existing structures is smart design.', 'content' => '<p>Some of our most rewarding projects involve breathing new life into existing buildings. Adaptive reuse honours the embodied energy of existing structures while creating spaces that serve contemporary needs.</p><p>In this case study, we follow the transformation of a derelict 1920s warehouse into a thriving mixed-use development with studios, retail, and community space — preserving its industrial character while adding modern comfort and accessibility.</p>'],
            ['slug' => 'interior-trends-forecast', 'title' => 'Interior Design Trends: What\'s Defining Spaces in 2025', 'excerpt' => 'The materials, colours, and spatial ideas shaping interior design this year.', 'content' => '<p>Interior design reflects how we want to live. This year\'s trends point toward spaces that are warm, personal, and deeply considered — a reaction against the minimalist uniformity that dominated the last decade.</p><p>Key movements include the return of rich, saturated colours, a preference for natural and reclaimed materials, biophilic design moving from trend to standard practice, and a growing emphasis on multi-functional spaces that adapt to how we actually use our homes.</p>'],
            ['slug' => 'project-award-announcement', 'title' => 'Our Riverside Project Wins National Design Award', 'excerpt' => 'Celebrating recognition for design excellence and community impact.', 'content' => '<p>We\'re honoured to announce that our Riverside Cultural Centre has received a National Design Award for architectural excellence. This recognition reflects years of collaborative work with the community, the client, and our talented project team.</p><p>The judges praised the building\'s sensitive integration with the riverfront landscape, its innovative use of cross-laminated timber, and the way the interior spaces flow naturally between public gathering areas and intimate contemplation rooms.</p>'],
        ];

        // Tattoo articles
        $tattooArticles = [
            ['slug' => 'choosing-first-tattoo', 'title' => 'Your First Tattoo: Everything You Need to Know', 'excerpt' => 'A comprehensive guide for first-timers — from choosing a design to aftercare.', 'content' => '<p>Getting your first tattoo is exciting and maybe a little nerve-wracking. That\'s completely normal. The more prepared you are, the better your experience — and your tattoo — will be.</p><p>We cover everything: how to choose a design you won\'t regret, what placement means for pain and aging, how to find and vet an artist, what the session actually feels like, and the aftercare routine that ensures your tattoo heals beautifully.</p>'],
            ['slug' => 'tattoo-styles-explained', 'title' => 'Tattoo Styles Explained: Finding Your Aesthetic', 'excerpt' => 'From traditional to geometric — a guide to the major tattoo styles.', 'content' => '<p>The world of tattooing encompasses dozens of distinct styles, each with its own history, techniques, and aesthetic principles. Understanding these styles helps you communicate with your artist and choose a look that resonates.</p><p>We break down the major categories: Traditional (bold lines, limited palette), Japanese (flowing compositions, symbolic imagery), Realism (photographic detail), Geometric (precision patterns), Blackwork (bold graphic impact), Watercolour (painterly flow), and several emerging styles pushing boundaries today.</p>'],
            ['slug' => 'guest-artist-announcement', 'title' => 'Guest Artist Spotlight: Welcome to Our Studio', 'excerpt' => 'A renowned artist joins us for a limited residency — bookings now open.', 'content' => '<p>We\'re thrilled to announce a two-week guest residency featuring one of the most exciting artists working in fine-line botanical illustration today. Their work combines delicate linework with incredible botanical accuracy.</p><p>The guest spot runs from the 15th to the 28th of next month. A limited number of appointments are available for custom pieces. To book, submit a request through our booking form with "Guest Artist" in the subject line — these spots will fill quickly.</p>'],
            ['slug' => 'aftercare-guide-updated', 'title' => 'Tattoo Aftercare: The Updated Guide', 'excerpt' => 'Our definitive aftercare instructions for beautiful, long-lasting tattoos.', 'content' => '<p>Proper aftercare is just as important as the tattoo itself. How you treat your new tattoo in the first 2-4 weeks determines how it heals, how the colours settle, and how it will look years from now.</p><p>We\'ve updated our aftercare guide based on the latest dermatological research and our artists\' combined decades of experience. This covers washing, moisturizing, sun protection, swimming restrictions, and the signs that something might need medical attention.</p>'],
        ];

        // Directory / Wiki / Resume articles
        $directoryArticles = [
            ['slug' => 'how-to-get-listed', 'title' => 'How to Get Your Listing Featured', 'excerpt' => 'Tips for creating a standout profile that attracts attention and engagement.', 'content' => '<p>With thousands of listings in our directory, standing out takes more than just showing up. The most successful profiles share certain qualities: completeness, accuracy, compelling descriptions, and regular updates.</p><p>In this guide, we share the best practices that consistently lead to higher visibility, more engagement, and featured placement — from writing an effective description to choosing the right categories and responding to reviews.</p>'],
            ['slug' => 'platform-update-new-features', 'title' => 'Platform Update: New Search Features and Improvements', 'excerpt' => 'What\'s new on the platform this month — better search, filters, and user experience.', 'content' => '<p>We\'ve been listening to your feedback and this month\'s update brings significant improvements to how you discover and interact with content on our platform.</p><p>New features include advanced filtering by multiple criteria, improved search relevance ranking, a redesigned mobile experience, and the ability to save and organize your favourite listings into custom collections.</p>'],
            ['slug' => 'community-guidelines-update', 'title' => 'Updated Community Guidelines: What You Need to Know', 'excerpt' => 'Our refreshed guidelines for contributing quality content to the platform.', 'content' => '<p>As our community grows, maintaining quality and trust becomes increasingly important. We\'ve updated our community guidelines to address new challenges and reflect the values that make this platform valuable.</p><p>Key updates include clearer policies on review authenticity, updated spam prevention measures, expanded accessibility requirements for listings, and a streamlined dispute resolution process. These changes take effect next month.</p>'],
            ['slug' => 'top-listings-this-quarter', 'title' => 'Top Listings This Quarter: Community Picks', 'excerpt' => 'The highest-rated and most-visited entries as chosen by our community.', 'content' => '<p>Every quarter, we spotlight the listings that our community has rated highest, visited most, and engaged with most actively. These aren\'t paid placements — they\'re genuine community favourites.</p><p>This quarter\'s standouts span multiple categories and share common traits: detailed profiles, responsive owners, regular updates, and consistently positive user experiences. Congratulations to all who made the list.</p>'],
        ];

        // Landing / Coming Soon / Specialty articles
        $specialtyArticles = [
            ['slug' => 'why-we-are-building-this', 'title' => 'Why We\'re Building This: The Problem We\'re Solving', 'excerpt' => 'The frustration that sparked an idea and became a mission.', 'content' => '<p>Every product starts with a problem. Ours started with a personal frustration that we later discovered was shared by millions of people — and the existing solutions weren\'t cutting it.</p><p>In this founding story, we explain the moment of clarity that led to our first prototype, the conversations with potential users that validated our approach, and the principles guiding our development as we build toward launch.</p>'],
            ['slug' => 'early-access-program', 'title' => 'Introducing Our Early Access Program', 'excerpt' => 'Be among the first to experience what we\'re building — and help shape it.', 'content' => '<p>We believe the best products are built with users, not just for them. That\'s why we\'re launching an early access program that gives a select group of people hands-on experience before our public release.</p><p>Early access members get first look at new features, direct communication with our product team, influence on our roadmap, and founding-member pricing that\'s locked in permanently. Here\'s how to apply.</p>'],
            ['slug' => 'development-update', 'title' => 'Development Update: Progress, Milestones, and What\'s Next', 'excerpt' => 'A transparent look at where we are in our development journey.', 'content' => '<p>Transparency is a core value, so we\'re sharing regular updates on our development progress. Here\'s an honest look at what we\'ve accomplished, what\'s taken longer than expected, and what\'s coming next.</p><p>This month we completed our core infrastructure, onboarded our first beta testers, and began iterating on the user experience based on real feedback. We also encountered some scaling challenges that pushed our timeline by two weeks — but the product is better for it.</p>'],
            ['slug' => 'team-introduction', 'title' => 'Meet the Team: The People Behind the Product', 'excerpt' => 'Get to know the founders and early team members driving this project.', 'content' => '<p>Behind every product is a team of people who care deeply about what they\'re building. We want you to know who we are, where we come from, and what motivates us to work on this problem every day.</p><p>Our founding team combines technical expertise, domain experience, and a shared belief that the status quo isn\'t good enough. In this post, each team member shares their background, their role, and the personal experience that connects them to our mission.</p>'],
        ];

        // Memorial articles
        $memorialArticles = [
            ['slug' => 'a-life-of-impact', 'title' => 'A Life of Impact: Remembering What Mattered Most', 'excerpt' => 'Reflecting on the values, achievements, and relationships that defined a remarkable life.', 'content' => '<p>Some people move through life so gracefully that you don\'t realize how much they\'ve given until you step back and look at the full picture. This reflection attempts to capture just a fraction of that impact.</p><p>From quiet acts of kindness that changed individual lives to public contributions that benefited the broader community, their legacy is woven into the fabric of every life they touched.</p>'],
            ['slug' => 'photo-memories-collection', 'title' => 'Through the Years: A Photo Journey', 'excerpt' => 'A collection of photographs spanning decades of cherished moments.', 'content' => '<p>Photos have a remarkable ability to stop time — to preserve a smile, a gathering, a moment of joy that might otherwise fade with the years. This collection spans decades of captured memories.</p><p>From childhood adventures and family milestones to career achievements and quiet everyday moments, these photographs tell the story of a life lived fully and surrounded by love.</p>'],
            ['slug' => 'community-remembers', 'title' => 'The Community Remembers: Tributes and Reflections', 'excerpt' => 'Messages of remembrance from friends, colleagues, and community members.', 'content' => '<p>The outpouring of love and remembrance from the community has been deeply moving. People from every chapter of their life have shared stories that paint a vivid portrait of someone truly exceptional.</p><p>From childhood friends to professional colleagues to neighbours and community members — each tribute adds another dimension to our understanding of someone who meant so much to so many.</p>'],
            ['slug' => 'continuing-the-legacy', 'title' => 'Continuing the Legacy: How to Honor Their Memory', 'excerpt' => 'Ways to carry forward the values and work that were closest to their heart.', 'content' => '<p>The most meaningful tribute we can offer is to continue the work that mattered to them. Their passions — education, community service, environmental stewardship — live on through the people and organizations they championed.</p><p>Here are concrete ways you can honor their memory: supporting the charities they believed in, volunteering your time, mentoring the next generation, or simply living with the kindness and generosity that they modelled every day.</p>'],
        ];

        // Map industries to article templates
        $templates = [
            // Food & Hospitality
            'restaurant' => $foodArticles,
            'cafe' => $foodArticles,
            'bar' => $foodArticles,
            'bakery' => $foodArticles,
            'foodtruck' => $foodArticles,
            'catering' => $foodArticles,
            'winery' => $foodArticles,

            // Hotels
            'hotel' => $hotelArticles,
            'resort' => $hotelArticles,

            // Tech
            'saas' => $techArticles,
            'startup' => $techArticles,
            'ai' => $techArticles,
            'app' => $techArticles,
            'crypto' => $techArticles,
            'cybersecurity' => $techArticles,
            'devtools' => $techArticles,
            'hosting' => $techArticles,
            'itsupport' => $techArticles,
            'gamedev' => $techArticles,

            // Law & Finance
            'law' => $lawFinanceArticles,
            'finance' => $lawFinanceArticles,
            'consulting' => $lawFinanceArticles,
            'accounting' => $lawFinanceArticles,
            'insurance' => $lawFinanceArticles,
            'recruiting' => $lawFinanceArticles,
            'translation' => $lawFinanceArticles,

            // Medical & Health
            'medical' => $medicalArticles,
            'dental' => $medicalArticles,
            'veterinary' => $medicalArticles,
            'therapy' => $medicalArticles,
            'mentalhealth' => $medicalArticles,
            'nutrition' => $medicalArticles,
            'physiotherapy' => $medicalArticles,
            'pharmacy' => $medicalArticles,
            'spa' => $spaWellnessArticles,
            'yoga' => $spaWellnessArticles,

            // Construction & Trades
            'construction' => $constructionArticles,
            'plumbing' => $constructionArticles,
            'electrical' => $constructionArticles,
            'hvac' => $constructionArticles,
            'roofing' => $constructionArticles,
            'painting' => $constructionArticles,
            'landscaping' => $constructionArticles,
            'cleaning' => $constructionArticles,
            'moving' => $constructionArticles,
            'handyman' => $constructionArticles,
            'solar' => $constructionArticles,

            // Creative & Portfolio
            'portfolio' => $creativeArticles,
            'design' => $creativeArticles,
            'photography' => $creativeArticles,
            'videography' => $creativeArticles,
            'animation' => $creativeArticles,
            'agency' => $creativeArticles,
            'marketing' => $creativeArticles,

            // Music, Film & Art
            'music' => $musicArtArticles,
            'film' => $musicArtArticles,
            'art' => $musicArtArticles,

            // Architecture & Interior
            'architecture' => $architectureArticles,
            'interior' => $architectureArticles,

            // Tattoo & Body Art
            'tattoo' => $tattooArticles,

            // Content & Publishing
            'blog' => $contentArticles,
            'personal' => $contentArticles,
            'magazine' => $contentArticles,
            'news' => $contentArticles,
            'podcast' => $contentArticles,
            'newsletter' => $contentArticles,
            'author' => $contentArticles,
            'influencer' => $contentArticles,

            // Education
            'education' => $educationArticles,
            'onlinecourse' => $educationArticles,
            'coaching' => $educationArticles,
            'tutoring' => $educationArticles,
            'language' => $educationArticles,
            'driving' => $educationArticles,
            'childcare' => $educationArticles,
            'library' => $educationArticles,
            'training' => $educationArticles,

            // Fitness
            'fitness' => $fitnessArticles,

            // Real Estate
            'realestate' => $realestateArticles,
            'propertymanagement' => $realestateArticles,

            // Events
            'events' => $eventsArticles,
            'wedding' => $eventsArticles,
            'party' => $eventsArticles,
            'venue' => $eventsArticles,
            'theater' => $eventsArticles,
            'cinema' => $eventsArticles,
            'escape' => $eventsArticles,
            'festival' => $eventsArticles,

            // Travel
            'travel' => $travelArticles,
            'tourism' => $travelArticles,
            'camping' => $travelArticles,
            'skiing' => $travelArticles,
            'diving' => $travelArticles,
            'golf' => $travelArticles,
            'marina' => $travelArticles,

            // Ecommerce & Retail
            'ecommerce' => $ecommerceArticles,
            'fashion' => $ecommerceArticles,
            'jewelry' => $ecommerceArticles,
            'beauty' => $ecommerceArticles,
            'furniture' => $ecommerceArticles,
            'electronics' => $ecommerceArticles,
            'bookshop' => $ecommerceArticles,
            'grocery' => $ecommerceArticles,
            'pets' => $ecommerceArticles,
            'florist' => $ecommerceArticles,
            'marketplace' => $ecommerceArticles,

            // Nonprofit & Community
            'nonprofit' => $nonprofitArticles,
            'church' => $nonprofitArticles,
            'volunteer' => $nonprofitArticles,
            'political' => $nonprofitArticles,
            'community' => $nonprofitArticles,
            'association' => $nonprofitArticles,

            // Automotive
            'automotive' => $automotiveArticles,
            'mechanic' => $automotiveArticles,
            'carwash' => $automotiveArticles,
            'taxi' => $automotiveArticles,
            'trucking' => $automotiveArticles,
            'motorcycle' => $automotiveArticles,
            'boating' => $automotiveArticles,

            // Government
            'government' => $governmentArticles,
            'police' => $governmentArticles,
            'military' => $governmentArticles,
            'embassy' => $governmentArticles,

            // Directory / Wiki / Resume
            'resume' => $directoryArticles,
            'wiki' => $directoryArticles,
            'directory' => $directoryArticles,

            // Landing / Coming Soon / Other
            'landing' => $specialtyArticles,
            'comingsoon' => $specialtyArticles,
            'other' => $specialtyArticles,

            // Memorial
            'memorial' => $memorialArticles,
        ];

        $generic = [
            ['slug' => 'welcome-to-our-blog', 'title' => "Welcome to {$themeName}", 'excerpt' => 'Our journey begins here. Follow along for updates and insights.', 'content' => "<p>Welcome to the {$themeName} blog. Here we'll share news, insights, and stories from our journey. Whether you\'re a long-time supporter or discovering us for the first time, we\'re glad you\'re here.</p><p>Stay tuned for regular updates on what we\'re working on, industry insights, and behind-the-scenes looks at our process.</p>"],
            ['slug' => 'industry-trends', 'title' => 'Top Trends to Watch This Year', 'excerpt' => 'A look at the most important trends shaping our industry.', 'content' => '<p>The landscape is evolving rapidly, driven by technology, changing consumer expectations, and new regulatory frameworks. Here are the key trends we\'re watching this year.</p><p>From digital transformation and sustainability initiatives to shifting demographic preferences, understanding these trends helps us serve you better and stay ahead of the curve.</p>'],
            ['slug' => 'behind-the-scenes', 'title' => 'Behind the Scenes', 'excerpt' => 'A glimpse into our process and what drives us.', 'content' => '<p>Ever wondered what goes on behind the scenes? In this post, we pull back the curtain on our daily operations, creative process, and the decisions that shape what we deliver.</p><p>From morning stand-ups to late-night problem-solving sessions, this is an honest look at how we work and why we love what we do.</p>'],
            ['slug' => 'community-spotlight', 'title' => 'Community Spotlight', 'excerpt' => 'Celebrating the people who make our community great.', 'content' => '<p>Our community is at the heart of everything we do. This month, we highlight some of the amazing people who inspire us, challenge us, and make our work meaningful.</p><p>From loyal clients who\'ve been with us since day one to team members who go above and beyond, these are the stories that remind us why we do what we do.</p>'],
        ];

        return $templates[$industry] ?? $generic;
    }

    /**
     * Generate home.php that dynamically loads sections from sections/ directory.
     */
    private function generateHomePHP(array $sectionIds): string
    {
        return <<<'PHP'
<?php
/**
 * Home page template — Dynamic section loader
 * Sections are loaded from ../sections/{id}.php in user-defined order.
 * Variables available: $pages, $articles, $themePath (from controller)
 */
$themeConfig = get_theme_config(get_active_theme());
$defaultOrder = array_column($themeConfig['homepage_sections'] ?? [], 'id');
$sectionOrder = theme_get_section_order();
if (empty($sectionOrder)) $sectionOrder = $defaultOrder;

foreach ($sectionOrder as $sectionId) {
    if (!theme_section_enabled($sectionId)) continue;
    $sectionFile = __DIR__ . '/../sections/' . $sectionId . '.php';
    if (file_exists($sectionFile)) {
        require $sectionFile;
    }
}
PHP;
    }

    /**
     * Try to split a monolithic HTML blob into individual sections by <section id="xxx"> tags.
     */
    /**
     * Fix broken/fictional image paths in section PHP files.
     * AI often generates paths like /themes/slug/assets/gallery/dining.jpg that don't exist.
     * Replace them with Pexels URLs from $this->pexelsImages or a placeholder gradient.
     */
    /**
     * Extract theme_get() default values from section PHP files and save to theme_customizations DB.
     * This ensures the Visual Editor panel shows actual content, not empty fields.
     */
    private function saveSectionContentToDb(string $slug, string $themeDir): void
    {
        $pdo = \core\Database::connection();

        // Also scan layout.php (header/footer have data-ts attrs with theme_get defaults)
        $files = glob($themeDir . '/sections/*.php');
        $layoutFile = $themeDir . '/layout.php';
        if (file_exists($layoutFile)) {
            $files[] = $layoutFile;
        }

        $saved = 0;
        $upsertStmt = $pdo->prepare("
            INSERT INTO theme_customizations (theme_slug, section, field_key, field_value, field_type)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE field_value = VALUES(field_value), field_type = VALUES(field_type)
        ");

        foreach ($files as $file) {
            $code = file_get_contents($file);

            // ── Pass 1: Static keys ──
            // Match theme_get('section.key', 'default value') with literal string keys
            preg_match_all(
                '/theme_get\s*\(\s*[\'"]([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)[\'"]\s*,\s*[\'"](.+?)[\'"]\s*\)/',
                $code,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $m) {
                $section = $m[1];
                $key = $m[2];
                $defaultValue = $m[3];

                if (trim($defaultValue) === '') continue;

                $defaultValue = str_replace("\\'", "'", $defaultValue);
                $defaultValue = str_replace('\\"', '"', $defaultValue);

                $type = $this->guessFieldType($section, $key, $defaultValue);
                $upsertStmt->execute([$slug, $section, $key, $defaultValue, $type]);
                $saved++;
            }

            // ── Pass 2: Dynamic keys with {$i} or {$idx} variable interpolation ──
            // Match: theme_get("section.item{$i}_key", 'default')
            // Match: theme_get("section.member{$i}_name", 'default')
            // Match: theme_get("section.item{$i}_key", 'prefix ' . ['a','b'][$i-1])
            // Match: theme_get("section.item{$i}_key", 'prefix ' . $i)
            preg_match_all(
                '/theme_get\s*\(\s*"([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\{\$[a-z]+\}(_[a-zA-Z0-9_]+)?"\s*,\s*(.+?)\s*\)/',
                $code,
                $dynMatches,
                PREG_SET_ORDER
            );

            foreach ($dynMatches as $dm) {
                $section = $dm[1];
                $keyPrefix = $dm[2];  // "item", "member", "image", etc.
                $keySuffix = $dm[3] ?? '';  // "_title", "_text", "" (gallery has no suffix)
                $defaultExpr = $dm[4];

                // Expand for $i = 1..10
                for ($i = 1; $i <= 10; $i++) {
                    $expandedKey = $keyPrefix . $i . $keySuffix;

                    $defaultValue = $this->evaluateDefaultExpr($defaultExpr, $i);
                    if ($defaultValue === null || trim($defaultValue) === '') continue;

                    $type = $this->guessFieldType($section, $expandedKey, $defaultValue);
                    $upsertStmt->execute([$slug, $section, $expandedKey, $defaultValue, $type]);
                    $saved++;
                }
            }

            // ── Pass 3: data-ts-bg image URLs ──
            preg_match_all(
                '/data-ts-bg=[\'"]([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)[\'"]/',
                $code,
                $bgMatches,
                PREG_SET_ORDER | PREG_OFFSET_CAPTURE
            );
            foreach ($bgMatches as $bgm) {
                $section = $bgm[1][0];
                $key = $bgm[2][0];
                $offset = $bgm[0][1];

                $before = substr($code, max(0, $offset - 500), min(500, $offset));
                if (preg_match('/url\([\'"]?([^\'")]+)[\'"]?\)/', $before, $urlMatch)) {
                    $imageUrl = $urlMatch[1];
                    if (str_starts_with($imageUrl, 'http')) {
                        $upsertStmt->execute([$slug, $section, $key, $imageUrl, 'image']);
                        $saved++;
                    }
                }
            }
        }

        // Clear theme cache so new values are visible immediately
        \Cache::clear('theme_custom_' . $slug);

        @file_put_contents('/tmp/aitb-assembly.log',
            date('H:i:s') . " saveSectionContentToDb: saved {$saved} values for {$slug}\n",
            FILE_APPEND
        );
    }

    /**
     * Evaluate a PHP default expression from theme_get(), expanding $i variable.
     * Handles: 'literal string', 'prefix ' . ['a','b',...][$i-1], 'prefix ' . $i, (int) wrapper
     */
    private function evaluateDefaultExpr(string $expr, int $i): ?string
    {
        $expr = trim($expr);

        // Simple literal: 'string' or "string"
        if (preg_match("/^['\"](.+?)['\"]$/s", $expr, $m)) {
            return str_replace(["\\'", '\\"'], ["'", '"'], $m[1]);
        }

        // Concatenation: 'prefix ' . ['One','Two',...][$i-1]
        if (preg_match("/^['\"](.+?)['\"]\s*\.\s*\[(.+?)\]\s*\[\s*\\$[a-z]+\s*-\s*1\s*\]/", $expr, $m)) {
            $prefix = str_replace(["\\'", '\\"'], ["'", '"'], $m[1]);
            preg_match_all("/['\"]([^'\"]+)['\"]/", $m[2], $arrMatches);
            $arr = $arrMatches[1] ?? [];
            $idx = $i - 1;
            return isset($arr[$idx]) ? $prefix . $arr[$idx] : null;
        }

        // Concatenation: 'prefix ' . $i
        if (preg_match("/^['\"](.+?)['\"]\s*\.\s*\\$[a-z]+$/", $expr, $m)) {
            return str_replace(["\\'", '\\"'], ["'", '"'], $m[1]) . $i;
        }

        // Cast wrapper: (int) theme_get(...)
        if (str_starts_with($expr, '(int)')) {
            $inner = trim(substr($expr, 5));
            return $this->evaluateDefaultExpr($inner, $i);
        }

        return null;
    }

    /**
     * Guess field type from section, key name, and value.
     */
    private function guessFieldType(string $section, string $key, string $value): string
    {
        if (preg_match('/(bg_image|image|logo|avatar|photo|thumbnail)/', $key)) return 'image';
        if (preg_match('/(rating|count|number)/', $key)) return 'number';
        if (strlen($value) > 100) return 'textarea';
        return 'text';
    }

    private function fixSectionImagePaths(string $themeDir): void
    {
        $slug = basename($themeDir);
        $pexelsUrls = array_column($this->pexelsImages, 'src');
        $pexelsIdx = 0;

        foreach (glob($themeDir . '/sections/*.php') as $file) {
            $code = file_get_contents($file);
            $changed = false;

            // Pattern 1: src="/themes/slug/assets/..." (fictional local paths in img tags)
            $code = preg_replace_callback(
                '#src="(/themes/' . preg_quote($slug, '#') . '/assets/[^"]+)"#',
                function ($m) use (&$pexelsIdx, $pexelsUrls, $themeDir, &$changed) {
                    // Check if file actually exists
                    $localPath = $themeDir . str_replace('/themes/' . basename($themeDir), '', $m[1]);
                    if (file_exists($localPath)) {
                        return $m[0]; // File exists, keep it
                    }
                    $changed = true;
                    if (!empty($pexelsUrls[$pexelsIdx])) {
                        return 'src="' . $pexelsUrls[$pexelsIdx++ % count($pexelsUrls)] . '"';
                    }
                    return 'src="https://placehold.co/800x600/1a1611/d4a03a?text=Image"';
                },
                $code
            );

            // Pattern 2: src="/assets/images/..." or src="/images/..." (other fictional paths)
            $code = preg_replace_callback(
                '#src="(/(?:assets/)?images?/[^"]+)"#',
                function ($m) use (&$pexelsIdx, $pexelsUrls, &$changed) {
                    $changed = true;
                    if (!empty($pexelsUrls[$pexelsIdx])) {
                        return 'src="' . $pexelsUrls[$pexelsIdx++ % count($pexelsUrls)] . '"';
                    }
                    return 'src="https://placehold.co/800x600/1a1611/d4a03a?text=Image"';
                },
                $code
            );

            // Pattern 3: url('/themes/slug/assets/...') in inline CSS (background-image)
            $code = preg_replace_callback(
                '#url\([\'"]?(/themes/' . preg_quote($slug, '#') . '/assets/[^\'")]+)[\'"]?\)#',
                function ($m) use (&$pexelsIdx, $pexelsUrls, $themeDir, &$changed) {
                    $localPath = $themeDir . str_replace('/themes/' . basename($themeDir), '', $m[1]);
                    if (file_exists($localPath)) {
                        return $m[0];
                    }
                    $changed = true;
                    if (!empty($pexelsUrls[$pexelsIdx])) {
                        return "url('" . $pexelsUrls[$pexelsIdx++ % count($pexelsUrls)] . "')";
                    }
                    return "url('https://placehold.co/1200x800/1a1611/d4a03a?text=Background')";
                },
                $code
            );

            if ($changed) {
                file_put_contents($file, $code);
            }
        }
    }

    private function splitMonolithicSections(string $html, array $sectionIds): array
    {
        $result = [];
        foreach ($sectionIds as $id) {
            $safeId = preg_quote($id, '/');
            // Match <section ...id="sectionId"...> ... </section> (greedy enough)
            if (preg_match('/((?:<\?php.*?\?>\s*)?<section[^>]*id=["\']' . $safeId . '["\'][^>]*>.*?<\/section>)/s', $html, $m)) {
                // Also capture preceding <?php block if any
                $code = $m[1];
                // Look for preceding PHP variable block
                $pos = strpos($html, $code);
                if ($pos !== false && $pos > 0) {
                    $before = substr($html, 0, $pos);
                    // Check if there's a PHP block right before (variable assignments)
                    if (preg_match('/((?:<\?php\s+(?:\/\/[^\n]*\n|\$\w+\s*=\s*theme_get\([^;]+;\s*)+\?>\s*))$/s', $before, $bm)) {
                        $code = $bm[1] . $code;
                    }
                }
                $result[$id] = trim($code);
            }
        }
        return $result;
    }

    /**
     * Regenerate CSS for an existing AI-generated theme.
     * Reads theme.json + sections, generates fresh CSS.
     */
    public function regenerateCss(string $slug, string $instructions = ''): array
    {
        $themeDir = CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir) || !file_exists($themeDir . '/theme.json')) {
            return ['ok' => false, 'error' => 'Theme not found'];
        }

        $brief = json_decode(file_get_contents($themeDir . '/theme.json'), true);
        if (!$brief) return ['ok' => false, 'error' => 'Invalid theme.json'];

        $this->slug = $slug;

        // Collect all HTML from sections + layout
        $htmlParts = [];
        $layoutFile = $themeDir . '/layout.php';
        if (file_exists($layoutFile)) $htmlParts[] = file_get_contents($layoutFile);
        foreach (glob($themeDir . '/sections/*.php') as $sf) {
            $htmlParts[] = file_get_contents($sf);
        }
        if (file_exists($themeDir . '/templates/home.php')) {
            $htmlParts[] = file_get_contents($themeDir . '/templates/home.php');
        }

        $html = [
            'header_html' => file_get_contents($layoutFile),
            'sections' => [],
        ];
        foreach (glob($themeDir . '/sections/*.php') as $sf) {
            $html['sections'][basename($sf, '.php')] = file_get_contents($sf);
        }

        try {
            $t0 = microtime(true);
            if ($instructions) {
                $this->prompt = $instructions; // Use as additional context
            }
            $css = $this->step3_cssGeneration($brief, $html);
            $css = $this->repairBrokenCss($css); // Fix AI truncation artifacts
            $css = $this->stripStructuralFromAiCss($css, $slug); // Remove structural props AI shouldn't set
            $timing = (int)((microtime(true) - $t0) * 1000);

            // Write new CSS — append structural header & footer CSS
            $headerStructuralCss = $this->headerPatternResult['structural_css'] ?? '';
            $footerStructuralCss = $this->footerPatternResult['structural_css'] ?? '';
            $heroStructuralCss = $this->heroPatternResult['structural_css'] ?? '';
            $sectionStructuralCss = '';
            foreach ($this->sectionPatternResults as $sid => $result) {
                $sectionStructuralCss .= "\n" . ($result['structural_css'] ?? '');
            }
            $finalCss2 = $css . "\n" . $heroStructuralCss . "\n" . $sectionStructuralCss . "\n" . $headerStructuralCss . "\n" . $footerStructuralCss;

            // ── Wave-fill fix (same as primary assembly) ──
            $heroPatternId2 = $this->heroPatternResult['pattern_id'] ?? '';
            if ($heroPatternId2 === 'gradient-wave') {
                $sections2 = $brief['homepage_sections'] ?? [];
                $firstAfterHero2 = null; $foundHero2 = false;
                foreach ($sections2 as $sec2) {
                    if (($sec2['id'] ?? '') === 'hero') { $foundHero2 = true; continue; }
                    if ($foundHero2) { $firstAfterHero2 = $sec2['id'] ?? ''; break; }
                }
                if ($firstAfterHero2) {
                    $prefix2 = \HeaderPatternRegistry::generatePrefix($brief['name'] ?? $slug);
                    $nextClass2 = ".{$prefix2}-{$firstAfterHero2}";
                    if (preg_match('/\\' . preg_quote($nextClass2, '/') . '\s*\{[^}]*background\s*:\s*([^;]+)/i', $finalCss2, $bgM2)) {
                        $finalCss2 .= "\n.{$prefix2}-hero-wave path { fill: " . trim($bgM2[1]) . " !important; }\n";
                    }
                }
            }

            file_put_contents($themeDir . '/assets/css/style.dev.css', $finalCss2);
            file_put_contents($themeDir . '/assets/css/style.css', self::minifyCss($finalCss2));
            $this->chownRecursive($themeDir . '/assets/css');

            return [
                'ok' => true,
                'slug' => $slug,
                'css_lines' => substr_count($css, "\n") + 1,
                'timing_ms' => $timing,
                'coverage' => $this->steps['css']['selector_coverage'] ?? null,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update theme brief (colors, typography, etc.) and regenerate CSS.
     */
    public function updateBrief(string $slug, array $changes): array
    {
        $themeDir = CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir) || !file_exists($themeDir . '/theme.json')) {
            return ['ok' => false, 'error' => 'Theme not found'];
        }

        $brief = json_decode(file_get_contents($themeDir . '/theme.json'), true);
        if (!$brief) return ['ok' => false, 'error' => 'Invalid theme.json'];

        // Apply changes
        if (!empty($changes['colors'])) {
            $brief['colors'] = array_merge($brief['colors'] ?? [], $changes['colors']);
        }
        if (!empty($changes['typography'])) {
            $brief['typography'] = array_merge($brief['typography'] ?? [], $changes['typography']);
        }
        if (!empty($changes['buttons'])) {
            $brief['buttons'] = array_merge($brief['buttons'] ?? [], $changes['buttons']);
        }
        if (!empty($changes['layout'])) {
            $brief['layout'] = array_merge($brief['layout'] ?? [], $changes['layout']);
        }

        // Update Google Fonts URL if fonts changed
        if (!empty($changes['typography'])) {
            $heading = $changes['typography']['headingFont'] ?? $brief['typography']['headingFont'] ?? '';
            $body = $changes['typography']['fontFamily'] ?? $brief['typography']['fontFamily'] ?? '';
            if ($heading || $body) {
                $families = [];
                if ($heading) $families[] = str_replace(' ', '+', $heading) . ':wght@400;500;600;700';
                if ($body && $body !== $heading) $families[] = str_replace(' ', '+', $body) . ':wght@300;400;500;600;700';
                $brief['google_fonts_url'] = 'https://fonts.googleapis.com/css2?' . implode('&', array_map(fn($f) => 'family=' . $f, $families)) . '&display=swap';
            }
        }

        // Save updated theme.json
        file_put_contents($themeDir . '/theme.json', json_encode($brief, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->chownRecursive($themeDir . '/theme.json');

        // Regenerate CSS with updated brief
        return $this->regenerateCss($slug);
    }

    /**
     * AI-driven refinement: parse natural language instruction and apply changes.
     * Returns what was changed so UI can update.
     */
    public function refine(string $slug, string $instruction): array
    {
        $themeDir = CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir) || !file_exists($themeDir . '/theme.json')) {
            return ['ok' => false, 'error' => 'Theme not found'];
        }

        $brief = json_decode(file_get_contents($themeDir . '/theme.json'), true);
        if (!$brief) return ['ok' => false, 'error' => 'Invalid theme.json'];

        $this->slug = $slug;

        // Ask AI to interpret the instruction and return JSON changes
        $briefJson = json_encode($brief, JSON_PRETTY_PRINT);
        $systemPrompt = <<<PROMPT
You are a theme design assistant. The user wants to modify an existing theme.

CURRENT THEME (theme.json):
{$briefJson}

USER INSTRUCTION: {$instruction}

Analyze the instruction and return a JSON object with the changes to apply.
The JSON should have these optional keys:
- "colors": object of color changes (e.g. {"primary": "#ff0000", "background": "#000"})
- "typography": object (e.g. {"headingFont": "Playfair Display", "fontFamily": "Inter"})
- "buttons": object (e.g. {"borderRadius": "20"})
- "layout": object (e.g. {"sectionSpacing": "80"})
- "css_instructions": string — additional instructions for CSS regeneration (e.g. "make cards have glassmorphism effect")
- "summary": string — one-line summary of what was changed

Return ONLY valid JSON, no explanation.
PROMPT;

        $result = $this->aiQuery($instruction, $this->queryOptions([
            'system_prompt' => $systemPrompt,
            'max_tokens' => 1000,
            'temperature' => $this->getCreativityTemp('refine'),
            'json_mode' => true,
        ]));

        $changes = $this->extractJson($result);
        if (!$changes) {
            return ['ok' => false, 'error' => 'AI could not interpret the instruction'];
        }

        $summary = $changes['summary'] ?? 'Changes applied';
        $cssInstructions = $changes['css_instructions'] ?? '';
        unset($changes['summary'], $changes['css_instructions']);

        // Apply brief changes if any
        $briefChanged = false;
        foreach (['colors', 'typography', 'buttons', 'layout'] as $key) {
            if (!empty($changes[$key]) && is_array($changes[$key])) {
                $brief[$key] = array_merge($brief[$key] ?? [], $changes[$key]);
                $briefChanged = true;
            }
        }

        if ($briefChanged) {
            file_put_contents($themeDir . '/theme.json', json_encode($brief, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->chownRecursive($themeDir . '/theme.json');
        }

        // Regenerate CSS with optional extra instructions
        $regenResult = $this->regenerateCss($slug, $cssInstructions);

        return [
            'ok' => $regenResult['ok'],
            'summary' => $summary,
            'changes' => $changes,
            'css_lines' => $regenResult['css_lines'] ?? null,
            'error' => $regenResult['error'] ?? null,
        ];
    }

    /**
     * Activate a theme by slug
     */
    public function activateTheme(string $slug): bool
    {
        $themeDir = CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir) || !file_exists($themeDir . '/theme.json')) {
            return false;
        }

        // Verify essential files exist
        foreach (['layout.php', 'templates/home.php', 'assets/css/style.css'] as $required) {
            if (!file_exists($themeDir . '/' . $required)) {
                return false;
            }
        }

        try {
            $pdo = \core\Database::connection();

            // Update system_settings (primary — used by get_active_theme())
            $stmt = $pdo->prepare("UPDATE system_settings SET active_theme = ? WHERE id = 1");
            $stmt->execute([$slug]);

            // Also update settings table (secondary — legacy/backup)
            $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = 'active_theme'");
            $stmt->execute([$slug]);
            if ($stmt->rowCount() === 0) {
                $stmt = $pdo->prepare("INSERT INTO settings (`key`, value) VALUES ('active_theme', ?)");
                $stmt->execute([$slug]);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Delete a generated theme by slug
     */
    public function deleteTheme(string $slug): bool
    {
        $themeDir = CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir)) return false;

        $jsonFile = $themeDir . '/theme.json';
        if (file_exists($jsonFile)) {
            $data = @json_decode(file_get_contents($jsonFile), true);
            if (($data['author'] ?? '') !== 'AI Theme Builder') return false;
        }

        try {
            $pdo = \core\Database::connection();
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'active_theme'");
            $stmt->execute();
            if ($stmt->fetchColumn() === $slug) return false;
        } catch (\Throwable $e) {
            return false;
        }

        // Clean up database records
        try {
            $pdo = \core\Database::connection();
            $pdo->prepare("DELETE FROM theme_customizations WHERE theme_slug = ?")->execute([$slug]);
            $pdo->prepare("DELETE FROM pages WHERE theme_slug = ?")->execute([$slug]);
            $pdo->prepare("DELETE FROM articles WHERE theme_slug = ?")->execute([$slug]);
            // Clean menu items then menus
            $menuStmt = $pdo->prepare("SELECT id FROM menus WHERE theme_slug = ?");
            $menuStmt->execute([$slug]);
            foreach ($menuStmt->fetchAll(\PDO::FETCH_COLUMN) as $menuId) {
                $pdo->prepare("DELETE FROM menu_items WHERE menu_id = ?")->execute([$menuId]);
            }
            $pdo->prepare("DELETE FROM menus WHERE theme_slug = ?")->execute([$slug]);
            // Clean gallery images then galleries
            $galStmt = $pdo->prepare("SELECT id FROM galleries WHERE theme = ?");
            $galStmt->execute([$slug]);
            foreach ($galStmt->fetchAll(\PDO::FETCH_COLUMN) as $galId) {
                $pdo->prepare("DELETE FROM gallery_images WHERE gallery_id = ?")->execute([$galId]);
            }
            $pdo->prepare("DELETE FROM galleries WHERE theme = ?")->execute([$slug]);
        } catch (\Throwable $e) {
            // Non-critical — theme files still get deleted
        }

        $this->deleteDir($themeDir);
        return true;
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->deleteDir($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    private function extractJson(array $result): ?array
    {
        if (empty($result['ok'])) return null;

        if (!empty($result['json']) && is_array($result['json'])) return $result['json'];

        $text = $result['text'] ?? '';
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/```\s*$/m', '', $text);

        // Try balanced bracket extraction (finds first complete JSON object)
        $depth = 0;
        $start = -1;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            if ($text[$i] === '{') {
                if ($depth === 0) $start = $i;
                $depth++;
            } elseif ($text[$i] === '}') {
                $depth--;
                if ($depth === 0 && $start >= 0) {
                    $candidate = substr($text, $start, $i - $start + 1);
                    $parsed = json_decode($candidate, true);
                    if (is_array($parsed)) return $parsed;
                    $start = -1; // Try next block
                }
            }
        }

        // Fallback: greedy regex
        if (preg_match('/\{[\s\S]*\}/s', $text, $m)) {
            $parsed = json_decode($m[0], true);
            if (is_array($parsed)) return $parsed;
        }

        return null;
    }

    /**
     * Get industry-specific service names for footer menu.
     */
    private function getIndustryServiceNames(string $industry): array
    {
        $map = [
            // Food & Hospitality
            'restaurant' => ['Dine In', 'Takeaway', 'Catering', 'Private Events'],
            'cafe' => ['Coffee', 'Pastries', 'Breakfast', 'Lunch'],
            'bar' => ['Cocktails', 'Events', 'Happy Hour', 'VIP Booking'],
            'bakery' => ['Artisan Breads', 'Custom Cakes', 'Pastries & Pies', 'Catering Orders'],
            'foodtruck' => ['Street Food', 'Event Catering', 'Corporate Lunch', 'Festival Bookings'],
            'catering' => ['Wedding Catering', 'Corporate Events', 'Private Parties', 'Menu Planning'],
            'hotel' => ['Rooms & Suites', 'Fine Dining', 'Spa & Wellness', 'Event Spaces'],
            'resort' => ['Luxury Villas', 'All-Inclusive Packages', 'Activities & Excursions', 'Wellness Retreat'],
            'winery' => ['Wine Tastings', 'Vineyard Tours', 'Wine Club', 'Private Events'],

            // Tech & Digital
            'saas' => ['Features', 'Pricing', 'Enterprise', 'API Docs'],
            'startup' => ['Product', 'Solutions', 'Pricing', 'Developers'],
            'ai' => ['Machine Learning', 'NLP Solutions', 'Computer Vision', 'AI Consulting'],
            'app' => ['iOS App', 'Android App', 'Web Platform', 'API Access'],
            'crypto' => ['Trading Platform', 'Staking', 'DeFi Solutions', 'Wallet Security'],
            'cybersecurity' => ['Penetration Testing', 'Security Audits', 'Incident Response', 'Compliance'],
            'devtools' => ['IDE Plugins', 'CI/CD Pipeline', 'Code Review', 'Documentation'],
            'hosting' => ['Shared Hosting', 'VPS Servers', 'Dedicated Servers', 'Cloud Hosting'],
            'itsupport' => ['Help Desk', 'Network Setup', 'Data Recovery', 'Managed IT'],
            'gamedev' => ['Game Design', 'Unity Development', 'Mobile Games', '3D Modeling'],

            // Creative & Media
            'portfolio' => ['Web Design', 'Branding', 'Illustration', 'Freelance Work'],
            'design' => ['Brand Identity', 'UI/UX Design', 'Print Design', 'Packaging'],
            'photography' => ['Weddings', 'Portraits', 'Commercial', 'Events'],
            'videography' => ['Corporate Videos', 'Weddings', 'Documentaries', 'Social Media Content'],
            'animation' => ['2D Animation', '3D Animation', 'Motion Graphics', 'Explainer Videos'],
            'agency' => ['Digital Strategy', 'Creative Campaigns', 'Brand Development', 'Media Buying'],
            'marketing' => ['SEO', 'Social Media', 'PPC Advertising', 'Content Marketing'],
            'music' => ['Recording Studio', 'Mixing & Mastering', 'Songwriting', 'Live Performance'],
            'film' => ['Film Production', 'Post-Production', 'Screenwriting', 'Distribution'],
            'art' => ['Original Paintings', 'Commissioned Works', 'Art Prints', 'Exhibitions'],
            'architecture' => ['Residential Design', 'Commercial Projects', '3D Visualization', 'Interior Planning'],
            'interior' => ['Residential Interiors', 'Office Design', 'Space Planning', 'Furniture Sourcing'],
            'tattoo' => ['Custom Tattoos', 'Cover-Ups', 'Piercings', 'Aftercare'],

            // Content & Publishing
            'blog' => ['Featured Posts', 'Guest Writing', 'Tutorials', 'Newsletter'],
            'personal' => ['About Me', 'Portfolio', 'Blog', 'Contact'],
            'magazine' => ['Latest Issue', 'Subscriptions', 'Advertising', 'Submit a Story'],
            'news' => ['Breaking News', 'Investigations', 'Opinion', 'Newsletters'],
            'podcast' => ['Episodes', 'Sponsorships', 'Guest Booking', 'Merch'],
            'newsletter' => ['Subscribe', 'Archives', 'Premium Content', 'Sponsorships'],
            'author' => ['Books', 'Speaking Events', 'Writing Workshops', 'Manuscript Review'],
            'influencer' => ['Brand Partnerships', 'Content Creation', 'Speaking Engagements', 'Merch Store'],

            // Commerce & Retail
            'ecommerce' => ['New Arrivals', 'Best Sellers', 'Sale', 'Gift Cards'],
            'fashion' => ['Women', 'Men', 'Accessories', 'New Collection'],
            'jewelry' => ['Engagement Rings', 'Custom Pieces', 'Fine Jewelry', 'Repairs'],
            'beauty' => ['Skincare', 'Makeup', 'Hair Care', 'Beauty Boxes'],
            'furniture' => ['Living Room', 'Bedroom', 'Office', 'Custom Orders'],
            'electronics' => ['Smartphones', 'Laptops', 'Audio', 'Accessories'],
            'bookshop' => ['Fiction', 'Non-Fiction', 'Children\'s Books', 'Book Club'],
            'grocery' => ['Fresh Produce', 'Organic Selection', 'Meal Kits', 'Delivery'],
            'pets' => ['Pet Food', 'Grooming', 'Veterinary Care', 'Pet Supplies'],
            'florist' => ['Wedding Bouquets', 'Event Arrangements', 'Sympathy Flowers', 'Subscriptions'],
            'marketplace' => ['Browse Categories', 'Sell on Our Platform', 'Featured Vendors', 'Deals'],

            // Professional Services
            'law' => ['Corporate Law', 'Family Law', 'Litigation', 'Consultation'],
            'finance' => ['Investments', 'Tax Planning', 'Accounting', 'Advisory'],
            'consulting' => ['Strategy Consulting', 'Operations', 'Digital Transformation', 'Workshops'],
            'accounting' => ['Tax Returns', 'Bookkeeping', 'Payroll', 'Business Advisory'],
            'insurance' => ['Life Insurance', 'Home Insurance', 'Auto Insurance', 'Business Coverage'],
            'recruiting' => ['Executive Search', 'Staffing Solutions', 'RPO Services', 'Career Coaching'],
            'translation' => ['Document Translation', 'Certified Translation', 'Localization', 'Interpretation'],
            'realestate' => ['Buy a Home', 'Sell Your Property', 'Rentals', 'Property Valuation'],
            'propertymanagement' => ['Tenant Screening', 'Rent Collection', 'Maintenance', 'Property Marketing'],

            // Health & Wellness
            'medical' => ['General Practice', 'Specialists', 'Emergency', 'Telehealth'],
            'dental' => ['General Dentistry', 'Cosmetic', 'Implants', 'Orthodontics'],
            'fitness' => ['Personal Training', 'Group Classes', 'Nutrition', 'Membership'],
            'yoga' => ['Classes', 'Workshops', 'Retreats', 'Private Sessions'],
            'spa' => ['Massage', 'Facials', 'Body Treatments', 'Packages'],
            'veterinary' => ['Wellness Exams', 'Surgery', 'Dental Care', 'Emergency'],
            'therapy' => ['Individual Therapy', 'Couples Counseling', 'Group Therapy', 'Online Sessions'],
            'mentalhealth' => ['Anxiety Treatment', 'Depression Support', 'PTSD Therapy', 'Mindfulness Programs'],
            'nutrition' => ['Meal Plans', 'Weight Management', 'Sports Nutrition', 'Dietary Consultations'],
            'physiotherapy' => ['Sports Rehab', 'Back & Neck Pain', 'Post-Surgery Recovery', 'Manual Therapy'],
            'pharmacy' => ['Prescriptions', 'Vaccinations', 'Health Screenings', 'Compounding'],

            // Education & Training
            'education' => ['Courses', 'Workshops', 'Certifications', 'Tutoring'],
            'onlinecourse' => ['Video Courses', 'Live Webinars', 'Certifications', 'Mentorship'],
            'coaching' => ['Life Coaching', 'Executive Coaching', 'Career Coaching', 'Group Programs'],
            'tutoring' => ['Math & Science', 'Languages', 'Test Prep', 'Academic Writing'],
            'language' => ['English Courses', 'Business Language', 'Conversation Practice', 'Exam Preparation'],
            'driving' => ['Beginner Lessons', 'Intensive Courses', 'Highway Driving', 'License Test Prep'],
            'childcare' => ['Daycare', 'After-School Programs', 'Early Learning', 'Holiday Camps'],
            'library' => ['Book Catalog', 'Digital Resources', 'Events & Readings', 'Membership'],
            'training' => ['Corporate Training', 'Safety Certifications', 'Leadership Development', 'Online Modules'],

            // Construction & Trade
            'construction' => ['Residential', 'Commercial', 'Renovations', 'Free Estimates'],
            'plumbing' => ['Emergency Repairs', 'Pipe Installation', 'Drain Cleaning', 'Boiler Service'],
            'electrical' => ['Rewiring', 'Lighting Installation', 'Fuse Box Upgrades', 'EV Charger Install'],
            'hvac' => ['AC Installation', 'Heating Repair', 'Duct Cleaning', 'Maintenance Plans'],
            'roofing' => ['Roof Replacement', 'Leak Repairs', 'Flat Roofing', 'Guttering'],
            'painting' => ['Interior Painting', 'Exterior Painting', 'Wallpapering', 'Color Consulting'],
            'landscaping' => ['Garden Design', 'Lawn Care', 'Hardscaping', 'Tree Surgery'],
            'cleaning' => ['Residential Cleaning', 'Office Cleaning', 'Deep Clean', 'End of Tenancy'],
            'moving' => ['Local Moving', 'Long Distance', 'Packing Services', 'Storage Solutions'],
            'handyman' => ['Furniture Assembly', 'Odd Jobs', 'Home Repairs', 'Installation'],
            'solar' => ['Solar Panels', 'Battery Storage', 'EV Charging', 'Energy Audits'],

            // Automotive & Transport
            'automotive' => ['New Vehicles', 'Pre-Owned Cars', 'Financing', 'Trade-In'],
            'mechanic' => ['Engine Repair', 'Brake Service', 'MOT Testing', 'Diagnostics'],
            'carwash' => ['Express Wash', 'Full Detail', 'Interior Clean', 'Ceramic Coating'],
            'taxi' => ['Airport Transfers', 'City Rides', 'Corporate Accounts', 'Hourly Hire'],
            'trucking' => ['Freight Transport', 'Express Delivery', 'Warehousing', 'Fleet Management'],
            'motorcycle' => ['New Bikes', 'Used Motorcycles', 'Parts & Accessories', 'Service & Repair'],
            'boating' => ['Boat Sales', 'Charter Hire', 'Marina Berths', 'Maintenance'],

            // Events & Entertainment
            'events' => ['Corporate Events', 'Conferences', 'Product Launches', 'Team Building'],
            'wedding' => ['Full Planning', 'Day Coordination', 'Venue Styling', 'Floral Design'],
            'party' => ['Birthday Parties', 'DJ & Music', 'Decorations', 'Photo Booths'],
            'venue' => ['Weddings', 'Corporate Events', 'Concerts', 'Private Hire'],
            'theater' => ['Current Shows', 'Season Tickets', 'Group Bookings', 'Backstage Tours'],
            'cinema' => ['Now Showing', 'Coming Soon', 'Private Screenings', 'Gift Vouchers'],
            'escape' => ['Escape Rooms', 'Team Challenges', 'Birthday Packages', 'Corporate Events'],
            'festival' => ['Lineup', 'Tickets', 'Camping Passes', 'VIP Packages'],

            // Travel & Leisure
            'travel' => ['Flights', 'Hotels', 'Tour Packages', 'Travel Insurance'],
            'tourism' => ['Guided Tours', 'Day Trips', 'Cultural Experiences', 'Adventure Activities'],
            'camping' => ['Tent Pitches', 'Glamping Pods', 'Campfire Experiences', 'Equipment Hire'],
            'skiing' => ['Ski Passes', 'Lessons', 'Equipment Rental', 'Accommodation'],
            'diving' => ['PADI Courses', 'Guided Dives', 'Equipment Rental', 'Liveaboards'],
            'golf' => ['Membership', 'Green Fees', 'Pro Shop', 'Lessons'],
            'marina' => ['Berth Rental', 'Boat Storage', 'Fuel Station', 'Chandlery'],

            // Community & Non-Profit
            'nonprofit' => ['Our Programs', 'Donate', 'Volunteer', 'Events'],
            'church' => ['Sunday Services', 'Youth Programs', 'Community Groups', 'Outreach'],
            'volunteer' => ['Find Opportunities', 'Register', 'Impact Stories', 'Partner With Us'],
            'political' => ['Our Platform', 'Campaign Events', 'Donate', 'Volunteer'],
            'community' => ['Events Calendar', 'Membership', 'News & Updates', 'Resources'],
            'association' => ['Join Us', 'Member Benefits', 'Events', 'Publications'],

            // Government & Public
            'government' => ['Services', 'Permits & Licenses', 'Public Notices', 'Contact Us'],
            'police' => ['Report a Crime', 'Community Programs', 'News & Alerts', 'Career Opportunities'],
            'military' => ['Recruitment', 'Training Programs', 'Veterans Support', 'News & Updates'],
            'embassy' => ['Visa Services', 'Passport Renewal', 'Consular Assistance', 'Travel Advisories'],

            // Other / Specialty
            'resume' => ['Resume Templates', 'Cover Letters', 'Portfolio', 'Career Resources'],
            'wiki' => ['Knowledge Base', 'Getting Started', 'API Reference', 'Community'],
            'directory' => ['Browse Listings', 'Add Your Business', 'Featured Listings', 'Categories'],
            'landing' => ['Features', 'Pricing', 'Testimonials', 'Get Started'],
            'comingsoon' => ['Notify Me', 'Our Vision', 'Sneak Peek', 'Follow Us'],
            'memorial' => ['Tribute Wall', 'Photo Gallery', 'Share a Memory', 'Donate'],
            'sports' => ['Training Programs', 'Memberships', 'Facilities', 'Youth Academy'],
            'saas-landing' => ['Features', 'Pricing', 'Integrations', 'Get Started'],
            'other' => ['Our Services', 'What We Offer', 'Solutions', 'Get Started'],
        ];
        return $map[$industry] ?? ['Our Services', 'What We Offer', 'Solutions', 'Get Started'];
    }

    /**
     * CSS Coverage Validator — scans section HTML for prefixed classes,
     * checks if each has at least one CSS rule, generates fallback CSS for gaps.
     */
    private function validateAndFixCssCoverage(string $themeDir, string $css, array $brief): array
    {
        $prefix = \HeaderPatternRegistry::generatePrefix($brief['name'] ?? basename($themeDir));
        $prefixEsc = preg_quote($prefix, '/');

        // 1. Extract ALL prefixed class names from section PHP files + layout.php
        $htmlClasses = [];
        $files = glob($themeDir . '/sections/*.php') ?: [];
        if (file_exists($themeDir . '/layout.php')) $files[] = $themeDir . '/layout.php';

        foreach ($files as $file) {
            $code = file_get_contents($file);
            $basename = pathinfo($file, PATHINFO_FILENAME);

            preg_match_all('/class\s*=\s*["\']([^"\']+)["\']/', $code, $classMatches);
            foreach ($classMatches[1] as $classList) {
                foreach (preg_split('/\s+/', $classList) as $cls) {
                    $cls = trim($cls);
                    // Skip PHP expressions embedded in class
                    $phpOpen = '<' . '?';
                    if (str_contains($cls, $phpOpen) || str_contains($cls, '{$') || str_contains($cls, '${')) continue;
                    if ($cls && preg_match("/^{$prefixEsc}-/", $cls)) {
                        $htmlClasses[$cls] = $basename;
                    }
                }
            }
        }

        if (empty($htmlClasses)) {
            return ['fallback_css' => '', 'stats' => ['html_classes' => 0, 'covered' => 0, 'missing' => 0]];
        }

        // 2. Extract ALL selectors from CSS that reference prefixed classes
        $coveredClasses = [];
        preg_match_all('/\.(' . $prefixEsc . '-[a-zA-Z0-9_-]+)/', $css, $cssMatches);
        foreach ($cssMatches[1] as $cls) {
            $coveredClasses[$cls] = true;
        }

        // 3. Find gaps
        $missing = [];
        foreach ($htmlClasses as $cls => $section) {
            if (!isset($coveredClasses[$cls])) {
                $missing[$section][] = $cls;
            }
        }

        if (empty($missing)) {
            $stats = ['html_classes' => count($htmlClasses), 'covered' => count($htmlClasses), 'missing' => 0, 'sections' => []];
            @file_put_contents('/tmp/aitb-assembly.log',
                date('H:i:s') . " CSS Coverage: 100% ({$stats['html_classes']} classes, 0 missing)\n", FILE_APPEND);
            return ['fallback_css' => '', 'stats' => $stats];
        }

        // 4. Generate fallback CSS for missing classes
        $fallback = "\n/* === CSS Coverage Fallback (auto-generated) === */\n";
        $missingCount = 0;
        foreach ($missing as $section => $classes) {
            $fallback .= "/* Section: {$section} — " . count($classes) . " missing */\n";
            foreach ($classes as $cls) {
                $missingCount++;
                $fallback .= $this->generateFallbackCss($cls, $prefix);
            }
        }

        $stats = [
            'html_classes' => count($htmlClasses),
            'covered' => count($htmlClasses) - $missingCount,
            'missing' => $missingCount,
            'sections' => array_keys($missing),
            'coverage_pct' => round((1 - $missingCount / count($htmlClasses)) * 100, 1),
        ];

        @file_put_contents('/tmp/aitb-assembly.log',
            date('H:i:s') . " CSS Coverage: {$stats['coverage_pct']}% ({$stats['covered']}/{$stats['html_classes']}, {$missingCount} missing: " . implode(', ', $stats['sections']) . ")\n", FILE_APPEND);

        return ['fallback_css' => $fallback, 'stats' => $stats];
    }

    private function generateFallbackCss(string $class, string $prefix): string
    {
        $semantic = preg_replace('/^' . preg_quote($prefix, '/') . '-/', '', $class);
        $parts = explode('-', $semantic);
        if (str_contains($class, '--')) return '';
        $last = end($parts);
        $lastTwo = count($parts) >= 2 ? $parts[count($parts)-2] . '-' . $last : $last;

        // Section root
        if (count($parts) <= 2) return ".{$class} { padding: 80px 0; position: relative; }\n";

        $map = [
            'container,wrapper,inner,content' => "max-width: 1200px; margin: 0 auto; padding: 0 20px;",
            'grid,row,list,items' => "display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;",
            'card,item,block,box' => "background: var(--surface, #1a1a2e); border-radius: 12px; padding: 30px; transition: transform 0.3s ease, box-shadow 0.3s ease;",
            'title,heading,headline,name' => "font-family: var(--font-heading); font-weight: 700; margin-bottom: 10px; color: var(--text);",
            'subtitle,desc,description,text,excerpt,summary' => "color: var(--muted); line-height: 1.7;",
            'label,tag,badge,category' => "font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--primary);",
            'divider,separator,line' => "width: 60px; height: 3px; background: var(--primary); margin: 15px 0; border-radius: 3px;",
            'btn,button,cta,link,action' => "display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; text-decoration: none;",
            'image,img,photo,visual,media,thumb,thumbnail' => "border-radius: 12px; overflow: hidden;",
            'icon,ico' => "font-size: 2rem; color: var(--primary); margin-bottom: 15px;",
            'actions,buttons,ctas' => "display: flex; gap: 15px; flex-wrap: wrap; margin-top: 20px;",
            'overlay,backdrop' => "position: absolute; inset: 0; background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.3) 100%); z-index: 1;",
            'bg,background' => "position: absolute; inset: 0; background-size: cover; background-position: center; z-index: 0;",
            'number,count,stat,value,amount' => "font-size: 2.5rem; font-weight: 800; color: var(--primary); font-family: var(--font-heading);",
            'wave,shape' => "position: absolute; bottom: 0; left: 0; width: 100%; overflow: hidden; line-height: 0;",
            'avatar,profile' => "width: 60px; height: 60px; border-radius: 50%; overflow: hidden;",
            'stars,rating' => "color: #f59e0b; font-size: 1rem; margin-bottom: 10px;",
            'quote,testimonial,review' => "font-style: italic; color: var(--muted); line-height: 1.8; margin-bottom: 20px;",
            'price,cost' => "font-size: 3rem; font-weight: 800; color: var(--text); font-family: var(--font-heading);",
            'gallery,masonry,collage' => "display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;",
        ];

        if ($lastTwo === 'section-header' || $last === 'header') {
            return ".{$class} { text-align: center; margin-bottom: 50px; max-width: 700px; margin-left: auto; margin-right: auto; }\n";
        }

        foreach ($map as $keys => $css) {
            if (in_array($last, explode(',', $keys))) {
                return ".{$class} { {$css} }\n";
            }
        }

        return ".{$class} { /* auto-fallback */ }\n";
    }

    /**
     * Seed button links in sections to point to real sub-pages instead of '#'.
     */
    private function seedButtonLinks(string $slug, array $brief): void
    {
        $pdo = \core\Database::connection();

        $stmt = $pdo->prepare("SELECT slug, title FROM pages WHERE theme_slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($pages)) return;

        $pageUrls = [];
        foreach ($pages as $page) {
            $url = '/page/' . $page['slug'];
            $t = mb_strtolower($page['title']);
            $s = $page['slug'];
            if (str_contains($t, 'contact') || str_contains($s, 'contact')) $pageUrls['contact'] = $url;
            elseif (str_contains($t, 'about') || str_contains($s, 'about')) $pageUrls['about'] = $url;
            elseif (str_contains($t, 'service') || str_contains($s, 'service')) $pageUrls['services'] = $url;
            elseif (str_contains($t, 'pricing') || str_contains($s, 'pricing')) $pageUrls['pricing'] = $url;
            elseif (str_contains($t, 'portfolio') || str_contains($s, 'portfolio') || str_contains($t, 'gallery')) $pageUrls['portfolio'] = $url;
            elseif (str_contains($t, 'team') || str_contains($s, 'team')) $pageUrls['team'] = $url;
            elseif (str_contains($t, 'faq') || str_contains($s, 'faq')) $pageUrls['faq'] = $url;
            elseif (str_contains($t, 'blog') || str_contains($s, 'blog')) $pageUrls['blog'] = $url;
            elseif (str_contains($t, 'testimonial')) $pageUrls['testimonials'] = $url;
            elseif (!isset($pageUrls['generic'])) $pageUrls['generic'] = $url;
        }

        $ctaLink = $pageUrls['contact'] ?? $pageUrls['services'] ?? $pageUrls['about'] ?? $pageUrls['generic'] ?? '/contact';
        $learnMoreLink = $pageUrls['about'] ?? $pageUrls['services'] ?? $pageUrls['generic'] ?? '/about';
        $servicesLink = $pageUrls['services'] ?? $pageUrls['about'] ?? $pageUrls['generic'] ?? '/services';
        $pricingLink = $pageUrls['pricing'] ?? $pageUrls['services'] ?? $ctaLink;
        $portfolioLink = $pageUrls['portfolio'] ?? '/gallery';
        $blogLink = $pageUrls['blog'] ?? '/articles';

        $stmt = $pdo->prepare("
            SELECT id, section, field_key FROM theme_customizations 
            WHERE theme_slug = ? AND field_key LIKE '%btn%link%' AND (field_value = '#' OR field_value = '' OR field_value IS NULL)
        ");
        $stmt->execute([$slug]);
        $deadLinks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($deadLinks)) return;

        $updateStmt = $pdo->prepare("UPDATE theme_customizations SET field_value = ? WHERE id = ?");
        $fixed = 0;

        foreach ($deadLinks as $link) {
            $sec = $link['section'];
            $key = $link['field_key'];
            if (in_array($sec, ['hero', 'cta'])) $url = str_contains($key, 'btn2') ? $learnMoreLink : $ctaLink;
            elseif (in_array($sec, ['features', 'services'])) $url = $servicesLink;
            elseif ($sec === 'about') $url = $learnMoreLink;
            elseif ($sec === 'pricing') $url = $pricingLink;
            elseif (in_array($sec, ['gallery', 'portfolio'])) $url = $portfolioLink;
            elseif (in_array($sec, ['blog', 'articles'])) $url = $blogLink;
            else $url = $servicesLink;

            $updateStmt->execute([$url, $link['id']]);
            $fixed++;
        }

        @file_put_contents('/tmp/aitb-assembly.log',
            date('H:i:s') . " seedButtonLinks: fixed {$fixed} dead links for {$slug}\n", FILE_APPEND);
    }

    /**
     * Regenerate CSS for a single section of an existing theme.
     * Finds existing CSS rules for the section, replaces them with freshly generated CSS.
     */
    public function regenerateSectionCss(string $slug, string $sectionId, string $instructions = ''): array
    {
        $themeDir = CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir) || !file_exists($themeDir . '/theme.json')) {
            return ['ok' => false, 'error' => 'Theme not found'];
        }

        $brief = json_decode(file_get_contents($themeDir . '/theme.json'), true);
        if (!$brief) return ['ok' => false, 'error' => 'Invalid theme.json'];

        $this->slug = $slug;

        // Verify section exists
        $sectionFile = $themeDir . '/sections/' . $sectionId . '.php';
        if (!file_exists($sectionFile)) {
            return ['ok' => false, 'error' => "Section '{$sectionId}' not found"];
        }

        // Read current CSS (prefer dev version for readability, fall back to minified)
        $cssFile = $themeDir . '/assets/css/style.css';
        $devCssFile = $themeDir . '/assets/css/style.dev.css';
        if (!file_exists($cssFile)) {
            return ['ok' => false, 'error' => 'style.css not found'];
        }
        $currentCss = file_exists($devCssFile) ? file_get_contents($devCssFile) : file_get_contents($cssFile);

        // Determine theme prefix
        $prefix = \HeaderPatternRegistry::generatePrefix($brief['name'] ?? $slug);

        // Extract class names from the section PHP file
        $sectionHtml = file_get_contents($sectionFile);
        $sectionClasses = $this->extractClassesFromHtml($sectionHtml);

        // Also include prefix-based section classes (e.g., {prefix}-hero, {prefix}-hero-*)
        $prefixedSectionClasses = [];
        foreach ($sectionClasses as $cls) {
            if (str_starts_with($cls, $prefix . '-' . $sectionId)) {
                $prefixedSectionClasses[] = $cls;
            }
        }

        // If we couldn't find prefixed classes, scan by prefix-sectionId pattern
        if (empty($prefixedSectionClasses)) {
            $pattern = $prefix . '-' . $sectionId;
            preg_match_all('/\.(' . preg_quote($pattern, '/') . '[\w-]*)\b/', $currentCss, $cssClassMatches);
            if (!empty($cssClassMatches[1])) {
                $prefixedSectionClasses = array_unique($cssClassMatches[1]);
            }
        }

        // Build the full list of classes to regenerate
        $targetClasses = array_unique(array_merge($sectionClasses, $prefixedSectionClasses));
        if (empty($targetClasses)) {
            return ['ok' => false, 'error' => "No CSS classes found for section '{$sectionId}'"];
        }

        // Determine the pattern registry and get decorative guide
        $sectionRegistryMap = [
            'hero' => 'HeroPatternRegistry',
            'features' => 'FeaturesPatternRegistry',
            'services' => 'FeaturesPatternRegistry',
            'about' => 'AboutPatternRegistry',
            'testimonials' => 'TestimonialsPatternRegistry',
            'pricing' => 'PricingPatternRegistry',
            'cta' => 'CTAPatternRegistry',
            'faq' => 'FAQPatternRegistry',
            'stats' => 'StatsPatternRegistry',
            'clients' => 'ClientsPatternRegistry',
            'partners' => 'ClientsPatternRegistry',
            'gallery' => 'GalleryPatternRegistry',
            'portfolio' => 'GalleryPatternRegistry',
            'team' => 'TeamPatternRegistry',
            'blog' => 'BlogPatternRegistry',
            'articles' => 'BlogPatternRegistry',
            'contact' => 'ContactPatternRegistry',
            'newsletter' => 'CTAPatternRegistry',
        ];

        // Try to detect pattern ID from section HTML comments or class modifiers
        $patternId = 'unknown';
        $registryClass = $sectionRegistryMap[$sectionId] ?? null;

        // Check for pattern modifier class like {prefix}-hero--gradient, {prefix}-features--grid-3col
        $sectionMainClass = $prefix . '-' . $sectionId;
        if (preg_match('/\b' . preg_quote($sectionMainClass, '/') . '--([a-z0-9-]+)/', $sectionHtml, $modMatch)) {
            $patternId = $modMatch[1];
        }

        // Get decorative guide from pattern registry
        $decorativeGuide = '';
        if ($registryClass && class_exists('\\' . $registryClass)) {
            $fqClass = '\\' . $registryClass;
            if (method_exists($fqClass, 'getDecorativeGuide')) {
                $decorativeGuide = $fqClass::getDecorativeGuide($patternId);
            }
        }

        // Build class list string
        $classList = implode(', ', array_map(fn($c) => '.' . $c, $targetClasses));

        // Build color context from theme.json
        $colors = $brief['colors'] ?? [];
        $colorBlock = '';
        foreach ($colors as $name => $value) {
            $colorBlock .= "  --{$name}: {$value};\n";
        }

        $typography = $brief['typography'] ?? [];
        $headingFont = $typography['headingFont'] ?? $typography['fontFamily'] ?? 'sans-serif';
        $bodyFont = $typography['fontFamily'] ?? 'sans-serif';

        // Build the AI prompt
        $sectionIdUpper = strtoupper($sectionId);
        $decorativeBlock = $decorativeGuide
            ? "\n⚡ PATTERN-SPECIFIC visual approach for \"{$patternId}\":\n{$decorativeGuide}"
            : '';

        $instructionBlock = $instructions
            ? "\n\nADDITIONAL USER INSTRUCTIONS:\n{$instructions}"
            : '';

        // Strip PHP from section HTML for reference
        $htmlForRef = preg_replace('/<\?php.*?\?>/s', '', $sectionHtml);
        $htmlForRef = preg_replace('/\s+/', ' ', $htmlForRef);
        if (strlen($htmlForRef) > 4000) {
            $htmlForRef = substr($htmlForRef, 0, 4000) . '<!-- truncated -->';
        }

        $systemPrompt = <<<PROMPT
You are a senior CSS developer. Generate DECORATIVE CSS for a single section of a website theme.

THEME COLORS (CSS variables available):
:root {
{$colorBlock}}

TYPOGRAPHY:
- Heading font: {$headingFont}
- Body font: {$bodyFont}

SECTION: {$sectionIdUpper}
Pattern: "{$patternId}"

HTML STRUCTURE (PHP tags stripped):
{$htmlForRef}

CLASS NAMES to style (EXACT — do NOT rename or invent alternatives):
{$classList}

RULES:
- Write ONLY decorative CSS (colors, fonts, backgrounds, borders, border-radius, box-shadow, opacity, transitions, transforms on hover, text-decoration, letter-spacing, line-height, font-size, font-weight, font-family, cursor, filter, backdrop-filter, text-shadow, animation, outline)
- DO NOT write structural CSS (position, display, flex, grid, width, height, max-width, min-height, padding, margin, overflow, z-index, order, gap, align-items, justify-content, flex-direction, flex-wrap, text-align, grid-template-columns, inset) — structural CSS is injected separately
- Use CSS variables (var(--primary), var(--text), etc.) for theme colors
- Include hover effects and transitions on interactive elements
- Use clamp() for responsive font sizes on headings
- Include @media responsive adjustments for decorative properties only
- Create a visually DIFFERENT variation from the current design while maintaining the same theme identity
- Write CSS for ALL classes listed above{$decorativeBlock}{$instructionBlock}

Return ONLY CSS code. No markdown backticks, no explanation. No \`\`\`css wrapper.
PROMPT;

        try {
            $t0 = microtime(true);

            $result = $this->aiQuery("Generate decorative CSS for the {$sectionId} section", $this->queryOptions([
                'system_prompt' => $systemPrompt,
                'max_tokens' => 4000,
                'temperature' => 0.7,
            ]));

            if (empty($result['ok']) || empty($result['text'])) {
                return ['ok' => false, 'error' => 'AI generation failed: ' . ($result['error'] ?? 'No CSS generated')];
            }

            $newSectionCss = $result['text'];
            $newSectionCss = preg_replace('/^```(?:css)?\s*/m', '', $newSectionCss);
            $newSectionCss = preg_replace('/```\s*$/m', '', $newSectionCss);
            $newSectionCss = trim($newSectionCss);

            if (strlen($newSectionCss) < 50) {
                return ['ok' => false, 'error' => 'Generated CSS too short (' . strlen($newSectionCss) . ' chars)'];
            }

            // Strip structural properties from new CSS
            $newSectionCss = $this->stripStructuralFromAiCss($newSectionCss, $slug);

            $timing = (int)((microtime(true) - $t0) * 1000);

            // Replace old section CSS in style.css
            $updatedCss = $this->replaceSectionCss($currentCss, $sectionId, $prefix, $newSectionCss, $targetClasses);

            // Write updated CSS (both minified and dev)
            file_put_contents($themeDir . '/assets/css/style.dev.css', $updatedCss);
            file_put_contents($cssFile, self::minifyCss($updatedCss));
            $this->chownRecursive($themeDir . '/assets/css');

            return [
                'ok' => true,
                'slug' => $slug,
                'section_id' => $sectionId,
                'css_lines' => substr_count($newSectionCss, "\n") + 1,
                'timing_ms' => $timing,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Extract CSS class names from HTML/PHP source.
     */
    private function extractClassesFromHtml(string $html): array
    {
        $classes = [];
        // Match class="..." attributes
        preg_match_all('/class="([^"]*)"/', $html, $matches);
        foreach ($matches[1] as $classStr) {
            foreach (preg_split('/\s+/', $classStr) as $cls) {
                $cls = trim($cls);
                // Skip PHP expressions, empty strings, and non-CSS tokens
                if ($cls && !str_starts_with($cls, '<?') && !str_contains($cls, '<?')
                    && !str_contains($cls, '(') && !str_contains($cls, ')')
                    && !str_contains($cls, '$') && !str_contains($cls, '?>')
                    && preg_match('/^[a-zA-Z][\w-]*$/', $cls)) {
                    $classes[] = $cls;
                }
            }
        }
        return array_values(array_unique($classes));
    }

    /**
     * Replace section-specific CSS rules in the full stylesheet.
     * First tries marker-based replacement, then falls back to selector-based.
     */
    private function replaceSectionCss(string $css, string $sectionId, string $prefix, string $newCss, array $sectionClasses = []): string
    {
        $markerStart = "/* ═══ SECTION: {$sectionId} ═══ */";
        $markerEnd = "/* ═══ END: {$sectionId} ═══ */";

        // Strategy 1: Marker-based replacement
        $startPos = strpos($css, $markerStart);
        $endPos = strpos($css, $markerEnd);
        if ($startPos !== false && $endPos !== false && $endPos > $startPos) {
            $before = substr($css, 0, $startPos);
            $after = substr($css, $endPos + strlen($markerEnd));
            return $before . $markerStart . "\n" . $newCss . "\n" . $markerEnd . $after;
        }

        // Strategy 2: Remove all rules targeting section classes, insert new block with markers
        // Build list of prefixes to match — includes singular form (e.g., "feature" for "features")
        $prefixes = [$prefix . '-' . $sectionId];
        // Add singular form if section ID ends with 's'
        if (str_ends_with($sectionId, 's') && strlen($sectionId) > 1) {
            $prefixes[] = $prefix . '-' . substr($sectionId, 0, -1);
        }

        // Find and remove CSS rules matching any of these prefixes or exact class names
        $cleanedCss = $this->removeSectionRules($css, $prefix, $sectionId, $sectionClasses);

        // Find insertion point: before structural CSS section or at end of decorative CSS
        $structuralMarker = "/* ═══ Structural CSS (auto-generated — do not edit) ═══ */";
        $heroStructuralMarker = "/* ═══ Hero Structural CSS (auto-generated — do not edit) ═══ */";

        // Try to find the first structural CSS marker
        $insertBefore = null;
        foreach ([
            $structuralMarker,
            $heroStructuralMarker,
            "/* ═══ " . ucfirst($sectionId) . " Structural CSS",
        ] as $marker) {
            $pos = strpos($cleanedCss, $marker);
            if ($pos !== false) {
                if ($insertBefore === null || $pos < $insertBefore) {
                    $insertBefore = $pos;
                }
            }
        }

        // Also look for any structural CSS marker pattern
        if ($insertBefore === null) {
            if (preg_match('/\/\* ═══ \w+ Structural CSS/', $cleanedCss, $m, PREG_OFFSET_CAPTURE)) {
                $insertBefore = $m[0][1];
            }
        }

        $newBlock = "\n{$markerStart}\n{$newCss}\n{$markerEnd}\n";

        if ($insertBefore !== null) {
            return substr($cleanedCss, 0, $insertBefore) . $newBlock . "\n" . substr($cleanedCss, $insertBefore);
        }

        // Fallback: append before end
        return $cleanedCss . $newBlock;
    }

    /**
     * Remove all CSS rules that target a specific section's classes.
     * Handles both top-level rules and rules inside @media blocks.
     */
    private function removeSectionRules(string $css, string $prefix, string $sectionId, array $sectionClasses = []): string
    {
        // Build list of prefixes to check: both plural and singular forms
        $sectionPrefixes = [$prefix . '-' . $sectionId];
        if (str_ends_with($sectionId, 's') && strlen($sectionId) > 1) {
            $sectionPrefixes[] = $prefix . '-' . substr($sectionId, 0, -1);
        }

        // Build a set of prefixed class names from section HTML for exact matching
        $prefixedClassSet = [];
        foreach ($sectionClasses as $cls) {
            if (str_starts_with($cls, $prefix . '-')) {
                $prefixedClassSet[$cls] = true;
            }
        }

        $sectionPrefix = $prefix . '-' . $sectionId;

        // Parse CSS into tokens: rules and @-blocks
        $result = '';
        $i = 0;
        $len = strlen($css);

        while ($i < $len) {
            // Skip whitespace
            $wsStart = $i;
            while ($i < $len && ctype_space($css[$i])) $i++;
            $whitespace = substr($css, $wsStart, $i - $wsStart);

            if ($i >= $len) {
                $result .= $whitespace;
                break;
            }

            // Check for comment
            if (substr($css, $i, 2) === '/*') {
                $commentEnd = strpos($css, '*/', $i + 2);
                if ($commentEnd === false) {
                    $result .= $whitespace . substr($css, $i);
                    break;
                }
                $comment = substr($css, $i, $commentEnd + 2 - $i);
                $result .= $whitespace . $comment;
                $i = $commentEnd + 2;
                continue;
            }

            // Check for @media or other @-rule
            if ($css[$i] === '@') {
                // Find the opening brace
                $bracePos = strpos($css, '{', $i);
                if ($bracePos === false) {
                    $result .= $whitespace . substr($css, $i);
                    break;
                }
                $atRule = substr($css, $i, $bracePos - $i);

                // Find matching closing brace
                $blockEnd = $this->findMatchingBrace($css, $bracePos);
                if ($blockEnd === false) {
                    $result .= $whitespace . substr($css, $i);
                    break;
                }

                $blockContent = substr($css, $bracePos + 1, $blockEnd - $bracePos - 1);

                // For @media blocks, process inner rules
                if (stripos($atRule, '@media') !== false || stripos($atRule, '@supports') !== false) {
                    $filteredContent = $this->removeSectionRulesFlat($blockContent, $sectionPrefix, $sectionPrefixes, $prefixedClassSet);
                    // Only keep @media block if it still has content
                    $trimmedContent = trim($filteredContent);
                    if (!empty($trimmedContent)) {
                        $result .= $whitespace . $atRule . '{' . $filteredContent . '}';
                    }
                } else {
                    // Other @-rules (e.g., @keyframes, @font-face): keep as-is
                    $result .= $whitespace . substr($css, $i, $blockEnd + 1 - $i);
                }
                $i = $blockEnd + 1;
                continue;
            }

            // Regular rule: find selector then { ... }
            $bracePos = strpos($css, '{', $i);
            if ($bracePos === false) {
                $result .= $whitespace . substr($css, $i);
                break;
            }

            $selector = substr($css, $i, $bracePos - $i);
            $blockEnd = $this->findMatchingBrace($css, $bracePos);
            if ($blockEnd === false) {
                $result .= $whitespace . substr($css, $i);
                break;
            }

            $fullRule = substr($css, $i, $blockEnd + 1 - $i);

            // Check if selector targets this section (check all prefixes + exact classes)
            if ($this->selectorTargetsSectionMulti($selector, $sectionPrefixes, $prefixedClassSet)) {
                // Skip this rule (remove it)
                $i = $blockEnd + 1;
                continue;
            }

            $result .= $whitespace . $fullRule;
            $i = $blockEnd + 1;
        }

        return $result;
    }

    /**
     * Remove section-specific rules from a flat CSS block (inside @media).
     */
    private function removeSectionRulesFlat(string $css, string $sectionPrefix, array $sectionPrefixes = [], array $prefixedClassSet = []): string
    {
        $result = '';
        $i = 0;
        $len = strlen($css);

        while ($i < $len) {
            $wsStart = $i;
            while ($i < $len && ctype_space($css[$i])) $i++;
            $whitespace = substr($css, $wsStart, $i - $wsStart);

            if ($i >= $len) {
                $result .= $whitespace;
                break;
            }

            // Comment
            if (substr($css, $i, 2) === '/*') {
                $commentEnd = strpos($css, '*/', $i + 2);
                if ($commentEnd === false) {
                    $result .= $whitespace . substr($css, $i);
                    break;
                }
                $result .= $whitespace . substr($css, $i, $commentEnd + 2 - $i);
                $i = $commentEnd + 2;
                continue;
            }

            $bracePos = strpos($css, '{', $i);
            if ($bracePos === false) {
                $result .= $whitespace . substr($css, $i);
                break;
            }

            $selector = substr($css, $i, $bracePos - $i);
            $blockEnd = $this->findMatchingBrace($css, $bracePos);
            if ($blockEnd === false) {
                $result .= $whitespace . substr($css, $i);
                break;
            }

            $fullRule = substr($css, $i, $blockEnd + 1 - $i);

            if ($this->selectorTargetsSectionMulti($selector, $sectionPrefixes ?: [$sectionPrefix], $prefixedClassSet)) {
                $i = $blockEnd + 1;
                continue;
            }

            $result .= $whitespace . $fullRule;
            $i = $blockEnd + 1;
        }

        return $result;
    }

    /**
     * Check if a CSS selector targets a section by prefix.
     * e.g., ".nhr-hero" prefix matches ".nhr-hero-bg", ".nhr-hero:hover", ".nhr-hero--gradient"
     */
    private function selectorTargetsSection(string $selector, string $sectionPrefix): bool
    {
        // Check if selector contains .{prefix}-{sectionId} as a class selector
        // Must match: .nhr-hero, .nhr-hero-bg, .nhr-hero--variant, .nhr-hero:hover
        // Must NOT match: .nhr-heroics (different section), .nhr-header (different section)
        $pattern = '/\.' . preg_quote($sectionPrefix, '/') . '(?:--|[-:.\s,\[{]|$)/';
        return (bool) preg_match($pattern, $selector);
    }

    /**
     * Check if a CSS selector targets a section using multiple prefixes or exact class names.
     */
    private function selectorTargetsSectionMulti(string $selector, array $sectionPrefixes, array $prefixedClassSet = []): bool
    {
        // Check each prefix
        foreach ($sectionPrefixes as $sectionPrefix) {
            if ($this->selectorTargetsSection($selector, $sectionPrefix)) {
                return true;
            }
        }
        // Check exact class names from the section HTML
        foreach ($prefixedClassSet as $cls => $_) {
            if (str_contains($selector, '.' . $cls)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Find the position of the matching closing brace for an opening brace.
     */
    private function findMatchingBrace(string $css, int $openPos): int|false
    {
        $depth = 0;
        $len = strlen($css);
        for ($i = $openPos; $i < $len; $i++) {
            if ($css[$i] === '{') $depth++;
            elseif ($css[$i] === '}') {
                $depth--;
                if ($depth === 0) return $i;
            }
        }
        return false;
    }

    /**
     * Add section CSS markers to a complete stylesheet.
     * Groups CSS rules by section based on class prefix, wrapping them in marker comments.
     * Used during assembly and CSS regeneration to enable per-section replacement later.
     */
    public function addSectionCssMarkers(string $css, string $prefix, array $sectionIds): string
    {
        // Collect rules per section and non-section rules
        $sectionRules = [];
        $otherRules = '';
        $i = 0;
        $len = strlen($css);

        foreach ($sectionIds as $sid) {
            $sectionRules[$sid] = '';
        }

        while ($i < $len) {
            $wsStart = $i;
            while ($i < $len && ctype_space($css[$i])) $i++;
            $whitespace = substr($css, $wsStart, $i - $wsStart);

            if ($i >= $len) {
                $otherRules .= $whitespace;
                break;
            }

            // Comment
            if (substr($css, $i, 2) === '/*') {
                $commentEnd = strpos($css, '*/', $i + 2);
                if ($commentEnd === false) {
                    $otherRules .= $whitespace . substr($css, $i);
                    break;
                }
                $comment = substr($css, $i, $commentEnd + 2 - $i);
                // Skip AI-generated section header comments (we'll replace them with our markers)
                if (!preg_match('/={5,}.*(?:SECTION|HERO|FEATURES|ABOUT|TESTIMONIAL|CTA|PRICING|FAQ|STAT|CLIENT|GALLERY|TEAM|BLOG|CONTACT)/i', $comment)) {
                    $otherRules .= $whitespace . $comment;
                }
                $i = $commentEnd + 2;
                continue;
            }

            // @-rule
            if ($css[$i] === '@') {
                $bracePos = strpos($css, '{', $i);
                if ($bracePos === false) {
                    $otherRules .= $whitespace . substr($css, $i);
                    break;
                }
                $atRule = substr($css, $i, $bracePos - $i);
                $blockEnd = $this->findMatchingBrace($css, $bracePos);
                if ($blockEnd === false) {
                    $otherRules .= $whitespace . substr($css, $i);
                    break;
                }

                $fullBlock = substr($css, $i, $blockEnd + 1 - $i);

                if (stripos($atRule, '@media') !== false || stripos($atRule, '@supports') !== false) {
                    // Check inner content for section-specific rules
                    $blockContent = substr($css, $bracePos + 1, $blockEnd - $bracePos - 1);
                    $assigned = false;
                    foreach ($sectionIds as $sid) {
                        $secPrefixes2 = [$prefix . '-' . $sid];
                        if (str_ends_with($sid, 's') && strlen($sid) > 1) {
                            $secPrefixes2[] = $prefix . '-' . substr($sid, 0, -1);
                        }
                        foreach ($secPrefixes2 as $secPrefix) {
                            if (preg_match('/\.' . preg_quote($secPrefix, '/') . '(?:--|[-:.\s,\[{]|$)/', $blockContent)) {
                                $sectionRules[$sid] .= $whitespace . $fullBlock;
                                $assigned = true;
                                break 2;
                            }
                        }
                    }
                    if (!$assigned) {
                        $otherRules .= $whitespace . $fullBlock;
                    }
                } else {
                    $otherRules .= $whitespace . $fullBlock;
                }
                $i = $blockEnd + 1;
                continue;
            }

            // Regular rule
            $bracePos = strpos($css, '{', $i);
            if ($bracePos === false) {
                $otherRules .= $whitespace . substr($css, $i);
                break;
            }

            $selector = substr($css, $i, $bracePos - $i);
            $blockEnd = $this->findMatchingBrace($css, $bracePos);
            if ($blockEnd === false) {
                $otherRules .= $whitespace . substr($css, $i);
                break;
            }

            $fullRule = substr($css, $i, $blockEnd + 1 - $i);

            $assigned = false;
            foreach ($sectionIds as $sid) {
                // Check both plural and singular prefix forms
                $secPrefixes = [$prefix . '-' . $sid];
                if (str_ends_with($sid, 's') && strlen($sid) > 1) {
                    $secPrefixes[] = $prefix . '-' . substr($sid, 0, -1);
                }
                foreach ($secPrefixes as $secPrefix) {
                    if ($this->selectorTargetsSection($selector, $secPrefix)) {
                        $sectionRules[$sid] .= $whitespace . $fullRule;
                        $assigned = true;
                        break 2;
                    }
                }
            }
            if (!$assigned) {
                $otherRules .= $whitespace . $fullRule;
            }

            $i = $blockEnd + 1;
        }

        // Rebuild CSS with markers
        $result = $otherRules;
        foreach ($sectionIds as $sid) {
            $rules = trim($sectionRules[$sid]);
            if (!empty($rules)) {
                $result .= "\n\n/* ═══ SECTION: {$sid} ═══ */\n";
                $result .= $rules . "\n";
                $result .= "/* ═══ END: {$sid} ═══ */\n";
            }
        }

        return $result;
    }

    /**
     * Minify CSS — strip comments (except structural markers), collapse whitespace.
     * Used for production style.css; original kept as style.dev.css.
     */
    private static function minifyCss(string $css): string
    {
        // Remove CSS comments but preserve structural markers (═══)
        $css = preg_replace_callback('/\/\*.*?\*\//s', function($m) {
            return (strpos($m[0], '═') !== false) ? $m[0] : '';
        }, $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([{};:,>~+])\s*/', '$1', $css);
        $css = str_replace(';}', '}', $css);
        return trim($css);
    }

    /**
     * Returns the PHP template for the products-showcase homepage section.
     * Uses real product data from the database — not AI-generated.
     */
    private function getProductsShowcaseTemplate(): string
    {
        return <<<'SECTION'
<?php
// Products Showcase Section — displays featured/active products
require_once CMS_ROOT . '/core/shop.php';
$featured = \Shop::getFeaturedProducts(8);
if (empty($featured)) {
    $result = \Shop::getProducts(['status' => 'active'], 1, 8);
    $featured = $result['products'] ?? [];
}
if (!empty($featured)):
?>
<section class="section products-showcase">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title" data-ts="products-showcase.title"><?= esc(theme_get('products-showcase.title', 'Featured Products')) ?></h2>
            <p class="section-subtitle" data-ts="products-showcase.subtitle"><?= esc(theme_get('products-showcase.subtitle', 'Discover our most popular items')) ?></p>
        </div>
        <div class="product-grid">
            <?php foreach ($featured as $p): ?>
            <div class="product-card">
                <a href="/shop/<?= htmlspecialchars($p['slug'], ENT_QUOTES, 'UTF-8') ?>" class="product-card-link">
                    <div class="product-card-image">
                        <?php if (!empty($p['image'])): ?>
                            <img src="<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                        <?php else: ?>
                            <div class="product-card-placeholder">📦</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-body">
                        <h3 class="product-card-title"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="product-card-price">
                            <?php if ($p['sale_price'] !== null && (float)$p['sale_price'] > 0): ?>
                                <span class="original"><?= \Shop::formatPrice((float)$p['price']) ?></span>
                                <span class="sale"><?= \Shop::formatPrice((float)$p['sale_price']) ?></span>
                            <?php else: ?>
                                <?= \Shop::formatPrice((float)$p['price']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:32px">
            <a href="/shop" class="btn btn-primary"><?= esc(theme_get('products-showcase.cta_text', 'View All Products')) ?></a>
        </div>
    </div>
</section>
<?php endif; ?>
SECTION;
    }

        private function chownRecursive(string $path): void
    {
        if (!@chown($path, 'www-data') || !@chgrp($path, 'www-data')) {
            // Fallback: ensure group-writable if chown fails (non-root PHP)
            @chmod($path, is_dir($path) ? 0775 : 0664);
        }
        if (is_dir($path)) {
            foreach (scandir($path) as $item) {
                if ($item === '.' || $item === '..') continue;
                $this->chownRecursive($path . '/' . $item);
            }
        }
    }
}
