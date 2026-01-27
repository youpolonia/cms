<?php require_once __DIR__ . '/../includes/header.php'; 
?><div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Content Moderation Queue</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pending Review</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="moderationQueue" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Priority</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($queueItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['id']); ?></td>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo htmlspecialchars($item['author']); ?></td>
                            <td>
                                <span class="badge badge-<?php
                                    echo $item['priority'] === 'high' ? 'danger' : 
                                    ($item['priority'] === 'medium' ? 'warning' : 'primary');
                                ?>">
                                    <?php echo ucfirst($item['priority']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y H:i', strtotime($item['created_at'])); ?></td>
                            <td>
                                <a href="/admin/moderation/review/<?php echo $item['content_id']; ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </td>
                        </tr>
                        <?php endforeach;  ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
