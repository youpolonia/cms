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
        $page = max(1, (int)($request->get('page') ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        // Count total
        $countStmt = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'");
        $total = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        // Get articles
        $stmt = $pdo->prepare("
            SELECT a.*, c.name as category_name, c.slug as category_slug 
            FROM articles a 
            LEFT JOIN article_categories c ON a.category_id = c.id 
            WHERE a.status = 'published' 
            ORDER BY a.published_at DESC, a.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get categories for sidebar
        $catStmt = $pdo->query("
            SELECT c.*, COUNT(a.id) as article_count 
            FROM article_categories c 
            LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
            GROUP BY c.id 
            ORDER BY c.name
        ");
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
