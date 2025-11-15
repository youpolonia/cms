<?php
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/security.php';
verifyAdminAccess();

require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../../core/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }
    // Verify CSRF token
    if (!isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    // Basic validation
    $errors = [];
    if (empty($_POST['title'])) {
        $errors[] = 'Title is required';
    }

    // If no errors, save widget
    if (empty($errors)) {
        try {
            $db = \core\Database::connection();
            $title = $_POST['title'];
            $status = $_POST['status'] ?? 0;
            $css = $_POST['css'] ?? '';
            
            if (isset($_POST['id'])) {
                // Update existing widget
                $stmt = $db->prepare("UPDATE widgets SET
                    title = :title,
                    status = :status,
                    css = :css,
                    updated_at = NOW()
                    WHERE id = :id");
                $stmt->execute([
                    ':title' => $title,
                    ':status' => $status,
                    ':css' => $css,
                    ':id' => $_POST['id']
                ]);
                $widgetId = $_POST['id'];
            } else {
                // Create new widget
                $stmt = $db->prepare("INSERT INTO widgets
                    (title, status, css, created_at, updated_at)
                    VALUES (:title, :status, :css, NOW(), NOW())");
                $stmt->execute([
                    ':title' => $title,
                    ':status' => $status,
                    ':css' => $css
                ]);
                $widgetId = $db->lastInsertId();
            }

            header('Location: ?id='.$widgetId.'&success=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            error_log('Widget save error: ' . $e->getMessage());
        }
    }
}

// Get widget data if editing
$widget = [];
if (isset($_GET['id'])) {
    try {
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM widgets WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $widget = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Widget fetch error: ' . $e->getMessage());
    }
}

// Display success message if redirected after save
if (isset($_GET['success'])) {
    echo '
<div class="alert alert-success">Widget saved successfully</div>';
}

?><div class="container">
    <h1>Widget Management</h1>
    
    <div class="alert alert-info">
        <?= count($widgets) ?> widgets available
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($widgets as $widget): ?>
                <tr>
                    <td><?= htmlspecialchars($widget['id']) ?></td>
                    <td><?= htmlspecialchars($widget['name']) ?></td>
                    <td><?= htmlspecialchars($widget['description'] ?? 'N/A') ?></td>
                    <td><span class="badge bg-<?= $widget['active'] ? 'success' : 'secondary' ?>">
                        <?= $widget['active'] ? 'Active' : 'Inactive' 
?>                    </span></td>
                    <td>
                        <a href="?action=settings&id=<?= $widget['id'] ?>" class="btn btn-sm btn-primary">Settings</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php';
