<?php
/**
 * AI Website Optimizer
 * One-click website health analysis and auto-fix
 */

if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) {
        die('Cannot determine CMS_ROOT');
    }
    define('CMS_ROOT', $cmsRoot);
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_website_optimizer.php';

cms_session_start('admin');
csrf_boot('admin');

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

cms_require_admin_role();

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$message = '';
$messageType = 'info';
$scanResults = null;
$fixResult = null;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'run_scan':
            $scanResults = ai_optimizer_run_scan();
            if ($scanResults['ok']) {
                $message = 'Scan completed! Analyzed ' . $scanResults['summary']['pages_scanned'] . ' pages.';
                $messageType = 'success';
            } else {
                $message = 'Scan failed: ' . ($scanResults['error'] ?? 'Unknown error');
                $messageType = 'danger';
            }
            break;

        case 'fix_meta':
            $pageId = (int)($_POST['page_id'] ?? 0);
            if ($pageId > 0) {
                $fixResult = ai_optimizer_fix_meta_description($pageId);
                if ($fixResult['ok']) {
                    $message = 'Meta description generated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Fix failed: ' . ($fixResult['error'] ?? 'Unknown error');
                    $messageType = 'danger';
                }
            }
            break;

        case 'fix_all_meta':
            $fixResult = ai_optimizer_fix_all_meta();
            if ($fixResult['ok']) {
                $message = "Bulk fix complete: {$fixResult['fixed']} fixed, {$fixResult['failed']} failed.";
                $messageType = $fixResult['failed'] > 0 ? 'warning' : 'success';
            } else {
                $message = 'Bulk fix failed: ' . ($fixResult['error'] ?? 'Unknown error');
                $messageType = 'danger';
            }
            break;

        case 'fix_alt':
            $pageId = (int)($_POST['page_id'] ?? 0);
            $imageSrc = $_POST['image_src'] ?? '';
            if ($pageId > 0 && !empty($imageSrc)) {
                $fixResult = ai_optimizer_fix_alt_tag($pageId, $imageSrc);
                if ($fixResult['ok']) {
                    $message = 'ALT tag generated: "' . esc($fixResult['alt_text']) . '"';
                    $messageType = 'success';
                } else {
                    $message = 'Fix failed: ' . ($fixResult['error'] ?? 'Unknown error');
                    $messageType = 'danger';
                }
            }
            break;
    }
}

// Load latest results if not just scanned
if (!$scanResults) {
    $scanResults = ai_optimizer_load_results();
}

