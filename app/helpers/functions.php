<?php
declare(strict_types=1);

/**
 * Global helper functions
 * With Theme Loading System for frontend
 */

// ═══════════════════════════════════════════════════════════
// BASIC HELPERS
// ═══════════════════════════════════════════════════════════

if (!function_exists('esc')) {
    function esc(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . esc(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_validate_or_403')) {
    function csrf_validate_or_403(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            exit('CSRF token mismatch');
        }
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        return esc($_SESSION['_old'][$key] ?? $_POST[$key] ?? $default);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($base === '/public') {
            $base = '';
        }
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): never
    {
        \Core\Response::redirect(url($url));
    }
}

if (!function_exists('db')) {
    function db(): \PDO
    {
        static $pdo = null;
        if ($pdo === null) {
            require_once CMS_CORE . '/database.php';
            $pdo = \core\Database::connection();
        }
        return $pdo;
    }
}

// ═══════════════════════════════════════════════════════════
// THEME SYSTEM
// ═══════════════════════════════════════════════════════════

if (!function_exists('get_active_theme')) {
    /**
     * Get active theme name
     * Priority: 1) preview_theme GET param, 2) database setting, 3) 'jessie' default
     */
    function get_active_theme(): string
    {
        static $activeTheme = null;
        
        if ($activeTheme !== null) {
            return $activeTheme;
        }
        
        // Check for preview mode
        if (!empty($_GET['preview_theme'])) {
            $previewTheme = preg_replace('/[^a-z0-9\-_]/', '', strtolower($_GET['preview_theme']));
            if (is_dir(CMS_ROOT . '/themes/' . $previewTheme)) {
                $activeTheme = $previewTheme;
                return $activeTheme;
            }
        }
        
        // Get from database (system_settings table)
        try {
            $pdo = db();
            $stmt = $pdo->query("SELECT active_theme FROM system_settings LIMIT 1");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result && !empty($result['active_theme'])) {
                $themeName = $result['active_theme'];
                if (is_dir(CMS_ROOT . '/themes/' . $themeName)) {
                    $activeTheme = $themeName;
                    return $activeTheme;
                }
            }
        } catch (\Exception $e) {
            // Silently fail, use default
        }
        
        // Default theme
        $activeTheme = 'jessie';
        return $activeTheme;
    }
}

if (!function_exists('get_theme_config')) {
    /**
     * Get theme configuration from theme.json
     */
    function get_theme_config(?string $themeName = null): array
    {
        $themeName = $themeName ?? get_active_theme();
        $configPath = CMS_ROOT . '/themes/' . $themeName . '/theme.json';
        
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            return is_array($config) ? $config : [];
        }
        
        return [];
    }
}

if (!function_exists('generate_theme_css_variables')) {
    /**
     * Generate CSS :root variables from theme.json colors
     * Standardized names: --primary, --secondary, --text, --surface, --background, --border
     * Compatible with tb-frontend.css
     */
    function generate_theme_css_variables(?array $themeConfig = null): string
    {
        $config = $themeConfig ?? get_theme_config();
        $colors = $config['colors'] ?? [];
        $typography = $config['typography'] ?? [];
        
        // Default colors (from default theme.json)
        $defaults = [
            'primary' => '#0ea5e9',
            'secondary' => '#06b6d4',
            'accent' => '#f59e0b',
            'background' => '#0c1222',
            'surface' => '#1e293b',
            'text' => '#f1f5f9',
            'text_muted' => '#94a3b8',
            'border' => '#334155',
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'error' => '#ef4444'
        ];
        
        // Merge with theme colors
        $colors = array_merge($defaults, $colors);
        
        // Typography defaults
        $fontFamily = $typography['fontFamily'] ?? 'Inter';
        $headingFont = $typography['headingFont'] ?? $fontFamily;
        $baseFontSize = $typography['baseFontSize'] ?? '16';
        $lineHeight = $typography['lineHeight'] ?? '1.6';
        
        $css = ":root {\n";
        
        // Primary color variables (standard names for tb-frontend.css)
        $css .= "    --primary: {$colors['primary']};\n";
        $css .= "    --secondary: {$colors['secondary']};\n";
        $css .= "    --accent: {$colors['accent']};\n";
        $css .= "    --background: {$colors['background']};\n";
        $css .= "    --surface: {$colors['surface']};\n";
        $css .= "    --text: {$colors['text']};\n";
        $css .= "    --text-muted: {$colors['text_muted']};\n";
        $css .= "    --border: {$colors['border']};\n";
        $css .= "    --success: {$colors['success']};\n";
        $css .= "    --warning: {$colors['warning']};\n";
        $css .= "    --error: {$colors['error']};\n";
        
        // Legacy aliases (for backward compatibility)
        $css .= "    /* Legacy aliases */\n";
        $css .= "    --color-primary: var(--primary);\n";
        $css .= "    --color-secondary: var(--secondary);\n";
        $css .= "    --color-accent: var(--accent);\n";
        $css .= "    --color-background: var(--background);\n";
        $css .= "    --color-surface: var(--surface);\n";
        $css .= "    --color-text: var(--text);\n";
        $css .= "    --color-text-muted: var(--text-muted);\n";
        $css .= "    --color-border: var(--border);\n";
        
        // Typography
        $css .= "    /* Typography */\n";
        $css .= "    --font-family: '{$fontFamily}', -apple-system, BlinkMacSystemFont, sans-serif;\n";
        $css .= "    --font-heading: '{$headingFont}', -apple-system, BlinkMacSystemFont, sans-serif;\n";
        $css .= "    --font-size-base: {$baseFontSize}px;\n";
        $css .= "    --line-height: {$lineHeight};\n";
        
        $css .= "}\n";
        
        return $css;
    }
}

