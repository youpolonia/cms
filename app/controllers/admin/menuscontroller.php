<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class MenusController
{
    // ═══════════════════════════════════════════════════════════
    // MENU CRUD
    // ═══════════════════════════════════════════════════════════

    public function index(Request $request): void
    {
        $pdo = db();
        $stmt = $pdo->query("
            SELECT m.*,
                   (SELECT COUNT(*) FROM menu_items WHERE menu_id = m.id) as item_count
            FROM menus m
            ORDER BY m.name ASC
        ");
        $menus = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/menus/index', [
            'menus' => $menus,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(Request $request): void
    {
        render('admin/menus/form', [
            'menu' => null,
            'action' => 'create'
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim($request->post('name', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($name);
        $description = trim($request->post('description', ''));
        $location = trim($request->post('location', '')) ?: null;
        $isActive = (int)$request->post('is_active', 1);
        $maxDepth = (int)$request->post('max_depth', 3);

        if (empty($name)) {
            Session::flash('error', 'Name is required.');
            Response::redirect('/admin/menus/create');
            return;
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM menus WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A menu with this slug already exists.');
            Response::redirect('/admin/menus/create');
            return;
        }

        $createdBy = Session::get('user_id');
        $stmt = $pdo->prepare("
            INSERT INTO menus (name, slug, description, location, is_active, max_depth, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $slug, $description, $location, $isActive, $maxDepth, $createdBy]);

        $menuId = $pdo->lastInsertId();

        Session::flash('success', 'Menu created successfully.');
        Response::redirect("/admin/menus/{$menuId}/items");
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $menu = $this->findMenu($id);

        if (!$menu) {
            Session::flash('error', 'Menu not found.');
            Response::redirect('/admin/menus');
            return;
        }

        render('admin/menus/form', [
            'menu' => $menu,
            'action' => 'edit'
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $menu = $this->findMenu($id);

        if (!$menu) {
            Session::flash('error', 'Menu not found.');
            Response::redirect('/admin/menus');
            return;
        }

        $name = trim($request->post('name', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($name);
        $description = trim($request->post('description', ''));
        $location = trim($request->post('location', '')) ?: null;
        $isActive = (int)$request->post('is_active', 1);
        $maxDepth = (int)$request->post('max_depth', 3);

        if (empty($name)) {
            Session::flash('error', 'Name is required.');
            Response::redirect("/admin/menus/{$id}/edit");
            return;
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM menus WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A menu with this slug already exists.');
            Response::redirect("/admin/menus/{$id}/edit");
            return;
        }

        $stmt = $pdo->prepare("
            UPDATE menus 
            SET name = ?, slug = ?, description = ?, location = ?, is_active = ?, max_depth = ? 
            WHERE id = ?
        ");
        $stmt->execute([$name, $slug, $description, $location, $isActive, $maxDepth, $id]);

        Session::flash('success', 'Menu updated successfully.');
        Response::redirect('/admin/menus');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        
        // Delete items first (foreign key)
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE menu_id = ?");
        $stmt->execute([$id]);
        
        // Delete menu
        $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Menu deleted successfully.');
        Response::redirect('/admin/menus');
    }

    public function toggleActive(Request $request): void
    {
        $id = (int)$request->param('id');
        $menu = $this->findMenu($id);

        if (!$menu) {
            Session::flash('error', 'Menu not found.');
            Response::redirect('/admin/menus');
            return;
        }

        $pdo = db();
        $newStatus = $menu['is_active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE menus SET is_active = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        $statusText = $newStatus ? 'activated' : 'deactivated';
        Session::flash('success', "Menu {$statusText} successfully.");
        Response::redirect('/admin/menus');
    }

    public function duplicate(Request $request): void
    {
        $id = (int)$request->param('id');
        $menu = $this->findMenu($id);

        if (!$menu) {
            Session::flash('error', 'Menu not found.');
            Response::redirect('/admin/menus');
            return;
        }

        $pdo = db();
        
        // Generate unique slug
        $baseSlug = $menu['slug'] . '-copy';
        $newSlug = $baseSlug;
        $counter = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM menus WHERE slug = ?");
            $stmt->execute([$newSlug]);
            if (!$stmt->fetch()) break;
            $newSlug = $baseSlug . '-' . $counter++;
        }

        $createdBy = Session::get('user_id');
        
        // Duplicate menu
        $stmt = $pdo->prepare("
            INSERT INTO menus (name, slug, description, location, is_active, max_depth, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $menu['name'] . ' (Copy)',
            $newSlug,
            $menu['description'],
            null, // Don't copy location to avoid conflict
            $menu['is_active'] ?? 1,
            $menu['max_depth'] ?? 3,
            $createdBy
        ]);
        $newMenuId = $pdo->lastInsertId();

        // Duplicate items
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE menu_id = ? ORDER BY parent_id, sort_order");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $idMap = []; // old_id => new_id
        foreach ($items as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO menu_items (menu_id, parent_id, title, icon, description, url, page_id, target, css_class, is_active, visibility, open_in_new_tab, sort_order, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $parentId = $item['parent_id'] ? ($idMap[$item['parent_id']] ?? null) : null;
            $stmt->execute([
                $newMenuId,
                $parentId,
                $item['title'],
                $item['icon'] ?? null,
                $item['description'] ?? null,
                $item['url'],
                $item['page_id'],
                $item['target'],
                $item['css_class'],
                $item['is_active'] ?? 1,
                $item['visibility'] ?? 'all',
                $item['open_in_new_tab'] ?? 0,
                $item['sort_order']
            ]);
            $idMap[$item['id']] = $pdo->lastInsertId();
        }

        Session::flash('success', 'Menu duplicated successfully.');
        Response::redirect("/admin/menus/{$newMenuId}/items");
    }

    public function exportMenu(Request $request): void
    {
        $id = (int)$request->param('id');
        $menu = $this->findMenu($id);

        if (!$menu) {
            Response::json(['error' => 'Menu not found'], 404);
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE menu_id = ? ORDER BY parent_id, sort_order");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $export = [
            'version' => '1.0',
            'exported_at' => date('Y-m-d H:i:s'),
            'menu' => [
                'name' => $menu['name'],
                'slug' => $menu['slug'],
                'description' => $menu['description'],
                'location' => $menu['location'],
                'is_active' => $menu['is_active'] ?? 1,
                'max_depth' => $menu['max_depth'] ?? 3,
            ],
            'items' => array_map(function($item) {
                return [
                    'id' => $item['id'],
                    'parent_id' => $item['parent_id'],
                    'title' => $item['title'],
                    'icon' => $item['icon'] ?? null,
                    'description' => $item['description'] ?? null,
                    'url' => $item['url'],
                    'page_id' => $item['page_id'],
                    'target' => $item['target'],
                    'css_class' => $item['css_class'],
                    'is_active' => $item['is_active'] ?? 1,
                    'visibility' => $item['visibility'] ?? 'all',
                    'open_in_new_tab' => $item['open_in_new_tab'] ?? 0,
                    'sort_order' => $item['sort_order'],
                ];
            }, $items)
        ];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="menu-' . $menu['slug'] . '.json"');
        echo json_encode($export, JSON_PRETTY_PRINT);
        exit;
    }

    public function importMenu(Request $request): void
    {
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please select a valid JSON file to import.');
            Response::redirect('/admin/menus');
            return;
        }

        $content = file_get_contents($_FILES['import_file']['tmp_name']);
        $data = json_decode($content, true);

        if (!$data || !isset($data['menu']) || !isset($data['items'])) {
            Session::flash('error', 'Invalid import file format.');
            Response::redirect('/admin/menus');
            return;
        }

        $pdo = db();
        $menuData = $data['menu'];

        // Generate unique slug
        $baseSlug = $menuData['slug'];
        $newSlug = $baseSlug;
        $counter = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM menus WHERE slug = ?");
            $stmt->execute([$newSlug]);
            if (!$stmt->fetch()) break;
            $newSlug = $baseSlug . '-imported-' . $counter++;
        }

        $createdBy = Session::get('user_id');

        // Create menu
        $stmt = $pdo->prepare("
            INSERT INTO menus (name, slug, description, location, is_active, max_depth, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $menuData['name'] . ' (Imported)',
            $newSlug,
            $menuData['description'] ?? '',
            null, // Don't import location
            $menuData['is_active'] ?? 1,
            $menuData['max_depth'] ?? 3,
            $createdBy
        ]);
        $newMenuId = $pdo->lastInsertId();

        // Import items
        $idMap = [];
        foreach ($data['items'] as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO menu_items (menu_id, parent_id, title, icon, description, url, page_id, target, css_class, is_active, visibility, open_in_new_tab, sort_order, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $parentId = $item['parent_id'] ? ($idMap[$item['parent_id']] ?? null) : null;
            $stmt->execute([
                $newMenuId,
                $parentId,
                $item['title'],
                $item['icon'] ?? null,
                $item['description'] ?? null,
                $item['url'] ?? null,
                null, // Don't import page_id (may not exist)
                $item['target'] ?? '_self',
                $item['css_class'] ?? null,
                $item['is_active'] ?? 1,
                $item['visibility'] ?? 'all',
                $item['open_in_new_tab'] ?? 0,
                $item['sort_order'] ?? 0
            ]);
            $idMap[$item['id']] = $pdo->lastInsertId();
        }

        Session::flash('success', 'Menu imported successfully.');
        Response::redirect("/admin/menus/{$newMenuId}/items");
    }

    public function preview(Request $request): void
    {
        $id = (int)$request->param('id');
        $menu = $this->findMenu($id);

        if (!$menu) {
            Response::json(['error' => 'Menu not found'], 404);
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT mi.*, p.title as page_title, p.slug as page_slug
            FROM menu_items mi
            LEFT JOIN pages p ON mi.page_id = p.id
            WHERE mi.menu_id = ? AND (mi.is_active = 1 OR mi.is_active IS NULL)
            ORDER BY mi.parent_id IS NULL DESC, mi.parent_id ASC, mi.sort_order ASC
        ");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Build tree
        $tree = $this->buildMenuTree($items);
        $html = $this->renderMenuPreview($tree);

        Response::json([
            'success' => true,
            'html' => $html,
            'menu' => $menu
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // MENU ITEMS
    // ═══════════════════════════════════════════════════════════

    public function items(Request $request): void
    {
        $id = (int)$request->param('id');
        $menu = $this->findMenu($id);

        if (!$menu) {
            Session::flash('error', 'Menu not found.');
            Response::redirect('/admin/menus');
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT mi.*, p.title as page_title, parent.title as parent_title
            FROM menu_items mi
            LEFT JOIN pages p ON mi.page_id = p.id
            LEFT JOIN menu_items parent ON mi.parent_id = parent.id
            WHERE mi.menu_id = ?
            ORDER BY mi.parent_id IS NULL DESC, mi.parent_id ASC, mi.sort_order ASC
        ");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $pages = $pdo->query("SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/menus/items', [
            'menu' => $menu,
            'items' => $items,
            'pages' => $pages,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function addItem(Request $request): void
    {
        $menuId = (int)$request->param('id');
        $menu = $this->findMenu($menuId);

        if (!$menu) {
            Session::flash('error', 'Menu not found.');
            Response::redirect('/admin/menus');
            return;
        }

        $title = trim($request->post('title', ''));
        $icon = trim($request->post('icon', ''));
        $description = trim($request->post('description', ''));
        $url = trim($request->post('url', ''));
        $pageId = (int)$request->post('page_id') ?: null;
        $parentId = (int)$request->post('parent_id') ?: null;
        $target = in_array($request->post('target'), ['_self', '_blank']) ? $request->post('target') : '_self';
        $cssClass = $this->sanitizeCssClass($request->post('css_class', ''));
        $visibility = in_array($request->post('visibility'), ['all', 'logged_in', 'logged_out', 'admin']) 
            ? $request->post('visibility') : 'all';
        $openInNewTab = (int)$request->post('open_in_new_tab', 0);

        if (empty($title)) {
            Session::flash('error', 'Title is required.');
            Response::redirect("/admin/menus/{$menuId}/items");
            return;
        }

        // Validate URL
        if ($url && !$this->isValidUrl($url)) {
            Session::flash('error', 'Invalid URL format.');
            Response::redirect("/admin/menus/{$menuId}/items");
            return;
        }

        $pdo = db();

        // Get max sort order
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM menu_items WHERE menu_id = ?");
        $stmt->execute([$menuId]);
        $sortOrder = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("
            INSERT INTO menu_items (menu_id, parent_id, title, icon, description, url, page_id, target, css_class, is_active, visibility, open_in_new_tab, sort_order, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $menuId, 
            $parentId, 
            $title, 
            $icon ?: null,
            $description ?: null,
            $url ?: null, 
            $pageId, 
            $target, 
            $cssClass ?: null,
            $visibility,
            $openInNewTab,
            $sortOrder
        ]);

        Session::flash('success', 'Menu item added.');
        Response::redirect("/admin/menus/{$menuId}/items");
    }

    public function editItem(Request $request): void
    {
        $menuId = (int)$request->param('id');
        $itemId = (int)$request->param('itemId');
        
        $menu = $this->findMenu($menuId);
        if (!$menu) {
            Session::flash('error', 'Menu not found.');
            Response::redirect('/admin/menus');
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ? AND menu_id = ?");
        $stmt->execute([$itemId, $menuId]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$item) {
            Session::flash('error', 'Menu item not found.');
            Response::redirect("/admin/menus/{$menuId}/items");
            return;
        }

        // Get pages for dropdown
        $pages = $pdo->query("SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);

        // Get other menu items for parent dropdown (exclude current item and its children)
        $stmt = $pdo->prepare("SELECT id, title, parent_id FROM menu_items WHERE menu_id = ? AND id != ? ORDER BY sort_order");
        $stmt->execute([$menuId, $itemId]);
        $menuItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/menus/edit_item', [
            'menu' => $menu,
            'item' => $item,
            'pages' => $pages,
            'menuItems' => $menuItems
        ]);
    }

    public function updateItem(Request $request): void
    {
        $menuId = (int)$request->param('id');
        $itemId = (int)$request->param('itemId');

        $menu = $this->findMenu($menuId);
        if (!$menu) {
            Session::flash('error', 'Menu not found.');
            Response::redirect('/admin/menus');
            return;
        }

        $title = trim($request->post('title', ''));
        $icon = trim($request->post('icon', ''));
        $description = trim($request->post('description', ''));
        $url = trim($request->post('url', ''));
        $pageId = (int)$request->post('page_id') ?: null;
        $parentId = (int)$request->post('parent_id') ?: null;
        $target = in_array($request->post('target'), ['_self', '_blank']) ? $request->post('target') : '_self';
        $cssClass = $this->sanitizeCssClass($request->post('css_class', ''));
        $visibility = in_array($request->post('visibility'), ['all', 'logged_in', 'logged_out', 'admin']) 
            ? $request->post('visibility') : 'all';
        $openInNewTab = (int)$request->post('open_in_new_tab', 0);

        if (empty($title)) {
            Session::flash('error', 'Title is required.');
            Response::redirect("/admin/menus/{$menuId}/items/{$itemId}/edit");
            return;
        }

        // Validate URL
        if ($url && !$this->isValidUrl($url)) {
            Session::flash('error', 'Invalid URL format.');
            Response::redirect("/admin/menus/{$menuId}/items/{$itemId}/edit");
            return;
        }

        // Prevent item from being its own parent
        if ($parentId === $itemId) {
            $parentId = null;
        }

        $pdo = db();
        $stmt = $pdo->prepare("
            UPDATE menu_items 
            SET title = ?, icon = ?, description = ?, url = ?, page_id = ?, parent_id = ?, target = ?, css_class = ?, visibility = ?, open_in_new_tab = ?
            WHERE id = ? AND menu_id = ?
        ");
        $stmt->execute([
            $title, 
            $icon ?: null,
            $description ?: null,
            $url ?: null, 
            $pageId, 
            $parentId, 
            $target, 
            $cssClass ?: null,
            $visibility,
            $openInNewTab,
            $itemId, 
            $menuId
        ]);

        Session::flash('success', 'Menu item updated.');
        Response::redirect("/admin/menus/{$menuId}/items");
    }

    public function deleteItem(Request $request): void
    {
        $menuId = (int)$request->param('id');
        $itemId = (int)$request->param('itemId');

        $pdo = db();
        
        // Also delete child items
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE parent_id = ? AND menu_id = ?");
        $stmt->execute([$itemId, $menuId]);
        
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ? AND menu_id = ?");
        $stmt->execute([$itemId, $menuId]);

        Session::flash('success', 'Menu item deleted.');
        Response::redirect("/admin/menus/{$menuId}/items");
    }

    public function toggleItem(Request $request): void
    {
        $menuId = (int)$request->param('id');
        $itemId = (int)$request->param('itemId');

        $pdo = db();
        $stmt = $pdo->prepare("SELECT is_active FROM menu_items WHERE id = ? AND menu_id = ?");
        $stmt->execute([$itemId, $menuId]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$item) {
            Session::flash('error', 'Menu item not found.');
            Response::redirect("/admin/menus/{$menuId}/items");
            return;
        }

        $newStatus = ($item['is_active'] ?? 1) ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE menu_items SET is_active = ? WHERE id = ?");
        $stmt->execute([$newStatus, $itemId]);

        Session::flash('success', 'Item status updated.');
        Response::redirect("/admin/menus/{$menuId}/items");
    }

    public function cloneItem(Request $request): void
    {
        $menuId = (int)$request->param('id');
        $itemId = (int)$request->param('itemId');

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ? AND menu_id = ?");
        $stmt->execute([$itemId, $menuId]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$item) {
            Session::flash('error', 'Menu item not found.');
            Response::redirect("/admin/menus/{$menuId}/items");
            return;
        }

        // Get max sort order
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM menu_items WHERE menu_id = ?");
        $stmt->execute([$menuId]);
        $sortOrder = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("
            INSERT INTO menu_items (menu_id, parent_id, title, icon, description, url, page_id, target, css_class, is_active, visibility, open_in_new_tab, sort_order, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $menuId,
            $item['parent_id'],
            $item['title'] . ' (Copy)',
            $item['icon'] ?? null,
            $item['description'] ?? null,
            $item['url'],
            $item['page_id'],
            $item['target'],
            $item['css_class'],
            $item['is_active'] ?? 1,
            $item['visibility'] ?? 'all',
            $item['open_in_new_tab'] ?? 0,
            $sortOrder
        ]);

        Session::flash('success', 'Menu item cloned.');
        Response::redirect("/admin/menus/{$menuId}/items");
    }

    public function bulkDeleteItems(Request $request): void
    {
        $menuId = (int)$request->param('id');
        $idsRaw = $request->post('ids', '[]');
        $ids = is_string($idsRaw) ? json_decode($idsRaw, true) : $idsRaw;

        if (empty($ids) || !is_array($ids)) {
            Session::flash('error', 'No items selected.');
            Response::redirect("/admin/menus/{$menuId}/items");
            return;
        }

        $pdo = db();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // Delete child items first
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE parent_id IN ({$placeholders}) AND menu_id = ?");
        $params = array_map('intval', $ids);
        $params[] = $menuId;
        $stmt->execute($params);
        
        // Delete selected items
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id IN ({$placeholders}) AND menu_id = ?");
        $stmt->execute($params);

        Session::flash('success', count($ids) . ' items deleted.');
        Response::redirect("/admin/menus/{$menuId}/items");
    }

    public function reorderItems(Request $request): void
    {
        $menuId = (int)$request->param('id');
        $orderRaw = $request->post('order', '');

        // Handle JSON-encoded array
        $order = is_string($orderRaw) ? json_decode($orderRaw, true) : $orderRaw;

        if (!is_array($order)) {
            Response::json(['success' => false, 'error' => 'Invalid data']);
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("UPDATE menu_items SET sort_order = ? WHERE id = ? AND menu_id = ?");

        foreach ($order as $position => $itemId) {
            $stmt->execute([(int)$position, (int)$itemId, $menuId]);
        }

        Response::json(['success' => true]);
    }

    // ═══════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════

    private function findMenu(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $menu = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $menu ?: null;
    }

    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    private function sanitizeCssClass(string $input): string
    {
        // Allow only alphanumeric, hyphens, underscores, spaces
        return preg_replace('/[^a-zA-Z0-9\s_-]/', '', trim($input));
    }

    private function isValidUrl(string $url): bool
    {
        // Allow relative URLs starting with /
        if (str_starts_with($url, '/')) {
            return true;
        }
        // Allow anchors
        if (str_starts_with($url, '#')) {
            return true;
        }
        // Validate absolute URLs
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function buildMenuTree(array $items, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $item['children'] = $this->buildMenuTree($items, $item['id']);
                $tree[] = $item;
            }
        }
        return $tree;
    }

    private function renderMenuPreview(array $items, int $level = 0): string
    {
        if (empty($items)) return '';
        
        $indent = str_repeat('  ', $level);
        $html = $indent . '<ul class="menu-preview-list level-' . $level . '">' . "\n";
        
        foreach ($items as $item) {
            $icon = $item['icon'] ? '<span class="menu-icon">' . esc($item['icon']) . '</span> ' : '';
            $url = $item['page_slug'] ? '/page/' . $item['page_slug'] : ($item['url'] ?: '#');
            $target = ($item['open_in_new_tab'] ?? 0) ? ' target="_blank"' : '';
            
            $html .= $indent . '  <li>';
            $html .= '<a href="' . esc($url) . '"' . $target . '>' . $icon . esc($item['title']) . '</a>';
            
            if (!empty($item['children'])) {
                $html .= "\n" . $this->renderMenuPreview($item['children'], $level + 1);
                $html .= $indent . '  ';
            }
            
            $html .= "</li>\n";
        }
        
        $html .= $indent . "</ul>\n";
        return $html;
    }
}
