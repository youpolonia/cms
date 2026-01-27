<?php
/**
 * Jessie AI-CMS - Header Component
 * Supports Theme Builder custom headers with Display Conditions
 */

// Load Theme Builder functions for dynamic header
if (!function_exists('tb_render_site_template')) {
    $tbDatabasePath = dirname(__DIR__, 4) . '/core/theme-builder/database.php';
    if (file_exists($tbDatabasePath)) {
        require_once $tbDatabasePath;
    }
}

$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$isHome = $currentPath === '/';

// Get current page context for Display Conditions
$urlPath = trim(parse_url($currentPath, PHP_URL_PATH) ?? '/', '/');
// Extract actual slug from URL patterns like /page/slug or just /slug
if (preg_match('#^page/(.+)$#', $urlPath, $m)) {
    $pageSlug = $m[1];
} else {
    $pageSlug = $urlPath;
}
// Also check if page data is available from controller
if (isset($page['slug']) && !empty($page['slug'])) {
    $pageSlug = $page['slug'];
}
$pageContext = [
    'slug' => $pageSlug ?: 'home',
    'category' => $pageCategory ?? ''
];

// Try to get TB Header matching current page conditions
$tbHeader = null;
if (function_exists('tb_render_site_template')) {
    $tbHeader = tb_render_site_template('header', $pageContext);
}

