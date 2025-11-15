<?php
// Check if tasks data was passed from controller
$tasks = $data['tasks'] ?? [];

?><div class="admin-container">
  <h2>Task Management</h2>
  
  <div class="admin-actions">
    <a href="/admin/tasks/create" class="btn btn-primary">Create New Task</a>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tasks as $task): ?>
        <tr>
          <td><?= htmlspecialchars($task['id']) ?></td>
          <td><?= htmlspecialchars($task['name']) ?></td>
          <td><?= htmlspecialchars($task['status']) ?></td>
          <td class="actions">
            <a href="/admin/tasks/edit/<?= $task['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
            <a href="/admin/tasks/toggle/<?= $task['id'] ?>" class="btn btn-sm btn-status">Toggle</a>
          </td>
        </tr>
      <?php endforeach;  ?>
    </tbody>
  </table>
</div>
