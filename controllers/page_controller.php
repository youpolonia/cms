<?php
namespace controllers;
require_once __DIR__ . '/../core/database.php';

class page_controller {
    public static function show(string $slug): void {
        // TEMP DEBUG
        if (isset($_GET['tb_preview'])) {
            die("DEBUG: page_controller reached! slug=$slug, tb_preview=" . $_GET['tb_preview']);
        }
        
        $slug = trim($slug);
        if ($slug === '') { http_response_code(404); echo 'Not Found'; return; }
        try {
            $pdo = \core\Database::connection();
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Check for Theme Builder preview mode
            $isPreview = isset($_GET['preview']) && $_GET['preview'] === '1';
            $tbPreviewId = isset($_GET['tb_preview']) ? (int)$_GET['tb_preview'] : 0;
            
            // DEBUG
            error_log("[PAGE_CTRL] slug=$slug, isPreview=" . ($isPreview ? '1' : '0') . ", tbPreviewId=$tbPreviewId");
            
            // For preview mode, first check if TB page exists for this slug
            if ($isPreview) {
                $tbPage = null;
                
                // If tb_preview ID provided, load by ID
                if ($tbPreviewId > 0) {
                    error_log("[PAGE_CTRL] Loading TB page by ID: $tbPreviewId");
                    $stmt = $pdo->prepare("SELECT id, title, slug, content_json FROM tb_pages WHERE id = ?");
                    $stmt->execute([$tbPreviewId]);
                    $tbPage = $stmt->fetch(\PDO::FETCH_ASSOC);
                    error_log("[PAGE_CTRL] TB page found: " . ($tbPage ? 'YES' : 'NO'));
                }
                
                // Otherwise, try to find TB page by slug
                if (!$tbPage) {
                    $stmt = $pdo->prepare("SELECT id, title, slug, content_json FROM tb_pages WHERE slug = ?");
                    $stmt->execute([$slug]);
                    $tbPage = $stmt->fetch(\PDO::FETCH_ASSOC);
                }
                
                // Render TB page if found
                if ($tbPage && !empty($tbPage['content_json'])) {
                    $content = json_decode($tbPage['content_json'], true);
                    if ($content) {
                        require_once __DIR__ . '/../core/theme-builder/renderer.php';
                        $html = tb_render_page($content, ['preview_mode' => true]);
                        
                        // Render with theme wrapper
                        self::renderWithTheme($tbPage['title'] ?? 'Preview', $html);
                        return;
                    }
                }
            }

            // find table: pages or cms_pages
            $table = null;
            foreach (['pages','cms_pages'] as $t) {
                $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t));
                if ($stmt && $stmt->fetchColumn()) {
                    $table = $t;
                    break;
                }
            }
            if (!$table) { http_response_code(404); echo 'Not Found'; return; }

            $q = $pdo->prepare("SELECT title, content, status FROM `$table` WHERE slug = :slug LIMIT 1");
            $q->execute([':slug'=>$slug]);
            $row = $q->fetch(\PDO::FETCH_ASSOC);
            if (!$row || (isset($row['status']) && $row['status'] !== 'published')) {
                http_response_code(404); echo 'Not Found'; return;
            }

            // Theme wrapper (header / footer) — resilient and non-blocking
            // Load active theme from config
            $activeTheme = 'default_public';
            $themeConfigPath = __DIR__ . '/../config_core/theme.php';
            if (file_exists($themeConfigPath)) {
                $themeConfig = @include $themeConfigPath;
                if (is_array($themeConfig) && !empty($themeConfig['active_theme'])) {
                    $activeTheme = $themeConfig['active_theme'];
                }
            }
            $themeDir = __DIR__ . '/../themes/' . $activeTheme;
            $header = $themeDir . '/header.php';
            $footer = $themeDir . '/footer.php';

            $title = htmlspecialchars((string)($row['title'] ?? $slug), ENT_QUOTES, 'UTF-8');
            $content = (string)($row['content'] ?? '');

            header('Content-Type: text/html; charset=UTF-8');
            if (is_file($header) && is_readable($header)) {
                require_once $header;
            } else {
                // minimal fallback nav
                echo '<header style="padding:12px 16px;border-bottom:1px solid #eee;font-family:sans-serif">';
                echo '<a href="/" style="margin-right:12px">Home</a>';
                echo '<a href="/page/about" style="margin-right:12px">About</a>';
                echo '<a href="/page/contact">Contact</a>';
                echo '</header>';
            }

            echo '<main class="container" style="max-width:800px;margin:80px auto;font-family:sans-serif;">';
            echo "<h1>{$title}</h1>";
            echo $content;
            echo '</main>';

            if (is_file($footer) && is_readable($footer)) {
                require_once $footer;
            } else {
                echo '<footer style="padding:24px 16px;border-top:1px solid #eee;font-family:sans-serif;margin-top:40px"><small>© CMS</small></footer>';
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log('[page_controller::show] '.$e->getMessage());
            // Always show a helpful message for localhost to reveal the real cause during dev
            $isLocal = isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1');
            if ($isLocal || (defined('DEV_MODE') && DEV_MODE === true)) {
                echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            } else {
                echo 'Error';
            }
        }
    }
    
    private static function renderWithTheme(string $title, string $content): void {
        // Load active theme from config
        $activeTheme = 'default_public';
        $themeConfigPath = __DIR__ . '/../config_core/theme.php';
        if (file_exists($themeConfigPath)) {
            $themeConfig = @include $themeConfigPath;
            if (is_array($themeConfig) && !empty($themeConfig['active_theme'])) {
                $activeTheme = $themeConfig['active_theme'];
            }
        }
        $themeDir = __DIR__ . '/../themes/' . $activeTheme;
        $header = $themeDir . '/header.php';
        $footer = $themeDir . '/footer.php';
        
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        
        header('Content-Type: text/html; charset=UTF-8');
        if (is_file($header) && is_readable($header)) {
            require_once $header;
        } else {
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $title . '</title></head><body>';
        }
        
        // Output TB content directly (already rendered HTML)
        echo $content;
        
        if (is_file($footer) && is_readable($footer)) {
            require_once $footer;
        } else {
            echo '</body></html>';
        }
    }
}
