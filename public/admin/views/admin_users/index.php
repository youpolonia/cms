<?php
/**
 * Admin Users Listing
 */
?><div class="admin-users">
    <h2>User Management</h2>
    
    <div class="actions">
        <a href="/admin/users/create" class="btn">Add New User</a>
    </div>

    <?php if (!empty($users)): ?>
        <table class="user-table">
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
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn">Edit</a>
                            <a href="/admin/users/delete/<?= $user['id'] ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="pagination">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="?page=<?= $pagination['current_page'] - 1 ?>">&laquo; Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <a href="?page=<?= $i ?>" <?= ($i == $pagination['current_page']) ? 'class="active"' : '' ?>>
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                    <a href="?page=<?= $pagination['current_page'] + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>
</div>
