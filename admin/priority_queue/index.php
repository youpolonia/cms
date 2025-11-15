<?php
require_once __DIR__ . '/../../includes/coreloader.php';

// Check authentication
if (!Auth::check('admin')) {
    header('Location: /admin/login.php');
    exit;
}

$controller = new PriorityQueueController();
$controller->index();
