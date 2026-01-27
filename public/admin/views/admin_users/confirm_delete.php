<?php
/**
 * Delete User Confirmation
 */

?><div class="admin-confirm">
    <h2>Delete User: <?= htmlspecialchars($user['username']) ?></h2>
    <div class="alert alert-warning">
        <p>Are you sure you want to permanently delete this user? This action cannot be undone.</p>
    </div>

    <form method="POST" action="/admin/users/delete/<?= $user['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="_method" value="DELETE">
        
        <div class="form-actions">
            <button type="submit" class="btn btn-danger">Confirm Delete</button>
            <a href="/admin/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
