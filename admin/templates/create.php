<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../core/csrf.php';

$templateModel = new NotificationTemplate($db);
$errors = [];
$template = [
    'name' => '',
    'description' => '',
    'type' => 'email',
    'subject_template' => '',
    'body_template' => '',
    'variables' => '[]',
    'channels' => '["email"]'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    // Validate and sanitize input
    $template['name'] = trim($_POST['name'] ?? '');
    $template['description'] = trim($_POST['description'] ?? '');
    $template['type'] = $_POST['type'] ?? 'email';
    $template['subject_template'] = trim($_POST['subject_template'] ?? '');
    $template['body_template'] = trim($_POST['body_template'] ?? '');
    
    // Process variables
    $variables = [];
    if (!empty($_POST['variables'])) {
        $variables = array_map('trim', explode(',', $_POST['variables']));
    }
    $template['variables'] = json_encode($variables);
    
    // Process channels
    $channels = [];
    if (!empty($_POST['channels'])) {
        $channels = is_array($_POST['channels']) ? $_POST['channels'] : [$_POST['channels']];
    }
    $template['channels'] = json_encode($channels);

    // Validate
    if (empty($template['name'])) {
        $errors['name'] = 'Name is required';
    }
    if (empty($template['subject_template'])) {
        $errors['subject_template'] = 'Subject template is required';
    }
    if (empty($template['body_template'])) {
        $errors['body_template'] = 'Body template is required';
    }

    if (empty($errors)) {
        if ($templateModel->create($template)) {
            header('Location: index.php');
            exit;
        } else {
            $errors['general'] = 'Failed to create template';
        }
    }
}

?><div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <h1>Create Notification Template</h1>
            
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif;  ?>
            <div class="card mb-4">
                <div class="card-header">
                    Template Details
                </div>
                <div class="card-body">
                    <form method="post">
                        <?= csrf_field();  ?>
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" 
                                   id="name" name="name" value="<?= htmlspecialchars($template['name']) ?>"
 required>
                            <?php if (!empty($errors['name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                            <?php endif;  ?>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="2"><?= htmlspecialchars($template['description']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="type">Type</label>
                            <select class="form-control" id="type" name="type">
                                <option value="email" <?= $template['type'] === 'email' ? 'selected' : '' ?>>Email</option>
                                <option value="sms" <?= $template['type'] === 'sms' ? 'selected' : '' ?>>SMS</option>
                                <option value="push" <?= $template['type'] === 'push' ? 'selected' : '' ?>>Push Notification</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subject_template">Subject Template *</label>
                            <input type="text" class="form-control <?= !empty($errors['subject_template']) ? 'is-invalid' : '' ?>" 
                                   id="subject_template" name="subject_template" 
                                   value="<?= htmlspecialchars($template['subject_template']) ?>"
 required>
                            <?php if (!empty($errors['subject_template'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['subject_template']) ?></div>
                            <?php endif;  ?>
                        </div>

                        <div class="form-group">
                            <label for="body_template">Body Template *</label>
                            <textarea class="form-control <?= !empty($errors['body_template']) ? 'is-invalid' : '' ?>" 
                                      id="body_template" name="body_template" rows="5"
 required><?= htmlspecialchars($template['body_template']) ?></textarea>
                            <?php if (!empty($errors['body_template'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['body_template']) ?></div>
                            <?php endif;  ?>
                        </div>

                        <div class="form-group">
                            <label for="variables">Available Variables (comma separated)</label>
                            <input type="text" class="form-control" id="variables" name="variables" 
                                   value="<?= implode(', ', json_decode($template['variables'], true)) ?>">
                            <small class="form-text text-muted">Example: first_name, last_name, order_number</small>
                        </div>

                        <div class="form-group">
                            <label>Delivery Channels</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="channels[]" 
                                       id="channel_email" value="email" <?= in_array('email', json_decode($template['channels'], true)) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="channel_email">Email</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="channels[]" 
                                       id="channel_sms" value="sms" <?= in_array('sms', json_decode($template['channels'], true)) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="channel_sms">SMS</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="channels[]" 
                                       id="channel_push" value="push" <?= in_array('push', json_decode($template['channels'], true)) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="channel_push">Push Notification</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Template</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php';
