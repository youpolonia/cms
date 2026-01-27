<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class CategoriesController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $stmt = $pdo->query("
            SELECT c.*, p.name as parent_name,
                   (SELECT COUNT(*) FROM articles WHERE category_id = c.id) as article_count
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.id
            ORDER BY c.sort_order ASC, c.name ASC
        ");
        $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/categories/index', [
            'categories' => $categories,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(Request $request): void
    {
        $parents = $this->getParentCategories();

        render('admin/categories/form', [
            'category' => null,
            'parents' => $parents,
            'action' => 'create'
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim($request->post('name', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($name);
        $description = trim($request->post('description', ''));
        $parent_id = (int)$request->post('parent_id') ?: null;
        $sort_order = (int)$request->post('sort_order', '0');

        if (empty($name)) {
            Session::flash('error', 'Name is required.');
            Response::redirect('/admin/categories/create');
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A category with this slug already exists.');
            Response::redirect('/admin/categories/create');
        }

        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, parent_id, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$name, $slug, $description, $parent_id, $sort_order]);

        Session::flash('success', 'Category created successfully.');
        Response::redirect('/admin/categories');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $category = $this->findCategory($id);

        if (!$category) {
            Session::flash('error', 'Category not found.');
            Response::redirect('/admin/categories');
        }

        $parents = $this->getParentCategories($id);

        render('admin/categories/form', [
            'category' => $category,
            'parents' => $parents,
            'action' => 'edit'
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $category = $this->findCategory($id);

        if (!$category) {
            Session::flash('error', 'Category not found.');
            Response::redirect('/admin/categories');
        }

        $name = trim($request->post('name', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($name);
        $description = trim($request->post('description', ''));
        $parent_id = (int)$request->post('parent_id') ?: null;
        $sort_order = (int)$request->post('sort_order', '0');

        if (empty($name)) {
            Session::flash('error', 'Name is required.');
            Response::redirect("/admin/categories/{$id}/edit");
        }

        // Prevent setting self as parent
        if ($parent_id === $id) {
            Session::flash('error', 'Category cannot be its own parent.');
            Response::redirect("/admin/categories/{$id}/edit");
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A category with this slug already exists.');
            Response::redirect("/admin/categories/{$id}/edit");
        }

        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, parent_id = ?, sort_order = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$name, $slug, $description, $parent_id, $sort_order, $id]);

        Session::flash('success', 'Category updated successfully.');
        Response::redirect('/admin/categories');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();

        // Check if category has articles
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            Session::flash('error', 'Cannot delete category with articles. Move articles first.');
            Response::redirect('/admin/categories');
        }

        // Update children to have no parent
        $stmt = $pdo->prepare("UPDATE categories SET parent_id = NULL WHERE parent_id = ?");
        $stmt->execute([$id]);

        // Delete category
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Category deleted successfully.');
        Response::redirect('/admin/categories');
    }

    private function findCategory(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $category ?: null;
    }

    private function getParentCategories(?int $excludeId = null): array
    {
        $pdo = db();
        $sql = "SELECT id, name FROM categories";
        if ($excludeId) {
            $sql .= " WHERE id != ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$excludeId]);
        } else {
            $stmt = $pdo->query($sql);
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
