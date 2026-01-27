<?php
declare(strict_types=1);
/**
 * Layout Library Controller
 * Browse, preview, and import layout templates
 *
 * Supports Divi-style full-site presets with header/footer
 */

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 3));
}

class LayoutLibraryController
{
    private \PDO $db;

    public function __construct()
    {
        require_once CMS_ROOT . '/core/theme-builder/init.php';
        tb_init();
        $this->db = \core\Database::connection();
    }

    /**
     * Browse layout library
     */
    public function index(Request $request): void
    {
        $category = $_GET['category'] ?? '';
        $industry = $_GET['industry'] ?? '';
        $style = $_GET['style'] ?? '';
        $search = $_GET['search'] ?? '';

        $where = [];
        $params = [];

        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }
        if ($industry) {
            $where[] = "industry = ?";
            $params[] = $industry;
        }
        if ($style) {
            $where[] = "style = ?";
            $params[] = $style;
        }
        if ($search) {
            $where[] = "(name LIKE ? OR description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT id, name, slug, description, category, industry, style, page_count,
                       thumbnail, is_premium, is_ai_generated, downloads, rating, created_at
                FROM tb_layout_library
                {$whereClause}
                ORDER BY downloads DESC, created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $layouts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $categories = $this->db->query("SELECT DISTINCT category FROM tb_layout_library ORDER BY category")->fetchAll(\PDO::FETCH_COLUMN);
        $industries = $this->db->query("SELECT DISTINCT industry FROM tb_layout_library WHERE industry IS NOT NULL ORDER BY industry")->fetchAll(\PDO::FETCH_COLUMN);
        $styles = $this->db->query("SELECT DISTINCT style FROM tb_layout_library WHERE style IS NOT NULL ORDER BY style")->fetchAll(\PDO::FETCH_COLUMN);

        render('admin/layout-library/index', [
            'layouts' => $layouts,
            'categories' => $categories,
            'industries' => $industries,
            'styles' => $styles,
            'filters' => [
                'category' => $category,
                'industry' => $industry,
                'style' => $style,
                'search' => $search
            ],
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * AJAX: Upload and install layout from JSON file
     *
     * Supports full-site presets with optional header/footer:
     * {
     *   "name": "...",
     *   "pages": [...],
     *   "header": {"sections": [...]},  // optional
     *   "footer": {"sections": [...]}   // optional
     * }
     */
    public function upload(Request $request): void
    {
        header('Content-Type: application/json');

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!csrf_validate($token)) {
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            return;
        }

        if (!isset($_FILES['layout_file']) || $_FILES['layout_file']['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
                UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            ];
            $error = $errorMessages[$_FILES['layout_file']['error'] ?? 0] ?? 'Unknown upload error';
            echo json_encode(['success' => false, 'error' => $error]);
            return;
        }

        $file = $_FILES['layout_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'json') {
            echo json_encode(['success' => false, 'error' => 'Only .json files are allowed']);
            return;
        }

        $content = file_get_contents($file['tmp_name']);
        $layoutData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]);
            return;
        }

        $required = ['name', 'pages'];
        foreach ($required as $field) {
            if (empty($layoutData[$field])) {
                echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
                return;
            }
        }

        if (!is_array($layoutData['pages']) || empty($layoutData['pages'])) {
            echo json_encode(['success' => false, 'error' => 'Layout must contain at least one page']);
            return;
        }

        foreach ($layoutData['pages'] as $index => $page) {
            if (empty($page['title'])) {
                echo json_encode(['success' => false, 'error' => "Page {$index} missing title"]);
                return;
            }
            if (!isset($page['content']['sections'])) {
                echo json_encode(['success' => false, 'error' => "Page '{$page['title']}' missing sections"]);
                return;
            }
        }

        $slug = $layoutData['slug'] ?? '';
        if (!$slug) {
            $slug = mb_strtolower($layoutData['name'], 'UTF-8');
            $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
            $slug = preg_replace('/[\s_]+/', '-', $slug);
            $slug = trim($slug, '-');
        }

