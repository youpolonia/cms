ob_start();
?><div class="container">
    <h2>Reset Password</h2>
    <form id="passwordResetForm" action="/auth/request-password-reset" method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
 required class="form-control">
?>        </div>
        <button type="submit" class="btn btn-primary">Request Reset Link</button>
    </form>
    <div id="responseMessage" class="mt-3"></div>
</div>

<script>
document.getElementById('passwordResetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
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
<div class="alert alert-success">Reset link sent to your email</div>';
        }
 else {
            messageEl.innerHTML = '
<div class="alert alert-danger">Error: ' + (result.error || 'Failed to send reset link') + '</div>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
});
?></script>
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
render_layout('Password Reset Request', $content);
