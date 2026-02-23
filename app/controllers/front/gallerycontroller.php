<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class GalleryController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $theme = get_active_theme();

        // Build a virtual page object for the gallery
        $page = [
            'title' => 'Gallery',
            'content' => '',
            'slug' => 'gallery',
            'template' => 'gallery',
            'featured_image' => null,
            'meta_description' => 'Photo gallery',
        ];

        // Try to find a real gallery page in DB (theme-specific or generic)
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = 'gallery' AND status = 'published' AND (theme_slug = ? OR theme_slug IS NULL OR theme_slug = '') LIMIT 1");
        $stmt->execute([$theme]);
        $dbPage = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($dbPage) {
            $page = $dbPage;
            $page['template'] = 'gallery';
        }

        render('front/gallery', [
            'page' => $page,
            '_toolbar_context' => ['type' => 'gallery']
        ]);
    }
}
