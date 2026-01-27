<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class HomeController
{
    public function index(Request $request): void
    {
        $pdo = db();
        
        // Check for Theme Builder homepage first
        $stmt = $pdo->query("SELECT id, title, slug, content_json, status FROM tb_pages WHERE is_homepage = 1 AND status = 'published' LIMIT 1");
        $tbHomepage = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($tbHomepage && !empty($tbHomepage['content_json'])) {
            $tbData = json_decode($tbHomepage['content_json'], true);
            if ($tbData && is_array($tbData)) {
                require_once CMS_ROOT . '/core/theme-builder/renderer.php';
                $renderedContent = tb_render_page($tbData, ['preview_mode' => false]);
                
                // Create page array - mark as TB page to avoid article wrapper
                $page = [
                    'id' => $tbHomepage['id'],
                    'title' => $tbHomepage['title'],
                    'slug' => $tbHomepage['slug'],
                    'content' => $renderedContent,
                    'status' => $tbHomepage['status'],
                    'is_tb_page' => true
                ];
                
                render('front/page', ['page' => $page, 'isPreview' => false]);
                return;
            }
        }
        
        // Fallback to static homepage
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
