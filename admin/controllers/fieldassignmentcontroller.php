<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../../core/csrf.php';

class FieldAssignmentController {
    public function saveAssignments(): void {
        csrf_validate_or_403();
        // Verify CSRF token
        if (!CsrfUtils::verifyPostToken('custom_fields_token')) {
            http_response_code(403);
            exit('Invalid CSRF token');
        }

        // Get content type ID and field IDs from POST
        $contentTypeId = (int)($_POST['content_type_id'] ?? 0);
        $fieldIds = array_map('intval', $_POST['field_ids'] ?? []);

        try {
            // Get database connection
            $db = \core\Database::connection();

            // Begin transaction
            $db->beginTransaction();

            // Clear existing assignments
            $db->prepare('DELETE FROM content_type_fields WHERE content_type_id = ?')
               ->execute([$contentTypeId]);

            // Insert new assignments
            $stmt = $db->prepare('INSERT INTO content_type_fields (content_type_id, field_id) VALUES (?, ?)');
            foreach ($fieldIds as $fieldId) {
                $stmt->execute([$contentTypeId, $fieldId]);
            }

            // Commit transaction
            $db->commit();

            // Redirect with success message
            $_SESSION['flash_message'] = 'Field assignments saved successfully';
            header('Location: /admin/custom-fields/assign?content_type_id=' . $contentTypeId);
            exit;
        } catch (PDOException $e) {
            // Rollback on error
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Field assignment error: ' . $e->getMessage());
            $_SESSION['error_message'] = 'Failed to save field assignments';
            header('Location: /admin/custom-fields/assign?content_type_id=' . $contentTypeId);
            exit;
        }
    }
}
