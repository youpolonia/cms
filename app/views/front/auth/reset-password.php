<?php
/**
 * Frontend Reset Password — uses active theme layout
 */
$theme = function_exists('get_active_theme') ? get_active_theme() : '';
$layoutFile = $theme ? (defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__, 4)) . '/themes/' . $theme . '/layout.php' : '';
$title = 'Reset Password';
$page = ['title' => $title, 'slug' => 'reset-password', 'meta_description' => 'Set a new password'];

if ($layoutFile && file_exists($layoutFile)) {
    ob_start();
    ?>
    <section class="auth-section" style="max-width:460px;margin:60px auto;padding:0 20px;">
        <div class="auth-card" style="background:var(--surface,#fff);border:1px solid var(--border,#e2e8f0);border-radius:12px;padding:32px;">
            <div style="text-align:center;margin-bottom:24px;">
                <div style="font-size:2.5rem;margin-bottom:8px;">🔒</div>
                <h1 style="font-size:1.5rem;margin:0 0 4px;">Set New Password</h1>
                <p style="color:var(--text-secondary,#64748b);margin:0;font-size:0.9rem;">Choose a strong password for your account.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="post" action="/reset-password">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= h($token ?? '') ?>">

                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">New Password</label>
                    <input type="password" name="password" required autofocus minlength="8"
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                    <p style="font-size:0.8rem;color:var(--text-secondary,#94a3b8);margin:4px 0 0;">Minimum 8 characters</p>
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">Confirm Password</label>
                    <input type="password" name="password_confirm" required minlength="8"
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                </div>
                <button type="submit" style="width:100%;padding:12px;background:var(--accent,#6366f1);color:#fff;border:none;border-radius:6px;font-size:1rem;font-weight:600;cursor:pointer;">
                    Reset Password
                </button>
            </form>
            <p style="text-align:center;margin-top:20px;font-size:0.9rem;color:var(--text-secondary,#64748b);">
                <a href="/login" style="color:var(--accent,#6366f1);text-decoration:none;">← Back to Sign In</a>
            </p>
        </div>
    </section>
    <?php
    $content = ob_get_clean();
    require $layoutFile;
} else {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset Password</title></head>';
    echo '<body style="font-family:system-ui;text-align:center;padding:60px 20px;"><h1>Reset Password</h1>';
    echo '<p>Please activate a theme for the full experience.</p></body></html>';
}
