<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;
use Core\Response;

class ArticleController
{
    public function show(Request $request): void
    {
        $slug = $request->param('slug');
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT a.*, c.name as category_name, c.slug as category_slug 
            FROM articles a 
            LEFT JOIN article_categories c ON a.category_id = c.id 
            WHERE a.slug = ? AND a.status = 'published'
        ");
        $stmt->execute([$slug]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$article) {
            render('front/404', []);
            return;
        }

        // Increment views
        $pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$article['id']]);

        render('front/article', ['article' => $article]);
    }
}
