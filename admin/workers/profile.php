<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
// Check admin access
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../core/csrf.php';

$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

$connection = new Connection();
$workerModel = new HumanWorker($connection);

// Get current worker data
$workerId = (int)($_GET['id'] ?? 0);
$worker = $workerModel->getById($workerId);

if (!$worker) {
    header('Location: /admin/workers');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!$auth->validateCsrfToken($csrfToken)) {
        die('Invalid CSRF token');
    }

    // Update profile info
    if (isset($_POST['update_profile'])) {
        $data = [
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'id' => $workerId
        ];
        
        if ($workerModel->update($data)) {
            $success = 'Profile updated successfully';
            $worker = $workerModel->getById($workerId); // Refresh data
        }
    }

    // Update password
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if ($new === $confirm) {
            if ($workerModel->verifyPassword($worker['id'], $current)) {
                if ($workerModel->updatePassword($worker['id'], $new)) {
                    $success = 'Password changed successfully';
                }
            } else {
                $error = 'Current password is incorrect';
            }
        } else {
            $error = 'New passwords do not match';
        }
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture'])) {
        $uploadDir = __DIR__ . '/../../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file = $_FILES['profile_picture'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $workerId . '_' . time() . '.' . $ext;
            $dest = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                if ($workerModel->updateProfilePicture($workerId, '/uploads/profiles/' . $filename)) {
                    $success = 'Profile picture updated';
                    $worker = $workerModel->getById($workerId); // Refresh data
                }
            }
        }
    }
}

// Prepare view
$title = 'Worker Profile: ' . htmlspecialchars($worker['first_name']) . ' ' . htmlspecialchars($worker['last_name']);
ob_start();
?><h2>Worker Profile</h2>

<?php if (isset($success)): ?>
    <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?><?php if (isset($error)): ?>
    <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<div class="profile-container">
    <div class="profile-sidebar">
        <div class="profile-picture">
            <?php if ($worker['profile_picture']): ?>
                <img src="<?php echo htmlspecialchars($worker['profile_picture']); ?>" alt="Profile Picture">
            <?php else: ?>
                <div class="placeholder">No Image</div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                <input type="file" name="profile_picture" accept="image/*">
                <button type="submit" name="upload_picture">Upload</button>
            </form>
        </div>
    </div>

    <div class="profile-content">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($worker['first_name']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($worker['last_name']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($worker['email']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($worker['phone']); ?>">
            </div>

            <button type="submit" name="update_profile">Save Changes</button>
        </form>

        <h3>Change Password</h3>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password"
                       required>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password"
                       required>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password"
                       required>
            </div>

            <button type="submit" name="change_password">Change Password</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
