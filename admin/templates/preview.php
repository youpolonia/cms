<?php
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__.'/../../includes/init.php';

$templateModel = new NotificationTemplate($db);
$templateId = $_GET['id'] ?? null;

if (!$templateId || !($template = $templateModel->getById($templateId))) {
    header('Location: index.php');
    exit;
}

// Process preview request
$previewData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    foreach (json_decode($template['variables'], true) as $var) {
        $previewData[$var] = $_POST[$var] ?? '';
    }
}

// Include admin header
require_once __DIR__.'/../templates/header.php';

?><div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Preview Template: <?= htmlspecialchars($template['name']) ?></h4>
                </div>
                <div class="card-body">
                    <form method="post">
                        <?= csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Template Variables</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach (json_decode($template['variables'], true) as $var): ?>
                                            <div class="form-group">
                                                <label for="<?= $var ?>"><?= ucfirst(str_replace('_', ' ', $var)) ?></label>
                                                <input type="text" class="form-control" id="<?= $var ?>" name="<?= $var ?>" 
                                                       value="<?= htmlspecialchars($previewData[$var] ?? '') ?>">
                                            </div>
                                        <?php endforeach; ?>
                                        <button type="submit" class="btn btn-primary">Generate Preview</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Preview Output</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                                            <div class="mb-4">
                                                <h6>Subject:</h6>
                                                <div class="p-3 bg-light rounded">
                                                    <?php
                                                    $search = array_map(function($k) { return '{'.$k.'}'; }, array_keys($previewData));
                                                    echo str_replace($search, array_values($previewData), $template['subject_template']);
?>                                                </div>
                                            </div>
                                            <div>
                                                <h6>Body:</h6>
                                                <div class="p-3 bg-light rounded">
                                                    <?php
                                                    $search = array_map(function($k) { return '{'.$k.'}'; }, array_keys($previewData));
                                                    echo str_replace($search, array_values($previewData), $template['body_template']);
?>                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">
                                                Fill in the template variables and click "Generate Preview" to see the output.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <a href="view.php?id=<?= $templateId ?>" class="btn btn-secondary">Back to Template</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
require_once __DIR__.'/../templates/footer.php';
