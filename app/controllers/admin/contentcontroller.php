<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class ContentController
{
    private array $types = [
        'html' => 'HTML',
        'text' => 'Plain Text',
        'json' => 'JSON Data',
        'markdown' => 'Markdown',
        'shortcode' => 'Shortcode'
    ];

    private array $categories = [
        'header' => 'Header',
        'footer' => 'Footer',
        'sidebar' => 'Sidebar',
        'global' => 'Global',
        'uncategorized' => 'Uncategorized'
    ];

    public function index(Request $request): void
    {
        $pdo = db();

        // Get filter parameters
        $filterType = $request->get('type', '');
        $filterCategory = $request->get('category', '');
        $search = trim($request->get('search', ''));

        // Build query with filters
        $sql = "SELECT * FROM content_blocks WHERE 1=1";
        $params = [];

        if ($filterType && array_key_exists($filterType, $this->types)) {
            $sql .= " AND type = ?";
            $params[] = $filterType;
        }

        if ($filterCategory && array_key_exists($filterCategory, $this->categories)) {
            $sql .= " AND category = ?";
            $params[] = $filterCategory;
        }

        if ($search !== '') {
            $sql .= " AND (name LIKE ? OR slug LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Check if position column exists (for backwards compatibility)
        $hasPosition = $this->columnExists($pdo, 'content_blocks', 'position');
        $sql .= $hasPosition ? " ORDER BY position ASC, name ASC" : " ORDER BY name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $blocks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/content/index', [
            'blocks' => $blocks,
            'types' => $this->types,
            'categories' => $this->categories,
            'filterType' => $filterType,
            'filterCategory' => $filterCategory,
            'search' => $search,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(Request $request): void
    {
        render('admin/content/form', [
            'block' => null,
            'types' => $this->types,
            'categories' => $this->categories,
            'action' => 'create'
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim($request->post('name', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($name);
        $content = $request->post('content', '');
        $type = in_array($request->post('type'), array_keys($this->types)) ? $request->post('type') : 'html';
        $category = in_array($request->post('category'), array_keys($this->categories)) ? $request->post('category') : 'uncategorized';
        $description = trim($request->post('description', ''));
        $cacheTtl = max(0, (int)$request->post('cache_ttl', 0));
        $isActive = $request->post('is_active') ? 1 : 0;

        if (empty($name)) {
            Session::flash('error', 'Name is required.');
            Response::redirect('/admin/content/create');
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM content_blocks WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A content block with this slug already exists.');
            Response::redirect('/admin/content/create');
        }

        // Check if new columns exist (backwards compatibility)
        $hasNewColumns = $this->columnExists($pdo, 'content_blocks', 'position');

        if ($hasNewColumns) {
            // Get max position
            $stmt = $pdo->query("SELECT MAX(position) FROM content_blocks");
            $maxPos = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("
                INSERT INTO content_blocks
                (name, slug, content, type, category, cache_ttl, description, is_active, position, version, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
            ");
            $stmt->execute([$name, $slug, $content, $type, $category, $cacheTtl, $description, $isActive, $maxPos + 1]);
        } else {
            // Old schema - only basic columns
            $stmt = $pdo->prepare("
                INSERT INTO content_blocks
                (name, slug, content, type, description, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $slug, $content, $type, $description, $isActive]);
        }

        Session::flash('success', 'Content block created successfully.');
        Response::redirect('/admin/content');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $block = $this->findBlock($id);

        if (!$block) {
            Session::flash('error', 'Content block not found.');
            Response::redirect('/admin/content');
        }

        render('admin/content/form', [
            'block' => $block,
            'types' => $this->types,
            'categories' => $this->categories,
            'action' => 'edit'
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $block = $this->findBlock($id);

        if (!$block) {
            Session::flash('error', 'Content block not found.');
            Response::redirect('/admin/content');
        }

        $name = trim($request->post('name', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($name);
        $content = $request->post('content', '');
        $type = in_array($request->post('type'), array_keys($this->types)) ? $request->post('type') : 'html';
        $category = in_array($request->post('category'), array_keys($this->categories)) ? $request->post('category') : 'uncategorized';
        $description = trim($request->post('description', ''));
        $cacheTtl = max(0, (int)$request->post('cache_ttl', 0));
        $isActive = $request->post('is_active') ? 1 : 0;

        if (empty($name)) {
            Session::flash('error', 'Name is required.');
            Response::redirect("/admin/content/{$id}/edit");
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM content_blocks WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A content block with this slug already exists.');
            Response::redirect("/admin/content/{$id}/edit");
        }

        // Increment version on update
        $newVersion = ((int)($block['version'] ?? 1)) + 1;

        $stmt = $pdo->prepare("
            UPDATE content_blocks
            SET name = ?, slug = ?, content = ?, type = ?, category = ?,
                cache_ttl = ?, description = ?, is_active = ?, version = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $slug, $content, $type, $category, $cacheTtl, $description, $isActive, $newVersion, $id]);

        Session::flash('success', 'Content block updated successfully.');
        Response::redirect('/admin/content');
    }

    public function toggle(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("UPDATE content_blocks SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Content block status updated.');
        Response::redirect('/admin/content');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM content_blocks WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Content block deleted successfully.');
        Response::redirect('/admin/content');
    }

    public function duplicate(Request $request): void
    {
        $id = (int)$request->param('id');
        $block = $this->findBlock($id);

        if (!$block) {
            Session::flash('error', 'Content block not found.');
            Response::redirect('/admin/content');
            return;
        }

        $pdo = db();

        // Generate unique slug
        $baseSlug = $block['slug'] . '-copy';
        $slug = $baseSlug;
        $counter = 1;

        do {
            $stmt = $pdo->prepare("SELECT id FROM content_blocks WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            } else {
                break;
            }
        } while ($counter < 100);

        // Check if new columns exist (backwards compatibility)
        $hasNewColumns = $this->columnExists($pdo, 'content_blocks', 'position');

        if ($hasNewColumns) {
            // Get max position
            $stmt = $pdo->query("SELECT MAX(position) FROM content_blocks");
            $maxPos = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("
                INSERT INTO content_blocks
                (name, slug, content, type, category, cache_ttl, description, is_active, position, version, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
            ");
            $stmt->execute([
                $block['name'] . ' (Copy)',
                $slug,
                $block['content'],
                $block['type'],
                $block['category'] ?? 'uncategorized',
                $block['cache_ttl'] ?? 0,
                $block['description'],
                0, // duplicates are inactive by default
                $maxPos + 1
            ]);
        } else {
            // Old schema
            $stmt = $pdo->prepare("
                INSERT INTO content_blocks
                (name, slug, content, type, description, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $block['name'] . ' (Copy)',
                $slug,
                $block['content'],
                $block['type'],
                $block['description'],
                0
            ]);
        }

        Session::flash('success', 'Content block duplicated successfully.');
        Response::redirect('/admin/content');
    }

    public function bulkDelete(Request $request): void
    {
        $ids = $request->post('ids', []);

        if (empty($ids) || !is_array($ids)) {
            Session::flash('error', 'No content blocks selected.');
            Response::redirect('/admin/content');
            return;
        }

        $pdo = db();

        // Sanitize IDs
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("DELETE FROM content_blocks WHERE id IN ({$placeholders})");
        $stmt->execute($ids);

        $count = $stmt->rowCount();
        Session::flash('success', "Deleted {$count} content block(s) successfully.");
        Response::redirect('/admin/content');
    }

    public function preview(Request $request): void
    {
        $id = (int)$request->param('id');
        $block = $this->findBlock($id);

        if (!$block) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Block not found']);
            return;
        }

        $renderedContent = $this->renderBlockContent($block);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'block' => [
                'name' => $block['name'],
                'type' => $block['type'],
                'category' => $block['category'] ?? 'uncategorized'
            ],
            'html' => $renderedContent
        ]);
    }

    public function export(Request $request): void
    {
        $pdo = db();
        
        // Check if position column exists (backwards compatibility)
        $hasPosition = $this->columnExists($pdo, 'content_blocks', 'position');
        $orderBy = $hasPosition ? "ORDER BY position ASC, name ASC" : "ORDER BY name ASC";
        
        $stmt = $pdo->query("SELECT * FROM content_blocks {$orderBy}");
        $blocks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Remove auto-generated fields that shouldn't be imported
        foreach ($blocks as &$block) {
            unset($block['id']);
            unset($block['created_at']);
            unset($block['updated_at']);
        }

        $export = [
            'export_date' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'count' => count($blocks),
            'blocks' => $blocks
        ];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="content-blocks-' . date('Y-m-d') . '.json"');
        echo json_encode($export, JSON_PRETTY_PRINT);
    }

    public function import(Request $request): void
    {
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please select a valid JSON file to import.');
            Response::redirect('/admin/content');
            return;
        }

        $fileContent = file_get_contents($_FILES['import_file']['tmp_name']);
        $data = json_decode($fileContent, true);

        if (!$data || !isset($data['blocks']) || !is_array($data['blocks'])) {
            Session::flash('error', 'Invalid import file format.');
            Response::redirect('/admin/content');
            return;
        }

        $pdo = db();
        $imported = 0;
        $skipped = 0;

        // Check if new columns exist (backwards compatibility)
        $hasNewColumns = $this->columnExists($pdo, 'content_blocks', 'position');
        $maxPos = 0;
        
        if ($hasNewColumns) {
            $stmt = $pdo->query("SELECT MAX(position) FROM content_blocks");
            $maxPos = (int)$stmt->fetchColumn();
        }

        foreach ($data['blocks'] as $block) {
            $slug = $block['slug'] ?? '';
            if (empty($slug)) {
                $skipped++;
                continue;
            }

            // Check if slug exists
            $stmt = $pdo->prepare("SELECT id FROM content_blocks WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                // Make slug unique
                $slug = $slug . '-imported-' . time();
            }

            $maxPos++;

            if ($hasNewColumns) {
                $stmt = $pdo->prepare("
                    INSERT INTO content_blocks
                    (name, slug, content, type, category, cache_ttl, description, is_active, position, version, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
                ");
                $stmt->execute([
                    $block['name'] ?? 'Imported Block',
                    $slug,
                    $block['content'] ?? '',
                    $block['type'] ?? 'html',
                    $block['category'] ?? 'uncategorized',
                    $block['cache_ttl'] ?? 0,
                    $block['description'] ?? '',
                    0, // imported blocks are inactive by default
                    $maxPos
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO content_blocks
                    (name, slug, content, type, description, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $block['name'] ?? 'Imported Block',
                    $slug,
                    $block['content'] ?? '',
                    $block['type'] ?? 'html',
                    $block['description'] ?? '',
                    0
                ]);
            }

            $imported++;
        }

        if ($imported > 0) {
            Session::flash('success', "Imported {$imported} content block(s) successfully." . ($skipped > 0 ? " Skipped {$skipped} invalid entries." : ''));
        } else {
            Session::flash('error', 'No content blocks were imported. Check the file format.');
        }

        Response::redirect('/admin/content');
    }

    private function findBlock(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM content_blocks WHERE id = ?");
        $stmt->execute([$id]);
        $block = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $block ?: null;
    }

    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    private function renderBlockContent(array $block): string
    {
        $content = $block['content'] ?? '';
        $type = $block['type'] ?? 'html';

        switch ($type) {
            case 'markdown':
                // Basic markdown rendering (simplified)
                $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
                $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
                $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
                $content = preg_replace('/`(.*?)`/', '<code>$1</code>', $content);
                $content = nl2br($content);
                return '<div class="markdown-preview">' . $content . '</div>';

            case 'json':
                return '<pre class="json-preview"><code>' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</code></pre>';

            case 'text':
                return '<div class="text-preview">' . nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) . '</div>';

            case 'shortcode':
                return '<div class="shortcode-preview"><code>' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</code></div>';

            case 'html':
            default:
                // For HTML, we show it as-is in preview (sanitized for display)
                return '<div class="html-preview">' . $content . '</div>';
        }
    }

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
}
