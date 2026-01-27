<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class WidgetsController
{
    private array $areas = [
        'sidebar' => 'Sidebar',
        'footer_1' => 'Footer Column 1',
        'footer_2' => 'Footer Column 2',
        'footer_3' => 'Footer Column 3',
        'header' => 'Header',
        'after_content' => 'After Content'
    ];

    private array $types = [
        'html' => 'HTML Content',
        'text' => 'Plain Text',
        'menu' => 'Navigation Menu',
        'recent_posts' => 'Recent Posts',
        'categories' => 'Categories List',
        'search' => 'Search Box',
        'social' => 'Social Links',
        'custom' => 'Custom Code'
    ];

    private array $visibilities = [
        'all' => 'Everyone',
        'logged_in' => 'Logged In Users',
        'logged_out' => 'Guests Only',
        'admin' => 'Admins Only'
    ];

    private ?bool $hasNewColumns = null;

    /**
     * Check if new columns exist (memoized)
     */
    private function columnExists(\PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Check for new columns presence (cached)
     */
    private function hasNewColumns(\PDO $pdo): bool
    {
        if ($this->hasNewColumns === null) {
            $this->hasNewColumns = $this->columnExists($pdo, 'widgets', 'icon');
        }
        return $this->hasNewColumns;
    }

    private function getAvailableMenus(\PDO $pdo): array
    {
        try {
            $stmt = $pdo->query("SELECT id, name, location FROM menus WHERE is_active = 1 ORDER BY name ASC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function isAiConfigured(): bool
    {
        $configPath = CMS_ROOT . '/config/ai_settings.json';
        if (!file_exists($configPath)) {
            return false;
        }
        $config = json_decode(file_get_contents($configPath), true);
        if (!is_array($config)) {
            return false;
        }
        // Check providers.openai.api_key
        if (!empty($config['providers']['openai']['api_key']) && !empty($config['providers']['openai']['enabled'])) {
            return true;
        }
        // Fallback to old format
        if (!empty($config['api_key']) || !empty($config['openai_api_key'])) {
            return true;
        }
        return false;
    }

    /**
     * List all widgets with filtering
     */
    public function index(Request $request): void
    {
        $pdo = db();
        $area = $request->get('area', '');
        $type = $request->get('type', '');
        $search = $request->get('search', '');
        $hasNew = $this->hasNewColumns($pdo);

        // Build query based on schema
        if ($hasNew) {
            $sql = "SELECT id, name, slug, icon, description, type, area, content, is_active, visibility, cache_ttl, version, sort_order, created_at FROM widgets";
        } else {
            $sql = "SELECT id, name, slug, type, area, content, is_active, sort_order, created_at FROM widgets";
        }

        $conditions = [];
        $params = [];

        if ($area && isset($this->areas[$area])) {
            $conditions[] = "area = ?";
            $params[] = $area;
        }

        if ($type && isset($this->types[$type])) {
            $conditions[] = "type = ?";
            $params[] = $type;
        }

        if ($search) {
            $conditions[] = "(name LIKE ? OR slug LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY area ASC, sort_order ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $widgets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/widgets/index', [
            'widgets' => $widgets,
            'areas' => $this->areas,
            'types' => $this->types,
            'visibilities' => $this->visibilities,
            'currentArea' => $area,
            'currentType' => $type,
            'search' => $search,
            'hasNewColumns' => $hasNew,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * Show create form
     */
    public function create(Request $request): void
    {
        $pdo = db();
        $hasNew = $this->hasNewColumns($pdo);
        
        // Get available menus for menu widget type
        $menus = $this->getAvailableMenus($pdo);

        render('admin/widgets/form', [
            'widget' => null,
            'areas' => $this->areas,
            'types' => $this->types,
            'visibilities' => $this->visibilities,
            'hasNewColumns' => $hasNew,
            'menus' => $menus,
            'aiConfigured' => $this->isAiConfigured(),
            'action' => 'create'
        ]);
    }

    /**
     * Store a new widget
     */
    public function store(Request $request): void
    {
        $pdo = db();
        $hasNew = $this->hasNewColumns($pdo);

        $name = trim($request->post('name', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($name);
        $type = in_array($request->post('type'), array_keys($this->types)) ? $request->post('type') : 'html';
        $area = in_array($request->post('area'), array_keys($this->areas)) ? $request->post('area') : 'sidebar';
        $content = $request->post('content', '');
        $isActive = $request->post('is_active') ? 1 : 0;

        if (empty($name)) {
            Session::flash('error', 'Name is required.');
            Response::redirect('/admin/widgets/create');
        }

        // Check for duplicate slug
        $stmt = $pdo->prepare("SELECT id FROM widgets WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A widget with this slug already exists.');
            Response::redirect('/admin/widgets/create');
        }

        // Get next sort order
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM widgets WHERE area = ?");
        $stmt->execute([$area]);
        $sortOrder = (int)$stmt->fetchColumn();

        if ($hasNew) {
            $icon = trim($request->post('icon', ''));
            $description = trim($request->post('description', ''));
            $visibility = in_array($request->post('visibility'), array_keys($this->visibilities)) ? $request->post('visibility') : 'all';
            $cacheTtl = max(0, (int)$request->post('cache_ttl', 0));

            $stmt = $pdo->prepare("
                INSERT INTO widgets (name, icon, slug, description, type, area, content, is_active, visibility, cache_ttl, version, sort_order, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())
            ");
            $stmt->execute([$name, $icon, $slug, $description, $type, $area, $content, $isActive, $visibility, $cacheTtl, $sortOrder]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO widgets (name, slug, type, area, content, is_active, sort_order, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $slug, $type, $area, $content, $isActive, $sortOrder]);
        }

        Session::flash('success', 'Widget created successfully.');
        Response::redirect('/admin/widgets');
    }

    /**
     * Show edit form
     */
    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();
        $hasNew = $this->hasNewColumns($pdo);

        $widget = $this->findWidget($id, $hasNew);

        if (!$widget) {
            Session::flash('error', 'Widget not found.');
            Response::redirect('/admin/widgets');
        }
        
        // Get available menus for menu widget type
        $menus = $this->getAvailableMenus($pdo);

        render('admin/widgets/form', [
            'widget' => $widget,
            'areas' => $this->areas,
            'types' => $this->types,
            'visibilities' => $this->visibilities,
            'hasNewColumns' => $hasNew,
            'menus' => $menus,
            'aiConfigured' => $this->isAiConfigured(),
            'action' => 'edit'
        ]);
    }

    /**
     * Update an existing widget
     */
    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();
        $hasNew = $this->hasNewColumns($pdo);

        $widget = $this->findWidget($id, $hasNew);

        if (!$widget) {
            Session::flash('error', 'Widget not found.');
            Response::redirect('/admin/widgets');
        }

        $name = trim($request->post('name', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($name);
        $type = in_array($request->post('type'), array_keys($this->types)) ? $request->post('type') : 'html';
        $area = in_array($request->post('area'), array_keys($this->areas)) ? $request->post('area') : 'sidebar';
        $content = $request->post('content', '');
        $isActive = $request->post('is_active') ? 1 : 0;

        if (empty($name)) {
            Session::flash('error', 'Name is required.');
            Response::redirect("/admin/widgets/{$id}/edit");
        }

        // Check for duplicate slug
        $stmt = $pdo->prepare("SELECT id FROM widgets WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A widget with this slug already exists.');
            Response::redirect("/admin/widgets/{$id}/edit");
        }

        if ($hasNew) {
            $icon = trim($request->post('icon', ''));
            $description = trim($request->post('description', ''));
            $visibility = in_array($request->post('visibility'), array_keys($this->visibilities)) ? $request->post('visibility') : 'all';
            $cacheTtl = max(0, (int)$request->post('cache_ttl', 0));
            $newVersion = ($widget['version'] ?? 1) + 1;

            $stmt = $pdo->prepare("
                UPDATE widgets
                SET name = ?, icon = ?, slug = ?, description = ?, type = ?, area = ?, content = ?, is_active = ?, visibility = ?, cache_ttl = ?, version = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $icon, $slug, $description, $type, $area, $content, $isActive, $visibility, $cacheTtl, $newVersion, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE widgets SET name = ?, slug = ?, type = ?, area = ?, content = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $type, $area, $content, $isActive, $id]);
        }

        Session::flash('success', 'Widget updated successfully.');
        Response::redirect('/admin/widgets');
    }

    /**
     * Toggle widget active status
     */
    public function toggle(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("UPDATE widgets SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Widget status updated.');
        Response::redirect('/admin/widgets');
    }

    /**
     * Delete a widget
     */
    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM widgets WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Widget deleted successfully.');
        Response::redirect('/admin/widgets');
    }

    /**
     * Duplicate a widget
     */
    public function duplicate(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();
        $hasNew = $this->hasNewColumns($pdo);

        $widget = $this->findWidget($id, $hasNew);

        if (!$widget) {
            Session::flash('error', 'Widget not found.');
            Response::redirect('/admin/widgets');
        }

        // Generate unique slug
        $baseSlug = $widget['slug'] . '-copy';
        $slug = $baseSlug;
        $counter = 1;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM widgets WHERE slug = ?");
        $stmt->execute([$slug]);
        while ($stmt->fetchColumn() > 0) {
            $slug = $baseSlug . '-' . $counter++;
            $stmt->execute([$slug]);
        }

        // Get next sort order
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM widgets WHERE area = ?");
        $stmt->execute([$widget['area']]);
        $sortOrder = (int)$stmt->fetchColumn();

        if ($hasNew) {
            $stmt = $pdo->prepare("
                INSERT INTO widgets (name, icon, slug, description, type, area, content, is_active, visibility, cache_ttl, version, sort_order, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())
            ");
            $stmt->execute([
                $widget['name'] . ' (Copy)',
                $widget['icon'] ?? '',
                $slug,
                $widget['description'] ?? '',
                $widget['type'],
                $widget['area'],
                $widget['content'],
                0, // Start as inactive
                $widget['visibility'] ?? 'all',
                $widget['cache_ttl'] ?? 0,
                $sortOrder
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO widgets (name, slug, type, area, content, is_active, sort_order, created_at)
                VALUES (?, ?, ?, ?, ?, 0, ?, NOW())
            ");
            $stmt->execute([
                $widget['name'] . ' (Copy)',
                $slug,
                $widget['type'],
                $widget['area'],
                $widget['content'],
                $sortOrder
            ]);
        }

        Session::flash('success', 'Widget duplicated successfully.');
        Response::redirect('/admin/widgets');
    }

    /**
     * Bulk delete widgets
     */
    public function bulkDelete(Request $request): void
    {
        $ids = $request->post('ids', []);

        if (empty($ids) || !is_array($ids)) {
            Session::flash('error', 'No widgets selected.');
            Response::redirect('/admin/widgets');
        }

        $pdo = db();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM widgets WHERE id IN ({$placeholders})");
        $stmt->execute(array_map('intval', $ids));

        $count = $stmt->rowCount();
        Session::flash('success', "{$count} widget(s) deleted successfully.");
        Response::redirect('/admin/widgets');
    }

    /**
     * Preview a widget (JSON response)
     */
    public function preview(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();
        $hasNew = $this->hasNewColumns($pdo);

        $widget = $this->findWidget($id, $hasNew);

        if (!$widget) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Widget not found']);
            return;
        }

        // Generate preview HTML based on type
        $previewHtml = $this->generatePreview($widget);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'widget' => [
                'id' => $widget['id'],
                'name' => $widget['name'],
                'type' => $widget['type'],
                'area' => $this->areas[$widget['area']] ?? $widget['area']
            ],
            'html' => $previewHtml
        ]);
    }

    /**
     * Export all widgets as JSON
     */
    public function export(Request $request): void
    {
        $pdo = db();
        $hasNew = $this->hasNewColumns($pdo);

        $area = $request->get('area', '');

        if ($hasNew) {
            $sql = "SELECT name, icon, slug, description, type, area, content, is_active, visibility, cache_ttl, sort_order FROM widgets";
        } else {
            $sql = "SELECT name, slug, type, area, content, is_active, sort_order FROM widgets";
        }

        $params = [];
        if ($area && isset($this->areas[$area])) {
            $sql .= " WHERE area = ?";
            $params[] = $area;
        }

        $sql .= " ORDER BY area ASC, sort_order ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $widgets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $export = [
            'export_date' => date('Y-m-d H:i:s'),
            'export_version' => '2.0',
            'widgets' => $widgets
        ];

        $filename = 'widgets-export-' . date('Y-m-d-His') . '.json';

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Import widgets from JSON
     */
    public function import(Request $request): void
    {
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please select a valid JSON file to import.');
            Response::redirect('/admin/widgets');
        }

        $content = file_get_contents($_FILES['import_file']['tmp_name']);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['widgets'])) {
            Session::flash('error', 'Invalid JSON file format.');
            Response::redirect('/admin/widgets');
        }

        $pdo = db();
        $hasNew = $this->hasNewColumns($pdo);
        $imported = 0;
        $skipped = 0;

        foreach ($data['widgets'] as $widget) {
            if (empty($widget['name']) || empty($widget['slug'])) {
                $skipped++;
                continue;
            }

            // Check if slug exists
            $stmt = $pdo->prepare("SELECT id FROM widgets WHERE slug = ?");
            $stmt->execute([$widget['slug']]);
            if ($stmt->fetch()) {
                // Append timestamp to make unique
                $widget['slug'] .= '-' . time();
            }

            // Get next sort order
            $area = $widget['area'] ?? 'sidebar';
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM widgets WHERE area = ?");
            $stmt->execute([$area]);
            $sortOrder = (int)$stmt->fetchColumn();

            if ($hasNew) {
                $stmt = $pdo->prepare("
                    INSERT INTO widgets (name, icon, slug, description, type, area, content, is_active, visibility, cache_ttl, version, sort_order, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())
                ");
                $stmt->execute([
                    $widget['name'],
                    $widget['icon'] ?? '',
                    $widget['slug'],
                    $widget['description'] ?? '',
                    $widget['type'] ?? 'html',
                    $area,
                    $widget['content'] ?? '',
                    $widget['is_active'] ?? 1,
                    $widget['visibility'] ?? 'all',
                    $widget['cache_ttl'] ?? 0,
                    $sortOrder
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO widgets (name, slug, type, area, content, is_active, sort_order, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $widget['name'],
                    $widget['slug'],
                    $widget['type'] ?? 'html',
                    $area,
                    $widget['content'] ?? '',
                    $widget['is_active'] ?? 1,
                    $sortOrder
                ]);
            }

            $imported++;
        }

        $message = "{$imported} widget(s) imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} skipped due to missing data.";
        }

        Session::flash('success', $message);
        Response::redirect('/admin/widgets');
    }

    /**
     * Reorder widgets within an area
     */
    public function reorder(Request $request): void
    {
        $order = $request->post('order', []);

        if (empty($order) || !is_array($order)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid order data']);
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("UPDATE widgets SET sort_order = ? WHERE id = ?");

        foreach ($order as $position => $id) {
            $stmt->execute([(int)$position, (int)$id]);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    /**
     * Find a widget by ID
     */
    private function findWidget(int $id, bool $hasNewColumns = false): ?array
    {
        $pdo = db();

        if ($hasNewColumns) {
            $sql = "SELECT id, name, icon, slug, description, type, area, content, is_active, visibility, cache_ttl, version, sort_order, created_at FROM widgets WHERE id = ?";
        } else {
            $sql = "SELECT id, name, slug, type, area, content, is_active, sort_order, created_at FROM widgets WHERE id = ?";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $widget = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $widget ?: null;
    }

    /**
     * Generate a URL-friendly slug
     */
    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Generate preview HTML for a widget
     */
    private function generatePreview(array $widget): string
    {
        $type = $widget['type'] ?? 'html';
        $content = $widget['content'] ?? '';

        switch ($type) {
            case 'html':
                return '<div class="widget-preview-html">' . $content . '</div>';

            case 'text':
                return '<div class="widget-preview-text">' . nl2br(esc($content)) . '</div>';

            case 'menu':
                return $this->generateMenuPreview($content);

            case 'recent_posts':
                return $this->generateRecentPostsPreview($content);

            case 'categories':
                return $this->generateCategoriesPreview($content);

            case 'search':
                return '<div class="widget-preview-search"><input type="text" placeholder="Search..." disabled style="width: 100%; padding: 0.5rem;"></div>';

            case 'social':
                return $this->generateSocialLinksPreview($content);

            case 'custom':
                return '<div class="widget-preview-custom"><pre style="background: var(--bg-secondary); padding: 1rem; overflow: auto; max-height: 200px;">' . esc($content) . '</pre></div>';

            default:
                return '<div class="widget-preview-default">' . esc($content) . '</div>';
        }
    }

    private function generateRecentPostsPreview(string $settings): string
    {
        $pdo = db();
        
        // Parse settings (could be JSON with limit, category, etc.)
        $limit = 5;
        if (!empty($settings)) {
            $decoded = json_decode($settings, true);
            if (is_array($decoded) && isset($decoded['limit'])) {
                $limit = min(10, max(1, (int)$decoded['limit']));
            } elseif (is_numeric($settings)) {
                $limit = min(10, max(1, (int)$settings));
            }
        }
        
        try {
            $stmt = $pdo->prepare("
                SELECT id, title, slug, created_at 
                FROM articles 
                WHERE status = 'published' 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($articles)) {
                return '<div class="widget-preview-posts"><p style="color: var(--text-muted);">No published articles found</p></div>';
            }
            
            $html = '<div class="widget-preview-posts"><ul style="list-style: none; padding: 0; margin: 0;">';
            foreach ($articles as $article) {
                $date = date('M j, Y', strtotime($article['created_at']));
                $html .= '<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">';
                $html .= '<a href="/article/' . esc($article['slug']) . '" style="color: var(--text-primary); text-decoration: none;" target="_blank">';
                $html .= esc($article['title']);
                $html .= '</a>';
                $html .= '<br><small style="color: var(--text-muted);">' . $date . '</small>';
                $html .= '</li>';
            }
            $html .= '</ul></div>';
            
            return $html;
        } catch (\Exception $e) {
            return '<div class="widget-preview-posts"><p style="color: var(--text-muted);">Error loading articles</p></div>';
        }
    }

    private function generateMenuPreview(string $settings): string
    {
        $pdo = db();
        
        // Settings could be menu_id or slug
        $menuId = null;
        if (!empty($settings)) {
            $decoded = json_decode($settings, true);
            if (is_array($decoded) && isset($decoded['menu_id'])) {
                $menuId = (int)$decoded['menu_id'];
            } elseif (is_numeric($settings)) {
                $menuId = (int)$settings;
            }
        }
        
        try {
            if ($menuId) {
                $stmt = $pdo->prepare("SELECT id, name FROM menus WHERE id = ?");
                $stmt->execute([$menuId]);
                $menu = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($menu) {
                    $stmt = $pdo->prepare("SELECT title, url FROM menu_items WHERE menu_id = ? ORDER BY sort_order ASC LIMIT 10");
                    $stmt->execute([$menuId]);
                    $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    
                    $html = '<div class="widget-preview-menu"><strong>' . esc($menu['name']) . '</strong><ul style="list-style: none; padding: 0; margin: 0.5rem 0 0 0;">';
                    foreach ($items as $item) {
                        $html .= '<li style="padding: 0.25rem 0;"><a href="' . esc($item['url']) . '" style="color: var(--text-primary);">' . esc($item['title']) . '</a></li>';
                    }
                    $html .= '</ul></div>';
                    return $html;
                }
            }
            
            // Show available menus
            $stmt = $pdo->query("SELECT id, name FROM menus WHERE is_active = 1 LIMIT 5");
            $menus = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($menus)) {
                return '<div class="widget-preview-menu"><p style="color: var(--text-muted);">No menus available</p></div>';
            }
            
            $html = '<div class="widget-preview-menu"><p style="color: var(--text-muted);">Select a menu (set menu_id in content):</p><ul>';
            foreach ($menus as $m) {
                $html .= '<li>' . esc($m['name']) . ' (ID: ' . $m['id'] . ')</li>';
            }
            $html .= '</ul></div>';
            return $html;
        } catch (\Exception $e) {
            return '<div class="widget-preview-menu"><p style="color: var(--text-muted);">Error loading menu</p></div>';
        }
    }

    private function generateCategoriesPreview(string $settings): string
    {
        $pdo = db();
        
        try {
            $stmt = $pdo->query("
                SELECT c.id, c.name, c.slug, COUNT(a.id) as count 
                FROM article_categories c 
                LEFT JOIN articles a ON a.category_id = c.id AND a.status = 'published'
                GROUP BY c.id, c.name, c.slug
                ORDER BY c.name ASC 
                LIMIT 10
            ");
            $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($categories)) {
                return '<div class="widget-preview-categories"><p style="color: var(--text-muted);">No categories found</p></div>';
            }
            
            $html = '<div class="widget-preview-categories"><ul style="list-style: none; padding: 0; margin: 0;">';
            foreach ($categories as $cat) {
                $html .= '<li style="padding: 0.25rem 0; display: flex; justify-content: space-between;">';
                $html .= '<a href="/category/' . esc($cat['slug']) . '" style="color: var(--text-primary);">' . esc($cat['name']) . '</a>';
                $html .= '<span style="color: var(--text-muted);">(' . (int)$cat['count'] . ')</span>';
                $html .= '</li>';
            }
            $html .= '</ul></div>';
            
            return $html;
        } catch (\Exception $e) {
            return '<div class="widget-preview-categories"><p style="color: var(--text-muted);">Error loading categories</p></div>';
        }
    }

    private function generateSocialLinksPreview(string $content): string
    {
        if (empty($content)) {
            return '<div class="widget-preview-social"><p style="color: var(--text-muted);">No social links configured</p></div>';
        }

        $links = json_decode($content, true);
        if (!is_array($links) || empty($links)) {
            return '<div class="widget-preview-social"><p style="color: var(--text-muted);">No social links configured</p></div>';
        }

        $icons = [
            'facebook' => '📘',
            'twitter' => '🐦',
            'instagram' => '📸',
            'linkedin' => '💼',
            'youtube' => '📺',
            'tiktok' => '🎵',
            'pinterest' => '📌',
            'github' => '💻'
        ];

        $html = '<div class="widget-preview-social" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">';
        
        foreach ($links as $platform => $url) {
            if (empty($url)) continue;
            
            $icon = $icons[$platform] ?? '🔗';
            $name = ucfirst($platform);
            $escapedUrl = esc($url);
            
            $html .= '<a href="' . $escapedUrl . '" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.8rem; background: var(--bg-secondary); border-radius: 6px; text-decoration: none; color: var(--text-primary); font-size: 0.9rem; transition: background 0.2s;" title="' . $name . '">';
            $html .= '<span style="font-size: 1.2rem;">' . $icon . '</span>';
            $html .= '<span>' . $name . '</span>';
            $html .= '</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
