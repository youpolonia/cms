<?php
/**
 * User Registration Page — uses active theme layout if available
 */
$theme = function_exists('get_active_theme') ? get_active_theme() : '';
$layoutFile = $theme ? (defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__, 4)) . '/themes/' . $theme . '/layout.php' : '';
$title = 'Create Account';
$page = ['title' => $title, 'slug' => 'register', 'meta_description' => 'Create your account'];
$old = $old ?? [];

if ($layoutFile && file_exists($layoutFile)) {
    ob_start();
    ?>
    <section class="auth-section" style="max-width:460px;margin:60px auto;padding:0 20px;">
        <div class="auth-card" style="background:var(--surface,#fff);border:1px solid var(--border,#e2e8f0);border-radius:12px;padding:32px;">
            <h1 style="font-size:1.5rem;margin:0 0 4px;text-align:center;">Create Account</h1>
            <p style="text-align:center;color:var(--text-secondary,#64748b);margin:0 0 24px;font-size:0.9rem;">Join us today — it's free!</p>

            <?php if (!empty($error)): ?>
                <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="post" action="/register">
                <?= csrf_field() ?>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">Username</label>
                    <input type="text" name="name" value="<?= h($old['name'] ?? '') ?>" required autofocus
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">Email</label>
                    <input type="email" name="email" value="<?= h($old['email'] ?? '') ?>" required
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">Password</label>
                    <input type="password" name="password" required minlength="8"
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                    <span style="font-size:0.8rem;color:var(--text-secondary,#94a3b8);">Minimum 8 characters</span>
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">Confirm Password</label>
                    <input type="password" name="password_confirm" required minlength="8"
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                </div>
                <button type="submit" style="width:100%;padding:12px;background:var(--accent,#6366f1);color:#fff;border:none;border-radius:6px;font-size:1rem;font-weight:600;cursor:pointer;">
                    Create Account
                </button>
            </form>
            <p style="text-align:center;margin-top:20px;font-size:0.9rem;color:var(--text-secondary,#64748b);">
                Already have an account? <a href="/login" style="color:var(--accent,#6366f1);text-decoration:none;">Sign In</a>
            </p>
        </div>
    </section>
    <?php
    $content = ob_get_clean();
    require $layoutFile;
} else {
    // Minimal fallback
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Register</title></head>';
    echo '<body style="font-family:system-ui;text-align:center;padding:60px 20px;"><h1>Registration</h1>';
    echo '<p>Please activate a theme for the full registration experience.</p></body></html>';
}
