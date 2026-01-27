<?php
// Verify admin access
require_once __DIR__ . '/../../includes/auth/admin_check.php';

// Set page title
$page_title = 'Workflow Management';

// Include header
require_once __DIR__ . '/../../includes/admin_header.php';

// DEV note: Vue templates inlined below; no file includes left.

// Get workflow ID from query string if present
$workflow_id = $_GET['id'] ?? null;

?><div id="workflow-app">
  <?php if ($workflow_id): ?>    <workflow-detail workflow-id="<?= htmlspecialchars($workflow_id) ?>"></workflow-detail>
  <?php else: ?>    <workflow-list></workflow-list>
  <?php endif; ?>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
  const { createApp } = Vue;

  // Import components
  const WorkflowList = {
    template: `<?php echo file_get_contents(__DIR__ . '/WorkflowList.vue'); ?>`
  };

  const WorkflowDetail = {
    props: ['workflowId'],
    template: `<?php echo file_get_contents(__DIR__ . '/WorkflowDetail.vue'); ?>`
  };

  // Create Vue app
  const app = createApp({
    components: {
      WorkflowList,
      WorkflowDetail
    }
  });

  app.mount('#workflow-app');
?></script>

// Include footer
require_once __DIR__ . '/../../includes/admin_footer.php';
