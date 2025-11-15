<?php
/**
 * Version Comparison View
 */
$title = 'Version Comparison';
$basePath = dirname(dirname(__DIR__));
// Secure include: resolve $basePath and load init.php within project
$__projectBase = realpath(dirname(__DIR__, 3));
$__base        = is_string($basePath) ? realpath($basePath) : false;
$__candidate   = ($__base !== false) ? realpath($__base . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'init.php') : false;
$__okPath      = ($__candidate !== false) && (strpos($__candidate, $__projectBase . DIRECTORY_SEPARATOR) === 0);
$__okExt       = ($__candidate !== false) && (pathinfo($__candidate, PATHINFO_EXTENSION) === 'php');
if (!$__okPath || !$__okExt) {
    http_response_code(400);
    echo '<div class="alert alert-error"><p>Invalid include base path.</p></div>';
} else {
    require_once $__candidate;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/assets/css/diff.css">
    <script src="/assets/js/version-comparison.js" defer></script>
</head>
<body>
    <div class="version-comparison-container">
        <header class="comparison-header">
            <h1>Version Comparison</h1>
            <div class="version-selectors">
                <select id="version1" class="version-select">
                    <?php foreach ($versions as $version): ?>                        <option value="<?= $version['id'] ?>" <?= $version['id'] == $version1 ? 'selected' : '' ?>>
                            Version <?= $version['id'] ?> - <?= $version['created_at']  ?>
                        </option>
                    <?php endforeach  ?>
                </select>
                <span class="compare-icon">↔</span>
                <select id="version2" class="version-select">
                    <?php foreach ($versions as $version): ?>                        <option value="<?= $version['id'] ?>" <?= $version['id'] == $version2 ? 'selected' : '' ?>>
                            Version <?= $version['id'] ?> - <?= $version['created_at']  ?>
                        </option>
                    <?php endforeach  ?>
                </select>
                <button id="compare-btn" class="btn btn-primary">Compare</button>
            </div>
        </header>

        <div class="comparison-stats">
            <div class="stat-item">
                <span class="stat-label">Added:</span>
                <span class="stat-value added"><?= $stats['added'] ?? 0 ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Removed:</span>
                <span class="stat-value removed"><?= $stats['removed'] ?? 0 ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Changed:</span>
                <span class="stat-value changed"><?= $stats['changed'] ?? 0 ?></span>
            </div>
        </div>

        <div class="diff-container">
            <div class="diff-panel left-panel">
                <div class="panel-header">Version <?= $version1 ?></div>
                <div class="diff-content"><?= $diff1 ?></div>
            </div>
            <div class="diff-panel right-panel">
                <div class="panel-header">Version <?= $version2 ?></div>
                <div class="diff-content"><?= $diff2 ?></div>
            </div>
        </div>

        <div class="diff-navigation">
            <button id="prev-diff" class="btn btn-secondary" disabled>Previous Change</button>
            <button id="next-diff" class="btn btn-secondary" disabled>Next Change</button>
        </div>
    </div>
</body>
</html>
