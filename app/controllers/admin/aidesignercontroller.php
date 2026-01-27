<?php
declare(strict_types=1);
/**
 * AI Designer 4.0 Controller
 * 
 * Handles AI-powered complete website theme generation.
 * Uses the new multi-step AI Designer system.
 *
 * @package App\Controllers\Admin
 * @version 4.0
 */

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 3));
}

// Load AI Designer system
require_once CMS_ROOT . '/core/ai-designer/Designer.php';
require_once CMS_ROOT . '/core/ai-designer/ImageFetcher.php';

class AiDesignerController
{
    private \PDO $db;
    private array $aiSettings = [];
    
    // 20 design styles with categories and color palettes
    private array $designStyles = [
        // Business & Professional
        'modern' => ['name' => 'Modern & Clean', 'icon' => '✨', 'category' => 'business', 'colors' => ['#3b82f6', '#1e40af', '#60a5fa']],
        'corporate' => ['name' => 'Corporate', 'icon' => '🏢', 'category' => 'business', 'colors' => ['#1e3a8a', '#1e40af', '#93c5fd']],
        'professional' => ['name' => 'Professional', 'icon' => '💼', 'category' => 'business', 'colors' => ['#334155', '#475569', '#94a3b8']],
        'startup' => ['name' => 'Startup & Tech', 'icon' => '🚀', 'category' => 'business', 'colors' => ['#8b5cf6', '#6366f1', '#a78bfa']],
        'saas' => ['name' => 'SaaS Product', 'icon' => '☁️', 'category' => 'business', 'colors' => ['#06b6d4', '#0891b2', '#67e8f9']],
        
        // Creative & Artistic
        'creative' => ['name' => 'Creative & Bold', 'icon' => '🎨', 'category' => 'creative', 'colors' => ['#ec4899', '#db2777', '#f472b6']],
        'artistic' => ['name' => 'Artistic', 'icon' => '🖌️', 'category' => 'creative', 'colors' => ['#f59e0b', '#d97706', '#fbbf24']],
        'playful' => ['name' => 'Playful & Fun', 'icon' => '🎪', 'category' => 'creative', 'colors' => ['#f43f5e', '#10b981', '#fbbf24']],
        'retro' => ['name' => 'Retro & Nostalgic', 'icon' => '📻', 'category' => 'creative', 'colors' => ['#ea580c', '#ca8a04', '#65a30d']],
        
        // Elegant & Luxury
        'elegant' => ['name' => 'Elegant', 'icon' => '👑', 'category' => 'luxury', 'colors' => ['#78716c', '#a8a29e', '#d6d3d1']],
        'luxury' => ['name' => 'Luxury', 'icon' => '💎', 'category' => 'luxury', 'colors' => ['#b45309', '#d4af37', '#fef3c7']],
        'minimal' => ['name' => 'Minimal', 'icon' => '○', 'category' => 'luxury', 'colors' => ['#18181b', '#71717a', '#f4f4f5']],
        'dark' => ['name' => 'Dark & Sophisticated', 'icon' => '🌙', 'category' => 'luxury', 'colors' => ['#1e1e2e', '#313244', '#cdd6f4']],
        
        // Nature & Wellness
        'organic' => ['name' => 'Organic & Natural', 'icon' => '🌿', 'category' => 'nature', 'colors' => ['#166534', '#22c55e', '#bbf7d0']],
        'eco' => ['name' => 'Eco-Friendly', 'icon' => '♻️', 'category' => 'nature', 'colors' => ['#15803d', '#84cc16', '#ecfccb']],
        'wellness' => ['name' => 'Wellness & Spa', 'icon' => '🧘', 'category' => 'nature', 'colors' => ['#0d9488', '#5eead4', '#f0fdfa']],
        
        // Bold & Dynamic
        'bold' => ['name' => 'Bold & Dynamic', 'icon' => '⚡', 'category' => 'bold', 'colors' => ['#dc2626', '#f97316', '#fbbf24']],
        'industrial' => ['name' => 'Industrial', 'icon' => '🔧', 'category' => 'bold', 'colors' => ['#374151', '#6b7280', '#f59e0b']],
        'vintage' => ['name' => 'Vintage & Classic', 'icon' => '📜', 'category' => 'bold', 'colors' => ['#78350f', '#a16207', '#fef3c7']],
        'brutalist' => ['name' => 'Brutalist', 'icon' => '🏗️', 'category' => 'bold', 'colors' => ['#000000', '#ffffff', '#ef4444']]
    ];
    
