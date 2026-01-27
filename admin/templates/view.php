<?php
require_once __DIR__ . '/../../includes/header.php';

$templateModel = new NotificationTemplate($db);
$templateId = $_GET['id'] ?? null;

if (!$templateId || !($template = $templateModel->getById($templateId))) {
    header('Location: index.php');
    exit;
}

// Format variables and channels for display
$variables = implode(', ', json_decode($template['variables'], true));
$channels = implode(', ', json_decode($template['channels'], true));

?><div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <h1>View Notification Template</h1>
            
            <div class="card mb-4">
                <div class="card-header">
                    Template Details
                    <div class="float-right">
                        <a href="edit.php?id=<?= $templateId ?>" class="btn btn-sm btn-info">Edit</a>
                        <a href="index.php" class="btn btn-sm btn-secondary">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name</label>
                                <p class="form-control-static"><?= htmlspecialchars($template['name']) ?></p>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <p class="form-control-static"><?= htmlspecialchars($template['description']) ?></p>
                            </div>

                            <div class="form-group">
                                <label>Type</label>
                                <p class="form-control-static"><?= htmlspecialchars($template['type']) ?></p>
                            </div>

                            <div class="form-group">
                                <label>Available Variables</label>
                                <p class="form-control-static"><?= htmlspecialchars($variables) ?></p>
                            </div>

                            <div class="form-group">
                                <label>Delivery Channels</label>
                                <p class="form-control-static"><?= htmlspecialchars($channels) ?></p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    Preview
                                </div>
                                <div class="card-body">
                                    <h5>Subject:</h5>
                                    <p><?= htmlspecialchars($template['subject_template']) ?></p>
                                    <h5>Body:</h5>
                                    <pre><?= htmlspecialchars($template['body_template']) ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php';