// Load active theme colors from theme.json
$themeColors = [
    'primary' => '#8b5cf6',
    'secondary' => '#6366f1',
    'accent' => '#06b6d4',
    'background' => '#0a0a0f',
    'surface' => '#12121a',
    'text' => '#ffffff',
    'text_muted' => '#a1a1aa',
    'border' => 'rgba(255, 255, 255, 0.08)',
    'success' => '#22c55e',
    'warning' => '#f59e0b',
    'error' => '#ef4444'
];
// Try to load from active theme
if (function_exists('get_theme_config')) {
    $themeConfig = get_theme_config();
    if (!empty($themeConfig['colors'])) {
        $themeColors = array_merge($themeColors, $themeConfig['colors']);
    }
} else {
    // Fallback: load directly from theme.json
    $activeTheme = 'default';
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->query("SELECT active_theme FROM system_settings LIMIT 1");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result && !empty($result['active_theme'])) {
            $activeTheme = $result['active_theme'];
        }
    } catch (\Exception $e) {}
    
    $themePath = dirname(__DIR__, 4) . '/themes/' . $activeTheme . '/theme.json';
    if (file_exists($themePath)) {
        $themeData = json_decode(file_get_contents($themePath), true);
        if (!empty($themeData['colors'])) {
            $themeColors = array_merge($themeColors, $themeData['colors']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? esc($pageTitle) . ' - Jessie AI-CMS' : 'Jessie AI-CMS - Intelligent Content Management' ?></title>
    <meta name="description" content="<?= isset($pageDescription) ? esc($pageDescription) : 'Jessie AI-CMS is a modern, AI-powered content management system built with pure PHP.' ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
    <style>
        :root {
            /* Theme colors from active theme */
            --bg-primary: <?= esc($themeColors['background'] ?? '#0a0a0f') ?>;
            --bg-secondary: <?= esc($themeColors['surface'] ?? '#12121a') ?>;
            --bg-tertiary: #1a1a25;
            --bg-card: rgba(26, 26, 37, 0.8);
            --bg-glass: rgba(255, 255, 255, 0.03);
            --border: <?= esc($themeColors['border'] ?? 'rgba(255, 255, 255, 0.08)') ?>;
            --border-light: rgba(255, 255, 255, 0.12);
            --text-primary: <?= esc($themeColors['text'] ?? '#ffffff') ?>;
            --text-secondary: <?= esc($themeColors['text_muted'] ?? '#a1a1aa') ?>;
            --text-muted: <?= esc($themeColors['text_muted'] ?? '#71717a') ?>;
            --accent-primary: <?= esc($themeColors['primary'] ?? '#8b5cf6') ?>;
            --accent-secondary: <?= esc($themeColors['secondary'] ?? '#6366f1') ?>;
            --accent-tertiary: <?= esc($themeColors['accent'] ?? '#06b6d4') ?>;
            --gradient-primary: linear-gradient(135deg, <?= esc($themeColors['primary'] ?? '#8b5cf6') ?> 0%, <?= esc($themeColors['secondary'] ?? '#6366f1') ?> 50%, <?= esc($themeColors['accent'] ?? '#06b6d4') ?> 100%);
            --gradient-text: linear-gradient(135deg, <?= esc($themeColors['primary'] ?? '#c4b5fd') ?> 0%, <?= esc($themeColors['secondary'] ?? '#818cf8') ?> 50%, <?= esc($themeColors['accent'] ?? '#22d3ee') ?> 100%);
            --success: <?= esc($themeColors['success'] ?? '#22c55e') ?>;
            --warning: <?= esc($themeColors['warning'] ?? '#f59e0b') ?>;
            --danger: <?= esc($themeColors['error'] ?? '#ef4444') ?>;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.5);
            --shadow-glow: 0 0 60px rgba(139, 92, 246, 0.3);
            /* CSS Variable Aliases for Theme Builder layouts */
            --color-primary: var(--accent-primary);
            --color-secondary: var(--accent-secondary);
            --color-accent: var(--accent-primary);
            --color-background: var(--bg-primary);
            --color-surface: var(--bg-secondary);
            --color-text: var(--text-primary);
            --color-text-muted: var(--text-secondary);
            --color-border: var(--border);
            /* Direct aliases for tb-frontend.css compatibility */
            --primary: var(--accent-primary);
            --secondary: var(--accent-secondary);
            --text: var(--text-primary);
            --surface: var(--bg-secondary);
            --background: var(--bg-primary);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            font-size: 16px;
        }
        .bg-grid {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none; z-index: 0;
        }
        .tb-section .bg-grid, .tb-section .bg-glow { display: none !important; }
        .bg-glow {
            position: fixed; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle at 30% 20%, rgba(139, 92, 246, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 60%, rgba(6, 182, 212, 0.06) 0%, transparent 50%);
            pointer-events: none; z-index: 0;
        }
        h1, h2, h3, h4 { font-weight: 700; line-height: 1.2; }
        h1 { font-size: clamp(2.5rem, 5vw, 4rem); }
        h2 { font-size: clamp(2rem, 4vw, 3rem); }
        h3 { font-size: clamp(1.5rem, 3vw, 2rem); }
        p { color: var(--text-secondary); }
        a { color: inherit; text-decoration: none; }
        .container { max-width: 1280px; margin: 0 auto; padding: 0 24px; }
        .site-header {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            padding: 16px 0; transition: all 0.3s ease;
        }
        .site-header.scrolled {
            background: rgba(10, 10, 15, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }
        .header-inner { display: flex; align-items: center; justify-content: space-between; }
        .logo { display: flex; align-items: center; gap: 12px; font-size: 1.5rem; font-weight: 800; }
        .logo-icon {
            width: 40px; height: 40px; background: var(--gradient-primary);
            border-radius: var(--radius-md); display: flex; align-items: center;
            justify-content: center; font-size: 1.25rem;
        }
        .logo-text {
            background: var(--gradient-text);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .nav-main { display: flex; align-items: center; gap: 8px; }
        .nav-link {
            padding: 10px 18px; color: var(--text-secondary); font-weight: 500;
            font-size: 0.95rem; border-radius: var(--radius-md); transition: all 0.2s ease;
        }
        .nav-link:hover, .nav-link.active { color: var(--text-primary); background: var(--bg-glass); }
        .nav-cta { margin-left: 16px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 24px; font-size: 0.95rem; font-weight: 600;
            border-radius: var(--radius-md); border: none; cursor: pointer; transition: all 0.2s ease;
        }
        .btn-primary {
            background: var(--gradient-primary); color: white;
            box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(139, 92, 246, 0.4); }
        .btn-secondary { background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border-light); }
        .btn-secondary:hover { background: var(--bg-card); border-color: var(--accent-primary); }
        .btn-ghost { background: transparent; color: var(--text-secondary); }
        .btn-ghost:hover { color: var(--text-primary); background: var(--bg-glass); }
        .btn-lg { padding: 16px 32px; font-size: 1.05rem; }
        .card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: var(--radius-lg); backdrop-filter: blur(10px); transition: all 0.3s ease;
        }
        .card:hover { border-color: var(--border-light); box-shadow: var(--shadow-md); }
        .card-glass { background: var(--bg-glass); border: 1px solid var(--border); border-radius: var(--radius-lg); backdrop-filter: blur(20px); }
        .tag {
            display: inline-flex; align-items: center; padding: 6px 14px;
            font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
            border-radius: 50px; background: rgba(139, 92, 246, 0.15);
            color: var(--accent-primary); border: 1px solid rgba(139, 92, 246, 0.3);
        }
        .tag-cyan { background: rgba(6, 182, 212, 0.15); color: var(--accent-tertiary); border-color: rgba(6, 182, 212, 0.3); }
        .gradient-text { background: var(--gradient-text); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .mobile-toggle {
            display: none; width: 40px; height: 40px; align-items: center; justify-content: center;
            background: var(--bg-tertiary); border: 1px solid var(--border);
            border-radius: var(--radius-md); cursor: pointer; color: var(--text-primary); font-size: 1.25rem;
        }
        @media (max-width: 768px) {
            .mobile-toggle { display: flex; }
            .nav-main {
                display: none; position: absolute; top: 100%; left: 0; right: 0;
                background: var(--bg-secondary); border-bottom: 1px solid var(--border);
                padding: 16px; flex-direction: column;
            }
            .nav-main.open { display: flex; }
            .nav-cta { margin: 16px 0 0 0; width: 100%; }
            .nav-cta .btn { width: 100%; }
        }
    
        /* Theme Builder page overrides */
        body.tb-page { background: transparent !important; }
        body.tb-page .bg-grid,
        body.tb-page .bg-glow { display: none !important; }
        body.tb-page .site-header { background: transparent; }
        body.tb-page .site-header.scrolled { background: rgba(0,0,0,0.9); }
        /* Ensure TB sections show their backgrounds */
        .tb-section { position: relative; z-index: 1; }
        .tb-section-overlay { z-index: 1 !important; }
        .tb-section-inner { z-index: 2 !important; }
    </style>
</head>
<?php $isTbPage = !empty($page["is_tb_page"]) || !empty($isTbPage); ?>
<body<?= $isTbPage ? " class=\"tb-page\"" : "" ?>>
    <div class="bg-grid"></div>
    <div class="bg-glow"></div>
    <?php if ($tbHeader): ?>
        <!-- Theme Builder Header -->
        <?= $tbHeader ?>
    <?php else: ?>
        <!-- Static Fallback Header -->
        <header class="site-header" id="site-header">
            <div class="container">
                <div class="header-inner">
                    <a href="/" class="logo">
                        <span class="logo-icon">🤖</span>
                        <span class="logo-text">Jessie</span>
                    </a>
                    <button class="mobile-toggle" onclick="document.querySelector('.nav-main').classList.toggle('open')">☰</button>
                    <nav class="nav-main">
                        <a href="/" class="nav-link <?= $currentPath === '/' ? 'active' : '' ?>">Home</a>
                        <a href="/features" class="nav-link <?= $currentPath === '/features' ? 'active' : '' ?>">Features</a>
                        <a href="/articles" class="nav-link <?= str_starts_with($currentPath, '/article') ? 'active' : '' ?>">Blog</a>
                        <div class="nav-cta">
                            <a href="/admin/login" class="btn btn-primary">Get Started →</a>
                        </div>
                    </nav>
                </div>
            </div>
        </header>
    <?php endif; ?>
    <main style="position: relative; z-index: 1;">
