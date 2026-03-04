<?php
/**
 * Account Deletion Confirmation — GDPR right to erasure
 */
$theme = function_exists('get_active_theme') ? get_active_theme() : '';
$layoutFile = $theme ? (defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__, 4)) . '/themes/' . $theme . '/layout.php' : '';
$title = 'Delete Account';
$page = ['title' => $title, 'slug' => 'delete-account', 'meta_description' => 'Delete your account'];

if ($layoutFile && file_exists($layoutFile)) {
    ob_start();
    ?>
    <section class="auth-section" style="max-width:460px;margin:60px auto;padding:0 20px;">
        <div class="auth-card" style="background:var(--surface,#fff);border:1px solid var(--border,#e2e8f0);border-radius:12px;padding:32px;">
            <div style="text-align:center;margin-bottom:24px;">
                <div style="font-size:2.5rem;margin-bottom:8px;">⚠️</div>
                <h1 style="font-size:1.5rem;margin:0 0 4px;color:#dc2626;">Delete Your Account</h1>
                <p style="color:var(--text-secondary,#64748b);margin:0;font-size:0.9rem;">This action is permanent and cannot be undone.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;"><?= h($error) ?></div>
            <?php endif; ?>

            <div style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:12px 14px;border-radius:6px;margin-bottom:20px;font-size:0.875rem;">
                <strong>What happens when you delete your account:</strong>
                <ul style="margin:8px 0 0;padding-left:18px;">
                    <li>Your personal data will be anonymized</li>
                    <li>Your orders and purchase history will remain for legal purposes</li>
                    <li>You won't be able to log in again</li>
                    <li>This cannot be reversed</li>
                </ul>
            </div>

            <form method="post" action="/account/delete">
                <?= csrf_field() ?>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">Confirm Password</label>
                    <input type="password" name="password" required
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.875rem;">Type <strong>DELETE</strong> to confirm</label>
                    <input type="text" name="confirm_delete" required placeholder="DELETE" autocomplete="off"
                           style="width:100%;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:1rem;">
                </div>
                <button type="submit" style="width:100%;padding:12px;background:#dc2626;color:#fff;border:none;border-radius:6px;font-size:1rem;font-weight:600;cursor:pointer;">
                    Permanently Delete My Account
                </button>
            </form>
            <p style="text-align:center;margin-top:20px;font-size:0.9rem;">
                <a href="/account" style="color:var(--accent,#6366f1);text-decoration:none;">← Back to Account</a>
            </p>
        </div>
    </section>
    <?php
    $content = ob_get_clean();
    require $layoutFile;
} else {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Delete Account</title></head>';
    echo '<body style="font-family:system-ui;text-align:center;padding:60px 20px;"><h1>Delete Account</h1>';
    echo '<p>Please activate a theme for the full experience.</p></body></html>';
}
