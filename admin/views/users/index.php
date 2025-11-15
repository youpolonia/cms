<?php require_once __DIR__ . '/../admin_header.php'; ?>
<div class="container">
    <h1>User Management</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                </tr>
            <?php endforeach;  ?>
        </tbody>
    </table>

    <nav aria-label="Page navigation">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++):  ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor;  ?>
        </ul>
    </nav>
</div>

<?php require_once __DIR__ . '/../admin_footer.php';
