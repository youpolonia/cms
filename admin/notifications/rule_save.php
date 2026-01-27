<?php
require_once __DIR__ . '/../core/csrf.php';

csrf_boot('admin');

// Verify admin access
if (!check_admin_access('notification_rules_edit')) {
    admin_redirect('dashboard.php', 'Access denied');
}

csrf_validate_or_403();

// Validate required fields
$required = ['name', 'type', 'status', 'conditions', 'actions'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        admin_redirect('rule_edit.php' . (!empty($_POST['id']) ? '?id=' . $_POST['id'] : ''), 
            "Missing required field: $field");
    }
}

// Validate JSON fields
try {
    $conditions = json_decode($_POST['conditions'], true, 512, JSON_THROW_ON_ERROR);
    $actions = json_decode($_POST['actions'], true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    admin_redirect('rule_edit.php' . (!empty($_POST['id']) ? '?id=' . $_POST['id'] : ''), 
        'Invalid JSON in conditions or actions');
}

// Prepare rule data
$ruleData = [
    'name' => $_POST['name'],
    'type' => $_POST['type'],
    'status' => $_POST['status'],
    'conditions' => $conditions,
    'actions' => $actions
];

// Save rule
try {
    if (!empty($_POST['id'])) {
        // Update existing rule
        $ruleData['id'] = $_POST['id'];
        NotificationRules::update($ruleData);
        $message = 'Rule updated successfully';
    } else {
        // Create new rule
        NotificationRules::create($ruleData);
        $message = 'Rule created successfully';
    }
    
    admin_redirect('rules_listing.php', $message);
} catch (Exception $e) {
    error_log('Failed to save notification rule: ' . $e->getMessage());
    admin_redirect('rule_edit.php' . (!empty($_POST['id']) ? '?id=' . $_POST['id'] : ''), 
        'Failed to save rule: ' . $e->getMessage());
}
