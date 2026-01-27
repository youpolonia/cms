<?php
declare(strict_types=1);
/**
 * AI Designer - Main Orchestrator
 * 
 * Multi-step AI agent that creates complete website themes.
 * Coordinates: Analysis → Design System → Pages → Header/Footer → Export
 *
 * Supports 10 design styles from AI Theme Builder:
 * modern, corporate, creative, minimal, elegant, vintage, luxury, bold, organic, industrial
 *
 * @package AiDesigner
 * @version 4.0
 */

namespace Core\AiDesigner;

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

require_once __DIR__ . '/Theme.php';
require_once __DIR__ . '/Analyzer.php';
require_once __DIR__ . '/DesignSystem.php';
require_once __DIR__ . '/PageBuilder.php';
require_once __DIR__ . '/HeaderFooterBuilder.php';
require_once __DIR__ . '/ThemeExporter.php';

class Designer
{
    private array $aiSettings;
    private string $themesPath;
    private Analyzer $analyzer;
    private ?DesignSystem $designSystem = null;
    private ?PageBuilder $pageBuilder = null;
    private ?HeaderFooterBuilder $headerFooterBuilder = null;
    private ?ThemeExporter $exporter = null;
    private ?\Closure $progressCallback = null;
    
    // ═══════════════════════════════════════════════════════════════════════════
    // 10 DESIGN STYLES (matching AI Theme Builder UI)
    // ═══════════════════════════════════════════════════════════════════════════
    
    private array $personalities = [
        'modern' => [
            'name' => 'Modern & Clean Designer',
            'traits' => 'Contemporary, clean lines, whitespace, geometric shapes, professional',
            'influences' => 'Apple, Stripe, modern SaaS products'
        ],
        'corporate' => [
            'name' => 'Corporate Designer',
            'traits' => 'Professional, trustworthy, structured, conservative colors',
            'influences' => 'Banks, law firms, enterprise software'
        ],
        'creative' => [
            'name' => 'Creative & Bold Designer',
            'traits' => 'Artistic, vibrant colors, unique layouts, expressive',
            'influences' => 'Design agencies, art galleries, creative studios'
        ],
        'minimal' => [
            'name' => 'Minimal Designer',
            'traits' => 'Ultra-clean, maximum whitespace, simple typography, monochromatic',
            'influences' => 'Apple, Muji, Scandinavian design'
        ],
        'elegant' => [
            'name' => 'Elegant Designer',
            'traits' => 'Refined, serif fonts, warm tones, sophisticated, premium feel',
            'influences' => 'Luxury brands, fine dining, boutique hotels'
        ],
        'vintage' => [
            'name' => 'Vintage & Classic Designer',
            'traits' => 'Retro-inspired, classic typography, warm earthy tones, nostalgic',
            'influences' => 'Classic brands, heritage companies, artisan businesses'
        ],
        'luxury' => [
            'name' => 'Luxury Designer',
            'traits' => 'High-end, dark backgrounds, gold accents, exclusive feel',
            'influences' => 'Rolex, Rolls-Royce, luxury fashion houses'
        ],
        'bold' => [
            'name' => 'Bold & Dynamic Designer',
            'traits' => 'High contrast, large typography, striking colors, energetic',
            'influences' => 'Nike, Spotify, modern startups'
        ],
        'organic' => [
            'name' => 'Organic & Natural Designer',
            'traits' => 'Natural colors, rounded shapes, earthy tones, friendly',
            'influences' => 'Eco brands, wellness companies, organic products'
        ],
        'industrial' => [
            'name' => 'Industrial Designer',
            'traits' => 'Raw, utilitarian, exposed elements, monochrome with accent',
            'influences' => 'Factories, workshops, construction companies'
        ]
    ];

    public function __construct(array $aiSettings)
    {
        $this->aiSettings = $aiSettings;
        $this->themesPath = CMS_ROOT . '/themes';
        $this->analyzer = new Analyzer($aiSettings);
    }

