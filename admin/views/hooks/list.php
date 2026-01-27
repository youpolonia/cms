<?php
/**
 * Hooks List View
 */
?><div class="container">
    <h1>System Hooks</h1>
    
    <div class="mb-3">
        <a href="/admin/hooks/create" class="btn btn-primary">Create New Hook</a>
    </div>

    <?php if (count($hooks) > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hooks as $hook): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hook['name']); ?></td>
                        <td><?php echo htmlspecialchars($hook['description'] ?? ''); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($hook['created_at'])); ?></td>
                        <td>
                            <a href="/admin/hooks/<?php echo $hook['id']; ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="/admin/hooks/<?php echo $hook['id']; ?>/delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No hooks found</div>
    <?php endif; ?>
</div>
