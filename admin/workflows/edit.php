<?php
declare(strict_types=1);
/** Workflow Editor with Version Control */
require_once __DIR__ . '/../includes/auth_check.php';

$workflowId = (int)($_GET['id'] ?? 0);
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Workflow #<?php echo $workflowId; ?></title>
  <link rel="stylesheet" href="/assets/css/version-comparison.css">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <div class="workflow-editor-container">
    <div class="editor-header">
      <h2>Editing Workflow <span class="workflow-id">#<?php echo $workflowId; ?></span></h2>
      <button class="btn btn-secondary" id="showVersions">Version History</button>
    </div>

    <div class="version-history-table">
      <table class="data-table">
        <thead>
          <tr>
            <th>Version</th>
            <th>Date</th>
            <th>Author</th>
            <th>Changes</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="versionList">
          <!-- Dynamic content from JS -->
        </tbody>
      </table>
    </div>

    <div class="editor-actions">
      <button class="btn btn-primary" id="saveWorkflow">Save Changes</button>
      <button class="btn btn-secondary" id="discardChanges">Discard</button>
    </div>
  </div>

  <?php require_once __DIR__ . '/../views/workflows/version_compare.php'; ?>
  <script src="/assets/js/version-management.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const workflowId = <?php echo $workflowId; ?>;
    
    // Load version history
    fetch(`/api/workflows/${workflowId}/versions`)
      .then(res => res.json())
      .then(versions => {
        const tbody = document.getElementById('versionList');
        tbody.innerHTML = versions.map(v => `
          <tr>
            <td>${v.version}</td>
            <td>${new Date(v.created_at).toLocaleString()}</td>
            <td>${v.author_name}</td>
            <td>${v.change_count} changes</td>
            <td>
              <button class="btn-link compare-btn" 
                data-v1="${v.version}" 
                data-v2="current">
                Compare
              </button>
              <button class="btn-link restore-btn" 
                data-version="${v.version}">
                Restore
              </button>
            </td>
          </tr>
        `).join('');
      });
  });
  </script>
</body>
</html>