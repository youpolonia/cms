/**
 * Edit User Form
 */
?><div class="admin-form">
    <h2>Edit User: <?= htmlspecialchars($user['username']) ?></h2>
    <form method="POST" action="/admin/users/update/<?= $user['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="_method" value="PUT">
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>"
 required>
            <