$scanAge = ai_optimizer_get_scan_age();

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <div>
                <h1 class="mb-0">🚀 AI Website Optimizer</h1>
                <p class="text-muted mb-0">One-click website health analysis and auto-fix</p>
            </div>
            <form method="post" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="run_scan">
                <button type="submit" class="btn btn-primary btn-lg">
                    🔍 Run Full Scan
                </button>
            </form>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= esc($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!$scanResults): ?>
            <!-- No Scan Yet -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="display-1 text-muted mb-4">🔍</div>
                    <h3>No Scan Results Yet</h3>
                    <p class="text-muted">Click "Run Full Scan" to analyze your website for SEO issues.</p>
                    <p class="text-muted small">The scan will check all published pages for:</p>
                    <div class="row justify-content-center mt-4">
                        <div class="col-md-6">
                            <ul class="list-unstyled text-start">
                                <li>✅ Title tag optimization</li>
                                <li>✅ Meta descriptions</li>
                                <li>✅ Heading structure (H1, H2, H3)</li>
                                <li>✅ Image ALT tags</li>
                                <li>✅ Content length</li>
                                <li>✅ Internal linking</li>
                                <li>✅ URL structure</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php
            $summary = $scanResults['summary'] ?? [];
            $grade = $summary['grade'] ?? ['letter' => '?', 'label' => 'Unknown', 'color' => 'secondary'];
            $recommendations = $scanResults['recommendations'] ?? [];
            $issuesByType = $scanResults['issues_by_type'] ?? [];
            $pages = $scanResults['pages'] ?? [];
            ?>

            <!-- Scan Summary -->
            <div class="row mb-4">
                <!-- Health Score -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card h-100 border-<?= $grade['color'] ?>">
                        <div class="card-body text-center">
                            <div class="display-1 text-<?= $grade['color'] ?>"><?= $grade['letter'] ?></div>
                            <h5 class="text-<?= $grade['color'] ?>"><?= esc($grade['label']) ?></h5>
                            <p class="display-6 mb-0"><?= $summary['average_score'] ?? 0 ?>/100</p>
                            <small class="text-muted">Average Health Score</small>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="col-lg-9 col-md-6">
                    <div class="row h-100">
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 bg-light">
                                <div class="card-body text-center">
                                    <div class="display-6"><?= $summary['pages_scanned'] ?? 0 ?></div>
                                    <small class="text-muted">Pages Scanned</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 bg-danger bg-opacity-10 border-danger">
                                <div class="card-body text-center">
                                    <div class="display-6 text-danger"><?= $summary['critical_issues'] ?? 0 ?></div>
                                    <small class="text-muted">Critical Issues</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 bg-warning bg-opacity-10 border-warning">
                                <div class="card-body text-center">
                                    <div class="display-6 text-warning"><?= $summary['warnings'] ?? 0 ?></div>
                                    <small class="text-muted">Warnings</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 bg-info bg-opacity-10 border-info">
                                <div class="card-body text-center">
                                    <div class="display-6 text-info"><?= $summary['info'] ?? 0 ?></div>
                                    <small class="text-muted">Info</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Issues by Type -->
            <?php if (!empty($issuesByType)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📊 Issues by Type</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($issuesByType as $type => $count): ?>
                            <?php
                            $icon = match($type) {
                                'title' => '📝',
                                'meta_description' => '📄',
                                'headings' => '📑',
                                'images' => '🖼️',
                                'content' => '📖',
                                'links' => '🔗',
                                'url' => '🌐',
                                default => '⚠️'
                            };
                            ?>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="fs-3 me-2"><?= $icon ?></span>
                                    <div>
                                        <div class="fw-bold"><?= ucfirst(str_replace('_', ' ', $type)) ?></div>
                                        <span class="badge bg-secondary"><?= $count ?> issue(s)</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recommendations -->
            <?php if (!empty($recommendations)): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">💡 Recommendations</h5>
                    <?php
                    $hasFixableMeta = false;
                    foreach ($recommendations as $rec) {
                        if ($rec['type'] === 'meta_description' && $rec['fix_available']) {
                            $hasFixableMeta = true;
                            break;
                        }
                    }
                    ?>
                    <?php if ($hasFixableMeta): ?>
                        <form method="post" class="d-inline" onsubmit="return confirm('This will generate meta descriptions for all pages missing them. Continue?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="fix_all_meta">
                            <button type="submit" class="btn btn-sm btn-light">
                                🔧 Fix All Missing Meta
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php foreach ($recommendations as $rec): ?>
                        <?php
                        $priorityColor = match($rec['priority']) {
                            'critical' => 'danger',
                            'high' => 'warning',
                            'medium' => 'info',
                            default => 'secondary'
                        };
                        ?>
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <span class="badge bg-<?= $priorityColor ?> me-3"><?= strtoupper($rec['priority']) ?></span>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= esc($rec['title']) ?></h6>
                                <p class="text-muted mb-1 small"><?= esc($rec['description']) ?></p>
                                <span class="badge bg-secondary"><?= $rec['affected_count'] ?> page(s) affected</span>
                                <?php if ($rec['fix_available']): ?>
                                    <span class="badge bg-success">🔧 Auto-fix available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Pages List -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📄 Page Analysis</h5>
                    <small class="text-muted">Last scan: <?= esc($scanAge ?? 'Unknown') ?></small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">Issues</th>
                                    <th>Stats</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Sort by score ascending (worst first)
                                usort($pages, fn($a, $b) => $a['score'] - $b['score']);
                                ?>
                                <?php foreach ($pages as $page): ?>
                                    <?php
                                    $scoreColor = $page['score'] >= 80 ? 'success' : ($page['score'] >= 60 ? 'warning' : 'danger');
                                    $criticalCount = count(array_filter($page['issues'], fn($i) => $i['severity'] === 'critical'));
                                    $warningCount = count(array_filter($page['issues'], fn($i) => $i['severity'] === 'warning'));
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($page['title']) ?></strong>
                                            <br><small class="text-muted">/<?= esc($page['slug']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $scoreColor ?> fs-6"><?= $page['score'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($criticalCount > 0): ?>
                                                <span class="badge bg-danger"><?= $criticalCount ?> critical</span>
                                            <?php endif; ?>
                                            <?php if ($warningCount > 0): ?>
                                                <span class="badge bg-warning text-dark"><?= $warningCount ?> warning</span>
                                            <?php endif; ?>
                                            <?php if ($criticalCount === 0 && $warningCount === 0): ?>
                                                <span class="badge bg-success">✓ OK</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small">
                                            <?= $page['stats']['word_count'] ?> words |
                                            <?= $page['stats']['image_count'] ?> images |
                                            <?= $page['stats']['internal_links'] ?> links
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#pageModal<?= $page['page_id'] ?>">
                                                View Issues
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Page Issues Modal -->
                                    <div class="modal fade" id="pageModal<?= $page['page_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><?= esc($page['title']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php if (empty($page['issues'])): ?>
                                                        <div class="alert alert-success">
                                                            ✅ No issues found! This page is well optimized.
                                                        </div>
                                                    <?php else: ?>
                                                        <?php foreach ($page['issues'] as $issue): ?>
                                                            <?php
                                                            $sevColor = match($issue['severity']) {
                                                                'critical' => 'danger',
                                                                'warning' => 'warning',
                                                                default => 'info'
                                                            };
                                                            ?>
                                                            <div class="alert alert-<?= $sevColor ?> d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <strong><?= ucfirst($issue['type']) ?>:</strong>
                                                                    <?= esc($issue['message']) ?>
                                                                    <?php if (isset($issue['current'])): ?>
                                                                        <br><small class="text-muted">Current: "<?= esc(mb_substr($issue['current'], 0, 100)) ?>..."</small>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php if (!empty($issue['fix_available']) && $issue['fix_type'] === 'generate_meta'): ?>
                                                                    <form method="post" class="ms-2">
                                                                        <?= csrf_field() ?>
                                                                        <input type="hidden" name="action" value="fix_meta">
                                                                        <input type="hidden" name="page_id" value="<?= $page['page_id'] ?>">
                                                                        <button type="submit" class="btn btn-sm btn-success">🔧 Fix</button>
                                                                    </form>
                                                                <?php endif; ?>
                                                                <?php if (!empty($issue['fix_available']) && $issue['fix_type'] === 'generate_alt' && !empty($issue['images'])): ?>
                                                                    <?php foreach ($issue['images'] as $img): ?>
                                                                        <form method="post" class="ms-2">
                                                                            <?= csrf_field() ?>
                                                                            <input type="hidden" name="action" value="fix_alt">
                                                                            <input type="hidden" name="page_id" value="<?= $page['page_id'] ?>">
                                                                            <input type="hidden" name="image_src" value="<?= esc($img['src']) ?>">
                                                                            <button type="submit" class="btn btn-sm btn-success" title="Fix: <?= esc($img['src']) ?>">🔧 Fix ALT</button>
                                                                        </form>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                    <hr>
                                                    <h6>Page Stats</h6>
                                                    <ul class="list-unstyled">
                                                        <li>📝 Word count: <strong><?= $page['stats']['word_count'] ?></strong></li>
                                                        <li>🖼️ Images: <strong><?= $page['stats']['image_count'] ?></strong></li>
                                                        <li>📑 Headings: <strong><?= $page['stats']['heading_count'] ?></strong></li>
                                                        <li>🔗 Internal links: <strong><?= $page['stats']['internal_links'] ?></strong></li>
                                                        <li>🌐 External links: <strong><?= $page['stats']['external_links'] ?></strong></li>
                                                    </ul>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="/admin/pages.php?id=<?= $page['page_id'] ?>" class="btn btn-primary">Edit Page</a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <div class="mt-4 mb-4">
            <a href="/admin/ai-seo-dashboard.php" class="btn btn-secondary">← SEO Dashboard</a>
            <a href="/admin/ai-seo-assistant.php" class="btn btn-primary">SEO Assistant</a>
        </div>

    </div>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
