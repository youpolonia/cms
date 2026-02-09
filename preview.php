<?php
/**
 * CMS Central Preview System
 * 
 * Unified preview handler for all CMS content types.
 * 
 * Usage:
 *   /preview.php?type=tb&id=26           - Theme Builder page by ID
 *   /preview.php?type=tb&slug=test-page  - Theme Builder page by slug
 *   /preview.php?type=page&id=3          - Regular CMS page by ID
 *   /preview.php?type=page&slug=about    - Regular CMS page by slug
 *   /preview.php?type=article&id=5       - Article by ID
 *   /preview.php?type=template&id=1      - Site template preview
 * 
 * Session preview (unsaved changes):
 *   /preview.php?type=tb&id=26&session=1 - Load from session if available
 */

declare(strict_types=1);

// Bootstrap
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

// Start session for preview data - use same session as admin panel
if (session_status() === PHP_SESSION_NONE) {
    // Check for admin session cookie
    if (isset($_COOKIE['CMSSESSID_ADMIN'])) {
        session_name('CMSSESSID_ADMIN');
    }
    session_start();
}

// Security: Only allow preview for logged-in admins
function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']) || !empty($_SESSION['user_id']) || !empty($_SESSION['admin_role']);
}

if (!isAdminLoggedIn()) {
    http_response_code(403);
    die('Access denied. Please log in to admin panel first.');
}

// Get parameters
$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = $_GET['slug'] ?? '';
$useSession = isset($_GET['session']) && $_GET['session'] === '1';

if (empty($type)) {
    http_response_code(400);
    die('Missing type parameter. Use: tb, page, article, template');
}

try {
    $pdo = \core\Database::connection();
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    
    $title = 'Preview';
    $content = '';
    $renderMode = 'html'; // html, tb (theme builder)
    
    switch ($type) {
        // ========================================
        // LEGACY THEME BUILDER (removed — use JTB)
        // ========================================
        case 'tb':
        case 'theme-builder':
            throw new \Exception('Legacy Theme Builder has been removed. Use JTB (Jessie Theme Builder) instead.');
            break;
            
        // ========================================
        // REGULAR CMS PAGES
        // ========================================
        case 'page':
            if ($id > 0) {
                $stmt = $pdo->prepare("SELECT id, title, slug, content, status FROM pages WHERE id = ?");
                $stmt->execute([$id]);
            } elseif (!empty($slug)) {
                $stmt = $pdo->prepare("SELECT id, title, slug, content, status FROM pages WHERE slug = ?");
                $stmt->execute([$slug]);
            } else {
                throw new Exception('Provide id or slug parameter for page preview');
            }
            
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                throw new Exception('Page not found');
            }
            
            $title = $row['title'] ?? 'Preview';
            $content = $row['content'] ?? '';
            $renderMode = 'html';
            break;
            
        // ========================================
        // ARTICLES
        // ========================================
        case 'article':
        case 'post':
            $articleData = null;
            
            // Try session first (unsaved changes)
            if ($useSession && $id > 0) {
                $sessionKey = 'article_preview_' . $id;
                if (isset($_SESSION[$sessionKey]) && is_array($_SESSION[$sessionKey])) {
                    $articleData = $_SESSION[$sessionKey];
                }
            }
            
            if ($articleData) {
                // Use session data
                $title = $articleData['title'] ?? 'Preview';
                $articleContent = $articleData['content'] ?? '';
                $featuredImage = $articleData['featured_image'] ?? '';
                $categoryName = $articleData['category_name'] ?? '';
                
                // Build HTML with featured image and category
                $content = '<article class="article-preview">';
                $content .= '<h1>' . htmlspecialchars($title) . '</h1>';
                if ($categoryName) {
                    $content .= '<div class="article-meta"><span class="category">📁 ' . htmlspecialchars($categoryName) . '</span></div>';
                }
                if ($featuredImage) {
                    $content .= '<img src="' . htmlspecialchars($featuredImage) . '" alt="' . htmlspecialchars($title) . '" class="featured-image" style="max-width:100%;height:auto;margin:20px 0;border-radius:8px;">';
                }
                $content .= '<div class="article-content">' . $articleContent . '</div>';
                $content .= '</article>';
            } else {
                // Load from database
                if ($id > 0) {
                    $stmt = $pdo->prepare("SELECT a.id, a.title, a.slug, a.content, a.status, a.featured_image, c.name as category_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id WHERE a.id = ?");
                    $stmt->execute([$id]);
                } elseif (!empty($slug)) {
                    $stmt = $pdo->prepare("SELECT a.id, a.title, a.slug, a.content, a.status, a.featured_image, c.name as category_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id WHERE a.slug = ?");
                    $stmt->execute([$slug]);
                } else {
                    throw new Exception('Provide id or slug parameter for article preview');
                }
                
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (!$row) {
                    throw new Exception('Article not found');
                }
                
                $title = $row['title'] ?? 'Preview';
                $featuredImage = $row['featured_image'] ?? '';
                $categoryName = $row['category_name'] ?? '';
                
                // Build HTML with featured image and category
                $content = '<article class="article-preview">';
                $content .= '<h1>' . htmlspecialchars($title) . '</h1>';
                if ($categoryName) {
                    $content .= '<div class="article-meta"><span class="category">📁 ' . htmlspecialchars($categoryName) . '</span></div>';
                }
                if ($featuredImage) {
                    $content .= '<img src="' . htmlspecialchars($featuredImage) . '" alt="' . htmlspecialchars($title) . '" class="featured-image" style="max-width:100%;height:auto;margin:20px 0;border-radius:8px;">';
                }
                $content .= '<div class="article-content">' . ($row['content'] ?? '') . '</div>';
                $content .= '</article>';
            }
            
            $renderMode = 'html';
            break;
            
        // Legacy template preview — removed (JTB has its own preview system)
        case 'template':
            throw new \Exception('Legacy template preview removed. Use JTB template preview instead.');
            break;
            
        default:
            throw new Exception('Unknown type: ' . htmlspecialchars($type));
    }
    
    // Render output
    renderPreview($title, $content, $renderMode, $type);
    
} catch (\Throwable $e) {
    http_response_code(500);
    renderError($e->getMessage());
}

