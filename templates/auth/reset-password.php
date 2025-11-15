require_once __DIR__ . '/../../includes/auth/CSRFToken.php';
/**
 * Reset Password Form Template
 */

?><!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h1>Reset Password</h1>
        <form action="/auth/reset-password" method="POST">
            <input type="hidden" name="<?= \Includes\Auth\CSRFToken::getTokenName() ?>" value="<?= \Includes\Auth\CSRFToken::generate() ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
 required>
?>            </div>
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password"
 required>
?>            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
 required>
?>            </div>
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
