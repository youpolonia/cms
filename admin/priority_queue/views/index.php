<?php
require_once __DIR__ . '/../../../includes/header.php';

?><div class="container">
    <h1>Priority Queues</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Priority Level</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($queues as $queue): ?>
            <tr>
                <td><?= htmlspecialchars($queue['id']) ?></td>
                <td><?= htmlspecialchars($queue['name']) ?></td>
                <td><?= htmlspecialchars($queue['priority_level']) ?></td>
                <td>
                    <a href="/admin/priority_queue/edit.php?id=<?= $queue['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                </td>
            </tr>
            <?php endforeach;  ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../../../includes/footer.php';
