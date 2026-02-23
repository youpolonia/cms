<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class SearchController
{
    public function index(Request $request): void
    {
        $query = trim($_GET['q'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;
        $results = [];
        $total = 0;

        if (mb_strlen($query) >= 2) {
            $pdo = db();
            $like = '%' . $query . '%';
            $activeTheme = get_active_theme();

            // Search pages — prefer active theme, include global (NULL theme_slug)
            $pageStmt = $pdo->prepare(
                "SELECT id, title, slug, content, meta_description, 'page' as type, theme_slug
                 FROM pages
                 WHERE status = 'published'
                   AND (theme_slug = ? OR theme_slug IS NULL)
                   AND (title LIKE ? OR content LIKE ? OR meta_description LIKE ?)
                 ORDER BY CASE WHEN title LIKE ? THEN 0 ELSE 1 END, title
                 LIMIT 50"
            );
            $pageStmt->execute([$activeTheme, $like, $like, $like, $like]);
            $pages = $pageStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Search articles — prefer active theme, include global
            $articleStmt = $pdo->prepare(
                "SELECT id, title, slug, content, excerpt, 'article' as type, theme_slug
                 FROM articles
                 WHERE status = 'published'
                   AND (theme_slug = ? OR theme_slug IS NULL)
                   AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)
                 ORDER BY CASE WHEN title LIKE ? THEN 0 ELSE 1 END, created_at DESC
                 LIMIT 50"
            );
            $articleStmt->execute([$activeTheme, $like, $like, $like, $like]);
            $articles = $articleStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Merge and build results
            $allResults = [];
            foreach ($pages as $p) {
                $allResults[] = [
                    'title' => $p['title'],
                    'url' => '/page/' . $p['slug'],
                    'excerpt' => $this->buildExcerpt($p['meta_description'] ?: $p['content'], $query),
                    'type' => 'page',
                ];
            }
            foreach ($articles as $a) {
                $allResults[] = [
                    'title' => $a['title'],
                    'url' => '/article/' . $a['slug'],
                    'excerpt' => $this->buildExcerpt($a['excerpt'] ?: $a['content'], $query),
                    'type' => 'article',
                ];
            }

            $total = count($allResults);
            $results = array_slice($allResults, $offset, $perPage);
        }

        $totalPages = max(1, (int)ceil($total / $perPage));

        // Render through active theme layout
        $title = 'Search' . ($query ? ': ' . htmlspecialchars($query) : '');
        $description = $query ? "Search results for {$query}" : 'Search';
        $currentPage = $page;
        unset($page); // Prevent collision with layout.php's $page variable
        $layoutFile = theme_path('layout.php');

        // Build content from search view
        ob_start();
        require CMS_APP . '/views/front/search.php';
        $content = ob_get_clean();

        if (file_exists($layoutFile)) {
            ob_start();
            require $layoutFile;
            $output = ob_get_clean();
            if (function_exists('cms_inject_admin_toolbar')) {
                $output = cms_inject_admin_toolbar($output, ['type' => 'search']);
            }
            echo $output;
        } else {
            echo $content;
        }
        exit;
    }

    /**
     * Build a search result excerpt with highlighted query terms
     */
    private function buildExcerpt(string $text, string $query, int $maxLen = 200): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // Try to find the query in the text and center around it
        $pos = mb_stripos($text, $query);
        if ($pos !== false) {
            $start = max(0, $pos - 60);
            $excerpt = mb_substr($text, $start, $maxLen);
            if ($start > 0) $excerpt = '...' . $excerpt;
            if (mb_strlen($text) > $start + $maxLen) $excerpt .= '...';
        } else {
            $excerpt = mb_substr($text, 0, $maxLen);
            if (mb_strlen($text) > $maxLen) $excerpt .= '...';
        }

        // Highlight query terms
        $excerpt = htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8');
        $escaped = preg_quote(htmlspecialchars($query, ENT_QUOTES, 'UTF-8'), '/');
        $excerpt = preg_replace('/(' . $escaped . ')/i', '<mark>$1</mark>', $excerpt);

        return $excerpt;
    }
}
