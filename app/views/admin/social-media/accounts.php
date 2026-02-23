<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$title = $title ?? 'Social Accounts';
ob_start();

$successMsg = $_GET['success'] ?? '';
$errorMsg = $_GET['error'] ?? '';
?>

<style>
.sa-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.sa-card {
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    transition: border-color 0.2s;
}
.sa-card:hover { border-color: var(--accent); }
.sa-card-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
.sa-card-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
}
.sa-icon-twitter { background: rgba(29,155,240,0.15); }
.sa-icon-linkedin { background: rgba(0,119,181,0.15); }
.sa-icon-facebook { background: rgba(24,119,242,0.15); }
.sa-icon-instagram { background: linear-gradient(135deg, rgba(253,29,29,0.15), rgba(131,58,180,0.15)); }

.sa-card-title { font-size: 1.1rem; font-weight: 600; color: var(--text-primary); }
.sa-card-subtitle { font-size: 0.85rem; color: var(--text-secondary); }

.sa-status {
    display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.8rem;
    border-radius: 9999px; font-size: 0.8rem; font-weight: 600;
}
.sa-status-connected { background: rgba(16,185,129,0.15); color: var(--success); }
.sa-status-disconnected { background: rgba(148,163,184,0.15); color: var(--text-muted); }

.sa-card-body { margin: 1rem 0; }
.sa-card-body p { margin: 0.3rem 0; font-size: 0.85rem; color: var(--text-secondary); }

