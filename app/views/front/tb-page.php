<?php
/**
 * Theme Builder Page Template
 * Renders TB pages without article wrapper - full layout control by TB
 * 
 * @var string $pageTitle
 * @var string $pageContent
 * @var string $pageSlug
 * @var bool $isPreview
 */

$title = htmlspecialchars($pageTitle ?? 'Page');
$content = $pageContent ?? '';
$slug = $pageSlug ?? '';

// Include Theme Builder functions
require_once CMS_ROOT . '/core/theme-builder/init.php';
require_once CMS_ROOT . '/core/theme-builder/database.php';

// Page context for display conditions
$pageContext = [
    'slug' => $slug ?? '',
    'category' => ''
];

// Get TB Header template
$tbHeader = tb_render_site_template('header', $pageContext);

// Get TB Footer template  
$tbFooter = tb_render_site_template('footer', $pageContext);

// Load theme settings
$activeTheme = 'default';
$themeColors = [];
try {
    $pdo = \core\Database::connection();
    $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'active_theme' LIMIT 1");
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($row) {
        $activeTheme = $row['value'];
    }
    
    // Load theme.json for colors
    $themeJsonPath = CMS_ROOT . '/themes/' . $activeTheme . '/theme.json';
    if (file_exists($themeJsonPath)) {
        $json = @file_get_contents($themeJsonPath);
        if ($json) {
            $themeData = json_decode($json, true);
            $themeColors = $themeData['colors'] ?? [];
        }
    }
} catch (Throwable $e) {
    // Silent fail
}

// Color defaults
$primaryColor = $themeColors['primary'] ?? '#1e40af';
$secondaryColor = $themeColors['secondary'] ?? '#3b82f6';
$accentColor = $themeColors['accent'] ?? '#f59e0b';
$bgColor = $themeColors['background'] ?? '#0f172a';
$surfaceColor = $themeColors['surface'] ?? '#1e293b';
$textColor = $themeColors['text'] ?? '#f1f5f9';
$textMuted = $themeColors['text_muted'] ?? '#a0a0b0';
$borderColor = $themeColors['border'] ?? '#2d2d3a';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | My Site</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
    
    <style>
        :root {
            --primary: <?= esc($primaryColor) ?>;
            --secondary: <?= esc($secondaryColor) ?>;
            --accent: <?= esc($accentColor) ?>;
            --background: <?= esc($bgColor) ?>;
            --surface: <?= esc($surfaceColor) ?>;
            --text: <?= esc($textColor) ?>;
            --text-muted: <?= esc($textMuted) ?>;
            --border: <?= esc($borderColor) ?>;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
            line-height: 1.6;
            color: var(--text);
            background: var(--background);
        }
        a { color: var(--primary); }
        a:hover { color: var(--accent); }
        
        <?php if ($isPreview): ?>
        body::before {
            content: 'PREVIEW MODE';
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: #f59e0b;
            color: #000;
            padding: 4px 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 9999;
            border-radius: 0 0 6px 6px;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <?php if ($tbHeader): ?>
        <?= $tbHeader ?>
    <?php endif; ?>
    
    <main>
        <?= $content ?>
    </main>
    
    <?php if ($tbFooter): ?>
        <?= $tbFooter ?>
    <?php endif; ?>

    <!-- Theme Builder Animations Handler -->
    <script>
    (function() {
        'use strict';
        function initAnimations() {
            var elements = document.querySelectorAll('[style*="animation:"]');
            if (!elements.length) return;

            var supportsObserver = 'IntersectionObserver' in window;

            elements.forEach(function(el) {
                var style = el.getAttribute('style') || '';
                var match = style.match(/animation:\s*([^;]+)/);
                if (!match) return;

                var animValue = match[1];
                var scrollTrigger = el.dataset.scrollTrigger === 'true';
                var triggerPoint = parseInt(el.dataset.triggerPoint || '80', 10);
                var animateOnce = el.dataset.animateOnce !== 'false';

                if (scrollTrigger && supportsObserver) {
                    el.style.opacity = '0';
                    el.style.animation = 'none';

                    var observer = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                entry.target.style.opacity = '';
                                entry.target.style.animation = animValue;
                                if (animateOnce) observer.unobserve(entry.target);
                            } else if (!animateOnce) {
                                entry.target.style.opacity = '0';
                                entry.target.style.animation = 'none';
                            }
                        });
                    }, { threshold: triggerPoint / 100 });

                    observer.observe(el);
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAnimations);
        } else {
            initAnimations();
        }
    })();
    </script>
</body>
</html>
