<?php
// Check if users data was passed from controller
$users = $data['users'] ?? [];

?><div class="admin-container">
  <h2>User Management</h2>
  
  <div class="admin-actions">
    <a href="/admin/users/create" class="btn btn-primary">Add New User</a>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
        <tr>
          <td><?= htmlspecialchars($user['id']) ?></td>
          <td><?= htmlspecialchars($user['username']) ?></td>
          <td><?= htmlspecialchars($user['email']) ?></td>
          <td><?= htmlspecialchars($user['role']) ?></td>
          <td class="actions">
            <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
            <a href="/admin/users/delete/<?= $user['id'] ?>" class="btn btn-sm btn-delete">Delete</a>
          </td>
        </tr>
      <?php endforeach;  ?>
    </tbody>
  </table>
</div>
