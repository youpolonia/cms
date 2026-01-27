<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class SearchController
{
    public function index(Request $request): void
    {
        $query = trim($request->get('q', ''));
        $type = $request->get('type', 'all');
        $results = [];
        $totalCount = 0;

        if (!empty($query) && strlen($query) >= 2) {
            $results = $this->performSearch($query, $type);
            $totalCount = array_sum(array_map('count', $results));

            // Log the search
            $this->logSearch($query, $totalCount);
        }

        render('admin/search/index', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'totalCount' => $totalCount
        ]);
    }

    public function analytics(Request $request): void
    {
        $pdo = db();

        // Top searches
        $stmt = $pdo->query("
            SELECT query, COUNT(*) as count, AVG(results_count) as avg_results
            FROM search_logs
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY query
            ORDER BY count DESC
            LIMIT 20
        ");
        $topSearches = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Searches with no results
        $stmt = $pdo->query("
            SELECT query, COUNT(*) as count
            FROM search_logs
            WHERE results_count = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY query
            ORDER BY count DESC
            LIMIT 20
        ");
        $noResults = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Daily stats
        $stmt = $pdo->query("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM search_logs
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 14 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        $dailyStats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/search/analytics', [
            'topSearches' => $topSearches,
            'noResults' => $noResults,
            'dailyStats' => $dailyStats,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function clearLogs(Request $request): void
    {
        $pdo = db();
        $pdo->exec("TRUNCATE TABLE search_logs");

        Session::flash('success', 'Search logs cleared.');
        Response::redirect('/admin/search/analytics');
    }

    private function performSearch(string $query, string $type): array
    {
        $pdo = db();
        $results = [];
        $searchTerm = '%' . $query . '%';

        // Search pages
        if ($type === 'all' || $type === 'pages') {
            $stmt = $pdo->prepare("SELECT id, title, slug, status, 'page' as entity_type FROM pages WHERE title LIKE ? OR content LIKE ? LIMIT 20");
            $stmt->execute([$searchTerm, $searchTerm]);
            $results['pages'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Search articles
        if ($type === 'all' || $type === 'articles') {
            $stmt = $pdo->prepare("SELECT id, title, slug, status, 'article' as entity_type FROM articles WHERE title LIKE ? OR content LIKE ? OR excerpt LIKE ? LIMIT 20");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            $results['articles'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Search categories
        if ($type === 'all' || $type === 'categories') {
            $stmt = $pdo->prepare("SELECT id, name, slug, 'category' as entity_type FROM categories WHERE name LIKE ? OR description LIKE ? LIMIT 20");
            $stmt->execute([$searchTerm, $searchTerm]);
            $results['categories'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Search users
        if ($type === 'all' || $type === 'users') {
            $stmt = $pdo->prepare("SELECT id, username, email, role, 'user' as entity_type FROM admins WHERE username LIKE ? OR email LIKE ? LIMIT 20");
            $stmt->execute([$searchTerm, $searchTerm]);
            $results['users'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Search content blocks
        if ($type === 'all' || $type === 'content') {
            $stmt = $pdo->prepare("SELECT id, name, slug, type, 'content_block' as entity_type FROM content_blocks WHERE name LIKE ? OR content LIKE ? LIMIT 20");
            $stmt->execute([$searchTerm, $searchTerm]);
            $results['content'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Search menus
        if ($type === 'all' || $type === 'menus') {
            $stmt = $pdo->prepare("SELECT id, name, slug, location, 'menu' as entity_type FROM menus WHERE name LIKE ? OR description LIKE ? LIMIT 20");
            $stmt->execute([$searchTerm, $searchTerm]);
            $results['menus'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $results;
    }

    private function logSearch(string $query, int $resultsCount): void
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("INSERT INTO search_logs (query, results_count, ip_address, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                substr($query, 0, 255),
                $resultsCount,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (\Exception $e) {
            // Silently fail - logging should not break search
        }
    }
}
