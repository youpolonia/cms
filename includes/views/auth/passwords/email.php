require_once __DIR__.'/../../layouts/auth.php';


?><form method="POST" action="/auth/password/email">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <div class="form-group">
        <label for="email">Email Address</label>
        <input id="email" type="email" name="email"
 required autofocus>
?>    </div>
    <button type="submit">Send Password Reset Link</button>
</form>
