<?php 
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../core/csrf.php';

?><div class="admin-container">
    <h1>Notifications</h1>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif;  ?>
    <div class="notification-filters mb-4">
        <a href="?filter=" class="btn btn-sm btn-<?= empty($filter) ? 'primary' : 'outline-secondary' ?>">All</a>
        <a href="?filter=info" class="btn btn-sm btn-<?= $filter === 'info' ? 'primary' : 'outline-secondary' ?>">Info</a>
        <a href="?filter=success" class="btn btn-sm btn-<?= $filter === 'success' ? 'primary' : 'outline-secondary' ?>">Success</a>
        <a href="?filter=warning" class="btn btn-sm btn-<?= $filter === 'warning' ? 'primary' : 'outline-secondary' ?>">Warning</a>
        <a href="?filter=error" class="btn btn-sm btn-<?= $filter === 'error' ? 'primary' : 'outline-secondary' ?>">Error</a>
    </div>

    <div class="notification-list">
        <?php if (empty($paginatedNotifications)): ?>
            <div class="alert alert-info">No notifications found</div>
        <?php else: ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedNotifications as $notification): ?>
                        <tr class="<?= $notification['is_read'] ? '' : 'unread' ?>">
                            <td>
                                <span class="badge badge-<?= $notification['type'] ?>">
                                    <?= ucfirst($notification['type'])  ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($notification['message']) ?></td>
                            <td><?= date('M j, Y g:i a', strtotime($notification['created_at'])) ?></td>
                            <td><?= $notification['is_read'] ? 'Read' : 'Unread' ?></td>
                            <td>
                                <?php if (!$notification['is_read']): ?>
                                    <form method="post" style="display:inline;">
                                        <?= csrf_field();  ?>
                                        <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                        <button type="submit" name="mark_as_read" class="btn btn-sm btn-outline-primary">
                                            Mark as Read
                                        </button>
                                    </form>
                                <?php endif;  ?>
                            </td>
                        </tr>
                    <?php endforeach;  ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page-1 ?><?= $filter ? '&filter='.$filter : '' ?>">Previous</a>
                            </li>
                        <?php endif;  ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++):  ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $filter ? '&filter='.$filter : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor;  ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page+1 ?><?= $filter ? '&filter='.$filter : '' ?>">Next</a>
                            </li>
                        <?php endif;  ?>
                    </ul>
                </nav>
            <?php endif;  ?>        <?php endif;  ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