        $stmt = $this->db->prepare("SELECT id FROM tb_layout_library WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $baseSlug = $slug;
            $counter = 1;
            while (true) {
                $slug = "{$baseSlug}-{$counter}";
                $stmt->execute([$slug]);
                if (!$stmt->fetch()) break;
                $counter++;
            }
        }

        try {
            // Build content structure with pages + optional header/footer (Divi-style full-site)
            $contentStructure = ['pages' => $layoutData['pages']];

            // Store header if provided (for full-site presets)
            if (!empty($layoutData['header']) && is_array($layoutData['header'])) {
                if (isset($layoutData['header']['sections'])) {
                    $contentStructure['header'] = $layoutData['header'];
                }
            }

            // Store footer if provided (for full-site presets)
            if (!empty($layoutData['footer']) && is_array($layoutData['footer'])) {
                if (isset($layoutData['footer']['sections'])) {
                    $contentStructure['footer'] = $layoutData['footer'];
                }
            }

            $contentJson = json_encode($contentStructure, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $stmt = $this->db->prepare("
                INSERT INTO tb_layout_library 
                (name, slug, description, category, industry, style, page_count, content_json, thumbnail, is_premium, is_ai_generated, downloads, rating, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 5.0, NOW())
            ");

            $stmt->execute([
                $layoutData['name'],
                $slug,
                $layoutData['description'] ?? '',
                $layoutData['category'] ?? 'other',
                $layoutData['industry'] ?? null,
                $layoutData['style'] ?? null,
                count($layoutData['pages']),
                $contentJson,
                $layoutData['thumbnail'] ?? null,
                (int)($layoutData['is_premium'] ?? 0),
                0
            ]);

            $layoutId = (int)$this->db->lastInsertId();

            echo json_encode([
                'success' => true,
                'layout_id' => $layoutId,
                'name' => $layoutData['name'],
                'slug' => $slug,
                'page_count' => count($layoutData['pages']),
                'has_header' => !empty($layoutData['header']),
                'has_footer' => !empty($layoutData['footer']),
                'message' => "Layout '{$layoutData['name']}' installed successfully with " . count($layoutData['pages']) . " pages" . (!empty($layoutData['header']) ? ' + header' : '') . (!empty($layoutData['footer']) ? ' + footer' : '')
            ]);

        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Get layout details for preview
     * Returns header/footer info for full-site presets
     */
    public function preview(Request $request): void
    {
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Invalid layout ID']);
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM tb_layout_library WHERE id = ?");
        $stmt->execute([$id]);
        $layout = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$layout) {
            echo json_encode(['success' => false, 'error' => 'Layout not found']);
            return;
        }

        $contentData = json_decode($layout['content_json'], true);

        $previewHtml = '';
        if (!empty($contentData['pages'][0]['content'])) {
            $previewHtml = tb_render_page($contentData['pages'][0]['content'], ['preview_mode' => true]);
        }

        echo json_encode([
            'success' => true,
            'layout' => [
                'id' => $layout['id'],
                'name' => $layout['name'],
                'description' => $layout['description'],
                'category' => $layout['category'],
                'industry' => $layout['industry'],
                'style' => $layout['style'],
                'page_count' => $layout['page_count'],
                'is_premium' => (bool)$layout['is_premium'],
                'downloads' => $layout['downloads'],
                'has_header' => !empty($contentData['header']),
                'has_footer' => !empty($contentData['footer'])
            ],
            'pages' => $contentData['pages'] ?? [],
            'header' => $contentData['header'] ?? null,
            'footer' => $contentData['footer'] ?? null,
            'preview_html' => $previewHtml
        ]);
    }

    /**
     * AJAX: Import layout to tb_pages
     * Creates tb_site_templates for header/footer if present in layout
     */
    public function import(Request $request): void
    {
        header('Content-Type: application/json');

        $input = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);

        $layoutId = (int)($input['layout_id'] ?? 0);
        $pageIndices = $input['page_indices'] ?? [];

        if (!$layoutId) {
            echo json_encode(['success' => false, 'error' => 'Invalid layout ID']);
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM tb_layout_library WHERE id = ?");
        $stmt->execute([$layoutId]);
        $layout = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$layout) {
            echo json_encode(['success' => false, 'error' => 'Layout not found']);
            return;
        }

        $contentData = json_decode($layout['content_json'], true);
        $pages = $contentData['pages'] ?? [];
        $headerData = $contentData['header'] ?? null;
        $footerData = $contentData['footer'] ?? null;

        if (empty($pages)) {
            echo json_encode(['success' => false, 'error' => 'Layout has no pages']);
            return;
        }

        if (!empty($pageIndices)) {
            $pages = array_filter($pages, function($key) use ($pageIndices) {
                return in_array($key, $pageIndices);
            }, ARRAY_FILTER_USE_KEY);
        }

        $userId = $_SESSION['admin_user_id'] ?? null;
        $createdPages = [];
        $updatedPages = [];

        try {
            $this->db->beginTransaction();

            foreach ($pages as $page) {
                $title = $page['title'] ?? 'Imported Page';
                // Use original slug first to check for existing page
                $originalSlug = $page['slug'] ?? '';
                if (!$originalSlug) {
                    $originalSlug = mb_strtolower($title, 'UTF-8');
                    $originalSlug = preg_replace('/[^a-z0-9\s-]/', '', $originalSlug);
                    $originalSlug = preg_replace('/[\s_]+/', '-', $originalSlug);
                    $originalSlug = trim($originalSlug, '-') ?: 'page';
                }
                $content = $page['content'] ?? ['sections' => []];
                $isHomepage = !empty($page['is_homepage']) ? 1 : 0;

                // Check if page with this slug already exists
                $stmt = $this->db->prepare("SELECT id FROM tb_pages WHERE slug = ?");
                $stmt->execute([$originalSlug]);
                $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                // Only generate unique slug if creating new page
                $slug = $existing ? $originalSlug : $this->generateSlug($originalSlug);

                if ($existing) {
                    $tbPageId = (int)$existing['id'];
                    $stmt = $this->db->prepare("UPDATE tb_pages SET content_json = ?, title = ?, updated_by = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([json_encode($content, JSON_UNESCAPED_UNICODE), $title, $userId, $tbPageId]);

                    $updatedPages[] = ['id' => $tbPageId, 'title' => $title, 'slug' => $slug, 'edit_url' => '/admin/theme-builder/' . $tbPageId . '/edit', 'action' => 'updated'];
                } else {
                    if ($isHomepage) {
                        $this->db->exec("UPDATE tb_pages SET is_homepage = 0");
                    }

                    $stmt = $this->db->prepare("INSERT INTO tb_pages (title, slug, content_json, status, is_homepage, created_by, updated_by) VALUES (?, ?, ?, 'draft', ?, ?, ?)");
                    $stmt->execute([$title, $slug, json_encode($content, JSON_UNESCAPED_UNICODE), $isHomepage, $userId, $userId]);

                    $tbPageId = (int)$this->db->lastInsertId();

                    if (function_exists('tb_create_revision')) {
                        tb_create_revision($tbPageId, $content, $userId);
                    }

                    $createdPages[] = ['id' => $tbPageId, 'title' => $title, 'slug' => $slug, 'edit_url' => '/admin/theme-builder/' . $tbPageId . '/edit', 'action' => 'created'];
                }
            }

            $this->db->prepare("UPDATE tb_layout_library SET downloads = downloads + 1 WHERE id = ?")->execute([$layoutId]);
            $this->db->commit();

            $allPages = array_merge($createdPages, $updatedPages);

            // Create site templates if header/footer provided
            $createdTemplates = [];

            if ($headerData && !empty($headerData['sections'])) {
                // Get slugs of all imported pages for conditions
                $importedSlugs = array_map(function($p) { return $p['slug']; }, $allPages);

                $templateId = tb_save_template([
                    'type' => 'header',
                    'name' => $layout['name'] . ' Header',
                    'content' => $headerData,
                    'conditions' => ['type' => 'specific', 'pages' => $importedSlugs],
                    'priority' => 10,
                    'is_active' => 1,
                    'created_by' => $userId,
                    'updated_by' => $userId
                ]);
                $createdTemplates[] = ['type' => 'header', 'id' => $templateId, 'name' => $layout['name'] . ' Header'];
            }

            if ($footerData && !empty($footerData['sections'])) {
                $importedSlugs = array_map(function($p) { return $p['slug']; }, $allPages);

                $templateId = tb_save_template([
                    'type' => 'footer',
                    'name' => $layout['name'] . ' Footer',
                    'content' => $footerData,
                    'conditions' => ['type' => 'specific', 'pages' => $importedSlugs],
                    'priority' => 10,
                    'is_active' => 1,
                    'created_by' => $userId,
                    'updated_by' => $userId
                ]);
                $createdTemplates[] = ['type' => 'footer', 'id' => $templateId, 'name' => $layout['name'] . ' Footer'];
            }

            $message = count($createdPages) . ' pages created, ' . count($updatedPages) . ' updated';
            if (!empty($createdTemplates)) {
                $message .= ', ' . count($createdTemplates) . ' site templates created';
            }

            echo json_encode([
                'success' => true,
                'pages' => $allPages,
                'templates' => $createdTemplates,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'error' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Delete layout from library
     */
    public function delete(Request $request): void
    {
        header('Content-Type: application/json');

        $input = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);

        $token = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!csrf_validate($token)) {
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            return;
        }

        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Invalid layout ID']);
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM tb_layout_library WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Layout deleted']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Delete failed: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Get sections from a layout page
     */
    public function getSections(Request $request): void
    {
        header('Content-Type: application/json');

        $layoutId = (int)($_GET['id'] ?? 0);
        $pageIndex = (int)($_GET['page_index'] ?? 0);

        if (!$layoutId) {
            echo json_encode(['success' => false, 'error' => 'Invalid layout ID']);
            return;
        }

        $stmt = $this->db->prepare("SELECT content_json, name FROM tb_layout_library WHERE id = ?");
        $stmt->execute([$layoutId]);
        $layout = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$layout) {
            echo json_encode(['success' => false, 'error' => 'Layout not found']);
            return;
        }

        $content = json_decode($layout['content_json'], true);
        $pages = $content['pages'] ?? [];

        if (!isset($pages[$pageIndex])) {
            echo json_encode(['success' => false, 'error' => 'Page index not found']);
            return;
        }

        $pageData = $pages[$pageIndex];
        $sections = $pageData['content']['sections'] ?? [];
        $sections = $this->regenerateSectionIds($sections);

        echo json_encode([
            'success' => true,
            'sections' => $sections,
            'page_title' => $pageData['title'] ?? 'Untitled',
            'layout_name' => $layout['name'],
            'section_count' => count($sections)
        ]);
    }

    /**
     * AJAX: List all layouts
     */
    public function list(Request $request): void
    {
        header('Content-Type: application/json');

        $category = $_GET['category'] ?? '';
        $search = $_GET['search'] ?? '';

        $where = [];
        $params = [];

        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }
        if ($search) {
            $where[] = "(name LIKE ? OR description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT id, name, slug, description, category, industry, style, page_count, thumbnail, is_ai_generated, downloads, created_at
                FROM tb_layout_library {$whereClause} ORDER BY downloads DESC, created_at DESC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $layouts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'layouts' => $layouts, 'count' => count($layouts)]);
    }

    private function regenerateSectionIds(array $sections): array
    {
        foreach ($sections as &$section) {
            $section['id'] = 'section_' . uniqid();
            if (isset($section['rows'])) {
                foreach ($section['rows'] as &$row) {
                    $row['id'] = 'row_' . uniqid();
                    if (isset($row['columns'])) {
                        foreach ($row['columns'] as &$col) {
                            $col['id'] = 'col_' . uniqid();
                            if (isset($col['modules'])) {
                                foreach ($col['modules'] as &$mod) {
                                    $mod['id'] = 'mod_' . uniqid();
                                }
                            }
                        }
                    }
                }
            }
        }
        return $sections;
    }

    private function generateSlug(string $title): string
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        $baseSlug = $slug ?: 'page';
        $counter = 0;

        while (true) {
            $testSlug = $counter ? "{$baseSlug}-{$counter}" : $baseSlug;
            $stmt = $this->db->prepare("SELECT id FROM tb_pages WHERE slug = ?");
            $stmt->execute([$testSlug]);
            if (!$stmt->fetch()) {
                return $testSlug;
            }
            $counter++;
        }
    }
}
