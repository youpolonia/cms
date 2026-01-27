<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="container">
    <h1>System Backups</h1>
    
    <?php if (isset($_GET['created'])): ?>
        <div class="alert alert-success">
            Backup created successfully: <?php echo htmlspecialchars($_GET['created']); ?>
        </div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-header">Create New Backup</div>
        <div class="card-body">
            <p>Backups include configuration files and a database dump (if available).</p>
            <a href="<?php echo ADMIN_BASE; ?>/system/backups/create" class="btn btn-primary">
                Create Backup Now
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Existing Backups</div>
        <div class="card-body">
            <?php if (empty($backups)): ?>
                <p>No backups available</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                <td><?php echo $this->fileUtils->formatBytes($backup['size']); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', $backup['date']); ?></td>
                                <td>
                                    <a href="<?php echo ADMIN_BASE; ?>/system/backups/download/<?php echo urlencode($backup['name']); ?>" 
                                       class="btn btn-sm btn-primary">Download</a>
                                    <a href="<?php echo ADMIN_BASE; ?>/system/backups/delete/<?php echo urlencode($backup['name']); ?>" 
                                       class="btn btn-sm btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php';