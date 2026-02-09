<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class HomeController
{
    public function index(Request $request): void
    {
        $pdo = db();
        
        // Legacy TB homepage check removed — JTB uses templates system
        
        // Static homepage
        // Get published pages
        $stmt = $pdo->query("SELECT * FROM pages WHERE status = 'published' ORDER BY created_at DESC LIMIT 10");
        $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get published articles with category
        $stmt = $pdo->query("
            SELECT a.*, c.name as category_name, c.slug as category_slug 
            FROM articles a 
            LEFT JOIN article_categories c ON a.category_id = c.id 
            WHERE a.status = 'published' 
            ORDER BY a.published_at DESC, a.created_at DESC 
            LIMIT 6
        ");
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('front/home', ['pages' => $pages, 'articles' => $articles]);
    }
}
