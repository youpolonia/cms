<?php 
require_once __DIR__.'/../../layouts/auth.php';


?><form method="POST" action="/auth/password/reset">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
    <div class="form-group">
        <label for="email">Email Address</label>
        <input id="email" type="email" name="email"
 required>
?>    </div>

    <div class="form-group">
        <label for="password">New Password</label>
        <input id="password" type="password" name="password"
 required>
?>    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation"
 required>
?>    </div>

    <button type="submit">Reset Password</button>
</form>
