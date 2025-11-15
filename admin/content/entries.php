<?php
require_once __DIR__ . '/../../admin/includes/auth.php';
require_once __DIR__ . '/../../admin/models/contententry.php';

// Check permissions
if (!has_permission('content_manage')) {
    header('Location: /admin/dashboard.php');
    exit;
}

// Create ContentEntry instance
$contentEntry = new ContentEntry();

// Get content types for filter
$content_types = ContentType::getAll();

// Handle filtering and sorting
$current_type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'title';
$order = $_GET['order'] ?? 'asc';
$per_page = 20;
$current_page = max(1, (int)($_GET['page'] ?? 1));

// Get entries with filters, pagination and sorting
$entries = $contentEntry->getFilteredEntries([
    'type' => $current_type,
    'status' => $status,
    'date_from' => $start_date,
    'date_to' => $end_date,
    'search' => $search
], $current_page, $per_page, $sort, $order);

$total_entries = $contentEntry->countFilteredEntries([
    'type' => $current_type,
    'status' => $status,
    'date_from' => $start_date,
    'date_to' => $end_date,
    'search' => $search
]);

$total_pages = ceil($total_entries / $per_page);

?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Content Entries</title>
    <link rel="stylesheet" href="/admin/assets/css/content.css">
</head>
<body>
    <?php require_once __DIR__ . '/../../admin/includes/header.php'; 
?>    <main class="content-container">
        <h1>Content Entries</h1>
        
        <!-- Filter by content type -->
        <form method="get" class="filter-form">
            <div class="filter-row">
                <select name="type" onchange="this.form.submit()">
                    <option value="">All Content Types</option>
                    <?php foreach ($content_types as $type): ?>                        <option value="<?= htmlspecialchars($type->slug) ?>" <?= $current_type === $type->slug ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type->name) 
?>                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                </select>

                <input type="text" name="search" placeholder="Search titles..."
                       value="<?= htmlspecialchars($search) ?>"
                       onchange="this.form.submit()">
            </div>

            <div class="filter-row">
                <label>Date Range:</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"
                       onchange="this.form.submit()">
                <span>to</span>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"
                       onchange="this.form.submit()">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
                <a href="/admin/content/entry_edit.php" class="button">Add New</a>
            </div>
        </form>
        
        <!-- Entries table -->
        <table class="entries-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Last Modified</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= htmlspecialchars($entry->title) ?></td>
                        <td><?= htmlspecialchars($entry->type_name) ?></td>
                        <td><?= $entry->status === 'published' ? 'Published' : 'Draft' ?></td>
                        <td><?= date('M j, Y', strtotime($entry->updated_at)) ?></td>
                        <td>
                            <a href="/admin/content/entry_edit.php?id=<?= $entry->id ?>">Edit</a>
                            <a href="/admin/content/entry_preview.php?id=<?= $entry->id ?>" target="_blank">Preview</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&type=<?= $current_type ?>"
                       class="<?= $i == $current_page ? 'active' : '' ?>">
                        <?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <?php require_once __DIR__ . '/../../admin/includes/footer.php'; 
?></body>
</html>
<?php
// Helper function to build query string with new sort parameters
function build_query_with_sort($sort_field) {
    $params = $_GET;
    $current_sort = $params['sort'] ?? '';
    $current_order = $params['order'] ?? 'asc';
    
    if ($sort_field === $current_sort) {
        $params['order'] = $current_order === 'asc' ? 'desc' : 'asc';
    } else {
        $params['sort'] = $sort_field;
        $params['order'] = 'asc';
    }
    
    return http_build_query($params);
}

// Helper function to build query string with new page number
function build_query_with_page($page) {
    $params = $_GET;
    $params['page'] = $page;
    return http_build_query($params);
}

// Helper function to display sort arrow
function sort_arrow($field) {
    if (($_GET['sort'] ?? '') !== $field) {
        return '';
    }
    
    return ($_GET['order'] ?? 'asc') === 'asc' ? '↑' : '↓';
}
