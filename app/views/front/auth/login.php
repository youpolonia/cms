<?php
/**
 * User Login Page — uses active theme layout
 */
$theme = function_exists('get_active_theme') ? get_active_theme() : '';
$layoutFile = $theme ? (defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__, 4)) . '/themes/' . $theme . '/layout.php' : '';
$title = 'Sign In';
$page = ['title' => $title, 'slug' => 'login', 'meta_description' => 'Sign in to your account'];

if ($layoutFile && file_exists($layoutFile)) {
    ob_start();
    ?>
    <section class="auth-section" style="max-width:460px;margin:60px auto;padding:0 20px;">
        <div class="auth-card" style="background:var(--surface,#fff);border:1px solid var(--border,#e2e8f0);border-radius:12px;padding:32px;">
            <h1 style="font-size:1.5rem;margin:0 0 4px;text-align:center;">Sign In</h1>
            <p style="text-align:center;color:var(--text-secondary,#64748b);margin:0 0 24px;font-size:0.9rem;">Welcome back!</p>

            <?php if (!empty($error)): ?>
                <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;">
                    <?= h($error) ?>
                    <?php $pendingEmail = \Core\Session::getFlash('pending_email'); if ($pendingEmail): ?>
                        <form method="post" action="/resend-verification" style="margin-top:8px;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="email" value="<?= h($pendingEmail) ?>">
                            <button type="submit" style="background:none;border:none;color:#6366f1;cursor:pointer;text-decoration:underline;font-size:inherit;padding:0;">Resend verification email</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div style="background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;"><?= h($success) ?></div>
            <?php endif; ?>

            <form method="post" action="/login">
                <?= csrf_field() ?>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">Email</label>
                    <input type="email" name="email" required autofocus
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">Password</label>
                    <input type="password" name="password" required
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                </div>
                <button type="submit" style="width:100%;padding:12px;background:var(--accent,#6366f1);color:#fff;border:none;border-radius:6px;font-size:1rem;font-weight:600;cursor:pointer;">
                    Sign In
                </button>
            </form>
            <p style="text-align:center;margin-top:12px;font-size:0.875rem;"><a href="/forgot-password" style="color:var(--accent,#6366f1);text-decoration:none;">Forgot your password?</a></p>
            <p style="text-align:center;margin-top:20px;font-size:0.9rem;color:var(--text-secondary,#64748b);">
                Don't have an account? <a href="/register" style="color:var(--accent,#6366f1);text-decoration:none;">Create one</a>
            </p>
        </div>
    </section>
    <?php
    $content = ob_get_clean();
    require $layoutFile;
} else {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login</title></head>';
    echo '<body style="font-family:system-ui;text-align:center;padding:60px 20px;"><h1>Login</h1>';
    echo '<p>Please activate a theme for the full login experience.</p></body></html>';
}
