<?php
define('CMS_ENTRY_POINT', true);
require_once __DIR__ . '/../../config.php';

// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
require_once CMS_ROOT . '/includes/controllers/auth/authcontroller.php';

$authController = new AuthController($dbConnection);
$authController->requireLogin();

$username = $authController->getCurrentUsername();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow Templates - Admin</title>
    <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
    <div class="admin-header">
        <h1>Workflow Templates</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
            <a href="/admin/logout.php">Logout</a>
        </div>
    </div>

    <div id="workflow-templates-app">
        <workflow-templates></workflow-templates>
    </div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="/admin/workflow/workflowtemplates.vue"></script>
    <script>
        const { createApp } = Vue;
        
        createApp({
            components: {
                'workflow-templates': httpVueLoader('/admin/workflow/WorkflowTemplates.vue')
            }
        }).mount('#workflow-templates-app');
    </script>
</body>
</html>