<?php
require_once __DIR__ . '/../../core/rolemanager.php';
require_once __DIR__ . '/../../core/workflowmanager.php';
require_once __DIR__ . '/../../core/csrf.php';

// Check permissions
$roleManager = RoleManager::getInstance();
if (!$roleManager->hasPermission($_SESSION['user_id'] ?? 0, 'run_scheduler')) {
    die('Permission denied');
}

// Process scheduler if requested
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $workflowManager = new WorkflowManager();
    $result = $workflowManager->processScheduledContent();
}

// Admin header
require_once __DIR__ . '/../views/includes/header.php';


?><div class="container">
    <h1>Run Content Scheduler</h1>
    
    <?php if ($result): ?>
<div class="alert alert-success">
            <h4>Scheduler Results</h4>
            <p>Published: <?= $result['published'] ?? 0 ?></p>
            <p>Unpublished: <?= $result['unpublished'] ?? 0 ?></p>
        </div>
    <?php endif; ?>
<form method="post">
        <?= csrf_field(); 
?><p>Click below to manually run the content scheduler:</p>
        <button type="submit" class="btn btn-primary">Run Scheduler Now</button>
        <a href="/admin/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </form>
</div>

<?php
// Admin footer
require_once __DIR__ . '/../views/includes/footer.php';
