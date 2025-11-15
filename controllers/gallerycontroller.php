<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/database.php';

class GalleryController {
    protected $db;
    public function __construct() { $this->db = \core\Database::connection(); }
    public function index() {
        try {
            $db = $this->db;
            $stmt = $db->prepare("SELECT image_path AS src, title FROM gallery_items WHERE is_active = 1");
            $stmt->execute();
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($images)) {
                $images = []; // Return empty array if no results
                error_log("No active gallery items found");
            }
        } catch (PDOException $e) {
            error_log("Gallery database error: " . $e->getMessage());
            $images = []; // Return empty array on error
        }

        $contentView = BASE_PATH . '/views/gallery/index.php';
        require_once BASE_PATH . '/views/layouts/main.php';
    }
}
