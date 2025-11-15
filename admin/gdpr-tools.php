<?php
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../includes/usermanager.php';
require_once __DIR__ . '/../includes/privacy/gdprlog.php';
// session boot (admin)
require_once __DIR__ . '/../core/session_boot.php';

csrf_boot('admin');

// Start session and check admin status
cms_session_start('admin');
if (empty($_SESSION['is_admin'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied. Admins only.');
}

$userManager = new UserManager();
$message = '';
$results = [];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $action = $_POST['action'] ?? '';
    $email = $_POST['email'] ?? '';
    
    try {
        switch ($action) {
            case 'export':
                $results = $userManager->exportUserDataByEmail($email);
                $message = "Data exported for $email";
                break;
                
            case 'anonymize':
                $success = $userManager->anonymizeUserByEmail($email);
                $message = $success 
                    ? "User $email anonymized successfully" 
                    : "Failed to anonymize user $email";
                break;
                
            default:
                $message = "Invalid action";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        error_log("GDPR Tools Error: " . $e->getMessage());
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GDPR Management Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">GDPR Management Tools</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <div class="card mb-4">
            <div class="card-header">User Actions</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field(); ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">User Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="btn-group" role="group">
                        <button type="submit" name="action" value="export" class="btn btn-primary">
                            Export User Data
                        </button>
                        <button type="submit" name="action" value="anonymize" class="btn btn-danger">
                            Anonymize User
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (!empty($results)): ?>
            <div class="card">
                <div class="card-header">Export Results</div>
                <div class="card-body">
                    <pre><?= htmlspecialchars(json_encode($results, JSON_PRETTY_PRINT)) ?></pre>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
