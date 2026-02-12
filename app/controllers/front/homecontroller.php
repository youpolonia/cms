<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class HomeController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $theme = get_active_theme();
        
        // Get published pages for this theme (theme_slug match OR NULL = shared)
        $stmt = $pdo->prepare("
            SELECT * FROM pages 
            WHERE status = 'published' AND (theme_slug = :theme OR theme_slug IS NULL)
            ORDER BY created_at DESC LIMIT 10
        ");
        $stmt->execute([':theme' => $theme]);
        $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get published articles for this theme (theme_slug match OR NULL = shared)
        $stmt = $pdo->prepare("
            SELECT a.*, c.name as category_name, c.slug as category_slug 
            FROM articles a 
            LEFT JOIN article_categories c ON a.category_id = c.id 
            WHERE a.status = 'published' AND (a.theme_slug = :theme OR a.theme_slug IS NULL)
            ORDER BY a.published_at DESC, a.created_at DESC 
            LIMIT 6
        ");
        $stmt->execute([':theme' => $theme]);
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('front/home', [
            'pages' => $pages, 
            'articles' => $articles,
            '_toolbar_context' => ['type' => 'home']
        ]);
    }
}
