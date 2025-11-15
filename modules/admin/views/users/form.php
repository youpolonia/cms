<?php
/**
 * User Form View
 * Handles both create and edit forms
 */
?><div class="container">
    <h1><?= isset($user) ? 'Edit User' : 'Create User' ?></h1>

    <?php if ($message = $response->getFlash('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= isset($user) ? '/admin/users/'.$user['id'].'/edit' : '/admin/users/create' ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?= isset($user) ? htmlspecialchars($user['name']) : '' ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= isset($user) ? htmlspecialchars($user['email']) : '' ?>" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" 
                   <?= !isset($user) ? 'required' : '' ?>>
            <?php if (isset($user)): ?>
                <small class="text-muted">Leave blank to keep current password</small>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Roles</label>
            <?php foreach ($roles as $role): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="roles[]"
                           value="<?= $role['id'] ?>" id="role_<?= $role['id'] ?>"
                           <?= isset($userRoles) && in_array($role['id'], $userRoles) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="role_<?= $role['id'] ?>">
                        <?= htmlspecialchars($role['name']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="/admin/users" class="btn btn-secondary">Cancel</a>
    </form>
</div>
