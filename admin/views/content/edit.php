<?php
// Security check
if (!defined('CMS_ADMIN')) {
    exit('Direct access not allowed');
}

require_once __DIR__ . '/../../../core/workflowmanager.php';
require_once __DIR__ . '/../../../core/permissionmanager.php';

// Get content data
$content_id = $_GET['id'] ?? 0;
$content = [];
$workflowManager = new WorkflowManager();

try {
    // Get base content
    $stmt = $db->prepare("SELECT * FROM content_entries WHERE id = ?");
    $stmt->execute([$content_id]);
    $content = $stmt->fetch(PDO::FETCH_ASSOC);
    $content['access_level'] = $content['access_level'] ?? 'public';
    
    // Get workflow state if not set
    if (!isset($content['workflow_state'])) {
        $content['workflow_state'] = $workflowManager->getWorkflowState($content_id);
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        try {
            $db->beginTransaction();
            
            $action = $_POST['action'] ?? '';
            $publish_at = !empty($_POST['publish_at']) ? $_POST['publish_at'] : null;
            
            switch ($action) {
                case 'save_draft':
                    $workflowManager->saveAsDraft($content_id);
                    $success = "Draft saved successfully";
                    break;
                    
                case 'submit_review':
                    $workflowManager->submitForReview($content_id);
                    $success = "Content submitted for review";
                    break;
                    
                case 'approve':
                    $workflowManager->approve($content_id);
                    $success = "Content approved";
                    break;
                    
                case 'reject':
                    $workflowManager->reject($content_id);
                    $success = "Content rejected";
                    break;
                    
                case 'publish':
                    $workflowManager->publish($content_id);
                    $success = "Content published";
                    break;
                    
                case 'schedule':
                    $workflowManager->schedule($content_id, $publish_at);
                    $success = "Content scheduled for publication";
                    break;
                    
                default:
                    $error = "Invalid action";
            }
            
            $db->commit();
            
            // Refresh data
            $stmt = $db->prepare("SELECT * FROM content_entries WHERE id = ?");
            $stmt->execute([$content_id]);
            $content = $stmt->fetch(PDO::FETCH_ASSOC);
            $content['workflow_state'] = $workflowManager->getWorkflowState($content_id);
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Workflow error: " . $e->getMessage();
        }
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?><div class="admin-container">
    <h2>Content Workflow</h2>
    <div class="version-metadata mb-3">
        <small class="text-muted">
            Version: <?= htmlspecialchars($content['version'] ?? '1.0') ?> |
            Last edited by: <?= htmlspecialchars($content['last_editor'] ?? 'krala') ?> |
            Updated: <?= htmlspecialchars($content['updated_at'] ?? 'Never') ?>
        </small>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (isset($_GET['rollback_success'])): ?>
        <div class="alert alert-info">Version successfully restored</div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3>Workflow State</h3>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <?php
                $state_class = match($content['workflow_state'] ?? 'draft') {
                    'draft' => 'bg-secondary',
                    'submitted' => 'bg-info',
                    'approved' => 'bg-primary',
                    'rejected' => 'bg-danger',
                    'published' => 'bg-success',
                    default => 'bg-secondary'
                };
                ?>
                <span class="badge <?= $state_class ?> me-2" style="width: 20px; height: 20px;"></span>
                <span class="text-capitalize fw-bold"><?= htmlspecialchars($content['workflow_state'] ?? 'draft') ?></span>
                
                <?php if (!empty($content['publish_at'])): ?>
                    <span class="ms-3">
                        <i class="bi bi-clock"></i> Scheduled: <?= htmlspecialchars($content['publish_at']) ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <small class="text-muted">Last updated: <?= htmlspecialchars($content['updated_at'] ?? 'Never') ?></small>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h3>Access Level</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="access_level">Content Visibility</label>
                <select class="form-control" id="access_level" name="access_level">
                    <option value="public" <?= ($content['access_level'] ?? 'public') === 'public' ? 'selected' : '' ?>>Public</option>
                    <option value="private" <?= ($content['access_level'] ?? 'public') === 'private' ? 'selected' : '' ?>>Private</option>
                    <option value="admin" <?= ($content['access_level'] ?? 'public') === 'admin' ? 'selected' : '' ?>>Admin Only</option>
                </select>
                <small class="form-text text-muted">Controls who can view this content</small>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Access Control</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="access_level" class="form-label">Access Level</label>
                <select class="form-select" id="access_level" name="access_level" required>
                    <option value="public" <?= ($content['access_level'] ?? 'public') === 'public' ? 'selected' : '' ?>>Public</option>
                    <option value="private" <?= ($content['access_level'] ?? 'public') === 'private' ? 'selected' : '' ?>>Private</option>
                    <option value="admin" <?= ($content['access_level'] ?? 'public') === 'admin' ? 'selected' : '' ?>>Admin Only</option>
                </select>
                <div class="form-text">Controls who can view this content</div>
            </div>
        </div>
    </div>
    
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="content_id" value="<?= $content_id ?>">
        <div class="card mb-4">
            <div class="card-header">
                <h3>Workflow Actions</h3>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php if (PermissionManager::canEditContent($content_id)): ?>
                        <button type="submit" name="action" value="save_draft" class="btn btn-outline-secondary">
                            Save Draft
                        </button>
                    
                        <?php if ($content['workflow_state'] === 'draft' || $content['workflow_state'] === 'rejected'): ?>
                            <button type="submit" name="action" value="submit_review" class="btn btn-info">
                                Submit for Review
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (PermissionManager::canReviewContent($content_id)): ?>
                        <?php if ($content['workflow_state'] === 'submitted'): ?>
                            <button type="submit" name="action" value="approve" class="btn btn-success">
                                Approve
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">
                                Reject
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (PermissionManager::canPublishContent($content_id)): ?>
                        <?php if ($content['workflow_state'] === 'approved'): ?>
                            <button type="submit" name="action" value="publish" class="btn btn-primary">
                                Publish Now
                            </button>
                            <button type="button" id="schedule-btn" class="btn btn-outline-primary">
                                Schedule Publication
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="/admin/version-management?id=<?= $content_id ?>" class="btn btn-outline-info">
                        <i class="bi bi-clock-history"></i> Version History
                    </a>
                </div>
                
                <div id="schedule-section" class="mb-3" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="publish_at" class="form-label">Publish Date/Time</label>
                            <input type="datetime-local" class="form-control" id="publish_at" name="publish_at"
                                   min="<?= date('Y-m-d\TH:i') ?>"
                                   value="<?= htmlspecialchars($content['publish_at'] ?? '') ?>">
                            <div class="form-text">Content will be published at this date/time</div>
                        </div>
                        <div class="col-md-6">
                            <label for="unpublish_at" class="form-label">Unpublish Date/Time</label>
                            <input type="datetime-local" class="form-control" id="unpublish_at" name="unpublish_at"
                                   min="<?= date('Y-m-d\TH:i') ?>"
                                   value="<?= htmlspecialchars($content['unpublish_at'] ?? '') ?>">
                            <div class="form-text">Content will be unpublished at this date/time</div>
                        </div>
                    </div>
                    
                    <button type="submit" name="action" value="schedule" class="btn btn-primary mt-2">
                        Confirm Schedule
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle schedule section
    document.getElementById('schedule-btn').addEventListener('click', function() {
        const section = document.getElementById('schedule-section');
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    });

    // Set minimum datetime to current time for both fields
    const now = new Date();
    const minDatetime = now.toISOString().slice(0, 16);
    document.getElementById('publish_at').min = minDatetime;
    document.getElementById('unpublish_at').min = minDatetime;
});
</script>
