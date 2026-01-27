<?php
declare(strict_types=1);

/**
 * Permission Matrix View
 * 
 * @package Admin
 * @subpackage Permissions
 */

// Check for direct access
defined('ADMIN_PATH') or exit('No direct script access allowed');

// Get current tenant from session or request
$currentTenant = $_SESSION['current_tenant_id'] ?? ($_GET['tenant_id'] ?? null);

// Get permissions data from controller
$permissions = $this->data['permissions'] ?? [];
$roles = $this->data['roles'] ?? [];
$modules = $this->data['modules'] ?? [];
$tenants = $this->data['tenants'] ?? [];

// Include common admin header
require_once __DIR__ . '/../common/header.php';

?><div class="admin-container">
    <div class="admin-header">
        <h1>Permission Matrix</h1>
        
        <!-- Tenant Filter -->
        <div class="admin-filter">
            <form method="get" action="<?php echo ADMIN_BASE_URL; ?>/permissions/matrix">
                <label for="tenant-filter">Filter by Tenant:</label>
                <select id="tenant-filter" name="tenant_id" onchange="this.form.submit()">
                    <option value="">All Tenants</option>
                    <?php foreach ($tenants as $tenant): ?>
                        <option value="<?php echo htmlspecialchars($tenant['id']); ?>" 
                            <?php echo ($currentTenant == $tenant['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tenant['name']);  ?>
                        </option>
                    <?php endforeach;  ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Permission Matrix Table -->
    <div class="admin-table-container">
        <table class="permission-matrix">
            <thead>
                <tr>
                    <th>Module/Permission</th>
                    <?php foreach ($roles as $role): ?>
                        <th><?php echo htmlspecialchars($role['name']); ?></th>
                    <?php endforeach;  ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $module): ?>
                    <tr class="module-row">
                        <td colspan="<?php echo count($roles) + 1; ?>">
                            <strong><?php echo htmlspecialchars($module['name']); ?></strong>
                        </td>
                    </tr>
                    
                    <?php foreach ($module['permissions'] as $perm): ?>
                        <tr class="permission-row">
                            <td><?php echo htmlspecialchars($perm['description']); ?></td>
                            <?php foreach ($roles as $role): ?>
                                <td>
                                    <?php 
                                    $hasPerm = in_array($perm['id'], $permissions[$role['id']] ?? []);
                                    $permId = "perm_{$role['id']}_{$perm['id']}";
                                    ?>
                                    <input type="checkbox" 
                                           id="<?php echo $permId; ?>"
                                           name="permissions[<?php echo $role['id']; ?>][]"
                                           value="<?php echo $perm['id']; ?>"
                                           <?php echo $hasPerm ? 'checked' : ''; ?>
                                           data-role="<?php echo $role['id']; ?>"
                                           data-perm="<?php echo $perm['id']; ?>">
                                    <label for="<?php echo $permId; ?>"></label>
                                </td>
                            <?php endforeach;  ?>
                        </tr>
                    <?php endforeach;  ?>
                <?php endforeach;  ?>
            </tbody>
        </table>
    </div>

    <!-- Save Button with Edit Integration -->
    <div class="admin-actions">
        <button type="button" 
                class="admin-button primary"
                onclick="savePermissions()">
            Save Changes
        </button>
        <a href="<?php echo ADMIN_BASE_URL; ?>/permissions/edit" 
           class="admin-button secondary">
            Edit Roles
        </a>
    </div>
</div>

<script>
function savePermissions() {
    const formData = new FormData();
    const checkboxes = document.querySelectorAll('.permission-matrix input[type="checkbox"]:checked');
    
    checkboxes.forEach(checkbox => {
        formData.append(`permissions[${checkbox.dataset.role}][]`, checkbox.dataset.perm);
    });

    fetch('<?php echo ADMIN_BASE_URL; ?>/permissions/update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Permissions updated successfully');
        } else {
            alert('Error updating permissions: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred');
    });
}
</script>

<?php
// Include common admin footer
require_once __DIR__ . '/../common/footer.php';