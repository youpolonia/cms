<?php
/**
 * User Account Dashboard — uses active theme layout
 */
$theme = function_exists('get_active_theme') ? get_active_theme() : '';
$layoutFile = $theme ? (defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__, 4)) . '/themes/' . $theme . '/layout.php' : '';
$title = 'My Account';
$page = ['title' => $title, 'slug' => 'account', 'meta_description' => 'Your account dashboard'];
$user = $user ?? [];
$stats = $stats ?? [];
?>
<?php if ($layoutFile && file_exists($layoutFile)): ?>
<?php ob_start(); ?>
<section class="account-section" style="max-width:800px;margin:40px auto;padding:0 20px;">

    <h1 style="font-size:1.75rem;margin:0 0 24px;">My Account</h1>

    <?php if (!empty($success)): ?>
        <div style="background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;"><?= h($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:32px;">
        <?php
        $cards = [
            ['label' => 'Orders', 'value' => $stats['orders'] ?? 0, 'icon' => '🛒', 'link' => '/account/orders'],
            ['label' => 'Bookings', 'value' => $stats['bookings'] ?? 0, 'icon' => '📅', 'link' => '/booking'],
            ['label' => 'Memberships', 'value' => $stats['memberships'] ?? 0, 'icon' => '⭐', 'link' => '/membership/portal'],
            ['label' => 'Courses', 'value' => $stats['enrollments'] ?? 0, 'icon' => '🎓', 'link' => '/courses'],
        ];
        foreach ($cards as $card):
        ?>
        <a href="<?= $card['link'] ?>" style="display:block;background:var(--surface,#fff);border:1px solid var(--border,#e2e8f0);border-radius:10px;padding:20px;text-decoration:none;color:inherit;transition:box-shadow 0.2s;">
            <div style="font-size:1.5rem;margin-bottom:4px;"><?= $card['icon'] ?></div>
            <div style="font-size:1.75rem;font-weight:700;"><?= $card['value'] ?></div>
            <div style="font-size:0.85rem;color:var(--text-secondary,#64748b);"><?= $card['label'] ?></div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Profile Form -->
    <div style="background:var(--surface,#fff);border:1px solid var(--border,#e2e8f0);border-radius:10px;padding:24px;margin-bottom:24px;">
        <h2 style="font-size:1.2rem;margin:0 0 16px;">Profile</h2>
        <form method="post" action="/account/update">
            <?= csrf_field() ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.85rem;">Username</label>
                    <input type="text" name="name" value="<?= h($user['username'] ?? '') ?>" required
                           style="width:100%;padding:10px;border:1px solid var(--border,#d1d5db);border-radius:6px;">
                </div>
                <div>
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.85rem;">Email</label>
                    <input type="email" name="email" value="<?= h($user['email'] ?? '') ?>" required
                           style="width:100%;padding:10px;border:1px solid var(--border,#d1d5db);border-radius:6px;">
                </div>
            </div>
            <div style="margin-top:12px;font-size:0.85rem;color:var(--text-secondary,#94a3b8);">
                Member since <?= date('F Y', strtotime($user['created_at'] ?? 'now')) ?> · Role: <?= h(ucfirst($user['role'] ?? 'user')) ?>
            </div>
            <button type="submit" style="margin-top:16px;padding:10px 24px;background:var(--accent,#6366f1);color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:500;">
                Save Changes
            </button>
        </form>
    </div>

    <!-- Change Password -->
    <div style="background:var(--surface,#fff);border:1px solid var(--border,#e2e8f0);border-radius:10px;padding:24px;margin-bottom:24px;">
        <h2 style="font-size:1.2rem;margin:0 0 16px;">Change Password</h2>
        <form method="post" action="/account/password">
            <?= csrf_field() ?>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div>
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.85rem;">Current Password</label>
                    <input type="password" name="current_password" required
                           style="width:100%;padding:10px;border:1px solid var(--border,#d1d5db);border-radius:6px;">
                </div>
                <div>
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.85rem;">New Password</label>
                    <input type="password" name="new_password" required minlength="8"
                           style="width:100%;padding:10px;border:1px solid var(--border,#d1d5db);border-radius:6px;">
                </div>
                <div>
                    <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.85rem;">Confirm New</label>
                    <input type="password" name="new_password_confirm" required minlength="8"
                           style="width:100%;padding:10px;border:1px solid var(--border,#d1d5db);border-radius:6px;">
                </div>
            </div>
            <button type="submit" style="margin-top:16px;padding:10px 24px;background:#ef4444;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:500;">
                Change Password
            </button>
        </form>
    </div>

    <!-- Logout -->
    <div style="text-align:center;padding:20px 0;">
        <a href="/logout" style="color:#ef4444;text-decoration:none;font-size:0.9rem;">Sign Out →</a>
    </div>

</section>
<?php
$content = ob_get_clean();
require $layoutFile;
?>
<?php else: ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Account</title></head>
<body style="font-family:system-ui;max-width:600px;margin:40px auto;padding:20px;">
<h1>My Account</h1>
<p>Username: <?= h($user['username'] ?? '') ?></p>
<p>Email: <?= h($user['email'] ?? '') ?></p>
<p><a href="/logout">Sign Out</a></p>
</body></html>
<?php endif; ?>
