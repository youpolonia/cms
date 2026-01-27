<?php
require_once __DIR__.'/../layout.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Validate input on form submission
$errors = [];
$input = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Invalid CSRF token. Please try submitting the form again.';
    }
    
    // Sanitize and validate inputs
    $input['title'] = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $input['message'] = filter_input(INPUT_POST, 'message', FILTER_UNSAFE_RAW);
    $input['user_id'] = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $input['category_id'] = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);

    // Title validation
    if (empty($input['title'])) {
        $errors['title'] = 'Title is required';
    } elseif (mb_strlen($input['title']) > 255) {
        $errors['title'] = 'Title cannot exceed 255 characters';
    }

    // Message validation
    if (empty($input['message'])) {
        $errors['message'] = 'Message is required';
    } elseif (mb_strlen($input['message']) > 2000) {
        $errors['message'] = 'Message cannot exceed 2000 characters';
    } elseif (preg_match_all('/\{\{(\w+)\}\}/', $input['message'], $matches)) {
        $validVariables = ['name', 'email', 'join_date'];
        foreach ($matches[1] as $var) {
            if (!in_array($var, $validVariables, true)) {
                $errors['message'] = "Invalid template variable: {{$var}}";
                break;
            }
        }
    }

    // User ID validation
    if (empty($input['user_id']) || !User::exists($input['user_id'])) {
        $errors['user_id'] = 'Invalid user selected';
    }

    // Category ID validation (optional)
    if ($input['category_id'] && !Category::exists($input['category_id'])) {
        $errors['category_id'] = 'Invalid category selected';
    }

    // If no errors, proceed with creation via API
    if (empty($errors) && empty($errors['csrf'])) {
        $formData = new FormData();
        // Form submission handled by create_notification.js
        unset($_SESSION['csrf_token']);
    }
}

?><div class="container">
    <h1>Create Notification</h1>
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['message'])  ?>
        </div>
        <?php unset($_SESSION['flash']);  ?>
    <?php endif;  ?>    <?php if (isset($errors['csrf'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['csrf']) ?></div>
    <?php endif;  ?>
    <form id="notification-form" method="post">
        <?= \Core\Security\CSRFToken::getInputField()  ?>
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" id="title" name="title"
                           value="<?= isset($input['title']) ? htmlspecialchars($input['title']) : '' ?>"
 required>
                    <?php if (isset($errors['title'])): ?>
                        <div class="text-danger"><?= htmlspecialchars($errors['title']) ?></div>
                    <?php endif;  ?>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5"
                              required><?= isset($input['message']) ? htmlspecialchars($input['message']) : '' ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <div class="text-danger"><?= htmlspecialchars($errors['message']) ?></div>
                    <?php endif;  ?>
                </div>

                <div class="form-group">
                    <label for="user_id">Recipient</label>
                    <select class="form-control" id="user_id" name="user_id"
 required>
                        <?php if (isset($errors['user_id'])): ?>
                            <div class="text-danger"><?= htmlspecialchars($errors['user_id']) ?></div>
                        <?php endif;  ?>                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                            </option>
                        <?php endforeach;  ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="category_id">Category (Optional)</label>
                    <select class="form-control" id="category_id" name="category_id">
                        <option value="">No Category</option>
                        <?php foreach ($categories as $category): ?>                            <option value="<?= $category['id'] ?>"
                                <?= (isset($input['category_id']) && $input['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name'])  ?>
                            </option>
                        <?php endforeach;  ?>
                    </select>
                </div>
            </div>
            
            <div class="card-footer">
                <button type="submit" class="btn btn-primary" id="submit-btn">Create Notification</button>
                <a href="/admin/notifications" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

// PHP content continues here if needed

?><script src="/admin/views/notifications/create_notification.js"></script>
