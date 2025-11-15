<?php
namespace controllers;
require_once __DIR__ . '/../core/database.php';

class page_controller {
    public static function show(string $slug): void {
        $slug = trim($slug);
        if ($slug === '') { http_response_code(404); echo 'Not Found'; return; }
        try {
            $pdo = \core\Database::connection();
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

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
            $themeDir = __DIR__ . '/../themes/default_public';
            $header = $themeDir . '/header.php';
            $footer = $themeDir . '/footer.php';

            $title = htmlspecialchars((string)($row['title'] ?? $slug), ENT_QUOTES, 'UTF-8');
            $content = (string)($row['content'] ?? '');

            header('Content-Type: text/html; charset=UTF-8');
            if (is_file($header) && is_readable($header)) {
                // use include (not require) so a warning does not fatally abort rendering
                @include $header;
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
                @include $footer;
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
}
