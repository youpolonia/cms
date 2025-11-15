<?php 
require_once __DIR__.'/../../../../includes/helpers.php';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Version Comparison</title>
    <link rel="stylesheet" href="/assets/css/diff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
</head>
<body>
    <div class="version-comparison-container">
        <h1>Version Comparison</h1>
        
        <div class="version-nav">
            <a href="/admin/page-builder/<?= $contentId ?>/versions" class="btn">Back to Versions</a>
            <div class="version-selectors">
                <select id="versionA-select" class="version-select">
                    <?php foreach($allVersions as $v): ?>                        <option value="<?= $v['id'] ?>" <?= $v['id'] == $versionA['id'] ? 'selected' : '' ?>>
                            Version #<?= $v['id'] ?> - <?= formatDate($v['created_at'])  ?>
                        </option>
                    <?php endforeach;  ?>
                </select>
                <span>vs</span>
                <select id="versionB-select" class="version-select">
                    <?php foreach($allVersions as $v): ?>                        <option value="<?= $v['id'] ?>" <?= $v['id'] == $versionB['id'] ? 'selected' : '' ?>>
                            Version #<?= $v['id'] ?> - <?= formatDate($v['created_at'])  ?>
                        </option>
                    <?php endforeach;  ?>
                </select>
                <button id="compare-btn" class="btn">Compare</button>
            </div>
        </div>

        <div class="diff-container">
            <div class="version-panel">
                <h3>Version #<?= $versionA['id'] ?> <small>(<?= formatDate($versionA['created_at']) ?>)</small></h3>
                <div class="version-content" id="versionA-content">
                    <?= htmlspecialchars($versionA['content'])  ?>
                </div>
            </div>
            
            <div class="version-panel">
                <h3>Version #<?= $versionB['id'] ?> <small>(<?= formatDate($versionB['created_at']) ?>)</small></h3>
                <div class="version-content" id="versionB-content">
                    <?= htmlspecialchars($versionB['content'])  ?>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="/admin/page-builder/<?= $contentId ?>/restore/<?= $versionA['id'] ?>" class="btn" 
               onclick="
return confirm('Restore version #<?= $versionA['id'] ?>?')">Restore Left Version</a>
            <a href="/admin/page-builder/<?= $contentId ?>/restore/<?= $versionB['id'] ?>" class="btn" 
               onclick="
return confirm('Restore version #<?= $versionB['id'] ?>?')">Restore Right Version</a>
        </div>
    </div>

    <script>
        document.getElementById('compare-btn').addEventListener('click', function() {
            const versionA = document.getElementById('versionA-select').value;
            const versionB = document.getElementById('versionB-select').value;
            window.location.href = `/admin/page-builder/<?= $contentId ?>/compare/${versionA}/${versionB}`;
        });

        // Syntax highlighting
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.version-content').forEach(el => {
                hljs.highlightElement(el);
            });
        });
    </script>
</body>
</html>