if (!function_exists('theme_path')) {
    /**
     * Get absolute path to theme file
     */
    function theme_path(string $file = '', ?string $themeName = null): string
    {
        $themeName = $themeName ?? get_active_theme();
        $basePath = CMS_ROOT . '/themes/' . $themeName;
        
        if ($file) {
            return $basePath . '/' . ltrim($file, '/');
        }
        
        return $basePath;
    }
}

if (!function_exists('theme_url')) {
    /**
     * Get URL to theme asset
     */
    function theme_url(string $file = '', ?string $themeName = null): string
    {
        $themeName = $themeName ?? get_active_theme();
        return '/themes/' . $themeName . '/' . ltrim($file, '/');
    }
}

if (!function_exists('is_theme_preview')) {
    /**
     * Check if we're in theme preview mode
     */
    function is_theme_preview(): bool
    {
        return !empty($_GET['preview_theme']);
    }
}

// ═══════════════════════════════════════════════════════════
// VIEW & RENDER SYSTEM (WITH THEME SUPPORT)
// ═══════════════════════════════════════════════════════════

if (!function_exists('view')) {
    /**
     * Render a view template and return as string
     * For admin views: uses app/views/
     * For front views: uses theme templates if available
     */
    function view(string $template, array $data = []): string
    {
        // TEMP DEBUG
        file_put_contents('/tmp/tb_debug.log', date('Y-m-d H:i:s').' view(): '.$template.' data keys: '.implode(',', array_keys($data))."\n", FILE_APPEND);
        extract($data);
        ob_start();
        
        // Check if this is a front-end template
        if (str_starts_with($template, 'front/')) {
            $templatePath = resolve_front_template($template);
        } else {
            $templatePath = CMS_APP . '/views/' . $template . '.php';
        }
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("View not found: {$template}");
        }
        
        require $templatePath;
        return ob_get_clean();
    }
}

if (!function_exists('resolve_front_template')) {
    /**
     * Resolve front-end template path with theme fallback
     * Priority: 1) theme/templates/, 2) app/views/front/
     */
    function resolve_front_template(string $template): string
    {
        // Extract template name (e.g., 'front/home' -> 'home')
        $templateName = str_replace('front/', '', $template);
        
        // Check theme templates first
        $themePath = theme_path('templates/' . $templateName . '.php');
        if (file_exists($themePath)) {
            return $themePath;
        }
        
        // Fallback to default views
        return CMS_APP . '/views/' . $template . '.php';
    }
}


// Admin toolbar for front-end
if (file_exists(CMS_ROOT . '/core/admin-toolbar.php')) {
    require_once CMS_ROOT . '/core/admin-toolbar.php';
}

if (!function_exists('render')) {
    /**
     * Render a view and exit
     * For front views: wraps content in theme layout
     */
    function render(string $template, array $data = []): never
    {
        // Check if this is a front-end template that should use theme layout
        if (str_starts_with($template, 'front/') && !str_contains($template, 'layouts/')) {
            render_with_theme($template, $data);
        }
        
        echo view($template, $data);
        exit;
    }
}


if (!function_exists('cms_inject_admin_toolbar')) {
    /**
     * Inject admin toolbar into HTML output (after <body> tag)
     */
    function cms_inject_admin_toolbar(string $html, array $context = []): string {
        if (!function_exists('cms_admin_toolbar') || !function_exists('cms_is_admin_logged_in')) {
            return $html;
        }
        if (!cms_is_admin_logged_in()) {
            return $html;
        }
        $toolbar = cms_admin_toolbar($context);
        if (empty($toolbar)) {
            return $html;
        }
        // Inject after <body> tag (with optional attributes)
        $html = preg_replace('/(<body[^>]*>)/i', '$1' . "\n" . $toolbar, $html, 1);
        return $html;
    }
}