    // Style categories for filtering
    private array $styleCategories = [
        'business' => ['name' => 'Business & Professional', 'icon' => '💼'],
        'creative' => ['name' => 'Creative & Artistic', 'icon' => '🎨'],
        'luxury' => ['name' => 'Elegant & Luxury', 'icon' => '💎'],
        'nature' => ['name' => 'Nature & Wellness', 'icon' => '🌿'],
        'bold' => ['name' => 'Bold & Dynamic', 'icon' => '⚡']
    ];
    
    // 30 industries from AI Theme Builder
    private array $industries = [
        'business' => 'Business',
        'restaurant' => 'Restaurant',
        'technology' => 'Technology',
        'healthcare' => 'Healthcare',
        'ecommerce' => 'E-commerce',
        'professional_services' => 'Professional Services',
        'barber' => 'Barbershop',
        'salon' => 'Hair Salon',
        'spa' => 'Spa & Wellness',
        'fitness' => 'Fitness / Gym',
        'yoga' => 'Yoga Studio',
        'cafe' => 'Cafe / Coffee',
        'bar' => 'Bar / Cocktails',
        'hotel' => 'Hotel',
        'catering' => 'Catering',
        'foodtruck' => 'Food Truck',
        'photography' => 'Photography',
        'wedding' => 'Wedding Planner',
        'music' => 'Music / Band',
        'tattoo' => 'Tattoo Studio',
        'art' => 'Art / Gallery',
        'realestate' => 'Real Estate',
        'finance' => 'Finance',
        'education' => 'Education',
        'nonprofit' => 'Non-Profit',
        'automotive' => 'Automotive',
        'construction' => 'Construction',
        'blog' => 'Blog',
        'portfolio' => 'Portfolio',
        'landing' => 'Landing Page'
    ];
    
    // 10 page types
    private array $pageTypes = [
        'homepage' => 'Homepage',
        'about' => 'About Us',
        'services' => 'Services',
        'contact' => 'Contact',
        'blog' => 'Blog',
        'portfolio' => 'Portfolio',
        'pricing' => 'Pricing',
        'team' => 'Team',
        'faq' => 'FAQ',
        'testimonials' => 'Testimonials'
    ];

    public function __construct()
    {
        $this->db = \core\Database::connection();
        
        // Load AI settings from JSON
        $aiSettingsPath = CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($aiSettingsPath)) {
            $this->aiSettings = json_decode(file_get_contents($aiSettingsPath), true) ?: [];
        }
        
