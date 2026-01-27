<?php
// Check if user data was passed (for edit mode)
$user = $data['user'] ?? null;
$isEdit = isset($user['id']);
$roles = $data['roles'] ?? [];

?><div class="admin-container">
  <h2><?= $isEdit ? 'Edit User' : 'Create User' ?></h2>
  <form method="post" action="/admin/users/<?= $isEdit ? 'update/' . $user['id'] : 'store' ?>">
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" 
             value="<?= htmlspecialchars($user['username'] ?? '') ?>"
 required>
?>    </div>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" 
             value="<?= htmlspecialchars($user['email'] ?? '') ?>"
 required>
?>    </div>

    <div class="form-group">
      <label for="password"><?= $isEdit ? 'New Password (leave blank to keep current)' : 'Password' ?></label>
      <input type="password" id="password" name="password" <?= $isEdit ? '' : 'required' ?>>
    </div>

    <div class="form-group">
      <label for="role">Role</label>
      <select id="role" name="role"
 required>
        <?php foreach ($roles as $role): ?>          <option value="<?= htmlspecialchars($role['id']) ?>" 
            <?= ($user['role_id'] ?? null) == $role['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($role['name'])  ?>
          </option>
        <?php endforeach;  ?>
      </select>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save</button>
      <a href="/admin/users" class="btn btn-cancel">Cancel</a>
    </div>
  </form>
</div>
