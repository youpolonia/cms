<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Session;

class JtbApiController
{
    private function initPlugin(): void
    {
        $pluginPath = \CMS_ROOT . '/plugins/jessie-theme-builder';

        require_once $pluginPath . '/includes/class-jtb-element.php';
        require_once $pluginPath . '/includes/class-jtb-registry.php';
        require_once $pluginPath . '/includes/class-jtb-fields.php';
        require_once $pluginPath . '/includes/class-jtb-renderer.php';
        require_once $pluginPath . '/includes/class-jtb-settings.php';
        require_once $pluginPath . '/includes/class-jtb-builder.php';

        \JessieThemeBuilder\JTB_Registry::init();
        \JessieThemeBuilder\JTB_Fields::init();

        $modulesPath = $pluginPath . '/modules';
        foreach (glob($modulesPath . '/*/*.php') as $moduleFile) {
            require_once $moduleFile;
        }
    }

    private function json($data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function modules(): void
    {
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            $this->json(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $this->initPlugin();

        $modules = [];
        foreach (\JessieThemeBuilder\JTB_Registry::getInstances() as $slug => $module) {
            $modules[$slug] = [
                'slug' => $slug,
                'name' => $module->getName(),
                'icon' => $module->icon,
                'category' => $module->category,
                'is_child' => $module->is_child ?? false,
                'child_slug' => $module->child_slug ?? '',
                'fields' => [
                    'content' => $module->getContentFields(),
                    'design' => $module->getDesignFields(),
                    'advanced' => $module->getAdvancedFields()
                ]
            ];
        }

        $this->json([
            'success' => true,
            'data' => [
                'modules' => $modules,
                'categories' => \JessieThemeBuilder\JTB_Registry::getCategories(),
                'count' => count($modules)
            ]
        ]);
    }

    public function load(int $id): void
    {
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            $this->json(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $this->initPlugin();

        $db = \core\Database::connection();
        $stmt = $db->prepare('SELECT id, title FROM pages WHERE id = ?');
        $stmt->execute([$id]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$post) {
            http_response_code(404);
            $this->json(['success' => false, 'error' => 'Post not found']);
            return;
        }

        $content = \JessieThemeBuilder\JTB_Builder::getContent($id);

        $stmt = $db->prepare('SELECT css_cache FROM jtb_pages WHERE post_id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $cssCache = $row ? $row['css_cache'] : '';

        $this->json([
            'success' => true,
            'data' => [
                'post_id' => $id,
                'post_title' => $post['title'],
                'content' => $content ?: \JessieThemeBuilder\JTB_Builder::getEmptyContent(),
                'css_cache' => $cssCache,
                'has_content' => $content !== null
            ]
        ]);
    }

    public function save(): void
    {
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            $this->json(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $this->initPlugin();

        $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        $content = $_POST['content'] ?? '';

        if (!$postId || $postId < 1) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'Invalid post ID']);
            return;
        }

        if (empty($content)) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'Content is required']);
            return;
        }

        $result = \JessieThemeBuilder\JTB_Builder::saveContent($postId, $content);

        if ($result) {
            $this->json(['success' => true, 'message' => 'Content saved successfully']);
        } else {
            http_response_code(500);
            $this->json(['success' => false, 'error' => 'Failed to save content']);
        }
    }

    public function render(): void
    {
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            $this->json(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $this->initPlugin();

        $content = $_POST['content'] ?? '';

        if (empty($content)) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'Content is required']);
            return;
        }

        $decoded = is_string($content) ? json_decode($content, true) : $content;
        if (!$decoded) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'Invalid JSON content']);
            return;
        }

        $html = \JessieThemeBuilder\JTB_Renderer::render($decoded);
        $css = \JessieThemeBuilder\JTB_Renderer::getCss();

        $this->json([
            'success' => true,
            'data' => ['html' => $html, 'css' => $css]
        ]);
    }

    public function upload(): void
    {
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            $this->json(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'Upload error']);
            return;
        }

        $file = $_FILES['file'];
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!isset($allowedTypes[$mimeType])) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'File type not allowed']);
            return;
        }

        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'File too large']);
            return;
        }

        $extension = $allowedTypes[$mimeType];
        $filename = uniqid('jtb_') . '_' . time() . '.' . $extension;
        $uploadDir = \CMS_ROOT . '/uploads/jtb/' . date('Y/m');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetPath = $uploadDir . '/' . $filename;
        $publicUrl = '/uploads/jtb/' . date('Y/m') . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            http_response_code(500);
            $this->json(['success' => false, 'error' => 'Failed to save file']);
            return;
        }

        $imageInfo = getimagesize($targetPath);

        $this->json([
            'success' => true,
            'data' => [
                'url' => $publicUrl,
                'filename' => $filename,
                'size' => $file['size'],
                'type' => $mimeType,
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null
            ]
        ]);
    }
}
