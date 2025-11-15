<?php
require_once __DIR__ . '/../../includes/header.php';

// Initialize template model
$templateModel = new NotificationTemplate($db);

// Handle delete action
if (isset($_GET['delete'])) {
    $templateModel->delete($_GET['delete']);
    header('Location: index.php');
    exit;
}

// Get paginated templates
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$templates = $templateModel->getPaginated($page, $perPage);
$totalTemplates = $templateModel->getCount();
$totalPages = ceil($totalTemplates / $perPage);
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <h1>Notification Templates</h1>
            
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Templates List</span>
                        <a href="create.php" class="btn btn-primary">Create New</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($templates)): ?>
<div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Variables</th>
                                        <th>Channels</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($templates as $template): ?>
<tr>
                                            <td><?= htmlspecialchars($template['template_id']) ?></td>
                                            <td><?= htmlspecialchars($template['name']) ?></td>
                                            <td><?= htmlspecialchars($template['type']) ?></td>
                                            <td><?= implode(', ', json_decode($template['variables'], true)) ?></td>
                                            <td><?= implode(', ', json_decode($template['channels'], true)) ?></td>
                                            <td>
                                                <a href="edit.php?id=<?= $template['template_id'] ?>" class="btn btn-sm btn-info">Edit</a>
                                                <a href="view.php?id=<?= $template['template_id'] ?>" class="btn btn-sm btn-secondary">View</a>
                                                <a href="index.php?delete=<?= $template['template_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($totalPages > 1): ?>
<nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
<li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="index.php?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>                    <?php else: ?>
<div class="alert alert-info">No templates found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php';
