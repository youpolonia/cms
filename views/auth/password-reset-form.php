ob_start();
?><div class="container">
    <h2>Set New Password</h2>
    <form id="passwordResetForm" action="/auth/reset-password" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password"
 required class="form-control" minlength="8">
?>        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
 required class="form-control">
?>        </div>
        
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
    <div id="responseMessage" class="mt-3"></div>
</div>

<script>
document.getElementById('passwordResetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (document.getElementById('password').value !==
        document.getElementById('confirm_password').value) {
        document.getElementById('responseMessage').innerHTML =
            '
<div class="alert alert-danger">Passwords do not match</div>';
        return;
    }

    const formData = new FormData(e.target);
    
    try {
        const response = await fetch(e.target.action, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        const messageEl = document.getElementById('responseMessage');
        if (result.success) {
            messageEl.innerHTML = '
<div class="alert alert-success">Password updated successfully</div>';
            setTimeout(() => window.location.href = '/login', 2000);
        } else {
            messageEl.innerHTML = '
<div class="alert alert-danger">Error: ' +
                (result.error || 'Failed to reset password') + '</div>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
});
?></script>
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
render_layout('Password Reset', $content);