.sa-card-actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
.sa-btn {
    padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--border);
    background: var(--bg-secondary); color: var(--text-primary); font-size: 0.85rem;
    cursor: pointer; text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.3rem;
}
.sa-btn:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
.sa-btn-danger:hover { background: var(--danger); color: #fff; border-color: var(--danger); }
.sa-btn-primary { background: var(--accent); color: #fff; border-color: var(--accent); }
.sa-btn-primary:hover { background: var(--accent-hover); }

.sa-alert {
    padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;
}
.sa-alert-success { background: rgba(16,185,129,0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
.sa-alert-error { background: rgba(239,68,68,0.15); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }

.sa-info-box {
    background: var(--bg-tertiary); border: 1px solid var(--border); border-radius: 12px;
    padding: 1.5rem; margin-bottom: 2rem;
}
.sa-info-box h3 { margin-top: 0; color: var(--text-primary); }
.sa-info-box p { color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6; }
.sa-info-box code {
    background: var(--bg-primary); padding: 0.15rem 0.4rem; border-radius: 4px;
    font-size: 0.85rem; color: var(--accent);
}
</style>

<div style="max-width:1000px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h1 style="margin:0; color:var(--text-primary);">🔗 Social Accounts</h1>
        <a href="/admin/social-media" class="sa-btn" style="padding:0.5rem 1rem;">← Back to Dashboard</a>
    </div>

    <?php if ($successMsg !== ''): ?>
        <div class="sa-alert sa-alert-success">✅ <?= h($successMsg) ?></div>
    <?php endif; ?>
    <?php if ($errorMsg !== ''): ?>
        <div class="sa-alert sa-alert-error">❌ <?= h($errorMsg) ?></div>
    <?php endif; ?>

    <div class="sa-grid">
        <?php
        $platformInfo = [
            'twitter' => [
                'name' => 'Twitter / X',
                'icon' => '𝕏',
                'iconClass' => 'sa-icon-twitter',
                'desc' => 'Post tweets via X API v2. Requires OAuth 2.0 app credentials.',
                'docs' => 'developer.twitter.com',
            ],
            'linkedin' => [
                'name' => 'LinkedIn',
                'icon' => '💼',
                'iconClass' => 'sa-icon-linkedin',
                'desc' => 'Publish professional posts. Requires LinkedIn app with w_member_social scope.',
                'docs' => 'developer.linkedin.com',
            ],
            'facebook' => [
                'name' => 'Facebook',
                'icon' => '📘',
                'iconClass' => 'sa-icon-facebook',
                'desc' => 'Post to Facebook Pages via Graph API. Requires Facebook App with pages_manage_posts.',
                'docs' => 'developers.facebook.com',
            ],
            'instagram' => [
                'name' => 'Instagram',
                'icon' => '📸',
                'iconClass' => 'sa-icon-instagram',
                'desc' => 'Publish to Instagram via Facebook Graph API. Requires connected Business/Creator account.',
                'docs' => 'developers.facebook.com/docs/instagram-api',
            ],
        ];

        foreach ($platformInfo as $platform => $info):
            $acc = $accountMap[$platform] ?? null;
            $connected = $acc && $acc['active'];
        ?>
        <div class="sa-card">
            <div class="sa-card-header">
                <div class="sa-card-icon <?= $info['iconClass'] ?>"><?= $info['icon'] ?></div>
                <div>
                    <div class="sa-card-title"><?= h($info['name']) ?></div>
                    <div class="sa-card-subtitle">
                        <?php if ($connected): ?>
                            <span class="sa-status sa-status-connected">● Connected</span>
                        <?php else: ?>
                            <span class="sa-status sa-status-disconnected">○ Not Connected</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="sa-card-body">
                <p><?= h($info['desc']) ?></p>
                <?php if ($connected): ?>
                    <p><strong>Account:</strong> <?= h($acc['account_name'] ?? 'Unknown') ?></p>
                    <?php if (!empty($acc['token_expires'])): ?>
                        <p><strong>Token expires:</strong> <?= h($acc['token_expires']) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="sa-card-actions">
                <?php if ($connected): ?>
                    <a href="/admin/social-media/connect/<?= h($platform) ?>" class="sa-btn">🔄 Reconnect</a>
                    <button class="sa-btn sa-btn-danger" onclick="disconnectPlatform('<?= h($platform) ?>')">🔌 Disconnect</button>
                <?php else: ?>
                    <a href="/admin/social-media/connect/<?= h($platform) ?>" class="sa-btn sa-btn-primary">🔗 Connect</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Setup Info -->
    <div class="sa-info-box">
        <h3>🔧 Setup Instructions</h3>
        <p>Before connecting platforms, you need to configure API credentials. For each platform:</p>
        <ol style="color:var(--text-secondary); line-height:2; font-size:0.9rem;">
            <li>Create a developer app on the platform's developer portal</li>
            <li>Set the OAuth callback URL to: <code><?= h((!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'yourdomain.com') . '/admin/social-media/callback/{platform}') ?></code></li>
            <li>Add your <code>client_id</code> and <code>client_secret</code> to the platform's account meta via the database:<br>
                <code>UPDATE social_accounts SET meta = '{"client_id":"YOUR_ID","client_secret":"YOUR_SECRET"}' WHERE platform = 'twitter';</code>
            </li>
            <li>Click "Connect" to start the OAuth flow</li>
        </ol>
        <p style="margin-top:1rem;"><strong>Tip:</strong> Create the account row first with INSERT if it doesn't exist yet:<br>
            <code>INSERT INTO social_accounts (platform, account_name, meta) VALUES ('twitter', 'My Twitter', '{"client_id":"...","client_secret":"..."}')</code>
        </p>
    </div>
</div>

<script>
function disconnectPlatform(platform) {
    if (!confirm('Disconnect ' + platform + '? You will need to reconnect to publish.')) return;

    // Simple approach: just redirect to remove the account via an AJAX call
    const form = new FormData();
    form.append('csrf_token', '<?= function_exists("csrf_token") ? csrf_token() : "" ?>');
    form.append('platform', platform);
    form.append('action', 'disconnect');

    fetch('/admin/social-media/settings', { method: 'POST', body: form })
        .then(() => location.reload())
        .catch(() => location.reload());
}
</script>

<?php
$content = ob_get_clean();
require_once CMS_APP . '/views/admin/layouts/topbar.php';