        // Load image API keys from database settings table
        $this->aiSettings['pexels_api_key'] = $this->getSettingValue('pexels_api_key', '');
        $this->aiSettings['unsplash_access_key'] = $this->getSettingValue('unsplash_access_key', '');
    }
    
    /**
     * Get setting value directly from database
     */
    private function getSettingValue(string $key, $default = null)
    {
        try {
            $stmt = $this->db->prepare("SELECT `value` FROM settings WHERE `key` = ? LIMIT 1");
            $stmt->execute([$key]);
            $result = $stmt->fetchColumn();
            return $result !== false ? $result : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Show AI Designer wizard interface
     */
    public function index(Request $request): void
    {
        render('admin/ai-designer/index', [
            'designStyles' => $this->designStyles,
            'industries' => $this->industries,
            'pageTypes' => $this->pageTypes,
            'aiConfigured' => $this->isAiConfigured(),
            'imageApiConfigured' => $this->isImageApiConfigured(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * AJAX: Generate complete theme
     */
    public function generate(Request $request): void
    {
        // AI generation can take 2-3 minutes
        set_time_limit(300);
        ignore_user_abort(true);
        
        // Disable all output buffering for SSE streaming
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable nginx/proxy buffering
        
        // Helper function to send SSE event
        $sendEvent = function(string $type, array $data) {
            echo "data: " . json_encode(['type' => $type] + $data) . "\n\n";
            flush();
        };
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $sendEvent('error', ['success' => false, 'error' => 'Invalid JSON data']);
            exit;
        }
        
        // Validate required fields
        $brief = trim($input['brief'] ?? '');
        $businessName = trim($input['business_name'] ?? '');
        $industry = trim($input['industry'] ?? 'business');
        $designStyle = trim($input['design_style'] ?? 'auto');
        $pages = $input['pages'] ?? ['homepage', 'about', 'services', 'contact'];
        
        if (empty($brief)) {
            $sendEvent('error', ['success' => false, 'error' => 'Project brief is required']);
            exit;
        }
        
        if (empty($businessName)) {
            $sendEvent('error', ['success' => false, 'error' => 'Business name is required']);
            exit;
        }
        
        if (!$this->isAiConfigured()) {
            $sendEvent('error', ['success' => false, 'error' => 'AI provider not configured. Go to Settings > AI Configuration.']);
            exit;
        }
        
        try {
            error_log('[AI-Designer] Starting theme generation for: ' . $businessName);
            
            // Create AI Designer instance
            $designer = new \Core\AiDesigner\Designer($this->aiSettings);
            
            // Generate theme with progress callback
            $theme = $designer->create([
                'brief' => $brief,
                'business_name' => $businessName,
                'industry' => $industry,
                'design_style' => $designStyle,
                'pages' => $pages
            ], function($progress) use ($sendEvent) {
                $sendEvent('progress', $progress);
            });
            
            // Process images if configured
            $autoFillImages = $input['auto_fill_images'] ?? true;
            if ($autoFillImages && $this->isImageApiConfigured()) {
                $this->processThemeImages($theme, $industry);
            }
            
            error_log('[AI-Designer] Theme generation complete: ' . $theme->getSlug());
            
            $sendEvent('complete', [
                'success' => true,
                'theme' => [
                    'id' => $theme->getId(),
                    'slug' => $theme->getSlug(),
                    'name' => $theme->getName(),
                    'path' => $theme->getPath(),
                    'design_style' => $theme->getPersonality(),
                    'pages' => $theme->getPageNames(),
                    'tb_export' => $theme->getTbExport(),
                    'preview_url' => '/admin/ai-designer/preview?slug=' . urlencode($theme->getSlug())
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log('[AI-Designer] ERROR: ' . $e->getMessage());
            $sendEvent('error', ['success' => false, 'error' => 'Generation failed: ' . $e->getMessage()]);
        }
        
        exit;
    }

    /**
     * AJAX: Preview generated theme
     */
    public function preview(Request $request): void
    {
        $slug = $request->get('slug', '');
        
        if (empty($slug)) {
            echo '<p>No theme specified</p>';
            return;
        }
        
        $themePath = CMS_ROOT . '/themes/' . $slug;
        
        if (!is_dir($themePath)) {
            echo '<p>Theme not found</p>';
            return;
        }
        
        // Load theme.json
        $themeData = [];
        if (file_exists($themePath . '/theme.json')) {
            $themeData = json_decode(file_get_contents($themePath . '/theme.json'), true) ?: [];
        }
        
        render('admin/ai-designer/preview', [
            'theme' => $themeData,
            'themePath' => $themePath,
            'slug' => $slug
        ]);
    }

    /**
     * AJAX: Get theme details
     */
    public function getTheme(Request $request): void
    {
        header('Content-Type: application/json');
        
        $slug = $request->get('slug', '');
        
        if (empty($slug)) {
            echo json_encode(['success' => false, 'error' => 'No slug provided']);
            exit;
        }
        
        $themePath = CMS_ROOT . '/themes/' . $slug;
        
        if (!is_dir($themePath)) {
            echo json_encode(['success' => false, 'error' => 'Theme not found']);
            exit;
        }
        
        // Load theme.json
        $themeData = [];
        if (file_exists($themePath . '/theme.json')) {
            $themeData = json_decode(file_get_contents($themePath . '/theme.json'), true) ?: [];
        }
        
        // Load TB export
        $tbExport = [];
        if (file_exists($themePath . '/tb-export/theme-export.json')) {
            $tbExport = json_decode(file_get_contents($themePath . '/tb-export/theme-export.json'), true) ?: [];
        }
        
        echo json_encode([
            'success' => true,
            'theme' => $themeData,
            'tb_export' => $tbExport
        ]);
        exit;
    }

    /**
     * AJAX: List generated themes
     */
    public function listThemes(Request $request): void
    {
        header('Content-Type: application/json');
        
        $themesPath = CMS_ROOT . '/themes';
        $themes = [];
        
        if (is_dir($themesPath)) {
            $dirs = scandir($themesPath);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..') continue;
                
                $themePath = $themesPath . '/' . $dir;
                if (!is_dir($themePath)) continue;
                
                $themeJson = $themePath . '/theme.json';
                if (file_exists($themeJson)) {
                    $data = json_decode(file_get_contents($themeJson), true);
                    if ($data) {
                        $themes[] = [
                            'slug' => $dir,
                            'name' => $data['name'] ?? $dir,
                            'design_style' => $data['design_style'] ?? 'modern',
                            'industry' => $data['industry'] ?? '',
                            'pages' => $data['pages'] ?? [],
                            'created_at' => $data['created_at'] ?? ''
                        ];
                    }
                }
            }
        }
        
        // Sort by created_at descending
        usort($themes, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        
        echo json_encode([
            'success' => true,
            'themes' => $themes
        ]);
        exit;
    }

    /**
     * AJAX: Delete theme
     */
    public function deleteTheme(Request $request): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $slug = $input['slug'] ?? '';
        
        if (empty($slug)) {
            echo json_encode(['success' => false, 'error' => 'No slug provided']);
            exit;
        }
        
        $themePath = CMS_ROOT . '/themes/' . $slug;
        
        if (!is_dir($themePath)) {
            echo json_encode(['success' => false, 'error' => 'Theme not found']);
            exit;
        }
        
        // Delete theme directory recursively
        $this->deleteDirectory($themePath);
        
        // Delete from database
        try {
            $stmt = $this->db->prepare("DELETE FROM tb_layout_library WHERE slug = ?");
            $stmt->execute([$slug]);
        } catch (\Exception $e) {
            // Ignore DB errors
        }
        
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * AJAX: Deploy theme to Theme Builder
     */
    public function deployTheme(Request $request): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $slug = $input['slug'] ?? '';
        
        if (empty($slug)) {
            echo json_encode(['success' => false, 'error' => 'No slug provided']);
            exit;
        }
        
        $themePath = CMS_ROOT . '/themes/' . $slug;
        $tbExportPath = $themePath . '/tb-export/theme-export.json';
        
        if (!file_exists($tbExportPath)) {
            echo json_encode(['success' => false, 'error' => 'TB export not found']);
            exit;
        }
        
        $tbExport = json_decode(file_get_contents($tbExportPath), true);
        
        if (!$tbExport) {
            echo json_encode(['success' => false, 'error' => 'Invalid TB export']);
            exit;
        }
        
        try {
            // Deploy header
            if (!empty($tbExport['header'])) {
                $this->deployTemplate('header', $tbExport['theme']['name'] . ' Header', $tbExport['header']);
            }
            
            // Deploy footer
            if (!empty($tbExport['footer'])) {
                $this->deployTemplate('footer', $tbExport['theme']['name'] . ' Footer', $tbExport['footer']);
            }
            
            // Deploy pages to Layout Library
            foreach ($tbExport['pages'] as $pageName => $pageData) {
                $this->deployToLayoutLibrary($tbExport['theme']['name'] . ' - ' . ucfirst($pageName), $pageData, $tbExport['theme']);
            }
            
            echo json_encode(['success' => true, 'message' => 'Theme deployed to Theme Builder']);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Deploy failed: ' . $e->getMessage()]);
        }
        
        exit;
    }

    /**
     * Process theme images with ImageFetcher
     */
    private function processThemeImages(\Core\AiDesigner\Theme $theme, string $industry): void
    {
        $config = [
            'pexels_api_key' => $this->aiSettings['pexels_api_key'] ?? '',
            'unsplash_access_key' => $this->aiSettings['unsplash_access_key'] ?? ''
        ];
        
        $fetcher = new \Core\AiDesigner\ImageFetcher($config);
        
        if (!$fetcher->isConfigured()) {
            return;
        }
        
        $themePath = $theme->getPath();
        
        // Process all page files
        $pagesDir = $themePath . '/pages';
        if (is_dir($pagesDir)) {
            $files = glob($pagesDir . '/*.php');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                $processed = $fetcher->processHtml($content, $industry);
                file_put_contents($file, $processed);
            }
        }
        
        // Process header
        $headerFile = $themePath . '/header.php';
        if (file_exists($headerFile)) {
            $content = file_get_contents($headerFile);
            $processed = $fetcher->processHtml($content, $industry);
            file_put_contents($headerFile, $processed);
        }
        
        // Process footer
        $footerFile = $themePath . '/footer.php';
        if (file_exists($footerFile)) {
            $content = file_get_contents($footerFile);
            $processed = $fetcher->processHtml($content, $industry);
            file_put_contents($footerFile, $processed);
        }
    }

    /**
     * Deploy template to tb_site_templates
     */
    private function deployTemplate(string $type, string $name, array $content): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO tb_site_templates (type, name, content, is_active, created_at)
            VALUES (?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$type, $name, json_encode($content, JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * Deploy to Layout Library
     */
    private function deployToLayoutLibrary(string $name, array $pageData, array $themeInfo): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO tb_layout_library 
            (name, slug, description, category, industry, style, page_count, content_json, is_ai_generated, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, ?, 1, 1, NOW())
        ");
        
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name)) . '-' . time();
        
        $stmt->execute([
            $name,
            $slug,
            $themeInfo['name'] ?? '',
            'page_layout',
            $themeInfo['industry'] ?? 'general',
            $themeInfo['design_system']['style'] ?? 'modern',
            json_encode($pageData, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * Delete directory recursively
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Check if AI is configured
     */
    private function isAiConfigured(): bool
    {
        $providers = $this->aiSettings['providers'] ?? [];
        
        foreach ($providers as $provider) {
            if (!empty($provider['api_key'])) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if image API is configured
     */
    private function isImageApiConfigured(): bool
    {
        return !empty($this->aiSettings['pexels_api_key']) || !empty($this->aiSettings['unsplash_access_key']);
    }
}
