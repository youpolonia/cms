/**
 * Plugins Index View
 */
?><div class="container">
    <h1>Installed Plugins</h1>
    
    <div class="mb-3">
        <a href="/admin/plugins/install" class="btn btn-primary">
            Install New Plugin
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Version</th>
                        <th>Author</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plugins as $plugin): ?>
                    <tr>
                        <td><?= htmlspecialchars($plugin['name']) ?></td>
                        <td><?= htmlspecialchars($plugin['version']) ?></td>
                        <td><?= htmlspecialchars($plugin['author']) ?></td>
                        <td>
                            <a href="/admin/plugins/<?= $plugin['id'] ?>" class="btn btn-sm btn-info">
                                View
                            </a>
                            <a href="/admin/plugins/<?= $plugin['id'] ?>/uninstall" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure?')">
                                Uninstall
?>                            </a>
                        </td>
                    </tr>
                    <?php endforeach;  ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
