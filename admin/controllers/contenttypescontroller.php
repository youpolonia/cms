<?php
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__.'/../../core/database.php';
require_once __DIR__ . '/../../includes/security.php';

class ContentTypesController {
    private static function validateSession() {
        if (!Security::validateAdminSession()) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public static function list() {
        self::validateSession();
        $db = \core\Database::connection();
        
        $stmt = $db->query("SELECT * FROM content_types ORDER BY name ASC");
        $contentTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../views/content_types/list.php';
    }

    public static function showForm($id = null) {
        self::validateSession();
        $db = \core\Database::connection();
        
        $contentType = null;
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM content_types WHERE id = ?");
            $stmt->execute([$id]);
            $contentType = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        require_once __DIR__ . '/../views/content_types/form.php';
    }

    public static function save() {
        csrf_validate_or_403();
        self::validateSession();
        $db = \core\Database::connection();
        
        if (!Security::validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /admin/content/types.php');
            exit;
        }

        $data = [
            'name' => trim($_POST['name']),
            'slug' => trim($_POST['slug']),
            'description' => trim($_POST['description'] ?? '')
        ];

        // Validate
        if (empty($data['name']) || empty($data['slug'])) {
            $_SESSION['error'] = 'Name and slug are required';
            header('Location: /admin/content/types.php?action=form&id='.($_POST['id'] ?? ''));
            exit;
        }

        try {
            if (!empty($_POST['id'])) {
                // Update
                $stmt = $db->prepare("
                    UPDATE content_types 
                    SET name = ?, slug = ?, description = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['name'],
                    $data['slug'],
                    $data['description'],
                    $_POST['id']
                ]);
                $_SESSION['success'] = 'Content type updated successfully';
            } else {
                // Create
                $stmt = $db->prepare("
                    INSERT INTO content_types (name, slug, description)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $data['name'],
                    $data['slug'],
                    $data['description']
                ]);
                $_SESSION['success'] = 'Content type created successfully';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $_SESSION['error'] = 'Operation failed';
        }

        header('Location: /admin/content/types.php');
        exit;
    }

    public static function delete() {
        csrf_validate_or_403();
        self::validateSession();
        
        if (!Security::validateCSRFToken($_GET['csrf_token'])) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /admin/content/types.php');
            exit;
        }

        if (!empty($_GET['id'])) {
            $db = \core\Database::connection();
            try {
                $stmt = $db->prepare("DELETE FROM content_types WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $_SESSION['success'] = 'Content type deleted successfully';
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $_SESSION['error'] = 'Operation failed';
            }
        }

        header('Location: /admin/content/types.php');
        exit;
    }
}
