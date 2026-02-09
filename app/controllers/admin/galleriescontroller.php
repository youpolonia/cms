<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class GalleriesController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $stmt = $pdo->query("
            SELECT g.*,
                   (SELECT COUNT(*) FROM gallery_images WHERE gallery_id = g.id) as image_count
            FROM galleries g
            ORDER BY g.sort_order ASC, g.name ASC
        ");
        $galleries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/galleries/index', [
            'galleries' => $galleries,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(Request $request): void
    {
        render('admin/galleries/form', [
            'gallery' => null,
            'action' => 'create'
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim($request->post('name', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($name);
        $description = trim($request->post('description', ''));
        $isPublic = $request->post('is_public') ? 1 : 0;

        if (empty($name)) {
            Session::flash('error', 'Name is required.');
            Response::redirect('/admin/galleries/create');
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM galleries WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A gallery with this slug already exists.');
            Response::redirect('/admin/galleries/create');
        }

        $displayTemplate = trim($request->post('display_template', 'grid'));
        $validTemplates = ['grid', 'masonry', 'mosaic', 'carousel', 'justified'];
        if (!in_array($displayTemplate, $validTemplates)) {
            $displayTemplate = 'grid';
        }

        $stmt = $pdo->prepare("INSERT INTO galleries (name, slug, description, is_public, display_template, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $slug, $description, $isPublic, $displayTemplate]);

        $galleryId = $pdo->lastInsertId();

        Session::flash('success', 'Gallery created successfully.');
        Response::redirect("/admin/galleries/{$galleryId}/images");
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $gallery = $this->findGallery($id);

        if (!$gallery) {
            Session::flash('error', 'Gallery not found.');
            Response::redirect('/admin/galleries');
        }

        render('admin/galleries/form', [
            'gallery' => $gallery,
            'action' => 'edit'
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $gallery = $this->findGallery($id);

        if (!$gallery) {
            Session::flash('error', 'Gallery not found.');
            Response::redirect('/admin/galleries');
        }

        $name = trim($request->post('name', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($name);
        $description = trim($request->post('description', ''));
        $isPublic = $request->post('is_public') ? 1 : 0;

        if (empty($name)) {
            Session::flash('error', 'Name is required.');
            Response::redirect("/admin/galleries/{$id}/edit");
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM galleries WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A gallery with this slug already exists.');
            Response::redirect("/admin/galleries/{$id}/edit");
        }

        $displayTemplate = trim($request->post('display_template', 'grid'));
        $validTemplates = ['grid', 'masonry', 'mosaic', 'carousel', 'justified'];
        if (!in_array($displayTemplate, $validTemplates)) {
            $displayTemplate = 'grid';
        }

        $stmt = $pdo->prepare("UPDATE galleries SET name = ?, slug = ?, description = ?, is_public = ?, display_template = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $description, $isPublic, $displayTemplate, $id]);

        Session::flash('success', 'Gallery updated successfully.');
        Response::redirect('/admin/galleries');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM galleries WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Gallery deleted successfully.');
        Response::redirect('/admin/galleries');
    }

    public function images(Request $request): void
    {
        $id = (int)$request->param('id');
        $gallery = $this->findGallery($id);

        if (!$gallery) {
            Session::flash('error', 'Gallery not found.');
            Response::redirect('/admin/galleries');
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE gallery_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/galleries/images', [
            'gallery' => $gallery,
            'images' => $images,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function deleteImage(Request $request): void
    {
        $galleryId = (int)$request->param('id');
        $imageId = (int)$request->param('imageId');

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ? AND gallery_id = ?");
        $stmt->execute([$imageId, $galleryId]);

        Session::flash('success', 'Image removed from gallery.');
        Response::redirect("/admin/galleries/{$galleryId}/images");
    }

    public function upload(Request $request): void
    {
        $galleryId = (int)$request->param('id');
        $gallery = $this->findGallery($galleryId);

        if (!$gallery) {
            Session::flash('error', 'Gallery not found.');
            Response::redirect('/admin/galleries');
        }

        if (empty($_FILES['images']) || !is_array($_FILES['images']['name'])) {
            Session::flash('error', 'No images selected.');
            Response::redirect("/admin/galleries/{$galleryId}/images");
        }

        $uploadDir = \CMS_ROOT . '/public/uploads/media/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        $uploaded = 0;
        $errors = [];

        $pdo = db();

        // Get current max sort_order
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM gallery_images WHERE gallery_id = ?");
        $stmt->execute([$galleryId]);
        $sortOrder = (int)$stmt->fetchColumn();

        $fileCount = count($_FILES['images']['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            $name = $_FILES['images']['name'][$i];
            $tmpName = $_FILES['images']['tmp_name'][$i];
            $size = $_FILES['images']['size'][$i];
            $error = $_FILES['images']['error'][$i];

            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = "Error uploading {$name}";
                continue;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = "{$name}: Invalid file type";
                continue;
            }

            if ($size > $maxSize) {
                $errors[] = "{$name}: File too large (max 10MB)";
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $filename = 'gallery_' . $galleryId . '_' . time() . '_' . $i . '.' . $ext;

            if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                $sortOrder++;
                $stmt = $pdo->prepare("
                    INSERT INTO gallery_images (gallery_id, filename, original_name, title, sort_order, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$galleryId, $filename, $name, pathinfo($name, PATHINFO_FILENAME), $sortOrder]);
                $uploaded++;
            } else {
                $errors[] = "{$name}: Failed to save file";
            }
        }

        if ($uploaded > 0) {
            Session::flash('success', "{$uploaded} image(s) uploaded successfully.");
        }
        if (!empty($errors)) {
            Session::flash('error', implode(', ', $errors));
        }

        Response::redirect("/admin/galleries/{$galleryId}/images");
    }

    public function reorder(Request $request): void
    {
        $galleryId = (int)$request->param('id');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['order']) || !is_array($data['order'])) {
            Response::json(['success' => false, 'error' => 'Invalid order data']);
        }

        $pdo = db();

        foreach ($data['order'] as $index => $imageId) {
            $stmt = $pdo->prepare("UPDATE gallery_images SET sort_order = ? WHERE id = ? AND gallery_id = ?");
            $stmt->execute([$index, (int)$imageId, $galleryId]);
        }

        Response::json(['success' => true]);
    }

    public function updateImageTitle(Request $request): void
    {
        $galleryId = (int)$request->param('id');
        $imageId = (int)$request->param('imageId');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $title = trim($data['title'] ?? '');

        $pdo = db();
        $stmt = $pdo->prepare("UPDATE gallery_images SET title = ? WHERE id = ? AND gallery_id = ?");
        $stmt->execute([$title, $imageId, $galleryId]);

        Response::json(['success' => true]);
    }

    private function findGallery(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM galleries WHERE id = ?");
        $stmt->execute([$id]);
        $gallery = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $gallery ?: null;
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
