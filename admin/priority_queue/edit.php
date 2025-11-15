<?php
require_once __DIR__ . '/../../includes/coreloader.php';

if (!Auth::check('admin')) {
    header('Location: /admin/login.php');
    exit;
}

$controller = new PriorityQueueController();
$controller->edit();
