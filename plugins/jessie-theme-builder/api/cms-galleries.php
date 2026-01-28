<?php
/**
 * CMS Galleries API
 * Returns list of CMS galleries with their images for JTB Gallery module integration
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

try {
    $db = \core\Database::connection();

    // Get gallery ID if specified (for single gallery fetch)
    $galleryId = isset($_GET['gallery_id']) ? (int)$_GET['gallery_id'] : null;

    if ($galleryId) {
        // Fetch single gallery with images
        $stmt = $db->prepare("
            SELECT id, name, slug, description, is_public,
                   (SELECT COUNT(*) FROM gallery_images WHERE gallery_id = g.id) as image_count
            FROM galleries g
            WHERE id = ? AND is_public = 1
        ");
        $stmt->execute([$galleryId]);
        $gallery = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$gallery) {
            echo json_encode(['success' => false, 'error' => 'Gallery not found']);
            exit;
        }

        // Fetch images for this gallery
        $stmt = $db->prepare("
            SELECT id, filename, title, caption, sort_order
            FROM gallery_images
            WHERE gallery_id = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$galleryId]);
        $images = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Format images with full URL
        $gallery['images'] = array_map(function($img) {
            return [
                'id' => (int)$img['id'],
                'url' => '/uploads/media/' . $img['filename'],
                'title' => $img['title'] ?? '',
                'caption' => $img['caption'] ?? '',
                'alt' => $img['title'] ?? 'Gallery image'
            ];
        }, $images);

        echo json_encode(['success' => true, 'data' => $gallery]);

    } else {
        // Fetch all public galleries (for dropdown)
        $stmt = $db->query("
            SELECT g.id, g.name, g.slug,
                   (SELECT COUNT(*) FROM gallery_images WHERE gallery_id = g.id) as image_count
            FROM galleries g
            WHERE g.is_public = 1
            ORDER BY g.name ASC
        ");
        $galleries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Format for select options
        $options = array_map(function($g) {
            return [
                'value' => (string)$g['id'],
                'label' => $g['name'] . ' (' . $g['image_count'] . ' images)',
                'image_count' => (int)$g['image_count']
            ];
        }, $galleries);

        echo json_encode(['success' => true, 'data' => $options]);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
