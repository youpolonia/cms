<?php require_once __DIR__.'/../../../includes/views/templates/admin/header.php'; 
?><div class="version-compare-container">
    <h1>Comparing Versions for Page #<?= $contentId ?></h1>
    <div class="version-nav">
        <a href="/admin/page-builder/<?= $contentId ?>/versions" class="btn btn-secondary">
            &larr; Back to Versions
        </a>
        <div class="version-selection">
            <span>Comparing:</span>
            <strong>Version <?= $versionA['version_number'] ?></strong> (<?= date('Y-m-d H:i', strtotime($versionA['created_at'])) ?>)
            <span>vs</span>
            <strong>Version <?= $versionB['version_number'] ?></strong> (<?= date('Y-m-d H:i', strtotime($versionB['created_at'])) ?>)
        </div>
    </div>

    <div class="diff-container">
        <div class="diff-side">
            <h3>Version <?= $versionA['version_number'] ?></h3>
            <div class="diff-content">
                <?= htmlspecialchars($versionA['content'])  ?>
            </div>
        </div>
        
        <div class="diff-side">
            <h3>Version <?= $versionB['version_number'] ?></h3>
            <div class="diff-content">
                <?= htmlspecialchars($versionB['content'])  ?>
            </div>
        </div>
    </div>

    <div class="version-actions">
        <form action="/admin/page-builder/<?= $contentId ?>/restore/<?= $versionA['id'] ?>" method="POST">
            <button type="submit" class="btn btn-warning">Restore Version <?= $versionA['version_number'] ?></button>
        </form>
        <form action="/admin/page-builder/<?= $contentId ?>/restore/<?= $versionB['id'] ?>" method="POST">
            <button type="submit" class="btn btn-warning">Restore Version <?= $versionB['version_number'] ?></button>
        </form>
    </div>
</div>

<?php require_once __DIR__.'/../../../includes/views/templates/admin/footer.php';
