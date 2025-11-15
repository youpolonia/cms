<?php
/**
 * Version Restoration View
 */
require_once __DIR__ . '/../../../core/versioncontrol.php';
require_once __DIR__ . '/../../core/csrf.php';

$contentType = $_GET['content_type'] ?? '';
$contentId = $_GET['content_id'] ?? '';
$versionId = $_GET['version_id'] ?? '';

if (empty($contentType) || empty($contentId) || empty($versionId)) {
    die('Invalid parameters');
}

$version = VersionControl::getVersion($contentType, $contentId, $versionId);
if (!$version) {
    die('Version not found');
}

// Handle restore form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $currentContent = []; // TODO: Get current content from CMS
    $patch = VersionControl::createPatch($contentType, $contentId, $versionId, 'current');
    $restoredContent = VersionControl::applyPatch($currentContent, $patch);
    
    // TODO: Save restored content back to CMS
    $message = 'Version restored successfully';
}

?><div class="version-restore">
    <h2>Restore Version <?= htmlspecialchars($versionId) ?></h2>
    
    <?php if (isset($message)): ?>
        <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif;  ?>
    <div class="version-preview">
        <h3>Preview</h3>
        <pre><?= htmlspecialchars(json_encode($version['data'], JSON_PRETTY_PRINT)) ?></pre>
    </div>

    <form method="post" class="restore-form">
        <?= csrf_field();  ?>
        <div class="form-group">
            <label for="restore-comment">Restore Comment:</label>
            <textarea id="restore-comment" name="comment"
 required></textarea>
?>        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-confirm">Confirm Restore</button>
            <a href="?page=version_list&content_type=<?= urlencode($contentType) ?>&content_id=<?= urlencode($contentId) ?>" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<style>
.version-restore {
    max-width: 800px;
    margin: 0 auto;
}
.version-preview {
    background: #f5f5f5;
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    max-height: 400px;
    overflow-y: auto;
}
.restore-form {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.form-group textarea {
    width: 100%;
    min-height: 100px;
    padding: 8px;
}
.form-actions {
    margin-top: 20px;
    text-align: right;
}
.btn-confirm {
    background: #d9534f;
    color: white;
    padding: 8px 15px;
    border: none;
    cursor: pointer;
}
.btn-cancel {
    padding: 8px 15px;
    margin-left: 10px;
}
.alert {
    padding: 10px;
    margin-bottom: 20px;
}
.alert.success {
    background: #dff0d8;
    color: #3c763d;
}
</style>
