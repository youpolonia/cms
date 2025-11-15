<?php
require_once __DIR__ . '/../../includes/session_config.php';
require_once __DIR__ . '/../../includes/security_enhancements.php';
require_once __DIR__ . '/models/policy.php';
require_once __DIR__ . '/../../core/csrf.php';

// Check authentication
if (!\Core\Security\SecureSession::get('authenticated')) {
    header('Location: /admin/login');
    exit;
}

// Check admin permissions
$policy = new \Admin\Security\Models\Policy();
if (!$policy->canAccessSecurityPanel(\Core\Security\SecureSession::get('user_id'))) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $policyData = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'rules' => json_decode($_POST['rules'] ?? '[]', true)
    ];

    if ($policy->validate($policyData)) {
        $policy->save($policyData);
        \core\Logger::securityLog("Policy updated by user " . \Core\Security\SecureSession::get('user_id'));
        header('Location: /admin/security/policy?success=1');
        exit;
    }
}

// Get all policies
$policies = $policy->getAll();
?><!DOCTYPE html>
<html>
<head>
    <title>Security Policy Configuration</title>
    <link rel="stylesheet" href="/admin/css/security.css">
</head>
<body>
    <div class="security-container">
        <h1>Security Policies</h1>
        
        <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success">Policy saved successfully</div>
        <?php endif;  ?>
        <form method="post">
            <div class="form-group">
                <label>Policy Name</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label>Rules (JSON)</label>
                <textarea name="rules" rows="10" required></textarea>
            </div>
            
            <button type="submit">Save Policy</button>
        </form>
        
        <div class="policy-list">
            <h2>Existing Policies</h2>
            <?php foreach ($policies as $policy): ?>
            <div class="policy-item">
                <h3><?= htmlspecialchars($policy['name']) ?></h3>
                <p><?= htmlspecialchars($policy['description']) ?></p>
                <pre><?= htmlspecialchars(json_encode($policy['rules'], JSON_PRETTY_PRINT)) ?></pre>
            </div>
            <?php endforeach;  ?>
        </div>
    </div>
</body>
</html>