    /**
     * Create complete theme from user input
     * 
     * @param array $input User input with brief, business_name, industry, design_style, pages
     * @param callable|null $onProgress Callback for progress updates
     * @return Theme Complete theme object
     */
    public function create(array $input, ?callable $onProgress = null): Theme
    {
        // Store progress callback
        $this->progressCallback = $onProgress;
        
        // Validate input
        $this->validateInput($input);
        
        // Generate unique theme slug
        $themeSlug = $this->generateSlug($input['business_name']);
        $themePath = $this->themesPath . '/' . $themeSlug;
        
        // Create theme directory structure
        $this->createThemeDirectories($themePath);
        
        // Initialize theme object
        $theme = new Theme([
            'slug' => $themeSlug,
            'path' => $themePath,
            'name' => $input['business_name'],
            'brief' => $input['brief'],
            'industry' => $input['industry'],
            'pages' => $input['pages']
        ]);

        // ═══════════════════════════════════════════════════════════════════════
        // STEP 1: ANALYSIS
        // ═══════════════════════════════════════════════════════════════════════
        $this->log('STEP 1: Analysis started');
        $this->progress(1, 5, 'Analyzing requirements', 'Understanding your business and goals...');
        $analysis = $this->analyzer->analyze($input);
        $theme->setAnalysis($analysis);
        
        // Determine style (from user or auto-selected by analyzer)
        $designStyle = $input['design_style'] ?? $input['style'] ?? 'auto';
        if ($designStyle === 'auto') {
            $designStyle = $analysis['design_style'] ?? 'modern';
        }
        $theme->setPersonality($designStyle);
        $this->log("Design style selected: {$designStyle}");

        // ═══════════════════════════════════════════════════════════════════════
        // STEP 2: DESIGN SYSTEM
        // ═══════════════════════════════════════════════════════════════════════
        $this->log('STEP 2: Design System generation started');
        $this->progress(2, 5, 'Creating design system', 'Generating colors, typography, and spacing...');
        $this->designSystem = new DesignSystem($this->aiSettings);
        $designSystemData = $this->designSystem->generate(
            $analysis,
            $this->personalities[$designStyle] ?? $this->personalities['modern'],
            $input
        );
        $theme->setDesignSystem($designSystemData);
        
        // Save design system as CSS
        $this->saveDesignSystemCSS($themePath, $designSystemData);
        $this->log('Design System saved');

        // ═══════════════════════════════════════════════════════════════════════
        // STEP 3: BUILD PAGES
        // ═══════════════════════════════════════════════════════════════════════
        $this->log('STEP 3: Page building started');
        $this->progress(3, 5, 'Building pages', 'Creating page layouts and content...');
        $this->pageBuilder = new PageBuilder($this->aiSettings);
        
        foreach ($input['pages'] as $pageName) {
            $this->log("Building page: {$pageName}");
            $pageHtml = $this->pageBuilder->build(
                $pageName,
                $analysis,
                $designSystemData,
                $this->personalities[$designStyle] ?? $this->personalities['modern'],
                $input
            );
            
            // Save page PHP file
            $this->savePageFile($themePath, $pageName, $pageHtml);
            $theme->addPage($pageName, $pageHtml);
        }
        $this->log('All pages built');

        // ═══════════════════════════════════════════════════════════════════════
        // STEP 4: HEADER + FOOTER
        // ═══════════════════════════════════════════════════════════════════════
        $this->log('STEP 4: Header/Footer building started');
        $this->progress(4, 5, 'Building header & footer', 'Creating navigation and footer sections...');
        $this->headerFooterBuilder = new HeaderFooterBuilder($this->aiSettings);
        
        // Build header
        $headerHtml = $this->headerFooterBuilder->buildHeader(
            $analysis,
            $designSystemData,
            $input['pages'],
            $input
        );
        $this->saveFile($themePath . '/header.php', $headerHtml);
        $theme->setHeader($headerHtml);
        
        // Build footer
        $footerHtml = $this->headerFooterBuilder->buildFooter(
            $analysis,
            $designSystemData,
            $input['pages'],
            $input
        );
        $this->saveFile($themePath . '/footer.php', $footerHtml);
        $theme->setFooter($footerHtml);
        $this->log('Header/Footer built');

        // ═══════════════════════════════════════════════════════════════════════
        // STEP 5: EXPORT TO TB JSON
        // ═══════════════════════════════════════════════════════════════════════
        $this->log('STEP 5: TB JSON export started');
        $this->progress(5, 5, 'Finalizing theme', 'Exporting to Theme Builder format...');
        $this->exporter = new ThemeExporter();
        $this->exporter->exportToTB($theme);
        $this->log('TB JSON export completed');

        // ═══════════════════════════════════════════════════════════════════════
        // SAVE THEME METADATA
        // ═══════════════════════════════════════════════════════════════════════
        $this->saveThemeJson($theme);
        $this->log('Theme metadata saved');

        // Save to database
        $this->saveToDatabase($theme);
        $this->log('Theme saved to database');

        return $theme;
    }

