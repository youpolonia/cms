<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;
use Core\Response;

class PageController
{
    public function show(Request $request): void
    {
        $slug = $request->param('slug');
        $pdo = db();
        
        $isPreview = isset($_GET['preview']) && $_GET['preview'] === '1';
        $isAdmin = $this->isAdminLoggedIn();
        
        // 1. Check for Theme Builder preview mode (from admin editor)
        $tbPreviewId = isset($_GET['tb_preview']) ? (int)$_GET['tb_preview'] : 0;
        if ($tbPreviewId > 0 && $isAdmin && $isPreview) {
            $this->renderTbPreview($pdo, $tbPreviewId);
            return;
        }
        
        // 2. Legacy TB pages removed — JTB uses templates system via index.php routing
        
        // 3. Fallback to regular pages table
        $pagesStatusCondition = ($isPreview && $isAdmin) ? '' : "AND status = 'published'";
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? $pagesStatusCondition LIMIT 1");
        $stmt->execute([$slug]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$page) {
            render('front/404', []);
            return;
        }
        
        // Get template from page
        $template = $page['template'] ?? 'default';
        
        // Check if template exists in active theme (for custom theme pages)
        $themeTemplates = ['services', 'projects', 'about', 'contact', 'home'];
        if (in_array($template, $themeTemplates)) {
            require_once CMS_ROOT . '/includes/thememanager.php';
            $output = \ThemeManager::render_theme_view_public($template, [
                'page' => $page,
                'title' => $page['title'] ?? '',
                'description' => $page['meta_description'] ?? ''
            ]);
            // Inject admin toolbar if logged in
            if (function_exists('cms_inject_admin_toolbar')) {
                $output = cms_inject_admin_toolbar($output, [
                    'page_id' => $page['id'] ?? null,
                    'page_title' => $page['title'] ?? '',
                    'type' => 'page'
                ]);
            }
            echo $output;
            return;
        }
        
        $templateViews = [
            'default' => 'front/page',
            'full-width' => 'front/page-full-width',
            'sidebar-left' => 'front/page-sidebar-left',
            'sidebar-right' => 'front/page-sidebar-right',
            'landing' => 'front/page-landing',
            'contact' => 'front/page-contact',
            'blank' => 'front/page-blank',
            'gallery' => 'front/gallery'
        ];
        
        $viewFile = $templateViews[$template] ?? 'front/page';
        // Check if view exists in app/views/front/ OR in theme templates
        $viewPath = CMS_APP . '/views/' . $viewFile . '.php';
        $themeViewPath = theme_path('templates/' . str_replace('front/', '', $viewFile) . '.php');
        if (!file_exists($viewPath) && !file_exists($themeViewPath)) {
            $viewFile = 'front/page';
        }

        render($viewFile, [
            'page' => $page, 
            'template' => $template, 
            'isPreview' => $isPreview,
            '_toolbar_context' => ['page_id' => $page['id'] ?? null, 'page_title' => $page['title'] ?? '', 'type' => 'page']
        ]);
    }
    
    /**
     * Legacy TB preview removed — JTB has its own preview system
     */
    private function renderTbPreview(\PDO $pdo, int $tbPreviewId): void
    {
        render('front/404', []);
    }
    
    private function isAdminLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (isset($_COOKIE['CMSSESSID_ADMIN'])) {
                session_name('CMSSESSID_ADMIN');
            }
            session_start();
        }
        return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_role']);
    }
}
