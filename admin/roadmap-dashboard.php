<?php
// Verify admin session
require_once __DIR__.'/../core/sessionmanager.php';
require_once __DIR__ . '/../core/csrf.php';
SessionManager::verifyAdminSession();

// Check permissions
if (!SessionManager::hasPermission('roadmap_view')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

csrf_boot('admin');

// Log access
$logEntry = date('Y-m-d H:i:s')." - Accessed by: ".SessionManager::getUserId()."\n";
file_put_contents(__DIR__.'/../logs/admin-roadmap-access.log', $logEntry, FILE_APPEND);

// Load markdown files
$cleanupPlan = file_get_contents(__DIR__.'/../tasks/cleanup-plan.md');
$roadmap = file_get_contents(__DIR__.'/../tasks/roadmap.md');

// Use DashboardRenderer for markdown conversion
use Admin\Renderers\DashboardRenderer;
?><!DOCTYPE html>
<html>
<head>
    <title>CMS Roadmap Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .tabs { display: flex; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border: 1px solid #ddd; }
        .tab.active { background: #f0f0f0; border-bottom: 1px solid #f0f0f0; }
        .tab-content { display: none; border: 1px solid #ddd; padding: 20px; }
        .tab-content.active { display: block; }
        .pending { color: #d9534f; }
        .completed { color: #5cb85c; text-decoration: line-through; }
    </style>
</head>
<body>
    <h1>CMS Administration Dashboard</h1>
    
    <div class="tabs">
        <div class="tab active" onclick="showTab('cleanup')">Cleanup Plan</div>
        <div class="tab" onclick="showTab('roadmap')">Development Roadmap</div>
    </div>

    <div id="cleanup" class="tab-content active">
        <?php echo DashboardRenderer::renderMarkdown($cleanupPlan); 
?>    </div>

    <div id="roadmap" class="tab-content">
        <?php echo DashboardRenderer::renderMarkdown($roadmap); 
?>    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(el => {
                el.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`.tab[onclick="showTab('${tabId}')"]`).classList.add('active');
        }
?>    </script>
</body>
</html>
