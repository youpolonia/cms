/**
 * Users Management View
 */

<?php require_once __DIR__ . '/layout.php'; 
?><div class="container">
    <h1><?= htmlspecialchars($title) ?></h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Created</th>
                <th>Last Login</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['created_at']) ?></td>
                <td><?= htmlspecialchars($user['last_login'] ?? 'Never') ?></td>
            </tr>
            <?php endforeach;  ?>
        </tbody>
    </table>
</div>
