<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class ThemeBuilderPreviewController
{
    public function show(Request $request): void
    {
        $id = (int) $request->param('id');
        $useSession = isset($_GET['session']) && $_GET['session'] === '1';
        $pdo = db();
        
        // Check if admin is logged in for preview access
        if (!$this->isAdminLoggedIn()) {
            http_response_code(403);
            echo 'Access denied - admin login required';
            return;
        }
        
        $content = null;
        $pageTitle = 'Preview';
        
        // Try session first if requested (for unsaved changes)
        if ($useSession) {
            $sessionKey = 'tb_preview_' . $id;
            if (isset($_SESSION[$sessionKey]) && is_array($_SESSION[$sessionKey])) {
                $previewData = $_SESSION[$sessionKey];
                $content = $previewData['content'] ?? null;
            }
        }
        
        // Load from database
        $stmt = $pdo->prepare("SELECT title, content_json, status FROM tb_pages WHERE id = ?");
        $stmt->execute([$id]);
        $tbPage = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$tbPage) {
            http_response_code(404);
            echo 'Theme Builder page not found';
            return;
        }
        
        $pageTitle = $tbPage['title'] ?? 'Preview';
        
        // Use DB content if no session content
        if (!$content) {
            $content = json_decode($tbPage['content_json'], true);
        }
        
        if (!$content) {
            http_response_code(404);
            echo 'No content found';
            return;
        }
        
        // Render Theme Builder content
        require_once CMS_ROOT . '/core/theme-builder/renderer.php';
        $html = tb_render_page($content, ['preview_mode' => true]);
        
        // Render directly without theme wrapper
        $viewPath = CMS_APP . '/views/front/tb-preview.php';
        $pageTitle = $pageTitle;
        $pageContent = $html;
        $isPreview = true;
        require $viewPath;
        exit;
    }
    


    public function showNewTemplate(Request $request): void
    {
        if (!$this->isAdminLoggedIn()) {
            http_response_code(403);
            echo "Access denied - admin login required";
            return;
        }
        
        $sessionKey = "tb_template_preview_new";
        if (!isset($_SESSION[$sessionKey]) || !is_array($_SESSION[$sessionKey])) {
            http_response_code(404);
            echo "No preview data found. Please try again.";
            return;
        }
        
        $previewData = $_SESSION[$sessionKey];
        $content = $previewData["content"] ?? null;
        $templateType = $previewData["template_type"] ?? "";
        
        if (!$content) {
            http_response_code(404);
            echo "No content found";
            return;
        }
        
        require_once CMS_ROOT . "/core/theme-builder/renderer.php";
        $html = tb_render_page($content, ["preview_mode" => true]);
        
        $viewPath = CMS_APP . "/views/front/tb-preview.php";
        $pageTitle = "New " . ucfirst($templateType) . " Template";
        $pageContent = $html;
        $isPreview = true;
        require $viewPath;
        exit;
    }

    public function showTemplate(Request $request): void
    {
        $id = (int) $request->param('id');
        $useSession = isset($_GET['session']) && $_GET['session'] === '1';
        $pdo = db();
        
        if (!$this->isAdminLoggedIn()) {
            http_response_code(403);
            echo 'Access denied - admin login required';
            return;
        }
        
        $content = null;
        $templateName = 'Template Preview';
        $templateType = '';
        
        if ($useSession) {
            $sessionKey = 'tb_template_preview_' . $id;
            if (isset($_SESSION[$sessionKey]) && is_array($_SESSION[$sessionKey])) {
                $previewData = $_SESSION[$sessionKey];
                $content = $previewData['content'] ?? null;
                $templateType = $previewData['template_type'] ?? '';
            }
        }
        
        $stmt = $pdo->prepare("SELECT name, type, content_json FROM tb_templates WHERE id = ?");
        $stmt->execute([$id]);
        $template = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$template) {
            http_response_code(404);
            echo 'Template not found';
            return;
        }
        
        $templateName = $template['name'] ?? 'Template Preview';
        if (empty($templateType)) {
            $templateType = $template['type'] ?? '';
        }
        
        if (!$content) {
            $content = json_decode($template['content_json'], true);
        }
        
        if (!$content) {
            http_response_code(404);
            echo 'No content found';
            return;
        }
        
        require_once CMS_ROOT . '/core/theme-builder/renderer.php';
        $html = tb_render_page($content, ['preview_mode' => true]);
        
        $viewPath = CMS_APP . '/views/front/tb-preview.php';
        $pageTitle = $templateName . ' (' . ucfirst($templateType) . ')';
        $pageContent = $html;
        $isPreview = true;
        require $viewPath;
        exit;
    }

    /**
     * Show AI Theme Builder preview from session data
     */
    public function showAiThemePreview(Request $request): void
    {
        if (!$this->isAdminLoggedIn()) {
            http_response_code(403);
            echo 'Access denied - admin login required';
            return;
        }
        
        $sessionKey = $_GET['key'] ?? '';
        if (empty($sessionKey) || !isset($_SESSION[$sessionKey])) {
            http_response_code(404);
            echo 'Preview data not found or expired. Please regenerate the preview.';
            return;
        }
        
        $previewData = $_SESSION[$sessionKey];
        $content = $previewData['content'] ?? null;
        $pageTitle = $previewData['title'] ?? 'AI Theme Preview';
        
        if (!$content) {
            http_response_code(404);
            echo 'No content found';
            return;
        }
        
        require_once CMS_ROOT . '/core/theme-builder/renderer.php';
        $html = tb_render_page($content, ['preview_mode' => true]);
        
        $viewPath = CMS_APP . '/views/front/tb-preview.php';
        $pageContent = $html;
        $isPreview = true;
        require $viewPath;
        exit;
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
