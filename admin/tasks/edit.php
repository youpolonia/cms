<?php
require_once __DIR__ . '/../../core/csrf.php';
// Check if task data was passed from controller
$task = $data['task'] ?? [];
$history = $data['history'] ?? [];

?><div class="admin-container">
  <h2>Edit Task: <?= htmlspecialchars($task['name'] ?? 'New Task') ?></h2>
  <form method="POST" action="/admin/tasks/save/<?= $task['id'] ?? '' ?>">
    <?= csrf_field(); 
?>    <div class="form-group">
      <label for="name">Task Name</label>
      <input type="text" id="name" name="name"
             value="<?= htmlspecialchars($task['name'] ?? '') ?>"
             class="form-control" required>
    </div>
    
    <div class="form-group">
      <label for="interval">Interval (minutes)</label>
      <input type="number" id="interval" name="interval" 
             value="<?= htmlspecialchars($task['interval'] ?? '60') ?>"
             min="1" class="form-control" required>
    </div>
    
    <div class="form-group">
      <label for="enabled">Status</label>
      <select id="enabled" name="enabled" class="form-control">
        <option value="1" <?= ($task['enabled'] ?? false) ? 'selected' : '' ?>>Enabled</option>
        <option value="0" <?= !($task['enabled'] ?? false) ? 'selected' : '' ?>>Disabled</option>
      </select>
    </div>
    
    <div class="form-group">
      <label>Last Run</label>
      <p><?= htmlspecialchars($task['last_run'] ?? 'Never') ?></p>
    </div>
    
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save Changes</button>
      <a href="/admin/tasks" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
  
  <h3>Execution History</h3>
  <table class="admin-table">
    <thead>
      <tr>
        <th>Timestamp</th>
        <th>Status</th>
        <th>Duration</th>
        <th>Message</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($history as $entry): ?>
        <tr>
          <td><?= htmlspecialchars($entry['timestamp']) ?></td>
          <td>
            <span class="status-badge <?= $entry['success'] ? 'success' : 'error' ?>">
              <?= $entry['success'] ? 'Success' : 'Failed' 
?>            </span>
          </td>
          <td><?= htmlspecialchars($entry['duration'] ?? 'N/A') ?> ms</td>
          <td><?= htmlspecialchars($entry['message'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>      <?php if (empty($history)): ?>
        <tr>
          <td colspan="4" class="text-center">No execution history found</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
