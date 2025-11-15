<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../includes/versioncomparator.php';
require_once __DIR__ . '/../../../includes/diffrenderer.php';
require_once __DIR__ . '/../../../includes/version/semanticversioncomparator.php';
if (file_exists(__DIR__ . '/../../../core/database.php')) {
    require_once __DIR__ . '/../../../core/database.php';
}

header('Content-Type: application/json');

try {
    // Validate input
    $contentId = filter_input(INPUT_GET, 'contentId', FILTER_VALIDATE_INT);
    $oldVersionId = filter_input(INPUT_GET, 'oldVersionId', FILTER_VALIDATE_INT);
    $newVersionId = filter_input(INPUT_GET, 'newVersionId', FILTER_VALIDATE_INT);

    if (!$contentId || !$oldVersionId || !$newVersionId) {
        throw new InvalidArgumentException('Invalid content or version IDs');
    }

    // Handle version listing request
    if (isset($_GET['list']) && $_GET['list'] === 'versions') {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT id, version, created_at FROM content_versions WHERE content_id = ? ORDER BY created_at DESC");
        $stmt->execute([$contentId]);
        $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'versions' => array_map(function($v) {
                return [
                    'id' => $v['id'],
                    'name' => 'v' . $v['version'] . ' (' . date('Y-m-d', strtotime($v['created_at'])) . ')'
                ];
            }, $versions)
        ]);
        exit;
    }

    // Get content versions from database
    $pdo = \core\Database::connection();
    $stmt = $pdo->prepare("SELECT data, version FROM content_versions WHERE content_id = ? AND id IN (?, ?)");
    $stmt->execute([$contentId, $oldVersionId, $newVersionId]);
    $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($versions) !== 2) {
        throw new RuntimeException('Could not find both versions');
    }

    // Determine which is old/new version
    $versionComparator = new SemanticVersionComparator();
    $compareResult = $versionComparator->compare($versions[0]['version'], $versions[1]['version']);
    
    $oldContent = $compareResult <= 0 ? $versions[0]['data'] : $versions[1]['data'];
    $newContent = $compareResult <= 0 ? $versions[1]['data'] : $versions[0]['data'];

    // Compare content
    $versionComparator = new VersionComparator();
    $diffReport = $versionComparator->compare($oldContent, $newContent);

    // Render diff
    $diffRenderer = new DiffRenderer();
    $diffData = $diffRenderer->compareTexts($oldContent, $newContent);
    $visualDiff = $diffRenderer->visualDiff($diffData['side_by_side']);

    // Prepare response
    echo json_encode([
        'success' => true,
        'diff' => $diffReport,
        'visualDiff' => $visualDiff,
        'oldVersion' => $compareResult <= 0 ? $versions[0]['version'] : $versions[1]['version'],
        'newVersion' => $compareResult <= 0 ? $versions[1]['version'] : $versions[0]['version']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
