<?php require_once __DIR__ . '/../includes/header.php'; 
?><div class="container">
    <h1>User Activation</h1>
    
    <?php if (!hasAnyPermission(['manage_user_activation'])): ?>
        <div class="alert alert-danger">You don't have permission to manage user activation</div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td>
                            <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $user['is_active'] ? 'Active' : 'Inactive' 
?>                            </span>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-activation" 
                                       type="checkbox" 
                                       data-user-id="<?= $user['id'] ?>"
                                       <?= $user['is_active'] ? 'checked' : '' ?>>
                            </div>
                        </td>
                    </tr>
                <?php endforeach;  ?>
            </tbody>
        </table>

        <div class="modal fade" id="activationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Activation Change</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to change this user's activation status?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmActivation">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif;  ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.toggle-activation');
    const modal = new bootstrap.Modal(document.getElementById('activationModal'));
    let currentToggle = null;

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            currentToggle = this;
            modal.show();
        });
    });

    document.getElementById('confirmActivation').addEventListener('click', function() {
        const userId = currentToggle.dataset.userId;
        const isActive = currentToggle.checked ? 1 : 0;

        fetch('/admin/api/user/activation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
            },
            body: JSON.stringify({ user_id: userId, is_active: isActive })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = currentToggle.closest('tr').querySelector('.badge');
                badge.className = isActive ? 'badge bg-success' : 'badge bg-danger';
                badge.textContent = isActive ? 'Active' : 'Inactive';
            } else {
                currentToggle.checked = !currentToggle.checked;
                alert('Error: ' + (data.message || 'Failed to update activation status'));
            }
            modal.hide();
        })
        .catch(error => {
            console.error('Error:', error);
            currentToggle.checked = !currentToggle.checked;
            modal.hide();
        });
    });
});
?></script>

<?php require_once __DIR__ . '/admin_footer.php';