if (!function_exists('render_with_theme')) {
    /**
     * Render front-end content wrapped in theme layout
     * ALWAYS uses theme layout if available, with template fallback chain
     *
     * Template resolution order:
     * 1. Theme templates (e.g., themes/jessie/templates/page-contact.php)
     * 2. Generic page.php in theme (for any page-* template not in theme)
     * 3. Fallback to app/views/front/ WITH their own layout (legacy support)
     */
    function render_with_theme(string $template, array $data = []): never
    {
        $themeName = get_active_theme();
        $themeConfig = get_theme_config($themeName);
        $layoutPath = theme_path('layout.php');

        // Extract template name (e.g., 'front/page-contact' -> 'page-contact')
        $templateName = str_replace('front/', '', $template);

        // Add theme data to view data
        $data['_theme'] = $themeName;
        $data['_themeConfig'] = $themeConfig;
        $data['_themeUrl'] = theme_url();

        // Theme MUST have layout.php to use theme system
        if (!file_exists($layoutPath)) {
            // No theme layout - use default views with their own layout (legacy)
            $defaultPath = CMS_APP . '/views/' . $template . '.php';
            if (file_exists($defaultPath)) {
                extract($data);
                require $defaultPath;
                exit;
            }
            http_response_code(404);
            echo '404 - Template not found';
            exit;
        }

        // Theme has layout - ALWAYS use it
        // Find template content using fallback chain
        $contentTemplatePath = null;

        // 1. Check for exact template in theme
        $themeTemplatePath = theme_path('templates/' . $templateName . '.php');
        if (file_exists($themeTemplatePath)) {
            $contentTemplatePath = $themeTemplatePath;
        }
        // 2. For page-* templates, try generic page.php in theme
        elseif (str_starts_with($templateName, 'page-') || $templateName === 'page') {
            $genericPagePath = theme_path('templates/page.php');
            if (file_exists($genericPagePath)) {
                $contentTemplatePath = $genericPagePath;
            }
        }
        // 3. Try exact template name in theme (e.g., 'features')
        else {
            $genericPagePath = theme_path('templates/page.php');
            if (file_exists($genericPagePath)) {
                $contentTemplatePath = $genericPagePath;
            }
        }

        // If we found a theme template, render it within theme layout
        if ($contentTemplatePath) {
            // Render theme template content first
            extract($data);
            ob_start();
            require $contentTemplatePath;
            $content = ob_get_clean();

            // Now render theme layout with content
            $data['content'] = $content;
            extract($data);

            ob_start();
            require $layoutPath;
            $output = ob_get_clean();
            echo cms_inject_admin_toolbar($output, $data['_toolbar_context'] ?? []);
            exit;
        }

        // No theme template found - fallback to app/views/front/ WRAPPED in layout
        // Always use layout.php for consistent header/footer
        $fallbackPath = CMS_APP . '/views/' . $template . '.php';
        if (file_exists($fallbackPath)) {
            extract($data);
            ob_start();
            require $fallbackPath;
            $content = ob_get_clean();
            
            // Wrap in theme layout
            $data['content'] = $content;
            extract($data);
            ob_start();
            require $layoutPath;
            $output = ob_get_clean();
            echo cms_inject_admin_toolbar($output, $data['_toolbar_context'] ?? []);
            exit;
        }

        // Nothing found - show 404 using theme
        http_response_code(404);
        $error404Path = theme_path('templates/404.php');
        if (file_exists($error404Path)) {
            extract($data);
            ob_start();
            require $error404Path;
            $content = ob_get_clean();

            $data['content'] = $content;
            extract($data);
            ob_start();
            require $layoutPath;
            $output = ob_get_clean();
            echo cms_inject_admin_toolbar($output, []);
            exit;
        }

        echo '404 - Template not found';
        exit;
    }
}

if (!function_exists('render_with_theme_css')) {
    /**
     * Render with theme CSS injected into default layout
     */
    function render_with_theme_css(string $template, array $data = []): never
    {
        $themeName = get_active_theme();
        $themeConfig = get_theme_config($themeName);
        
        // Add theme CSS path to data
        $themeCssPath = theme_path('assets/css/style.css');
        if (file_exists($themeCssPath)) {
            $data['_themeCss'] = theme_url('assets/css/style.css');
        }
        
        $data['_theme'] = $themeName;
        $data['_themeConfig'] = $themeConfig;
        
        echo view($template, $data);
        exit;
    }
}

// ═══════════════════════════════════════════════════════════
// THEME CSS GENERATION HELPER
// ═══════════════════════════════════════════════════════════

if (!function_exists('theme_css_variables')) {
    /**
     * Generate CSS custom properties from theme config
     */
    function theme_css_variables(?array $themeConfig = null): string
    {
        $config = $themeConfig ?? get_theme_config();
        
        if (empty($config)) {
            return '';
        }
        
        $css = ":root {\n";
        
        // Colors
        if (!empty($config['colors'])) {
            foreach ($config['colors'] as $name => $value) {
                $cssName = strtolower(preg_replace('/([A-Z])/', '-$1', $name));
                $css .= "    --color-{$cssName}: {$value};\n";
            }
        }
        
        // Typography
        if (!empty($config['typography'])) {
            $typo = $config['typography'];
            if (!empty($typo['headingFont'])) {
                $css .= "    --font-heading: '{$typo['headingFont']}', sans-serif;\n";
            }
            if (!empty($typo['bodyFont'])) {
                $css .= "    --font-body: '{$typo['bodyFont']}', sans-serif;\n";
            }
            if (!empty($typo['baseSize'])) {
                $css .= "    --font-size-base: {$typo['baseSize']}px;\n";
            }
            if (!empty($typo['h1Size'])) {
                $css .= "    --font-size-h1: {$typo['h1Size']}px;\n";
            }
            if (!empty($typo['h2Size'])) {
                $css .= "    --font-size-h2: {$typo['h2Size']}px;\n";
            }
            if (!empty($typo['h3Size'])) {
                $css .= "    --font-size-h3: {$typo['h3Size']}px;\n";
            }
            if (!empty($typo['lineHeight'])) {
                $css .= "    --line-height: {$typo['lineHeight']};\n";
            }
            if (!empty($typo['headingWeight'])) {
                $css .= "    --font-weight-heading: {$typo['headingWeight']};\n";
            }
        }
        
        // Spacing
        if (!empty($config['spacing'])) {
            $spacing = $config['spacing'];
            if (!empty($spacing['containerWidth'])) {
                $css .= "    --container-width: {$spacing['containerWidth']}px;\n";
            }
            if (!empty($spacing['sectionPadding'])) {
                $css .= "    --section-padding: {$spacing['sectionPadding']}px;\n";
            }
            if (!empty($spacing['radiusSmall'])) {
                $css .= "    --radius-sm: {$spacing['radiusSmall']}px;\n";
            }
            if (!empty($spacing['radiusMedium'])) {
                $css .= "    --radius-md: {$spacing['radiusMedium']}px;\n";
            }
            if (!empty($spacing['radiusLarge'])) {
                $css .= "    --radius-lg: {$spacing['radiusLarge']}px;\n";
            }
        }
        
        $css .= "}\n";
        
        return $css;
    }
}

