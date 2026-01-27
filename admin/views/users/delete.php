<?php
// Check if user data was passed
$user = $data['user'] ?? null;


?><div class="admin-container">
  <h2>Delete User</h2>
  
  <div class="confirmation-message">
    <p>Are you sure you want to delete user <strong><?= htmlspecialchars($user['username'] ?? '') ?></strong>?</p>
    <p>This action cannot be undone.</p>
  </div>

  <form method="post" action="/admin/users/destroy/<?= $user['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <div class="form-actions">
      <button type="submit" class="btn btn-danger">Confirm Delete</button>
      <a href="/admin/users" class="btn btn-cancel">Cancel</a>
    </div>
  </form>
</div>