    /**
     * Validate user input
     */
    private function validateInput(array $input): void
    {
        $required = ['brief', 'business_name', 'industry', 'pages'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }
        
        if (!is_array($input['pages']) || count($input['pages']) < 1) {
            throw new \InvalidArgumentException("At least one page is required");
        }
        
        // Validate design_style if provided
        $style = $input['design_style'] ?? $input['style'] ?? 'auto';
        if ($style !== 'auto' && !isset($this->personalities[$style])) {
            // Try to normalize
            $style = $this->normalizeStyleName($style);
            if (!isset($this->personalities[$style])) {
                throw new \InvalidArgumentException("Unknown design style: {$style}");
            }
        }
    }

    /**
     * Normalize style name
     */
    private function normalizeStyleName(string $style): string
    {
        $style = strtolower(trim($style));
        
        $styleMap = [
            'modern & clean' => 'modern',
            'modern_clean' => 'modern',
            'creative & bold' => 'creative',
            'creative_bold' => 'creative',
            'vintage & classic' => 'vintage',
            'vintage_classic' => 'vintage',
            'bold & dynamic' => 'bold',
            'bold_dynamic' => 'bold',
            'organic & natural' => 'organic',
            'organic_natural' => 'organic',
            'minimalist' => 'minimal'
        ];
        
        return $styleMap[$style] ?? $style;
    }