if (!function_exists('theme_google_fonts_link')) {
    /**
     * Generate Google Fonts link tag for theme fonts
     */
    function theme_google_fonts_link(?array $themeConfig = null): string
    {
        $config = $themeConfig ?? get_theme_config();
        
        if (empty($config['typography'])) {
            return '';
        }
        
        $fonts = [];
        $typo = $config['typography'];
        
        if (!empty($typo['headingFont'])) {
            $fonts[] = $typo['headingFont'];
        }
        if (!empty($typo['bodyFont']) && $typo['bodyFont'] !== ($typo['headingFont'] ?? '')) {
            $fonts[] = $typo['bodyFont'];
        }
        
        if (empty($fonts)) {
            return '';
        }
        
        $fontParams = [];
        foreach ($fonts as $font) {
            $fontParams[] = 'family=' . urlencode($font) . ':wght@400;500;600;700;800';
        }
        
        $url = 'https://fonts.googleapis.com/css2?' . implode('&', $fontParams) . '&display=swap';
        
        return '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n" .
               '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n" .
               '<link href="' . esc($url) . '" rel="stylesheet">';
    }
}

// ============================================================================
// NAVIGATION FUNCTIONS (like WordPress wp_nav_menu)
// ============================================================================

if (!function_exists('cms_get_menu')) {
    /**
     * Get menu items by menu location or slug
     * Falls back to published pages if no menu exists
     * 
     * @param string $location Menu location (header, footer, sidebar) or menu slug
     * @return array Menu items
     */
    function cms_get_menu(string $location = 'header'): array
    {
        try {
            $pdo = db();
            
            // Try to find menu by location first
            $stmt = $pdo->prepare("
                SELECT m.id, m.name, m.slug 
                FROM menus m 
                WHERE (m.location = :location OR m.slug = :location) 
                  AND m.is_active = 1 
                LIMIT 1
            ");
            $stmt->execute(['loc1' => $location, 'loc2' => $location]);
            $menu = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($menu) {
                // Get menu items
                $stmt = $pdo->prepare("
                    SELECT mi.*, p.slug as page_slug 
                    FROM menu_items mi 
                    LEFT JOIN pages p ON mi.page_id = p.id
                    WHERE mi.menu_id = :menu_id AND mi.is_active = 1
                    ORDER BY mi.sort_order ASC, mi.id ASC
                ");
                $stmt->execute(['menu_id' => $menu['id']]);
                $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                if (!empty($items)) {
                    return array_map(function($item) {
                        return [
                            'id' => $item['id'],
                            'title' => $item['title'],
                            'url' => $item['url'] ?: ($item['page_slug'] ? '/page/' . $item['page_slug'] : '#'),
                            'target' => $item['target'] ?? '_self',
                            'parent_id' => $item['parent_id'],
                            'css_class' => $item['css_class'] ?? '',
                            'icon' => $item['icon'] ?? ''
                        ];
                    }, $items);
                }
            }
            
            // Fallback: return all published pages (like WordPress wp_page_menu)
            return cms_get_pages_as_menu();
            
        } catch (\Exception $e) {
            return cms_get_pages_as_menu();
        }
    }
}

if (!function_exists('cms_get_pages_as_menu')) {
    /**
     * Get all published pages as menu items (fallback)
     * Similar to WordPress wp_list_pages()
     * 
     * @return array Menu items from pages
     */
    function cms_get_pages_as_menu(): array
    {
        try {
            $pdo = db();
            $stmt = $pdo->query("
                SELECT id, slug, title, menu_order 
                FROM pages 
                WHERE status = 'published' 
                ORDER BY menu_order ASC, title ASC
            ");
            $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return array_map(function($page) {
                return [
                    'id' => $page['id'],
                    'title' => $page['title'],
                    'url' => '/page/' . $page['slug'],
                    'target' => '_self',
                    'parent_id' => null,
                    'css_class' => '',
                    'icon' => ''
                ];
            }, $pages);
            
        } catch (\Exception $e) {
            return [];
        }
    }
}

if (!function_exists('cms_nav_menu')) {
    /**
     * Render navigation menu HTML
     * Similar to WordPress wp_nav_menu()
     * 
     * @param array $args Configuration options
     * @return string HTML output
     */
    function cms_nav_menu(array $args = []): string
    {
        $defaults = [
            'location' => 'header',
            'menu_class' => 'nav-menu',
            'menu_id' => '',
            'container' => 'nav',
            'container_class' => 'nav-container',
            'container_id' => '',
            'link_class' => 'nav-link',
            'show_home' => true,
            'home_text' => 'Home',
            'depth' => 0,
            'echo' => true
        ];
        
        $args = array_merge($defaults, $args);
        $items = cms_get_menu($args['location']);
        $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
        
        $html = '';
        
        // Container open
        if ($args['container']) {
            $containerAttrs = '';
            if ($args['container_class']) $containerAttrs .= ' class="' . esc($args['container_class']) . '"';
            if ($args['container_id']) $containerAttrs .= ' id="' . esc($args['container_id']) . '"';
            $html .= '<' . $args['container'] . $containerAttrs . '>';
        }
        
        // Menu open
        $menuAttrs = '';
        if ($args['menu_class']) $menuAttrs .= ' class="' . esc($args['menu_class']) . '"';
        if ($args['menu_id']) $menuAttrs .= ' id="' . esc($args['menu_id']) . '"';
        $html .= '<ul' . $menuAttrs . '>';
        
        // Home link
        if ($args['show_home']) {
            $homeClass = ($currentPath === '/' || $currentPath === '/index.php') ? 'current' : '';
            $html .= '<li class="menu-item ' . $homeClass . '">';
            $html .= '<a href="/" class="' . esc($args['link_class']) . '">' . esc($args['home_text']) . '</a>';
            $html .= '</li>';
        }
        
        // Menu items
        foreach ($items as $item) {
            $isActive = ($currentPath === $item['url'] || strpos($currentPath, $item['url']) === 0);
            $itemClass = 'menu-item';
            if ($isActive) $itemClass .= ' current';
            if ($item['css_class']) $itemClass .= ' ' . $item['css_class'];
            
            $html .= '<li class="' . $itemClass . '">';
            $html .= '<a href="' . esc($item['url']) . '" class="' . esc($args['link_class']) . '"';
            if ($item['target'] !== '_self') {
                $html .= ' target="' . esc($item['target']) . '"';
            }
            $html .= '>';
            if ($item['icon']) {
                $html .= '<i class="' . esc($item['icon']) . '"></i> ';
            }
            $html .= esc($item['title']);
            $html .= '</a>';
            $html .= '</li>';
        }
        
        // Menu close
        $html .= '</ul>';
        
        // Container close
        if ($args['container']) {
            $html .= '</' . $args['container'] . '>';
        }
        
        if ($args['echo']) {
            echo $html;
            return '';
        }
        
        return $html;
    }
}

if (!function_exists('cms_get_site_info')) {
    /**
     * Get site information from settings
     * 
     * @param string $key Setting key (site_name, tagline, etc.)
     * @param string $default Default value
     * @return string
     */
    function cms_get_site_info(string $key, string $default = ''): string
    {
        static $cache = null;
        
        if ($cache === null) {
            try {
                $pdo = db();
                $stmt = $pdo->query("SELECT `key`, value FROM settings WHERE `key` IN ('site_name', 'tagline', 'admin_email', 'site_logo')");
                $cache = [];
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $cache[$row['key']] = $row['value'];
                }
            } catch (\Exception $e) {
                $cache = [];
            }
        }
        
        return $cache[$key] ?? $default;
    }
}

// ═══════════════════════════════════════════════════════════
// THEME BUILDER CONTENT
// ═══════════════════════════════════════════════════════════

if (!function_exists('get_theme_builder_content')) {
    /**
     * Get rendered HTML content from theme builder page
     *
     * @param string $slug Page slug to look up
     * @return string|null Rendered HTML or null if not found
     */
    function get_theme_builder_content(string $slug): ?string
    {
        try {
            $pdo = db();

            // Check if tb_pages table exists (Theme Builder 3.0 uses tb_pages, not theme_builder_pages)
            $stmt = $pdo->query("SHOW TABLES LIKE 'tb_pages'");
            if (!$stmt->fetch()) {
                return null;
            }

            // Query for the page
            $stmt = $pdo->prepare("
                SELECT id, slug, title, content_json, status
                FROM tb_pages
                WHERE slug = :slug AND status = 'published'
                LIMIT 1
            ");
            $stmt->execute(['slug' => $slug]);
            $page = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$page || empty($page['content_json'])) {
                return null;
            }

            // Parse JSON structure
            $structure = json_decode($page['content_json'], true);
            if (!is_array($structure)) {
                return null;
            }

            // Use the proper Theme Builder renderer
            $rendererPath = CMS_ROOT . '/core/theme-builder/renderer.php';
            if (file_exists($rendererPath)) {
                require_once $rendererPath;
                if (function_exists('tb_render_page')) {
                    return tb_render_page($structure);
                }
            }

            // Fallback to simple renderer if TB renderer not available
            return render_theme_builder_structure($structure);

        } catch (\Exception $e) {
            error_log('get_theme_builder_content error: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('render_theme_builder_structure')) {
    /**
     * Render theme builder JSON structure to HTML
     */
    function render_theme_builder_structure(array $structure): string
    {
        $html = '';
        $blocks = $structure['blocks'] ?? $structure;
        if (!is_array($blocks)) {
            return '';
        }

        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            $type = $block['type'] ?? 'unknown';
            $content = $block['content'] ?? '';
            $settings = $block['settings'] ?? [];

            switch ($type) {
                case 'hero':
                    $html .= render_hero_block($block);
                    break;

                case 'text':
                case 'paragraph':
                    $html .= '<div class="tb-text-block">' . ($content ?: ($block['text'] ?? '')) . '</div>';
                    break;

                case 'heading':
                    $level = $settings['level'] ?? 2;
                    $text = $content ?: ($block['text'] ?? '');
                    $html .= "<h{$level} class=\"tb-heading\">" . esc($text) . "</h{$level}>";
                    break;

                case 'image':
                    $src = $block['src'] ?? ($settings['src'] ?? '');
                    $alt = $block['alt'] ?? ($settings['alt'] ?? '');
                    if ($src) {
                        $html .= '<figure class="tb-image-block">' .
                                 '<img src="' . esc($src) . '" alt="' . esc($alt) . '" loading="lazy">' .
                                 '</figure>';
                    }
                    break;

                case 'button':
                    $text = $block['text'] ?? ($content ?: 'Click Here');
                    $url = $block['url'] ?? ($settings['url'] ?? '#');
                    $style = $settings['style'] ?? 'primary';
                    $html .= '<div class="tb-button-block">' .
                             '<a href="' . esc($url) . '" class="tb-button tb-button-' . esc($style) . '">' .
                             esc($text) . '</a></div>';
                    break;

                case 'columns':
                case 'grid':
                    $html .= render_columns_block($block);
                    break;

                case 'section':
                    $html .= render_section_block($block);
                    break;

                case 'html':
                case 'raw':
                    $html .= '<div class="tb-html-block">' . $content . '</div>';
                    break;

                case 'spacer':
                    $height = $settings['height'] ?? 40;
                    $html .= '<div class="tb-spacer" style="height:' . (int)$height . 'px"></div>';
                    break;

                case 'divider':
                    $html .= '<hr class="tb-divider">';
                    break;

                default:
                    if ($content) {
                        $html .= '<div class="tb-block tb-block-' . esc($type) . '">' . $content . '</div>';
                    }
            }
        }

        return $html;
    }
}

if (!function_exists('render_hero_block')) {
    /**
     * Render a hero block
     */
    function render_hero_block(array $block): string
    {
        $settings = $block['settings'] ?? [];
        $title = $block['title'] ?? ($settings['title'] ?? '');
        $subtitle = $block['subtitle'] ?? ($settings['subtitle'] ?? '');
        $buttonText = $block['buttonText'] ?? ($settings['buttonText'] ?? '');
        $buttonUrl = $block['buttonUrl'] ?? ($settings['buttonUrl'] ?? '#');
        $bgImage = $block['backgroundImage'] ?? ($settings['backgroundImage'] ?? '');
        $bgColor = $settings['backgroundColor'] ?? '';

        $style = '';
        if ($bgImage) {
            $style .= "background-image:url('" . esc($bgImage) . "');";
        }
        if ($bgColor) {
            $style .= "background-color:" . esc($bgColor) . ";";
        }

        $html = '<section class="tb-hero"' . ($style ? ' style="' . $style . '"' : '') . '>';
        $html .= '<div class="tb-hero-content">';

        if ($title) {
            $html .= '<h1 class="tb-hero-title">' . esc($title) . '</h1>';
        }
        if ($subtitle) {
            $html .= '<p class="tb-hero-subtitle">' . esc($subtitle) . '</p>';
        }
        if ($buttonText) {
            $html .= '<a href="' . esc($buttonUrl) . '" class="tb-hero-button">' . esc($buttonText) . '</a>';
        }

        $html .= '</div></section>';
        return $html;
    }
}

if (!function_exists('render_columns_block')) {
    /**
     * Render a columns/grid block
     */
    function render_columns_block(array $block): string
    {
        $columns = $block['columns'] ?? ($block['children'] ?? []);
        $settings = $block['settings'] ?? [];
        $gap = $settings['gap'] ?? 20;
        $count = count($columns);

        if ($count === 0) {
            return '';
        }

        $html = '<div class="tb-columns tb-columns-' . $count . '" style="gap:' . (int)$gap . 'px">';

        foreach ($columns as $column) {
            $html .= '<div class="tb-column">';
            if (is_array($column)) {
                if (isset($column['blocks'])) {
                    $html .= render_theme_builder_structure($column['blocks']);
                } elseif (isset($column['content'])) {
                    $html .= $column['content'];
                } else {
                    $html .= render_theme_builder_structure([$column]);
                }
            } else {
                $html .= esc((string)$column);
            }
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('render_section_block')) {
    /**
     * Render a section block with nested content
     */
    function render_section_block(array $block): string
    {
        $settings = $block['settings'] ?? [];
        $children = $block['children'] ?? ($block['blocks'] ?? []);

        $classes = ['tb-section'];
        if (!empty($settings['className'])) {
            $classes[] = $settings['className'];
        }

        $style = '';
        if (!empty($settings['backgroundColor'])) {
            $style .= 'background-color:' . esc($settings['backgroundColor']) . ';';
        }
        if (!empty($settings['padding'])) {
            $style .= 'padding:' . (int)$settings['padding'] . 'px;';
        }

        $html = '<section class="' . esc(implode(' ', $classes)) . '"';
        if ($style) {
            $html .= ' style="' . $style . '"';
        }
        $html .= '><div class="tb-section-inner">';

        if (!empty($children)) {
            $html .= render_theme_builder_structure($children);
        }

        $html .= '</div></section>';
        return $html;
    }
}

// ═══════════════════════════════════════════════════════════
// SEO META TAGS
// ═══════════════════════════════════════════════════════════

if (!function_exists('render_seo_meta')) {
    /**
     * Render SEO meta tags for a page
     *
     * @param array|null $page Page data with optional meta_title, meta_description
     * @return string HTML meta tags
     */
    function render_seo_meta(?array $page = null): string
    {
        $siteName = get_site_name();

        // Title
        $title = $page['meta_title'] ?? ($page['title'] ?? $siteName);
        if ($title !== $siteName) {
            $title .= ' | ' . $siteName;
        }

        // Description
        $description = $page['meta_description'] ?? '';
        if (empty($description) && !empty($page['content'])) {
            $stripped = strip_tags($page['content']);
            $stripped = preg_replace('/\s+/', ' ', $stripped);
            $description = mb_substr(trim($stripped), 0, 160);
            if (mb_strlen($stripped) > 160) {
                $description .= '...';
            }
        }
        if (empty($description)) {
            $description = get_setting('site_description', '');
        }

        // Canonical URL
        $canonical = $page['canonical_url'] ?? '';
        if (empty($canonical)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
            $canonical = $protocol . '://' . $host . $uri;
        }

        // Featured image for OG
        $ogImage = $page['featured_image'] ?? ($page['og_image'] ?? '');
        $siteLogo = get_site_logo();
        if (empty($ogImage) && $siteLogo) {
            $ogImage = $siteLogo;
        }

        $html = '<title>' . esc($title) . '</title>' . "\n";

        if ($description) {
            $html .= '<meta name="description" content="' . esc($description) . '">' . "\n";
        }

        $html .= '<link rel="canonical" href="' . esc($canonical) . '">' . "\n";
        $html .= '<meta property="og:type" content="website">' . "\n";
        $html .= '<meta property="og:title" content="' . esc($page['title'] ?? $siteName) . '">' . "\n";
        $html .= '<meta property="og:site_name" content="' . esc($siteName) . '">' . "\n";
        $html .= '<meta property="og:url" content="' . esc($canonical) . '">' . "\n";

        if ($description) {
            $html .= '<meta property="og:description" content="' . esc($description) . '">' . "\n";
        }
        if ($ogImage) {
            $html .= '<meta property="og:image" content="' . esc($ogImage) . '">' . "\n";
        }

        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $html .= '<meta name="twitter:title" content="' . esc($page['title'] ?? $siteName) . '">' . "\n";
        if ($description) {
            $html .= '<meta name="twitter:description" content="' . esc($description) . '">' . "\n";
        }
        if ($ogImage) {
            $html .= '<meta name="twitter:image" content="' . esc($ogImage) . '">' . "\n";
        }

        $robots = $page['robots'] ?? 'index, follow';
        $html .= '<meta name="robots" content="' . esc($robots) . '">' . "\n";

        return $html;
    }
}

// ═══════════════════════════════════════════════════════════
// MENU RENDERING (render_menu function)
// ═══════════════════════════════════════════════════════════

if (!function_exists('render_menu')) {
    /**
     * Render a navigation menu by location
     *
     * @param string $location Menu location (header, footer, sidebar)
     * @param array $options Rendering options
     * @return string HTML nav element
     */
    function render_menu(string $location, array $options = []): string
    {
        $defaults = [
            'container_class' => 'site-nav',
            'menu_class' => 'nav-menu',
            'link_class' => 'nav-link',
            'active_class' => 'active',
            'show_home' => false,
            'home_text' => 'Home',
            'depth' => 2,
            'wrap' => true,
            'fallback_to_pages' => true
        ];

        // Accept 'class' as alias for 'menu_class'
        if (isset($options['class']) && !isset($options['menu_class'])) {
            $options['menu_class'] = $options['class'];
        }

        $options = array_merge($defaults, $options);
        $items = get_menu_items_for_render($location, $options['fallback_to_pages']);
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        $html = '';
        if ($options['wrap']) {
            $html .= '<nav class="' . esc($options['container_class']) . '">';
        }
        $html .= '<ul class="' . esc($options['menu_class']) . '">';

        if ($options['show_home']) {
            $homeActive = ($currentPath === '/' || $currentPath === '/index.php')
                         ? ' ' . esc($options['active_class']) : '';
            $html .= '<li class="menu-item' . $homeActive . '">';
            $html .= '<a href="/" class="' . esc($options['link_class']) . '">' . esc($options['home_text']) . '</a>';
            $html .= '</li>';
        }

        $html .= render_menu_items_recursive($items, $options, $currentPath, 1);
        $html .= '</ul>';
        if ($options['wrap']) {
            $html .= '</nav>';
        }

        return $html;
    }
}

if (!function_exists('get_menu_items_for_render')) {
    /**
     * Get menu items from database for render_menu
     */
    function get_menu_items_for_render(string $location, bool $fallbackToPages = true): array
    {
        try {
            $pdo = db();

            $stmt = $pdo->prepare("SELECT id, name, slug FROM menus WHERE location = :loc1 OR slug = :loc2 LIMIT 1");
            $stmt->execute(['loc1' => $location, 'loc2' => $location]);
            $menu = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($menu) {
                $stmt = $pdo->prepare("
                    SELECT mi.id, mi.parent_id, mi.title, mi.url, mi.page_id, mi.target, mi.css_class, mi.sort_order,
                           p.slug as page_slug, p.title as page_title
                    FROM menu_items mi
                    LEFT JOIN pages p ON mi.page_id = p.id
                    WHERE mi.menu_id = :menu_id
                    ORDER BY mi.parent_id ASC, mi.sort_order ASC, mi.id ASC
                ");
                $stmt->execute(['menu_id' => $menu['id']]);
                $rawItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if (!empty($rawItems)) {
                    return build_menu_tree_for_render($rawItems);
                }
            }

            if ($fallbackToPages) {
                return get_pages_as_menu_items_for_render();
            }

            return [];

        } catch (\Exception $e) {
            error_log('get_menu_items_for_render error: ' . $e->getMessage());
            return $fallbackToPages ? get_pages_as_menu_items_for_render() : [];
        }
    }
}

if (!function_exists('build_menu_tree_for_render')) {
    /**
     * Build hierarchical menu tree from flat items
     */
    function build_menu_tree_for_render(array $items, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($items as $item) {
            $itemParent = $item['parent_id'] ? (int)$item['parent_id'] : null;

            if ($itemParent === $parentId) {
                $url = $item['url'];
                if (empty($url) && $item['page_slug']) {
                    $url = '/page/' . $item['page_slug'];
                }
                if (empty($url)) {
                    $url = '#';
                }

                $tree[] = [
                    'id' => (int)$item['id'],
                    'title' => $item['title'] ?: ($item['page_title'] ?? 'Untitled'),
                    'url' => $url,
                    'target' => $item['target'] ?: '_self',
                    'css_class' => $item['css_class'] ?? '',
                    'children' => build_menu_tree_for_render($items, (int)$item['id'])
                ];
            }
        }

        return $tree;
    }
}

if (!function_exists('get_pages_as_menu_items_for_render')) {
    /**
     * Get published pages as menu items (fallback)
     */
    function get_pages_as_menu_items_for_render(): array
    {
        try {
            $pdo = db();
            $stmt = $pdo->query("SELECT id, slug, title FROM pages WHERE status = 'published' ORDER BY title ASC LIMIT 20");
            $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return array_map(function($page) {
                return [
                    'id' => (int)$page['id'],
                    'title' => $page['title'],
                    'url' => '/page/' . $page['slug'],
                    'target' => '_self',
                    'css_class' => '',
                    'children' => []
                ];
            }, $pages);

        } catch (\Exception $e) {
            return [];
        }
    }
}

if (!function_exists('render_menu_items_recursive')) {
    /**
     * Recursively render menu items HTML
     */
    function render_menu_items_recursive(array $items, array $options, string $currentPath, int $depth): string
    {
        $html = '';

        foreach ($items as $item) {
            $isActive = ($currentPath === $item['url']) ||
                       (str_starts_with($currentPath, $item['url']) && $item['url'] !== '/');
            $hasChildren = !empty($item['children']) && $depth < $options['depth'];

            $classes = ['menu-item'];
            if ($isActive) {
                $classes[] = $options['active_class'];
            }
            if ($hasChildren) {
                $classes[] = 'has-children';
            }
            if ($item['css_class']) {
                $classes[] = $item['css_class'];
            }

            $html .= '<li class="' . esc(implode(' ', $classes)) . '">';
            $html .= '<a href="' . esc($item['url']) . '" class="' . esc($options['link_class']) . '"';
            if ($item['target'] !== '_self') {
                $html .= ' target="' . esc($item['target']) . '"';
                if ($item['target'] === '_blank') {
                    $html .= ' rel="noopener noreferrer"';
                }
            }
            $html .= '>' . esc($item['title']) . '</a>';

            if ($hasChildren) {
                $html .= '<ul class="sub-menu">';
                $html .= render_menu_items_recursive($item['children'], $options, $currentPath, $depth + 1);
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        return $html;
    }
}

// ═══════════════════════════════════════════════════════════
// SETTINGS ACCESS (get_setting, get_site_logo, get_site_name)
// ═══════════════════════════════════════════════════════════

if (!function_exists('get_setting')) {
    /**
     * Get a setting value from the database
     */
    function get_setting(string $key, mixed $default = null): mixed
    {
        static $cache = [];

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = :key LIMIT 1");
            $stmt->execute(['key' => $key]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result !== false) {
                $cache[$key] = $result['value'];
                return $result['value'];
            }

        } catch (\Exception $e) {
            error_log('get_setting error: ' . $e->getMessage());
        }

        $cache[$key] = $default;
        return $default;
    }
}

if (!function_exists('get_site_logo')) {
    /**
     * Get the site logo URL
     */
    function get_site_logo(): ?string
    {
        $logo = get_setting('site_logo');
        return $logo ?: null;
    }
}

if (!function_exists('get_site_name')) {
    /**
     * Get the site name
     */
    function get_site_name(): string
    {
        return get_setting('site_name', 'My Site') ?: 'My Site';
    }
}

if (!function_exists('get_body_class')) {
    /**
     * Generate CSS classes for the body element based on current context
     */
    function get_body_class(): string
    {
        $classes = ['cms-body'];
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = rtrim($path, '/') ?: '/';

        if ($path === '/' || $path === '/index.php') {
            $classes[] = 'home';
            $classes[] = 'page-home';
        }

        if (str_starts_with($path, '/page/')) {
            $classes[] = 'page';
            $slug = basename($path);
            $classes[] = 'page-' . preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
        } elseif (str_starts_with($path, '/blog')) {
            $classes[] = 'blog';
            if ($path === '/blog' || $path === '/blog/') {
                $classes[] = 'blog-index';
            } else {
                $classes[] = 'blog-single';
            }
        } elseif (str_starts_with($path, '/category/')) {
            $classes[] = 'archive';
            $classes[] = 'category';
        } elseif (str_starts_with($path, '/tag/')) {
            $classes[] = 'archive';
            $classes[] = 'tag';
        } elseif (str_starts_with($path, '/search')) {
            $classes[] = 'search';
            $classes[] = 'search-results';
        }

        if (!empty($_GET['preview'])) {
            $classes[] = 'preview-mode';
        }
        if (!empty($_GET['preview_theme'])) {
            $classes[] = 'theme-preview';
        }
        if (!empty($_SESSION['user_id'])) {
            $classes[] = 'logged-in';
        }

        if (function_exists('get_active_theme')) {
            $classes[] = 'theme-' . preg_replace('/[^a-z0-9\-]/', '', strtolower(get_active_theme()));
        }

        return implode(' ', $classes);
    }
}
