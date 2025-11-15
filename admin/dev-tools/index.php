<?php
declare(strict_types=1);

// Verify developer role access
if (!Auth::hasRole('developer')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

require_once __DIR__ . '/../../core/responsehandler.php';

$title = 'Developer Tools';
$tools = [
    'event-monitor' => 'Event Bus Monitor',
    'scaffold' => 'Plugin Scaffold Generator',
    'sandbox' => 'Block Renderer Sandbox',
    'api-tester' => 'API Tester',
    'docs-viewer' => 'Documentation Browser'
];

ResponseHandler::render('dev-tools/index', [
    'title' => $title,
    'tools' => $tools,
    'activeTab' => $_GET['tab'] ?? null
]);
