<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../../core/csrf.php';

class ContentController {
    /**
     * Show form to edit/create content item
     */
    public function edit($id = null) {
        $contentItem = $id ? $this->getContentItem($id) : new ContentItem();
        
        // Check permissions
        if (!$this->canEditContent($contentItem)) {
            return $this->forbidden();
        }

        require_once __DIR__ . '/../views/content/edit_form.php';
    }

    /**
     * Save content item
     */
    public function save() {
        csrf_validate_or_403();

        $id = $_POST['id'] ?? null;
        $contentItem = $id ? $this->getContentItem($id) : new ContentItem();

        // Validate fields
        $allowedLevels = ['public', 'private', 'admin'];
        if (!in_array($_POST['access_level'], $allowedLevels)) {
            return $this->validationError('Invalid access level');
        }

        $allowedStatuses = ['draft', 'published', 'archived'];
        if (!in_array($_POST['status'], $allowedStatuses)) {
            return $this->validationError('Invalid status');
        }

        // Check permissions
        if (!$this->canEditContent($contentItem)) {
            return $this->forbidden();
        }

        // Save logic here
        $contentItem->title = $_POST['title'];
        $contentItem->content = $_POST['content'];
        $contentItem->access_level = $_POST['access_level'];
        $contentItem->status = $_POST['status'];
        $contentItem->author_id = $_POST['author_id'] ?? null;
        $contentItem->save();

        $this->redirect('/admin/content');
    }

    private function getContentItem($id) {
        // Implementation to fetch content item from DB
        // Should require_once the new fields: status, author_id, created_at, updated_at
    }

    private function canEditContent($contentItem) {
        // Implementation to check edit permissions
        // Must check if user can set admin access level
        return true; // Simplified for example
    }

    private function forbidden() {
        http_response_code(403);
        exit('Forbidden');
    }

    private function validationError($message) {
        http_response_code(400);
        exit($message);
    }

    private function redirect($url) {
        header("Location: $url");
        exit;
    }
}
