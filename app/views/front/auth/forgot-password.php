<?php
/**
 * Frontend Forgot Password — uses active theme layout
 */
$theme = function_exists('get_active_theme') ? get_active_theme() : '';
$layoutFile = $theme ? (defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__, 4)) . '/themes/' . $theme . '/layout.php' : '';
$title = 'Forgot Password';
$page = ['title' => $title, 'slug' => 'forgot-password', 'meta_description' => 'Reset your password'];

if ($layoutFile && file_exists($layoutFile)) {
    ob_start();
    ?>
    <section class="auth-section" style="max-width:460px;margin:60px auto;padding:0 20px;">
        <div class="auth-card" style="background:var(--surface,#fff);border:1px solid var(--border,#e2e8f0);border-radius:12px;padding:32px;">
            <div style="text-align:center;margin-bottom:24px;">
                <div style="font-size:2.5rem;margin-bottom:8px;">🔑</div>
                <h1 style="font-size:1.5rem;margin:0 0 4px;">Forgot Password</h1>
                <p style="color:var(--text-secondary,#64748b);margin:0;font-size:0.9rem;">Enter your email and we'll send you a reset link.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;"><?= h($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div style="background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;"><?= h($success) ?></div>
            <?php endif; ?>

            <form method="post" action="/forgot-password">
                <?= csrf_field() ?>
                <div style="margin-bottom:20px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">Email Address</label>
                    <input type="email" name="email" required autofocus placeholder="you@example.com"
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                </div>
                <button type="submit" style="width:100%;padding:12px;background:var(--accent,#6366f1);color:#fff;border:none;border-radius:6px;font-size:1rem;font-weight:600;cursor:pointer;">
                    Send Reset Link
                </button>
            </form>
            <p style="text-align:center;margin-top:20px;font-size:0.9rem;color:var(--text-secondary,#64748b);">
                Remember your password? <a href="/login" style="color:var(--accent,#6366f1);text-decoration:none;">Sign in</a>
            </p>
        </div>
    </section>
    <?php
    $content = ob_get_clean();
    require $layoutFile;
} else {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Forgot Password</title></head>';
    echo '<body style="font-family:system-ui;text-align:center;padding:60px 20px;"><h1>Forgot Password</h1>';
    echo '<p>Please activate a theme for the full experience.</p></body></html>';
}
