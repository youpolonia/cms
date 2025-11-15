<?php
declare(strict_types=1);
require_once __DIR__.'/../../includes/security/authservicewrapper.php';
require_once __DIR__.'/../../core/database.php';
require_once __DIR__.'/../../services/versioncomparator.php';
require_once __DIR__ . '/../../core/csrf.php';

$db = \core\Database::connection();
$versionComparator = new VersionComparator();

$version1Id = $_GET['version1'] ?? 0;
$version2Id = $_GET['version2'] ?? 0;

// Get version details
$version1 = $db->query("SELECT * FROM content_versions WHERE id = ?", [$version1Id])->fetch();
$version2 = $db->query("SELECT * FROM content_versions WHERE id = ?", [$version2Id])->fetch();

// Compare versions
$diff = $versionComparator->compareVersions($version1Id, $version2Id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $mergeOptions = [
        'include_additions' => isset($_POST['include_additions']),
        'exclude_deletions' => isset($_POST['exclude_deletions'])
    ];
    
    $success = $versionComparator->mergeVersions(
        $version1Id,
        $version2Id,
        $_SESSION['user_id'],
        $mergeOptions
    );
    
    if ($success) {
        header("Location: version_list.php?content_id=" . $version1['content_id']);
        exit;
    } else {
        $error = "Failed to merge versions";
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Merge Versions v<?= htmlspecialchars($version1['version_number']) ?> and v<?= htmlspecialchars($version2['version_number']) ?></title>
    <link rel="stylesheet" href="/admin/css/version-control.css">
    <style>
        .merge-preview {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            max-height: 300px;
            overflow-y: auto;
        }
        .merge-line {
            padding: 2px;
            margin: 1px 0;
        }
        .merge-conflict {
            background-color: #fff8c5;
        }
        .merge-added {
            background-color: #e6ffed;
        }
        .merge-removed {
            background-color: #ffeef0;
            text-decoration: line-through;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Merge Versions</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif;  ?>
        <form method="POST">
            <?= csrf_field(); ?>
            <div class="merge-options">
                <h2>Merge Options</h2>
                <div class="form-group">
                    <input type="checkbox" id="
include_additions" name="include_additions" checked>
                    <label for="include_additions">Include additions from v<?= htmlspecialchars($version2['version_number']) ?></label>
                </div>
                <div class="form-group">
                    <input type="checkbox" id="exclude_deletions" name="exclude_deletions">
                    <label for="exclude_deletions">Exclude deletions from v<?= htmlspecialchars($version1['version_number']) ?></label>
                </div>
            </div>

            <div class="merge-preview">
                <h2>Merge Preview</h2>
                <?php 
                $preview = $versionComparator->applyMergeStrategy(
                    $version1['body'],
                    $version2['body'],
                    $diff,
                    ['include_additions' => true, 'exclude_deletions' => false]
                );
                $lines = explode("\n", $preview);
                foreach ($lines as $line): ?>
                
                    <div class="merge-line <?php echo
                        strpos($line, '<<<<<<< BASE') === 0 ? 'merge-conflict' :
                        (strpos($line, '=======') === 0 ? 'merge-conflict' :
                        (strpos($line, '>>>>>>> OTHER') === 0 ? 'merge-conflict' : ''))
                    ?>">
                        <?= htmlspecialchars($line)  ?>
                    </div>
                <?php endforeach;  ?>
            </div>

            <div class="form-group">
                <label for="notes">Merge Notes:</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <a href="version_list.php?content_id=<?= $version1['content_id'] ?>" class="btn cancel">Cancel</a>
                <button type="submit" class="btn confirm">Confirm Merge</button>
            </div>
        </form>
    </div>
</body>
</html>
