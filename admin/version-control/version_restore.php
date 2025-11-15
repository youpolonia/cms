<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../core/database.php';
require_once __DIR__.'/../../services/versioncomparator.php';
require_once __DIR__.'/../../includes/database/middleware/tenantisolation.php';
require_once __DIR__ . '/../../core/csrf.php';

$db = new Database(); 
$versionComparator = new VersionComparator(); 
$versionId = $_GET['version_id'] ?? 0; 
$tenantId = $_SESSION['current_tenant'] ?? null; 
if (!$tenantId) {
    header('HTTP/1.1 403 Forbidden');
    die('Invalid tenant context');
}

// Get version details with tenant isolation
$version = $db->query("SELECT * FROM content_versions WHERE id = ? AND tenant_id = ?",
    [$versionId, $tenantId]
)->fetch();

if (!$version) {
    header('HTTP/1.1 404 Not Found');
    die('Version not found or not accessible');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    csrf_validate_or_403();
    $success = $versionComparator->restoreVersion($versionId, $_SESSION['user_id']);
    if ($success) {
        header("Location: version_list.php?content_id=" . $version['content_id']);
        exit;
    } else {
        $error = "Failed to restore version";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restore Version v<?= htmlspecialchars($version['version_number']) ?></title>

    <link rel="stylesheet" href="/admin/css/version-control.css">
</head>
<body>
    <div class="container">
        <h1>Restore Version v<?= htmlspecialchars($version['version_number']) ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="confirmation">
            <p>Are you sure you want to restore this version? This will create a new version based on v<?= htmlspecialchars($version['version_number']) ?>.</p>

            <form method="POST">
                <?= csrf_field(); ?>
                <div class="form-group">
                    <label for="notes">Restoration Notes:</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <a href="version_list.php?content_id=<?= $version['content_id'] ?>" class="btn cancel">Cancel</a>

                    <button type="submit" class="btn confirm">Confirm Restoration</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
