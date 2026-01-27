<?php
// Check if tasks data was passed from controller
$tasks = $data['tasks'] ?? [];
$currentPage = $data['currentPage'] ?? 1;
$totalPages = $data['totalPages'] ?? 1;
$tenantId = $_SESSION['tenant_id'] ?? null;

?><div class="admin-container">
  <h2>Scheduled Tasks</h2>

  <div class="admin-actions">
    <a href="/admin/tasks/create" class="btn btn-primary">Create New Task</a>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>Task Name</th>
        <th>Interval</th>
        <th>Last Run Status</th>
        <th>Last Run Time</th>
        <th>Next Run Time</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tasks as $task): ?>
        <tr>
          <td><?= htmlspecialchars($task['name']) ?></td>
          <td><?= htmlspecialchars($task['interval']) ?> minutes</td>
          <td>
            <span class="status-badge <?= $task['last_status'] === 'success' ? 'success' : 'error' ?>">
              <?= ucfirst($task['last_status'] ?? 'unknown') 
?>            </span>
          </td>
          <td><?= htmlspecialchars($task['last_run'] ?? 'Never') ?></td>
          <td><?= htmlspecialchars($task['next_run'] ?? 'Pending') ?></td>
          <td>
            <span class="status-badge <?= $task['enabled'] ? 'enabled' : 'disabled' ?>">
              <?= $task['enabled'] ? 'Enabled' : 'Disabled' 
?>            </span>
          </td>
          <td class="actions">
            <a href="/admin/tasks/edit/<?= $task['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
            <button class="btn btn-sm btn-toggle"
                    data-task-id="<?= $task['id'] ?>"
                    data-enabled="<?= $task['enabled'] ? '1' : '0' ?>">
              <?= $task['enabled'] ? 'Disable' : 'Enable' 
?>            </button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($currentPage > 1): ?>
        <a href="/admin/tasks?page=<?= $currentPage - 1 ?>" class="page-link">&laquo; Previous</a>
      <?php endif; ?>      <?php for ($i = 1; $i <= $totalPages; $i++): 
?>        <a href="/admin/tasks?page=<?= $i ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>">
          <?= $i 
?>        </a>
      <?php endfor; ?>      <?php if ($currentPage < $totalPages): ?>
        <a href="/admin/tasks?page=<?= $currentPage + 1 ?>" class="page-link">Next &raquo;</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.btn-toggle').forEach(btn => {
  btn.addEventListener('click', async (e) => {
    const taskId = e.target.dataset.taskId;
    const enabled = e.target.dataset.enabled === '1';

    try {
      const response = await fetch(`/admin/tasks/toggle/${taskId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
        },
        body: JSON.stringify({
          enabled: !enabled
        })
      });

      if (response.ok) {
        window.location.reload();
      } else {
        alert('Failed to toggle task status');
      }
    } catch (error) {
      console.error('Error toggling task:', error);
      alert('Network error while toggling task');
    }
  });
});
</script>
