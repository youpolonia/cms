<?php
require_once __DIR__ . '/../../../includes/header.php';

?><div class="container">
    <h1>Edit Priority Queue</h1>
    
    <form method="POST" action="/admin/priority_queue/update.php">
        <input type="hidden" name="id" value="<?= $queue['id'] ?>">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name"
                   value="<?= htmlspecialchars($queue['name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="priority_level">Priority Level (1-10)</label>
            <input type="number" class="form-control" id="priority_level" name="priority_level"
                   min="1" max="10" value="<?= $queue['priority_level'] ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="/admin/priority_queue/index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
require_once __DIR__ . '/../../../includes/footer.php';
