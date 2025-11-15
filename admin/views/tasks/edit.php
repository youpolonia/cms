<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/validation_helpers.php';

?><div class="container">
    <h1>Edit Task</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif;  ?>    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif;  ?>
    <form method="POST" action="/admin/tasks/update" onsubmit="return validateForm()">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
        <div class="form-group">
            <label for="title">Task Title</label>
            <input type="text" class="form-control" id="title" name="title"
                   value="<?php echo htmlspecialchars($task['title'] ?? ''); ?>"
                   minlength="3" maxlength="100"
                   required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description"
                      rows="5"><?php echo htmlspecialchars($task['description'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="is_completed" name="is_completed"
                       <?php echo ($task['is_completed'] ?? false) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_completed">Completed</label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Task</button>
    </form>
    
    <script>
    function validateForm() {
        const title = document.getElementById('title');
        
        if (title.value.length < 3 || title.value.length > 100) {
            alert('Title must be between 3-100 characters');
            return false;
        }
        
        return true;
    }
    </script>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';