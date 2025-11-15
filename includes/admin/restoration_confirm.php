<?php
/**
 * Conflict Resolution Interface
 * 
 * Shows conflicting versions side-by-side with merge options
 * 
 * @package CMS
 * @subpackage Admin
 * @version 1.0.0
 */

require_once __DIR__ . '/../versioning/diffengine.php';
require_once __DIR__ . '/../versioning/versionmetadata.php';
require_once __DIR__ . '/../middleware/adminauthmiddleware.php';
require_once __DIR__.'/../audit/auditlogger.php';

// Check admin permissions
$auth = new AdminAuthMiddleware();
if (!$auth->hasPermission('content_versions_manage')) {
    die('Access denied');
}

// Get content and version IDs
$contentId = $_GET['content_id'] ?? null;
$version1Id = $_GET['version1'] ?? null;
$version2Id = $_GET['version2'] ?? null;
if (!$contentId || !$version1Id || !$version2Id) {
    die('Content ID and both version IDs required');
}

// Get version metadata
$versionMeta = new VersionMetadata();
$version1 = $versionMeta->getVersion($version1Id);
$version2 = $versionMeta->getVersion($version2Id);
if (!$version1 || !$version2) {
    die('One or both versions not found');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $strategy = $_POST['strategy'] ?? 'newer';
    $mergedContent = '';
    
    // Apply selected merge strategy
    switch ($strategy) {
        case 'newer':
            $mergedContent = $version1['created_at'] > $version2['created_at'] 
                ? $version1['content'] 
                : $version2['content'];
            break;
            
        case 'older':
            $mergedContent = $version1['created_at'] < $version2['created_at'] 
                ? $version1['content'] 
                : $version2['content'];
            break;
            
        case 'manual':
            $mergedContent = $_POST['merged_content'] ?? '';
            break;
    }
    
    // Save merged version
    if (!empty($mergedContent)) {
        $versionMeta->createVersion($contentId, [
            'content' => $mergedContent,
            'content_type' => $version1['content_type'],
            'author_id' => $auth->getUserId(),
            'change_notes' => "Conflict resolution (strategy: $strategy)",
            'is_conflict_resolution' => true
        ]);
        
        // Log resolution
        AuditLogger::log('content_conflict_resolved', [
            'content_id' => $contentId,
            'version1_id' => $version1Id,
            'version2_id' => $version2Id,
            'strategy' => $strategy,
            'resolved_by' => $auth->getUserId()
        ]);
        
        // Redirect to editor
        header("Location: content_editor.php?content_id=$contentId");
        exit;
    }
}

// Compare content
$diff = DiffEngine::compare($version1['content'], $version2['content'], $version1['content_type'] === 'html');

// Format metadata
$formatDate = function($date) {
    return date('Y-m-d H:i:s', strtotime($date));
};

// Get author names
$getAuthor = function($id) {
    return function_exists('get_user_name') ? get_user_name($id) : 'User #'.$id;
};
?><!DOCTYPE html>
<html>
<head>
    <title>Resolve Conflicts</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .comparison-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .version-pane {
            flex: 1;
            border: 1px solid #ddd;
            padding: 15px;
        }
        .diff-line {
            margin: 2px 0;
            padding: 2px;
        }
        .diff-insert {
            background-color: #e6ffed;
        }
        .diff-delete {
            background-color: #ffeef0;
        }
        .diff-change {
            background-color: #fff8c5;
        }
        .metadata-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .merge-strategies {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }
        .manual-merge {
            display: none;
            margin-top: 15px;
        }
        .manual-merge.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="content-management">
        <h1>Resolve Content Conflicts</h1>
        
        <form method="post" action="restoration_confirm.php?content_id=<?= $contentId ?>&version1=<?= $version1Id ?>&version2=<?= $version2Id ?>">
            <div class="merge-strategies">
                <h2>Merge Strategy</h2>
                
                <div>
                    <input type="radio" id="strategy-newer" name="strategy" value="newer" checked>
                    <label for="strategy-newer">Keep newer version (default)</label>
                </div>
                
                <div>
                    <input type="radio" id="strategy-older" name="strategy" value="older">
                    <label for="strategy-older">Keep older version</label>
                </div>
                
                <div>
                    <input type="radio" id="strategy-manual" name="strategy" value="manual">
                    <label for="strategy-manual">Manual merge</label>
                    
                    <div class="manual-merge" id="manual-merge-container">
                        <textarea name="merged_content" rows="10" style="width: 100%">
                            <?= htmlspecialchars($version1['created_at'] > $version2['created_at']
                                ? $version1['content']
                                : $version2['content']) ?> 
                        </textarea>
                    </div>
                </div>
            </div>
            
            <h2>Version Comparison</h2>
            <table class="metadata-table">
                <tr>
                    <th></th>
                    <th>Version #<?= $version1Id ?></th>
                    <th>Version #<?= $version2Id ?></th>
                </tr>
                <tr>
                    <td>Created</td>
                    <td><?= $formatDate($version1['created_at']) ?></td>
                    <td><?= $formatDate($version2['created_at']) ?></td>
                </tr>
                <tr>
                    <td>Author</td>
                    <td><?= htmlspecialchars($getAuthor($version1['author_id'])) ?></td>
                    <td><?= htmlspecialchars($getAuthor($version2['author_id'])) ?></td>
                </tr>
            </table>
            
            <div class="comparison-container">
                <div class="version-pane">
                    <h3>Version #<?= $version1Id ?></h3>
                    <?php foreach (explode("\n", $version1['content']) as $i => $line): ?>
                        <div class="diff-line <?= 
                            isset($diff[$i]) && in_array($diff[$i]['type'], ['delete', 'change']) ? 'diff-'.$diff[$i]['type'] : '' 
                        ?>">
                            <?= htmlspecialchars($line) 
?>                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="version-pane">
                    <h3>Version #<?= $version2Id ?></h3>
                    <?php foreach (explode("\n", $version2['content']) as $i => $line): ?>
                        <div class="diff-line <?= 
                            isset($diff[$i]) && in_array($diff[$i]['type'], ['insert', 'change']) ? 'diff-'.$diff[$i]['type'] : '' 
                        ?>">
                            <?= htmlspecialchars($line) 
?>                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="actions">
                <button type="submit" class="button primary">Confirm Resolution</button>
                <a href="restoration_panel.php?content_id=<?= $contentId ?>" class="button">Cancel</a>
            </div>
        </form>
    </div>
    
    <script>
        // Simple script to toggle manual merge textarea (will work without JS too)
        document.querySelectorAll('input[name="strategy"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const manualMerge = document.getElementById('manual-merge-container');
                if (this.value === 'manual') {
                    manualMerge.classList.add('active');
                } else {
                    manualMerge.classList.remove('active');
                }
            });
        });
?>    </script>
</body>
</html>
