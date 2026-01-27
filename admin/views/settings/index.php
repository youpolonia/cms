<?php
/**
 * Settings Index View
 * Lists all settings grouped by group_name
 *
 * Variables:
 *   $settings - array of settings grouped by group_name
 *   $groups - array of unique group names
 *   $currentGroup - current group filter (or null)
 *   $search - current search term (or null)
 *   $totalCount - total number of settings
 */

// Escape helper
if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="content-header">
    <h1>Settings</h1>
    <div class="header-actions">
        <a href="create.php" class="btn btn-primary">Add New Setting</a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php echo esc($_GET['message'] ?? 'Operation completed successfully'); ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php echo esc($_GET['error']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="filters-row">
            <form method="get" action="" class="filter-form">
                <div class="filter-group">
                    <label for="group">Filter by Group:</label>
                    <select name="group" id="group" class="form-control" onchange="this.form.submit()">
                        <option value="">All Groups</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?php echo esc($group); ?>" <?php echo ($currentGroup === $group) ? 'selected' : ''; ?>>
                                <?php echo esc(ucfirst($group)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="search">Search:</label>
                    <input type="text" name="search" id="search" class="form-control"
                           value="<?php echo esc($search); ?>" placeholder="Search key or value...">
                    <button type="submit" class="btn btn-secondary">Search</button>
                </div>

                <?php if ($search || $currentGroup): ?>
                    <a href="index.php" class="btn btn-outline">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="header-info">
            <span class="badge"><?php echo (int) $totalCount; ?> total settings</span>
        </div>
    </div>

    <div class="card-body">
        <?php if (empty($settings)): ?>
            <div class="empty-state">
                <p>No settings found.</p>
                <a href="create.php" class="btn btn-primary">Add Your First Setting</a>
            </div>
        <?php else: ?>
            <?php foreach ($settings as $groupName => $groupSettings): ?>
                <div class="settings-group">
                    <h3 class="group-header">
                        <?php echo esc(ucfirst($groupName ?: 'General')); ?>
                        <span class="badge badge-secondary"><?php echo count($groupSettings); ?></span>
                    </h3>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                                <th>Last Updated</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groupSettings as $setting): ?>
                                <tr>
                                    <td>
                                        <code><?php echo esc($setting['key']); ?></code>
                                    </td>
                                    <td class="value-cell">
                                        <?php
                                        $value = $setting['value'] ?? '';
                                        $displayValue = strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
                                        echo esc($displayValue);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($setting['updated_at']) {
                                            echo date('M j, Y g:i A', strtotime($setting['updated_at']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?php echo (int) $setting['id']; ?>"
                                           class="btn btn-sm btn-secondary">Edit</a>
                                        <form method="post" action="delete.php" style="display: inline;"
                                              onsubmit="return confirm('Are you sure you want to delete this setting?');">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo (int) $setting['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.filters-row {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
}
.filter-form {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
}
.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.filter-group label {
    font-size: 0.875rem;
    color: #666;
}
.settings-group {
    margin-bottom: 2rem;
}
.group-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #ddd;
}
.value-cell {
    max-width: 400px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
}
.header-info {
    margin-top: 0.5rem;
}
code {
    background: #f4f4f4;
    padding: 0.125rem 0.375rem;
    border-radius: 3px;
    font-size: 0.875rem;
}
</style>
