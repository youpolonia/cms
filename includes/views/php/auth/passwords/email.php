<?php
/**
 * Password reset request form - PHP version
 */
require_once __DIR__ . '/../../layouts/auth.php';

// Start output buffering for the content
ob_start();
?>
<form method="POST" action="/auth/password/email">
    <input type="hidden" name="token" value="<?php echo generate_csrf_token(); ?>">
    <div class="form-group">
        <label for="email">Email Address</label>
        <input id="email" type="email" name="email" required autofocus>
    </div>
    <button type="submit">Send Password Reset Link</button>
</form>
<?php
$content = ob_get_clean();

// Render the page using the layout
render_auth_layout('Password Reset', $content);
