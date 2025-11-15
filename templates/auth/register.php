/**
 * Registration Form Template
 */

?><!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h1>Register</h1>
        <form action="/auth/register" method="POST">
            <input type="hidden" name="<?php echo \Includes\Auth\CSRFToken::getTokenName(); ?>" value="<?php echo \Includes\Auth\CSRFToken::generate(); ?>">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="/auth/login">Login here</a></p>
    </div>
</body>
</html>