/**
 * Render preview with theme wrapper - SAME AS tb-page.php
 */
function renderPreview(string $title, string $content, string $renderMode, string $type): void {
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    
    // Load active theme and colors - SAME AS tb-page.php
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
        $themeJsonPath = __DIR__ . '/themes/' . $activeTheme . '/theme.json';
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
    
    // Color defaults - SAME AS tb-page.php
    $primaryColor = $themeColors['primary'] ?? '#3b82f6';
    $secondaryColor = $themeColors['secondary'] ?? '#06b6d4';
    $accentColor = $themeColors['accent'] ?? '#f59e0b';
    $bgColor = $themeColors['background'] ?? '#0f172a';
    $surfaceColor = $themeColors['surface'] ?? '#1e293b';
    $textColor = $themeColors['text'] ?? '#f1f5f9';
    $textMuted = $themeColors['text_muted'] ?? '#a0a0b0';
    $borderColor = $themeColors['border'] ?? '#2d2d3a';
    
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . ' - Preview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/public/assets/css/tb-frontend.css">
    <style>
        :root {
            --primary: ' . htmlspecialchars($primaryColor) . ';
            --secondary: ' . htmlspecialchars($secondaryColor) . ';
            --accent: ' . htmlspecialchars($accentColor) . ';
            --background: ' . htmlspecialchars($bgColor) . ';
            --surface: ' . htmlspecialchars($surfaceColor) . ';
            --text: ' . htmlspecialchars($textColor) . ';
            --text-muted: ' . htmlspecialchars($textMuted) . ';
            --border: ' . htmlspecialchars($borderColor) . ';
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif; 
            line-height: 1.6;
            color: var(--text);
            background: var(--background);
        }
        a { color: var(--primary); }
        a:hover { color: var(--accent); }
        img { max-width: 100%; height: auto; }
        .preview-banner { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 12px 20px; 
            text-align: center; 
            font-size: 14px; 
            position: sticky; 
            top: 0; 
            z-index: 9999; 
        }
        .preview-banner a { color: white; margin-left: 15px; }
    </style>
</head>
<body>';
    
    // Preview banner
    echo '<div class="preview-banner">
        <strong>🔍 PREVIEW MODE</strong> - ' . htmlspecialchars($type) . ': ' . $title . '
        <a href="javascript:window.close()">✕ Close</a>
    </div>';
    
    // Content
    if ($renderMode === 'tb') {
        // Theme Builder content - already has full HTML structure
        echo $content;
    } else {
        // Regular HTML content - wrap in container
        echo '<main style="max-width: 900px; margin: 40px auto; padding: 0 20px;">
            <h1 style="margin-bottom: 20px;">' . $title . '</h1>
            <div class="content">' . $content . '</div>
        </main>';
    }
    
    // Animation JS - SAME AS tb-page.php
    echo '
    <script>
    (function() {
        "use strict";
        function initAnimations() {
            var elements = document.querySelectorAll("[style*=\'animation:\']");
            if (!elements.length) return;

            var supportsObserver = "IntersectionObserver" in window;

            elements.forEach(function(el) {
                var style = el.getAttribute("style") || "";
                var match = style.match(/animation:\\s*([^;]+)/);
                if (!match) return;

                var animValue = match[1];
                var scrollTrigger = el.dataset.scrollTrigger === "true";
                var triggerPoint = parseInt(el.dataset.triggerPoint || "80", 10);
                var animateOnce = el.dataset.animateOnce !== "false";

                if (scrollTrigger && supportsObserver) {
                    el.style.opacity = "0";
                    el.style.animation = "none";

                    var observer = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                entry.target.style.opacity = "";
                                entry.target.style.animation = animValue;
                                if (animateOnce) observer.unobserve(entry.target);
                            } else if (!animateOnce) {
                                entry.target.style.opacity = "0";
                                entry.target.style.animation = "none";
                            }
                        });
                    }, { threshold: triggerPoint / 100 });

                    observer.observe(el);
                }
            });
        }

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initAnimations);
        } else {
            initAnimations();
        }
    })();
    </script>
</body>
</html>';
}

/**
 * Render error page
 */
function renderError(string $message): void {
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Preview Error</title>
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f5f5f5; margin: 0; }
        .error-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); max-width: 500px; text-align: center; }
        .error-box h1 { color: #e53e3e; margin-bottom: 15px; }
        .error-box p { color: #666; }
        .error-box a { display: inline-block; margin-top: 20px; color: #667eea; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>⚠️ Preview Error</h1>
        <p>' . htmlspecialchars($message) . '</p>
        <a href="javascript:history.back()">← Go Back</a>
    </div>
</body>
</html>';
}