    /**
     * Generate URL-safe slug from business name
     */
    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        $slug .= '-' . date('Ymd-His');
        return $slug;
    }

    /**
     * Create theme directory structure
     */
    private function createThemeDirectories(string $themePath): void
    {
        $dirs = [
            $themePath,
            $themePath . '/pages',
            $themePath . '/assets',
            $themePath . '/assets/css',
            $themePath . '/assets/images',
            $themePath . '/tb-export',
            $themePath . '/tb-export/pages'
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Save design system as CSS file
     */
    private function saveDesignSystemCSS(string $themePath, array $designSystem): void
    {
        $css = $this->generateCSSFromDesignSystem($designSystem);
        $this->saveFile($themePath . '/assets/css/style.css', $css);
    }

    /**
     * Generate CSS from design system data
     */
    private function generateCSSFromDesignSystem(array $ds): string
    {
        $colors = $ds['colors'] ?? [];
        $typography = $ds['typography'] ?? [];
        $spacing = $ds['spacing'] ?? [];
        $borders = $ds['borders'] ?? [];
        $shadows = $ds['shadows'] ?? [];
        
        $css = "/* AI Designer Generated Theme - Design System */\n";
        $css .= "/* Style: " . ($ds['style'] ?? 'modern') . " */\n";
        $css .= "/* Generated: " . date('Y-m-d H:i:s') . " */\n\n";
        
        // CSS Custom Properties
        $css .= ":root {\n";
        
        // Colors
        foreach ($colors as $name => $value) {
            if (is_string($value)) {
                $cssName = str_replace('_', '-', $name);
                $css .= "    --color-{$cssName}: {$value};\n";
            }
        }
        
        // Typography
        if (!empty($typography['heading_font'])) {
            $css .= "    --font-heading: '{$typography['heading_font']}', serif;\n";
        }
        if (!empty($typography['body_font'])) {
            $css .= "    --font-body: '{$typography['body_font']}', sans-serif;\n";
        }
        foreach ($typography['sizes'] ?? [] as $name => $value) {
            $css .= "    --font-size-{$name}: {$value};\n";
        }
        
        // Spacing
        foreach ($spacing as $name => $value) {
            $css .= "    --spacing-{$name}: {$value};\n";
        }
        
        // Borders
        foreach ($borders as $name => $value) {
            $cssName = str_replace('_', '-', $name);
            $css .= "    --radius-{$cssName}: {$value};\n";
        }
        
        // Shadows
        foreach ($shadows as $name => $value) {
            $css .= "    --shadow-{$name}: {$value};\n";
        }
        
        $css .= "}\n\n";
        
        // Base styles
        $css .= "/* Base Styles */\n";
        $css .= "*, *::before, *::after { box-sizing: border-box; }\n";
        $css .= "html { font-size: 16px; scroll-behavior: smooth; }\n";
        $css .= "body {\n";
        $css .= "    font-family: var(--font-body);\n";
        $css .= "    color: var(--color-text, #333);\n";
        $css .= "    background-color: var(--color-background, #fff);\n";
        $css .= "    line-height: 1.6;\n";
        $css .= "    margin: 0;\n";
        $css .= "    padding: 0;\n";
        $css .= "}\n\n";
        
        // Typography
        $css .= "h1, h2, h3, h4, h5, h6 {\n";
        $css .= "    font-family: var(--font-heading);\n";
        $css .= "    font-weight: 700;\n";
        $css .= "    line-height: 1.2;\n";
        $css .= "    margin: 0 0 1rem 0;\n";
        $css .= "    color: var(--color-text);\n";
        $css .= "}\n";
        $css .= "h1 { font-size: var(--font-size-h1, 3.5rem); }\n";
        $css .= "h2 { font-size: var(--font-size-h2, 2.5rem); }\n";
        $css .= "h3 { font-size: var(--font-size-h3, 2rem); }\n";
        $css .= "h4 { font-size: var(--font-size-h4, 1.5rem); }\n";
        $css .= "p { margin: 0 0 1rem 0; }\n";
        $css .= "a { color: var(--color-primary); }\n\n";
        
        // Container
        $css .= ".container {\n";
        $css .= "    max-width: 1200px;\n";
        $css .= "    margin: 0 auto;\n";
        $css .= "    padding: 0 var(--spacing-md, 2rem);\n";
        $css .= "}\n\n";
        
        // Buttons
        $css .= ".btn {\n";
        $css .= "    display: inline-block;\n";
        $css .= "    padding: 14px 28px;\n";
        $css .= "    font-family: var(--font-body);\n";
        $css .= "    font-weight: 600;\n";
        $css .= "    text-decoration: none;\n";
        $css .= "    border-radius: var(--radius-md, 6px);\n";
        $css .= "    transition: all 0.3s ease;\n";
        $css .= "    cursor: pointer;\n";
        $css .= "    border: none;\n";
        $css .= "}\n";
        $css .= ".btn-primary {\n";
        $css .= "    background-color: var(--color-primary);\n";
        $css .= "    color: #fff;\n";
        $css .= "}\n";
        $css .= ".btn-primary:hover {\n";
        $css .= "    background-color: var(--color-primary-dark);\n";
        $css .= "    transform: translateY(-2px);\n";
        $css .= "    box-shadow: var(--shadow-md);\n";
        $css .= "}\n";
        $css .= ".btn-secondary {\n";
        $css .= "    background-color: transparent;\n";
        $css .= "    color: var(--color-primary);\n";
        $css .= "    border: 2px solid var(--color-primary);\n";
        $css .= "}\n";
        $css .= ".btn-secondary:hover {\n";
        $css .= "    background-color: var(--color-primary);\n";
        $css .= "    color: #fff;\n";
        $css .= "}\n\n";
        
        // Sections
        $css .= "section, .section {\n";
        $css .= "    padding: var(--spacing-xl, 80px) 0;\n";
        $css .= "}\n\n";
        
        // Cards
        $css .= ".card {\n";
        $css .= "    background: var(--color-surface, #fff);\n";
        $css .= "    border-radius: var(--radius-lg, 8px);\n";
        $css .= "    box-shadow: var(--shadow-md);\n";
        $css .= "    overflow: hidden;\n";
        $css .= "}\n\n";
        
        // Grid
        $css .= ".grid { display: grid; gap: var(--spacing-md, 2rem); }\n";
        $css .= ".grid-2 { grid-template-columns: repeat(2, 1fr); }\n";
        $css .= ".grid-3 { grid-template-columns: repeat(3, 1fr); }\n";
        $css .= ".grid-4 { grid-template-columns: repeat(4, 1fr); }\n\n";
        
        // Forms
        $css .= "input, textarea, select {\n";
        $css .= "    font-family: var(--font-body);\n";
        $css .= "    font-size: 1rem;\n";
        $css .= "    padding: 12px 16px;\n";
        $css .= "    border: 1px solid var(--color-border, #e5e5e5);\n";
        $css .= "    border-radius: var(--radius-sm, 4px);\n";
        $css .= "    transition: border-color 0.3s ease;\n";
        $css .= "}\n";
        $css .= "input:focus, textarea:focus, select:focus {\n";
        $css .= "    outline: none;\n";
        $css .= "    border-color: var(--color-primary);\n";
        $css .= "}\n\n";
        
        // Responsive
        $css .= "@media (max-width: 768px) {\n";
        $css .= "    .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }\n";
        $css .= "    h1 { font-size: 2.5rem; }\n";
        $css .= "    h2 { font-size: 2rem; }\n";
        $css .= "    section, .section { padding: var(--spacing-lg, 60px) 0; }\n";
        $css .= "}\n";
        
        return $css;
    }

    /**
     * Save page PHP file
     */
    private function savePageFile(string $themePath, string $pageName, string $html): void
    {
        $filename = strtolower(str_replace([' ', '-'], '_', $pageName)) . '.php';
        $this->saveFile($themePath . '/pages/' . $filename, $html);
    }

    /**
     * Save file with proper permissions
     */
    private function saveFile(string $path, string $content): void
    {
        file_put_contents($path, $content);
        chmod($path, 0644);
    }

    /**
     * Save theme.json metadata
     */
    private function saveThemeJson(Theme $theme): void
    {
        $data = [
            'name' => $theme->getName(),
            'slug' => $theme->getSlug(),
            'version' => '1.0.0',
            'created_at' => date('Y-m-d H:i:s'),
            'design_style' => $theme->getPersonality(),
            'industry' => $theme->getData()['industry'] ?? '',
            'pages' => $theme->getPageNames(),
            'design_system' => $theme->getDesignSystem(),
            'analysis' => $theme->getAnalysis()
        ];
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->saveFile($theme->getPath() . '/theme.json', $json);
    }

    /**
     * Save theme to database
     */
    private function saveToDatabase(Theme $theme): void
    {
        try {
            $db = \core\Database::connection();
            
            $stmt = $db->prepare("
                INSERT INTO tb_layout_library 
                (name, slug, description, category, industry, style, page_count, 
                 content_json, is_ai_generated, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 1, NOW())
            ");
            
            $stmt->execute([
                $theme->getName(),
                $theme->getSlug(),
                $theme->getData()['brief'] ?? '',
                'complete_theme',
                $theme->getData()['industry'] ?? 'general',
                $theme->getPersonality(),
                count($theme->getPageNames()),
                json_encode($theme->getTbExport(), JSON_UNESCAPED_UNICODE)
            ]);
            
            $themeId = $db->lastInsertId();
            $theme->setId((int)$themeId);
            
            // Save header to tb_site_templates
            $headerJson = $theme->getHeaderJson();
            if (!empty($headerJson)) {
                $this->saveTemplate('header', $theme->getName() . ' Header', $headerJson, (int)$themeId);
            }
            
            // Save footer to tb_site_templates
            $footerJson = $theme->getFooterJson();
            if (!empty($footerJson)) {
                $this->saveTemplate('footer', $theme->getName() . ' Footer', $footerJson, (int)$themeId);
            }
        } catch (\Exception $e) {
            error_log("[AI-Designer] Database save failed: " . $e->getMessage());
            // Don't throw - theme files are already saved
        }
    }

    /**
     * Save template to tb_site_templates
     */
    private function saveTemplate(string $type, string $name, array $content, int $themeId): void
    {
        try {
            $db = \core\Database::connection();
            
            $stmt = $db->prepare("
                INSERT INTO tb_site_templates (type, name, content, is_active, created_at)
                VALUES (?, ?, ?, 1, NOW())
            ");
            
            $stmt->execute([
                $type,
                $name,
                json_encode($content, JSON_UNESCAPED_UNICODE)
            ]);
        } catch (\Exception $e) {
            error_log("[AI-Designer] Template save failed: " . $e->getMessage());
        }
    }

    /**
     * Log message
     */
    private function log(string $message): void
    {
        error_log("[AI-Designer] " . $message);
    }

    /**
     * Report progress to callback if set (for SSE streaming)
     */
    private function progress(int $step, int $totalSteps, string $message, ?string $detail = null): void
    {
        if ($this->progressCallback) {
            call_user_func($this->progressCallback, [
                'step' => $step,
                'total' => $totalSteps,
                'message' => $message,
                'detail' => $detail,
                'percent' => (int) round(($step / $totalSteps) * 100)
            ]);
        }
    }

    /**
     * Get available design styles
     */
    public function getPersonalities(): array
    {
        return $this->personalities;
    }

    /**
     * Get available style names
     */
    public function getAvailableStyles(): array
    {
        return array_keys($this->personalities);
    }
}
