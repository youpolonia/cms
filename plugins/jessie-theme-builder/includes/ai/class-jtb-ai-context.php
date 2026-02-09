<?php
/**
 * JTB AI Context
 * Builds contextual information for AI prompts
 * Gathers site settings, page data, branding, and existing content
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Context
{
    // ========================================
    // Page Context Methods
    // ========================================

    /**
     * Get complete page context for AI
     * @param int $pageId Post/page ID
     * @return array Page context data
     */
    public static function getPageContext(int $pageId): array
    {
        $context = [
            'id' => $pageId,
            'title' => '',
            'type' => 'page',
            'slug' => '',
            'url' => '',
            'parent_id' => null,
            'template' => '',
            'status' => 'draft',
            'created_at' => null,
            'updated_at' => null,
            'excerpt' => '',
            'featured_image' => null,
            'categories' => [],
            'tags' => [],
            'author' => null,
            'meta' => []
        ];

        // Load page data from CMS
        try {
            $db = \core\Database::connection();

            // Get post data
            $stmt = $db->prepare("
                SELECT p.*, u.display_name as author_name, u.email as author_email
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$pageId]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($post) {
                $context['title'] = $post['title'] ?? '';
                $context['type'] = $post['post_type'] ?? 'page';
                $context['slug'] = $post['slug'] ?? '';
                $context['url'] = '/' . ($post['slug'] ?? '');
                $context['parent_id'] = $post['parent_id'] ?? null;
                $context['template'] = $post['template'] ?? '';
                $context['status'] = $post['status'] ?? 'draft';
                $context['created_at'] = $post['created_at'] ?? null;
                $context['updated_at'] = $post['updated_at'] ?? null;
                $context['excerpt'] = $post['excerpt'] ?? '';
                $context['featured_image'] = $post['featured_image'] ?? null;

                if ($post['author_name']) {
                    $context['author'] = [
                        'name' => $post['author_name'],
                        'email' => $post['author_email'] ?? ''
                    ];
                }
            }

            // Get categories
            $stmt = $db->prepare("
                SELECT c.name, c.slug
                FROM categories c
                JOIN post_categories pc ON c.id = pc.category_id
                WHERE pc.post_id = ?
            ");
            $stmt->execute([$pageId]);
            $context['categories'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get tags
            $stmt = $db->prepare("
                SELECT t.name, t.slug
                FROM tags t
                JOIN post_tags pt ON t.id = pt.tag_id
                WHERE pt.post_id = ?
            ");
            $stmt->execute([$pageId]);
            $context['tags'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get post meta
            $stmt = $db->prepare("
                SELECT meta_key, meta_value
                FROM post_meta
                WHERE post_id = ?
            ");
            $stmt->execute([$pageId]);
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $context['meta'][$row['meta_key']] = $row['meta_value'];
            }

        } catch (\Exception $e) {
            error_log('JTB_AI_Context::getPageContext error: ' . $e->getMessage());
        }

        return $context;
    }

    /**
     * Get site-wide context
     * @return array Site context data
     */
    public static function getSiteContext(): array
    {
        $context = [
            'name' => '',
            'tagline' => '',
            'description' => '',
            'url' => '',
            'logo' => null,
            'favicon' => null,
            'language' => 'en',
            'timezone' => 'UTC',
            'industry' => '',
            'business_type' => '',
            'contact' => [
                'email' => '',
                'phone' => '',
                'address' => ''
            ],
            'social' => [],
            'pages' => [],
            'menus' => [],
            'footer' => []
        ];

        try {
            $db = \core\Database::connection();

            // Get site settings
            $stmt = $db->query("SELECT option_name, option_value FROM options WHERE autoload = 1");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $key = $row['option_name'];
                $value = $row['option_value'];

                switch ($key) {
                    case 'site_title':
                    case 'sitename':
                        $context['name'] = $value;
                        break;
                    case 'site_tagline':
                    case 'tagline':
                        $context['tagline'] = $value;
                        break;
                    case 'site_description':
                        $context['description'] = $value;
                        break;
                    case 'site_url':
                    case 'siteurl':
                        $context['url'] = $value;
                        break;
                    case 'site_logo':
                    case 'logo':
                        $context['logo'] = $value;
                        break;
                    case 'favicon':
                        $context['favicon'] = $value;
                        break;
                    case 'language':
                    case 'site_language':
                        $context['language'] = $value;
                        break;
                    case 'timezone':
                        $context['timezone'] = $value;
                        break;
                    case 'contact_email':
                    case 'admin_email':
                        $context['contact']['email'] = $value;
                        break;
                    case 'contact_phone':
                        $context['contact']['phone'] = $value;
                        break;
                    case 'contact_address':
                        $context['contact']['address'] = $value;
                        break;
                    case 'social_links':
                        $decoded = @json_decode($value, true);
                        if (is_array($decoded)) {
                            $context['social'] = $decoded;
                        }
                        break;
                    case 'industry':
                    case 'business_industry':
                        $context['industry'] = $value;
                        break;
                    case 'business_type':
                        $context['business_type'] = $value;
                        break;
                }
            }

            // Get main pages
            $stmt = $db->query("
                SELECT id, title, slug, post_type
                FROM posts
                WHERE status = 'published' AND post_type IN ('page', 'post')
                ORDER BY menu_order, title
                LIMIT 50
            ");
            $context['pages'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get menus
            $stmt = $db->query("
                SELECT name, slug, location
                FROM menus
                LIMIT 10
            ");
            $context['menus'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            error_log('JTB_AI_Context::getSiteContext error: ' . $e->getMessage());
        }

        return $context;
    }

    /**
     * Get style context from JTB Global Settings
     * @return array Style context with colors, fonts, spacing
     */
    public static function getStyleContext(): array
    {
        $context = [
            'colors' => self::extractColors(),
            'fonts' => self::extractFonts(),
            'spacing' => self::extractSpacing(),
            'buttons' => self::extractButtonStyles(),
            'forms' => self::extractFormStyles(),
            'responsive' => self::extractResponsiveSettings()
        ];

        return $context;
    }

    /**
     * Get existing JTB content for a page
     * @param int $pageId Post/page ID
     * @return array Existing content structure
     */
    public static function getExistingContent(int $pageId): array
    {
        $content = [
            'has_content' => false,
            'sections' => [],
            'module_types' => [],
            'module_count' => 0,
            'layout_structure' => '',
            'summary' => ''
        ];

        try {
            $db = \core\Database::connection();

            $stmt = $db->prepare("SELECT content, css_cache FROM jtb_pages WHERE post_id = ?");
            $stmt->execute([$pageId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row && !empty($row['content'])) {
                $data = json_decode($row['content'], true);

                if (is_array($data) && !empty($data)) {
                    $content['has_content'] = true;
                    $content['sections'] = self::analyzeSections($data);
                    $content['module_types'] = self::countModuleTypes($data);
                    $content['module_count'] = array_sum($content['module_types']);
                    $content['layout_structure'] = self::describeLayout($data);
                    $content['summary'] = self::summarizeContent($data);
                }
            }
        } catch (\Exception $e) {
            error_log('JTB_AI_Context::getExistingContent error: ' . $e->getMessage());
        }

        return $content;
    }

    /**
     * Get branding context for consistent styling
     * @return array Branding information
     */
    public static function getBrandingContext(): array
    {
        $context = [
            'primary_color' => '#3B82F6',
            'secondary_color' => '#1E40AF',
            'accent_color' => '#F59E0B',
            'text_color' => '#1F2937',
            'background_color' => '#FFFFFF',
            'heading_font' => 'Inter',
            'body_font' => 'Inter',
            'logo_url' => null,
            'logo_text' => '',
            'brand_voice' => 'professional',
            'target_audience' => 'general'
        ];

        // Override with theme settings if available
        try {
            if (class_exists(__NAMESPACE__ . '\\JTB_Theme_Settings')) {
                $settings = JTB_Theme_Settings::getAll();

                if (!empty($settings['primary_color'])) {
                    $context['primary_color'] = $settings['primary_color'];
                }
                if (!empty($settings['secondary_color'])) {
                    $context['secondary_color'] = $settings['secondary_color'];
                }
                if (!empty($settings['accent_color'])) {
                    $context['accent_color'] = $settings['accent_color'];
                }
                if (!empty($settings['text_color'])) {
                    $context['text_color'] = $settings['text_color'];
                }
                if (!empty($settings['background_color'])) {
                    $context['background_color'] = $settings['background_color'];
                }
                if (!empty($settings['heading_font'])) {
                    $context['heading_font'] = $settings['heading_font'];
                }
                if (!empty($settings['body_font'])) {
                    $context['body_font'] = $settings['body_font'];
                }
            }

            // Get logo from site settings
            $db = \core\Database::connection();
            $stmt = $db->query("SELECT option_value FROM options WHERE option_name = 'site_logo'");
            $logo = $stmt->fetchColumn();
            if ($logo) {
                $context['logo_url'] = $logo;
            }

            $stmt = $db->query("SELECT option_value FROM options WHERE option_name IN ('site_title', 'sitename') LIMIT 1");
            $siteName = $stmt->fetchColumn();
            if ($siteName) {
                $context['logo_text'] = $siteName;
            }

        } catch (\Exception $e) {
            error_log('JTB_AI_Context::getBrandingContext error: ' . $e->getMessage());
        }

        return $context;
    }

    /**
     * Build complete prompt context from all sources
     * @param int $pageId Post/page ID
     * @param array $options Additional options
     * @return string Formatted context for AI prompts
     */
    public static function buildPromptContext(int $pageId, array $options = []): string
    {
        $includeExisting = $options['include_existing'] ?? true;
        $includeSite = $options['include_site'] ?? true;
        $includeStyles = $options['include_styles'] ?? true;
        $includeBranding = $options['include_branding'] ?? true;

        $context = [];

        // Page context
        $page = self::getPageContext($pageId);
        $context[] = "# Page Information";
        $context[] = "- Title: {$page['title']}";
        $context[] = "- Type: {$page['type']}";
        $context[] = "- URL: {$page['url']}";
        if (!empty($page['excerpt'])) {
            $context[] = "- Excerpt: {$page['excerpt']}";
        }
        if (!empty($page['categories'])) {
            $cats = array_column($page['categories'], 'name');
            $context[] = "- Categories: " . implode(', ', $cats);
        }

        // Site context
        if ($includeSite) {
            $site = self::getSiteContext();
            $context[] = "\n# Site Information";
            $context[] = "- Site Name: {$site['name']}";
            if ($site['tagline']) {
                $context[] = "- Tagline: {$site['tagline']}";
            }
            if ($site['industry']) {
                $context[] = "- Industry: {$site['industry']}";
            }
            if ($site['business_type']) {
                $context[] = "- Business Type: {$site['business_type']}";
            }
        }

        // Branding context
        if ($includeBranding) {
            $branding = self::getBrandingContext();
            $context[] = "\n# Brand Guidelines";
            $context[] = "- Primary Color: {$branding['primary_color']}";
            $context[] = "- Secondary Color: {$branding['secondary_color']}";
            $context[] = "- Accent Color: {$branding['accent_color']}";
            $context[] = "- Heading Font: {$branding['heading_font']}";
            $context[] = "- Body Font: {$branding['body_font']}";
            $context[] = "- Brand Voice: {$branding['brand_voice']}";
        }

        // Style context
        if ($includeStyles) {
            $styles = self::getStyleContext();
            $context[] = "\n# Design System";

            if (!empty($styles['spacing'])) {
                $context[] = "- Section Padding: {$styles['spacing']['section_padding_top']}px / {$styles['spacing']['section_padding_bottom']}px";
                $context[] = "- Content Width: {$styles['spacing']['content_width']}px";
            }

            if (!empty($styles['buttons'])) {
                $context[] = "- Button Style: {$styles['buttons']['border_radius']}px radius";
            }
        }

        // Existing content
        if ($includeExisting) {
            $existing = self::getExistingContent($pageId);
            if ($existing['has_content']) {
                $context[] = "\n# Existing Content";
                $context[] = "- Sections: " . count($existing['sections']);
                $context[] = "- Modules: {$existing['module_count']}";
                $context[] = "- Layout: {$existing['layout_structure']}";
                if ($existing['summary']) {
                    $context[] = "- Summary: {$existing['summary']}";
                }
            }
        }

        return implode("\n", $context);
    }

    // ========================================
    // Private Helper Methods - Color/Font/Spacing Extraction
    // ========================================

    /**
     * Extract color settings
     * @return array Color palette
     */
    private static function extractColors(): array
    {
        $defaults = [
            'primary' => '#3B82F6',
            'secondary' => '#1E40AF',
            'accent' => '#F59E0B',
            'text' => '#1F2937',
            'text_light' => '#6B7280',
            'heading' => '#111827',
            'link' => '#3B82F6',
            'link_hover' => '#1E40AF',
            'background' => '#FFFFFF',
            'surface' => '#F9FAFB',
            'border' => '#E5E7EB',
            'success' => '#10B981',
            'warning' => '#F59E0B',
            'error' => '#EF4444',
            'info' => '#3B82F6'
        ];

        try {
            if (class_exists(__NAMESPACE__ . '\\JTB_Theme_Settings')) {
                $settings = JTB_Theme_Settings::getAll();

                $mapping = [
                    'primary_color' => 'primary',
                    'secondary_color' => 'secondary',
                    'accent_color' => 'accent',
                    'text_color' => 'text',
                    'text_light_color' => 'text_light',
                    'heading_color' => 'heading',
                    'link_color' => 'link',
                    'link_hover_color' => 'link_hover',
                    'background_color' => 'background',
                    'surface_color' => 'surface',
                    'border_color' => 'border',
                    'success_color' => 'success',
                    'warning_color' => 'warning',
                    'error_color' => 'error',
                    'info_color' => 'info'
                ];

                foreach ($mapping as $settingKey => $colorKey) {
                    if (!empty($settings[$settingKey])) {
                        $defaults[$colorKey] = $settings[$settingKey];
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('JTB_AI_Context::extractColors error: ' . $e->getMessage());
        }

        return $defaults;
    }

    /**
     * Extract font settings
     * @return array Typography settings
     */
    private static function extractFonts(): array
    {
        $defaults = [
            'heading_family' => 'Inter',
            'body_family' => 'Inter',
            'heading_weight' => '700',
            'body_weight' => '400',
            'body_size' => '16',
            'body_line_height' => '1.6',
            'h1_size' => '48',
            'h2_size' => '36',
            'h3_size' => '28',
            'h4_size' => '24',
            'h5_size' => '20',
            'h6_size' => '18'
        ];

        try {
            if (class_exists(__NAMESPACE__ . '\\JTB_Theme_Settings')) {
                $settings = JTB_Theme_Settings::getAll();

                $mapping = [
                    'heading_font' => 'heading_family',
                    'body_font' => 'body_family',
                    'heading_weight' => 'heading_weight',
                    'body_weight' => 'body_weight',
                    'body_size' => 'body_size',
                    'body_line_height' => 'body_line_height',
                    'h1_size' => 'h1_size',
                    'h2_size' => 'h2_size',
                    'h3_size' => 'h3_size',
                    'h4_size' => 'h4_size',
                    'h5_size' => 'h5_size',
                    'h6_size' => 'h6_size'
                ];

                foreach ($mapping as $settingKey => $fontKey) {
                    if (!empty($settings[$settingKey])) {
                        $defaults[$fontKey] = $settings[$settingKey];
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('JTB_AI_Context::extractFonts error: ' . $e->getMessage());
        }

        return $defaults;
    }

    /**
     * Extract spacing settings
     * @return array Spacing values
     */
    private static function extractSpacing(): array
    {
        $defaults = [
            'content_width' => 1200,
            'gutter_width' => 30,
            'section_padding_top' => 80,
            'section_padding_bottom' => 80,
            'row_gap' => 30,
            'column_gap' => 30
        ];

        try {
            if (class_exists(__NAMESPACE__ . '\\JTB_Theme_Settings')) {
                $settings = JTB_Theme_Settings::getAll();

                foreach (array_keys($defaults) as $key) {
                    if (isset($settings[$key]) && is_numeric($settings[$key])) {
                        $defaults[$key] = (int)$settings[$key];
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('JTB_AI_Context::extractSpacing error: ' . $e->getMessage());
        }

        return $defaults;
    }

    /**
     * Extract button styles
     * @return array Button style settings
     */
    private static function extractButtonStyles(): array
    {
        $defaults = [
            'background' => '#3B82F6',
            'text_color' => '#FFFFFF',
            'border_color' => '#3B82F6',
            'border_width' => 0,
            'border_radius' => 6,
            'padding_tb' => 12,
            'padding_lr' => 24,
            'font_size' => 16,
            'font_weight' => '600',
            'text_transform' => 'none',
            'hover_background' => '#1E40AF',
            'hover_text' => '#FFFFFF',
            'hover_border' => '#1E40AF'
        ];

        try {
            if (class_exists(__NAMESPACE__ . '\\JTB_Theme_Settings')) {
                $settings = JTB_Theme_Settings::getAll();

                $mapping = [
                    'button_bg_color' => 'background',
                    'button_text_color' => 'text_color',
                    'button_border_color' => 'border_color',
                    'button_border_width' => 'border_width',
                    'button_border_radius' => 'border_radius',
                    'button_padding_tb' => 'padding_tb',
                    'button_padding_lr' => 'padding_lr',
                    'button_font_size' => 'font_size',
                    'button_font_weight' => 'font_weight',
                    'button_text_transform' => 'text_transform',
                    'button_hover_bg' => 'hover_background',
                    'button_hover_text' => 'hover_text',
                    'button_hover_border' => 'hover_border'
                ];

                foreach ($mapping as $settingKey => $btnKey) {
                    if (isset($settings[$settingKey])) {
                        $defaults[$btnKey] = $settings[$settingKey];
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('JTB_AI_Context::extractButtonStyles error: ' . $e->getMessage());
        }

        return $defaults;
    }

    /**
     * Extract form styles
     * @return array Form style settings
     */
    private static function extractFormStyles(): array
    {
        $defaults = [
            'input_background' => '#FFFFFF',
            'input_text' => '#1F2937',
            'input_border' => '#D1D5DB',
            'input_border_width' => 1,
            'input_border_radius' => 6,
            'input_padding_tb' => 10,
            'input_padding_lr' => 16,
            'input_font_size' => 16,
            'focus_border' => '#3B82F6',
            'placeholder_color' => '#9CA3AF',
            'label_color' => '#374151',
            'label_font_size' => 14
        ];

        try {
            if (class_exists(__NAMESPACE__ . '\\JTB_Theme_Settings')) {
                $settings = JTB_Theme_Settings::getAll();

                $mapping = [
                    'input_bg_color' => 'input_background',
                    'input_text_color' => 'input_text',
                    'input_border_color' => 'input_border',
                    'input_border_width' => 'input_border_width',
                    'input_border_radius' => 'input_border_radius',
                    'input_padding_tb' => 'input_padding_tb',
                    'input_padding_lr' => 'input_padding_lr',
                    'input_font_size' => 'input_font_size',
                    'input_focus_border_color' => 'focus_border',
                    'placeholder_color' => 'placeholder_color',
                    'label_color' => 'label_color',
                    'label_font_size' => 'label_font_size'
                ];

                foreach ($mapping as $settingKey => $formKey) {
                    if (isset($settings[$settingKey])) {
                        $defaults[$formKey] = $settings[$settingKey];
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('JTB_AI_Context::extractFormStyles error: ' . $e->getMessage());
        }

        return $defaults;
    }

    /**
     * Extract responsive settings
     * @return array Breakpoint and responsive values
     */
    private static function extractResponsiveSettings(): array
    {
        $defaults = [
            'tablet_breakpoint' => 980,
            'phone_breakpoint' => 767,
            'h1_tablet' => 36,
            'h2_tablet' => 28,
            'body_tablet' => 15,
            'section_padding_tablet' => 60,
            'h1_phone' => 28,
            'h2_phone' => 24,
            'body_phone' => 14,
            'section_padding_phone' => 40
        ];

        try {
            if (class_exists(__NAMESPACE__ . '\\JTB_Theme_Settings')) {
                $settings = JTB_Theme_Settings::getAll();

                $mapping = [
                    'tablet_breakpoint' => 'tablet_breakpoint',
                    'phone_breakpoint' => 'phone_breakpoint',
                    'h1_size_tablet' => 'h1_tablet',
                    'h2_size_tablet' => 'h2_tablet',
                    'body_size_tablet' => 'body_tablet',
                    'section_padding_tablet' => 'section_padding_tablet',
                    'h1_size_phone' => 'h1_phone',
                    'h2_size_phone' => 'h2_phone',
                    'body_size_phone' => 'body_phone',
                    'section_padding_phone' => 'section_padding_phone'
                ];

                foreach ($mapping as $settingKey => $respKey) {
                    if (isset($settings[$settingKey]) && is_numeric($settings[$settingKey])) {
                        $defaults[$respKey] = (int)$settings[$settingKey];
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('JTB_AI_Context::extractResponsiveSettings error: ' . $e->getMessage());
        }

        return $defaults;
    }

    // ========================================
    // Private Helper Methods - Content Analysis
    // ========================================

    /**
     * Analyze sections in content
     * @param array $content JTB content array
     * @return array Section analysis
     */
    private static function analyzeSections(array $content): array
    {
        $sections = [];

        foreach ($content as $item) {
            if (($item['type'] ?? '') === 'section') {
                $section = [
                    'id' => $item['id'] ?? null,
                    'rows' => 0,
                    'columns' => 0,
                    'modules' => []
                ];

                if (!empty($item['children'])) {
                    $section['rows'] = count($item['children']);

                    foreach ($item['children'] as $row) {
                        if (!empty($row['children'])) {
                            $section['columns'] += count($row['children']);

                            foreach ($row['children'] as $column) {
                                if (!empty($column['children'])) {
                                    foreach ($column['children'] as $module) {
                                        $section['modules'][] = $module['type'] ?? 'unknown';
                                    }
                                }
                            }
                        }
                    }
                }

                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * Count module types in content
     * @param array $content JTB content array
     * @return array Module type counts
     */
    private static function countModuleTypes(array $content): array
    {
        $counts = [];

        self::walkContent($content, function($item) use (&$counts) {
            $type = $item['type'] ?? null;
            if ($type && !in_array($type, ['section', 'row', 'column'])) {
                $counts[$type] = ($counts[$type] ?? 0) + 1;
            }
        });

        arsort($counts);
        return $counts;
    }

    /**
     * Walk through content tree
     * @param array $content Content array
     * @param callable $callback Function to call for each item
     */
    private static function walkContent(array $content, callable $callback): void
    {
        foreach ($content as $item) {
            $callback($item);

            if (!empty($item['children'])) {
                self::walkContent($item['children'], $callback);
            }
        }
    }

    /**
     * Describe layout structure
     * @param array $content JTB content array
     * @return string Layout description
     */
    private static function describeLayout(array $content): string
    {
        $sectionCount = 0;
        $rowPatterns = [];

        foreach ($content as $item) {
            if (($item['type'] ?? '') === 'section') {
                $sectionCount++;

                if (!empty($item['children'])) {
                    foreach ($item['children'] as $row) {
                        $colCount = count($row['children'] ?? []);
                        $rowPatterns[] = $colCount;
                    }
                }
            }
        }

        if ($sectionCount === 0) {
            return 'Empty';
        }

        $avgCols = count($rowPatterns) > 0 ? array_sum($rowPatterns) / count($rowPatterns) : 0;
        $colDesc = $avgCols <= 1 ? 'single-column' : ($avgCols <= 2 ? 'two-column' : 'multi-column');

        return "{$sectionCount} sections, {$colDesc} layout";
    }

    /**
     * Generate content summary
     * @param array $content JTB content array
     * @return string Brief summary
     */
    private static function summarizeContent(array $content): string
    {
        $types = self::countModuleTypes($content);

        if (empty($types)) {
            return 'No content modules';
        }

        $topTypes = array_slice(array_keys($types), 0, 3);
        $typeLabels = [];

        foreach ($topTypes as $type) {
            $count = $types[$type];
            $typeLabels[] = "{$count}x {$type}";
        }

        return implode(', ', $typeLabels);
    }

    // ========================================
    // Industry-Specific Context
    // ========================================

    /**
     * Get industry-specific context
     * @param string $industry Industry identifier
     * @return array Industry context
     */
    public static function getIndustryContext(string $industry): array
    {
        $industries = [
            'technology' => [
                'keywords' => ['innovation', 'solutions', 'digital', 'software', 'platform', 'scalable'],
                'tone' => 'professional, forward-thinking',
                'cta_examples' => ['Start Free Trial', 'Request Demo', 'Get Started'],
                'section_types' => ['hero', 'features', 'pricing', 'testimonials', 'faq', 'cta'],
                'common_modules' => ['blurb', 'pricing_table', 'testimonial', 'cta']
            ],
            'ecommerce' => [
                'keywords' => ['shop', 'products', 'deals', 'sale', 'free shipping', 'returns'],
                'tone' => 'engaging, promotional',
                'cta_examples' => ['Shop Now', 'Add to Cart', 'View Collection'],
                'section_types' => ['hero', 'products', 'categories', 'testimonials', 'newsletter'],
                'common_modules' => ['shop', 'gallery', 'slider', 'testimonial']
            ],
            'healthcare' => [
                'keywords' => ['care', 'health', 'wellness', 'treatment', 'professional', 'trusted'],
                'tone' => 'caring, trustworthy, professional',
                'cta_examples' => ['Book Appointment', 'Contact Us', 'Learn More'],
                'section_types' => ['hero', 'services', 'team', 'testimonials', 'contact'],
                'common_modules' => ['team_member', 'blurb', 'contact_form', 'map']
            ],
            'education' => [
                'keywords' => ['learn', 'courses', 'training', 'skills', 'certification', 'knowledge'],
                'tone' => 'inspiring, educational',
                'cta_examples' => ['Enroll Now', 'Start Learning', 'View Courses'],
                'section_types' => ['hero', 'courses', 'instructors', 'testimonials', 'pricing'],
                'common_modules' => ['blurb', 'team_member', 'pricing_table', 'accordion']
            ],
            'agency' => [
                'keywords' => ['creative', 'design', 'strategy', 'brand', 'results', 'partnership'],
                'tone' => 'creative, confident, professional',
                'cta_examples' => ['Start a Project', 'Get in Touch', 'View Our Work'],
                'section_types' => ['hero', 'services', 'portfolio', 'team', 'testimonials', 'contact'],
                'common_modules' => ['portfolio', 'team_member', 'blurb', 'testimonial']
            ],
            'restaurant' => [
                'keywords' => ['menu', 'dining', 'fresh', 'cuisine', 'reservation', 'experience'],
                'tone' => 'warm, inviting, appetizing',
                'cta_examples' => ['View Menu', 'Make Reservation', 'Order Online'],
                'section_types' => ['hero', 'menu', 'about', 'gallery', 'testimonials', 'contact'],
                'common_modules' => ['gallery', 'tabs', 'map', 'contact_form']
            ],
            'realestate' => [
                'keywords' => ['property', 'home', 'investment', 'listings', 'location', 'dream'],
                'tone' => 'professional, trustworthy, aspirational',
                'cta_examples' => ['View Listings', 'Schedule Viewing', 'Contact Agent'],
                'section_types' => ['hero', 'listings', 'services', 'team', 'testimonials', 'contact'],
                'common_modules' => ['gallery', 'team_member', 'map', 'contact_form']
            ],
            'fitness' => [
                'keywords' => ['workout', 'training', 'strength', 'goals', 'transform', 'results'],
                'tone' => 'motivating, energetic, empowering',
                'cta_examples' => ['Join Now', 'Start Training', 'Get Your Plan'],
                'section_types' => ['hero', 'programs', 'trainers', 'pricing', 'testimonials', 'cta'],
                'common_modules' => ['blurb', 'team_member', 'pricing_table', 'number_counter']
            ],
            'nonprofit' => [
                'keywords' => ['mission', 'impact', 'donate', 'volunteer', 'community', 'change'],
                'tone' => 'compassionate, inspiring, urgent',
                'cta_examples' => ['Donate Now', 'Volunteer', 'Learn More'],
                'section_types' => ['hero', 'mission', 'impact', 'team', 'stories', 'donate'],
                'common_modules' => ['number_counter', 'testimonial', 'team_member', 'cta']
            ],
            'legal' => [
                'keywords' => ['expertise', 'counsel', 'rights', 'representation', 'trusted', 'results'],
                'tone' => 'professional, authoritative, reassuring',
                'cta_examples' => ['Free Consultation', 'Contact Us', 'Learn More'],
                'section_types' => ['hero', 'practice_areas', 'team', 'testimonials', 'contact'],
                'common_modules' => ['blurb', 'team_member', 'accordion', 'contact_form']
            ]
        ];

        return $industries[$industry] ?? [
            'keywords' => ['professional', 'quality', 'service', 'solutions', 'trusted'],
            'tone' => 'professional',
            'cta_examples' => ['Get Started', 'Contact Us', 'Learn More'],
            'section_types' => ['hero', 'features', 'about', 'testimonials', 'contact'],
            'common_modules' => ['blurb', 'testimonial', 'cta', 'contact_form']
        ];
    }

    /**
     * Get page type context
     * @param string $pageType Type of page (landing, about, contact, etc.)
     * @return array Page type context
     */
    public static function getPageTypeContext(string $pageType): array
    {
        $pageTypes = [
            'landing' => [
                'purpose' => 'Convert visitors into leads or customers',
                'typical_sections' => ['hero', 'benefits', 'features', 'social_proof', 'cta'],
                'required_elements' => ['headline', 'value_proposition', 'cta_button'],
                'focus' => 'Single conversion goal'
            ],
            'homepage' => [
                'purpose' => 'Introduce the brand and guide visitors to key content',
                'typical_sections' => ['hero', 'services', 'about', 'portfolio', 'testimonials', 'cta', 'blog'],
                'required_elements' => ['logo', 'navigation', 'headline', 'key_services'],
                'focus' => 'Brand overview and navigation'
            ],
            'about' => [
                'purpose' => 'Tell the company story and build trust',
                'typical_sections' => ['hero', 'story', 'mission', 'team', 'values', 'milestones'],
                'required_elements' => ['company_story', 'team_photos', 'mission_statement'],
                'focus' => 'Trust and credibility'
            ],
            'services' => [
                'purpose' => 'Showcase services and their benefits',
                'typical_sections' => ['hero', 'services_grid', 'features', 'process', 'pricing', 'cta'],
                'required_elements' => ['service_descriptions', 'benefits', 'cta'],
                'focus' => 'Service value proposition'
            ],
            'contact' => [
                'purpose' => 'Enable visitors to get in touch',
                'typical_sections' => ['hero', 'contact_form', 'map', 'info'],
                'required_elements' => ['contact_form', 'email', 'phone', 'address'],
                'focus' => 'Easy contact methods'
            ],
            'pricing' => [
                'purpose' => 'Present pricing options and encourage purchase',
                'typical_sections' => ['hero', 'pricing_table', 'features', 'faq', 'testimonials', 'cta'],
                'required_elements' => ['pricing_plans', 'feature_comparison', 'cta_buttons'],
                'focus' => 'Clear value comparison'
            ],
            'portfolio' => [
                'purpose' => 'Showcase work and demonstrate expertise',
                'typical_sections' => ['hero', 'portfolio_grid', 'categories', 'case_studies', 'cta'],
                'required_elements' => ['project_images', 'descriptions', 'categories'],
                'focus' => 'Visual showcase'
            ],
            'blog' => [
                'purpose' => 'Share content and establish thought leadership',
                'typical_sections' => ['featured_post', 'post_grid', 'categories', 'newsletter', 'sidebar'],
                'required_elements' => ['post_list', 'search', 'categories'],
                'focus' => 'Content discovery'
            ]
        ];

        return $pageTypes[$pageType] ?? $pageTypes['homepage'];
    }
}
