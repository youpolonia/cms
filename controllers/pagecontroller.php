<?php
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

namespace Controllers;

class PageController
{
    public function show(string $slug): void
    {
        // Load and display page content
        require_once __DIR__ . '/../views/pages/show.php';
    }

    public function edit(string $slug): void
    {
        // Load page editor
        require_once __DIR__ . '/../views/pages/edit.php';
    }

    public function save(): void
    {
        csrf_validate_or_403();
        
        // Validate slug format (a-z, 0-9, hyphens only)
        if (!preg_match('/^[a-z0-9-]+$/', $_POST['slug'])) {
            $_SESSION['error'] = 'Slug can only contain lowercase letters, numbers and hyphens';
            header('Location: /pages/edit/' . ($_POST['existing_slug'] ?? ''));
            exit;
        }

        // Handle form submission and save page
        header('Location: /pages/' . $_POST['slug']);
        exit;
    }
}
