<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;
use Core\Response;

class ArticlesController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $theme = get_active_theme();
        $page = max(1, (int)($request->get('page') ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;
        $search = trim($request->get('q') ?? '');
        $category = trim($request->get('category') ?? '');

        // Build WHERE clause
        $where = "a.status = 'published' AND (a.theme_slug = :theme OR a.theme_slug IS NULL)";
        $params = [':theme' => $theme];

        if ($search !== '') {
            $where .= " AND (a.title LIKE :q OR a.content LIKE :q OR a.excerpt LIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }
        if ($category !== '') {
            $where .= " AND c.slug = :cat";
            $params[':cat'] = $category;
        }

        // Count total
        $countSql = "SELECT COUNT(*) FROM articles a LEFT JOIN article_categories c ON a.category_id = c.id WHERE {$where}";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        // Get articles
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug 
                FROM articles a 
                LEFT JOIN article_categories c ON a.category_id = c.id 
                WHERE {$where}
                ORDER BY a.published_at DESC, a.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get categories for sidebar (theme-filtered)
        $catStmt = $pdo->prepare("
            SELECT c.*, COUNT(a.id) as article_count 
            FROM article_categories c 
            LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published' AND (a.theme_slug = ? OR a.theme_slug IS NULL)
            GROUP BY c.id 
            HAVING article_count > 0
            ORDER BY c.name
        ");
        $catStmt->execute([$theme]);
        $categories = $catStmt->fetchAll(\PDO::FETCH_ASSOC);

        render('front/articles', [
            'articles' => $articles,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
}
