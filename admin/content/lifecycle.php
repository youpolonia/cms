<?php
require_once __DIR__ . '/../../services/contentlifecyclemanager.php';
require_once __DIR__ . '/../core/csrf.php';

// Check permissions
if (!isset($_SESSION['user_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$contentId = $_GET['id'] ?? null;
if (!$contentId) {
    die('Content ID required');
}

$manager = new ContentLifecycleManager($contentId);
$history = $manager->getTransitionHistory();

// Handle state transition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_state'])) {
    csrf_validate_or_403();
    try {
        $manager->transitionTo($_POST['new_state']);
        $success = "State changed successfully";
        $history = $manager->getTransitionHistory(); // Refresh history
    } catch (InvalidTransitionException $e) {
        $error = $e->getMessage();
    }
}

$currentState = $manager->getCurrentState();
$validTransitions = [
    ContentLifecycleManager::STATE_DRAFT => 'Draft',
    ContentLifecycleManager::STATE_REVIEW => 'Review',
    ContentLifecycleManager::STATE_PUBLISHED => 'Published',
    ContentLifecycleManager::STATE_ARCHIVED => 'Archived'
];
?><!DOCTYPE html>
<html>
<head>
    <title>Content Lifecycle Management</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
    <?php require_once __DIR__ . '/../views/layout.php'; 
?>    <div class="container">
        <h1>Content Lifecycle Management</h1>
        <p>Content ID: <?= htmlspecialchars($contentId) ?></p>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <div class="current-state">
            <h3>Current State: <?= $validTransitions[$currentState] ?? $currentState ?></h3>
            <form method="POST">
                <div class="form-group">
                    <label for="new_state">Change State:</label>
                    <select name="new_state" id="new_state" class="form-control">
                        <?php foreach ($validTransitions as $value => $label): ?>                            <?php if ($value !== $currentState): ?>                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endif; ?>                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Transition</button>
            </form>
        </div>
        
        <div class="history">
            <h3>Transition History</h3>
            <?php if (empty($history)): ?>
                <p>No transitions recorded</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $entry): ?>
                            <tr>
                                <td><?= date('Y-m-d H:i:s', $entry['timestamp']) ?></td>
                                <td><?= $validTransitions[$entry['from_state']] ?? $entry['from_state'] ?></td>
                                <td><?= $validTransitions[$entry['to_state']] ?? $entry['to_state'] ?></td>
                                <td><?= htmlspecialchars($entry['user_id']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
