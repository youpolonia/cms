<?php
require_once __DIR__ . '/navigation.php';
@require_once dirname(__DIR__, 2) . '/version.php';

if (!function_exists('admin_render_page_start')) {
    function admin_render_page_start(string $title = 'Admin'): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>';
        $cssPath = '/assets/css/style.css';
        $absCss  = $_SERVER['DOCUMENT_ROOT'] . $cssPath;
        if (is_file($absCss)) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($cssPath, ENT_QUOTES, 'UTF-8') . '">';
        }
        echo '<style>
            :root{--maxw:1200px;--pad:16px;--fg:#222;--muted:#666}
            body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif;color:var(--fg)}
            header{max-width:var(--maxw);margin:0 auto;padding:var(--pad)}
            main{max-width:var(--maxw);margin:0 auto;padding:var(--pad)}
            footer{max-width:var(--maxw);margin:32px auto;padding:var(--pad);color:var(--muted);border-top:1px solid #eee}
            h1{font-size:2rem;margin:.5rem 0}
            .dev-banner{background:#fff3cd;color:#664d03;border-bottom:1px solid #ffe69c;padding:8px 12px;font-size:.9rem}
            .version-badge{font-size:.85rem;color:#555;margin-left:.5rem}
        </style>';
        echo '</head><body>';
        if (defined('DEV_MODE') && DEV_MODE === true) {
            echo '<div class="dev-banner">DEV MODE ENABLED</div>';
        }
        echo '<header><h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
        if (defined('CMS_VERSION')) {
            echo '<span class="version-badge">v' . htmlspecialchars((string)CMS_VERSION, ENT_QUOTES, 'UTF-8') . '</span>';
        }
        echo '</header>';
        if (function_exists('renderAdminNavigation')) {
            renderAdminNavigation();
        }
        echo '<main>';
    }
}

if (!function_exists('admin_render_page_end')) {
    function admin_render_page_end(): void
    {
        echo '</main><footer>Admin • ' . date('Y') . '</footer>';
        $jsPath = '/assets/js/script.js';
        $absJs  = $_SERVER['DOCUMENT_ROOT'] . $jsPath;
        if (is_file($absJs)) {
            echo '<script src="' . htmlspecialchars($jsPath, ENT_QUOTES, 'UTF-8') . '"></script>';
        }
        echo '</body></html>';
    }
}
