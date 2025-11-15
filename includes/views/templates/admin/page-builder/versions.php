<?php 
require_once __DIR__.'/../../../../includes/helpers.php';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Versions</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="version-management-container">
        <h1>Content Versions</h1>
        
        <div class="version-actions">
            <a href="/admin/page-builder/<?= $contentId ?>" class="btn">Back to Editor</a>
            <form method="post" action="/admin/page-builder/<?= $contentId ?>/bulk-delete-versions" class="bulk-actions">
                <button type="submit" class="btn danger" onclick="
return confirm('Delete selected versions?')">Delete Selected</button>
        </div>

        <table class="version-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>Version</th>
                    <th>Created</th>
                    <th>Author</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Versions will be loaded dynamically via version-management.js -->
                <tr id="loading-indicator">
                    <td colspan="6" class="text-center">
                        <div class="loading-spinner">Loading versions...</div>
                    </td>
                </tr>
            </tbody>
        </table>
            </form>

        <script src="/assets/js/version-management.js"></script>
        <script>
            document.getElementById('select-all').addEventListener('change', function() {
                document.querySelectorAll('input[name="versions[]"]').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        </script>
    </div>
</body>
</html>
