/**
 * Login Form Template
 */

?><!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h1>Login</h1>
        <form action="/auth/login" method="POST">
            <input type="hidden" name="<?php echo \Includes\Auth\CSRFToken::getTokenName(); ?>" value="<?php echo \Includes\Auth\CSRFToken::generate(); ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="/auth/register">Register here</a></p>
        <p>Forgot password? <a href="/auth/reset-password">Reset here</a></p>
    </div>
</body>
</html>
