<?php
/**
 * User Management Index View
 * Lists all users with their roles
 */
?><div class="container">
    <h1>User Management</h1>
    
    <?php if ($message = $response->getFlash('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>    <?php if ($message = $response->getFlash('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="mb-3">
        <a href="/admin/users/create" class="btn btn-primary">Create New User</a>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['roles'] ? htmlspecialchars($user['roles']) : 'None' ?></td>
                <td>
                    <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="/admin/users/<?= $user['id'] ?>/delete" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="
return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach;  ?>
        </tbody>
    </table>
</div>
