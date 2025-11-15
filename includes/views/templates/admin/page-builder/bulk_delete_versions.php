<?php 
require_once __DIR__.'/../../../../includes/helpers.php';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Version Cleanup</title>
    <link rel="stylesheet" href="/assets/css/diff.css">
</head>
<body>
    <div class="version-comparison-container">
        <h1>Bulk Version Cleanup</h1>
        
        <a href="/admin/page-builder/<?= $contentId ?>/versions" class="btn">Back to Versions</a>
        <form method="post" action="/admin/page-builder/<?= $contentId ?>/bulk-delete-versions">
            <div class="version-list">
                <?php foreach($versions as $version): ?>
                    <div class="version-item">
                        <input type="checkbox" name="versions[]" value="<?= $version['id'] ?>" id="version_<?= $version['id'] ?>">
                        <label for="version_<?= $version['id'] ?>">
                            Version #<?= $version['id'] ?> - <?= formatDate($version['created_at'])  ?>                            <?php if ($version['is_autosave']): ?> (Autosave)<?php endif;  ?>
                        </label>
                    </div>
                <?php endforeach;  ?>
            </div>

            <div class="action-buttons">
                <button type="submit" class="btn btn-danger" onclick="
return confirm('Delete selected versions? A backup will be created first.')">
                    Delete Selected Versions
                </button>
            </div>
        </form>
    </div>
</body>
</html>
