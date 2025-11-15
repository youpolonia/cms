require_once __DIR__ . '/../../includes/auth/CSRFToken.php';
/**
 * Forgot Password Form Template
 */

?><!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h1>Forgot Password</h1>
        <form action="/auth/forgot-password" method="POST">
            <input type="hidden" name="<?= \Includes\Auth\CSRFToken::getTokenName() ?>" value="<?= \Includes\Auth\CSRFToken::generate() ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
 required>
?>            </div>
            <button type="submit">Send Reset Link</button>
        </form>
        <p>Remember your password? <a href="/auth/login">Login here</a></p>
    </div>
</body>
</html>
