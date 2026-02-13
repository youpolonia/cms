<?php $title = 'White Label'; ob_start(); ?>
<style>
.wl-page{max-width:700px;margin:0 auto;padding:2rem}
.wl-header h1{font-size:1.5rem;font-weight:700;color:var(--text-primary);margin-bottom:.5rem}
.wl-desc{color:var(--text-muted);font-size:.875rem;margin-bottom:2rem;line-height:1.6}

.wl-card{background:var(--bg-secondary);border:1px solid var(--border);border-radius:10px;padding:1.5rem;margin-bottom:1.5rem}
.wl-card h3{font-size:1rem;font-weight:600;color:var(--text-primary);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}

.wl-field{margin-bottom:1.25rem}
.wl-field label{display:block;font-size:.8rem;font-weight:500;color:var(--text-secondary);margin-bottom:.35rem}
.wl-field input[type=text],.wl-field textarea{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;background:var(--bg-primary);color:var(--text-primary);font-size:.875rem;font-family:inherit}
.wl-field input:focus,.wl-field textarea:focus{outline:none;border-color:var(--accent)}
.wl-field .hint{font-size:.7rem;color:var(--text-muted);margin-top:.25rem}
.wl-field input[type=color]{width:50px;height:34px;padding:2px;border:1px solid var(--border);border-radius:6px;cursor:pointer;background:var(--bg-primary)}

.wl-logo-box{display:flex;align-items:center;gap:1rem;padding:1rem;background:var(--bg-tertiary);border-radius:8px;margin-bottom:.5rem}
.wl-logo-box img{max-height:40px;max-width:200px}
.wl-logo-box .placeholder{color:var(--text-muted);font-size:.85rem}

.wl-preview{background:var(--bg-tertiary);border-radius:8px;padding:1rem;margin-top:1rem}
.wl-preview-label{font-size:.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem}
.wl-preview-bar{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px;color:white;font-weight:600;font-size:.9rem}

.wl-switch{display:flex;align-items:center;gap:.75rem;cursor:pointer}
.wl-switch input{display:none}
.wl-switch .track{width:40px;height:22px;background:var(--border);border-radius:11px;position:relative;transition:background .2s}
.wl-switch input:checked+.track{background:var(--accent)}
.wl-switch .track::after{content:"";position:absolute;top:2px;left:2px;width:18px;height:18px;background:white;border-radius:50%;transition:transform .2s}
.wl-switch input:checked+.track::after{transform:translateX(18px)}
.wl-switch span{font-size:.85rem;color:var(--text-primary)}
</style>

<div class="wl-page">
    <div class="wl-header">
        <h1>🏷️ White Label</h1>
    </div>
    <p class="wl-desc">
        Customize the admin panel branding. Replace the CMS name, logo, colors, and footer text with your own brand.
        Perfect for agencies and resellers who want to offer the CMS under their own brand.
    </p>

    <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success" style="margin-bottom:1rem"><?= esc($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); endif; ?>

    <form method="POST" action="/admin/white-label/save" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= $csrfToken ?>">

        <div class="wl-card">
            <h3>🎨 Admin Panel Branding</h3>

            <div class="wl-field">
                <label>Admin Panel Name</label>
                <input type="text" name="wl_admin_name" value="<?= esc($settings['wl_admin_name'] ?? 'Jessie AI-CMS') ?>" placeholder="Your Brand Name">
                <div class="hint">Shown in the sidebar header and browser title</div>
            </div>

            <div class="wl-field">
                <label>Sidebar Icon (Emoji)</label>
                <input type="text" name="wl_admin_icon" value="<?= esc($settings['wl_admin_icon'] ?? '🤖') ?>" placeholder="🤖" style="width:80px">
                <div class="hint">Single emoji shown next to your brand name in the sidebar</div>
            </div>

            <div class="wl-field">
                <label>Logo (optional — replaces icon + name)</label>
                <div class="wl-logo-box">
                    <?php if (!empty($settings['wl_admin_logo'])): ?>
                    <img src="<?= esc($settings['wl_admin_logo']) ?>" alt="Logo">
                    <label><input type="checkbox" name="clear_logo" value="1"> Remove logo</label>
                    <?php else: ?>
                    <span class="placeholder">No logo uploaded — using icon + name</span>
                    <?php endif; ?>
                </div>
                <input type="file" name="wl_admin_logo" accept="image/*" style="font-size:.8rem">
                <div class="hint">Recommended: 200×60px, PNG or SVG with transparent background</div>
            </div>

            <div class="wl-field">
                <label>Accent Color</label>
                <div style="display:flex;align-items:center;gap:.75rem">
                    <input type="color" name="wl_admin_accent" value="<?= esc($settings['wl_admin_accent'] ?? '#6366f1') ?>" id="wl-accent">
                    <span id="wl-accent-hex" style="font-family:monospace;font-size:.8rem;color:var(--text-muted)"><?= esc($settings['wl_admin_accent'] ?? '#6366f1') ?></span>
                </div>
                <div class="hint">Primary color for buttons, links, and active states</div>
            </div>

            <div class="wl-preview">
                <div class="wl-preview-label">Preview</div>
                <div class="wl-preview-bar" id="wl-preview-bar" style="background:<?= esc($settings['wl_admin_accent'] ?? '#6366f1') ?>">
                    <span id="wl-preview-icon"><?= esc($settings['wl_admin_icon'] ?? '🤖') ?></span>
                    <span id="wl-preview-name"><?= esc($settings['wl_admin_name'] ?? 'Jessie AI-CMS') ?></span>
                </div>
            </div>
        </div>

        <div class="wl-card">
            <h3>🔐 Login Page</h3>

            <div class="wl-field">
                <label>Login Title</label>
                <input type="text" name="wl_login_title" value="<?= esc($settings['wl_login_title'] ?? 'Welcome Back') ?>" placeholder="Welcome Back">
            </div>

            <div class="wl-field">
                <label>Login Subtitle</label>
                <input type="text" name="wl_login_subtitle" value="<?= esc($settings['wl_login_subtitle'] ?? 'Sign in to your dashboard') ?>" placeholder="Sign in to your dashboard">
            </div>
        </div>

        <div class="wl-card">
            <h3>📝 Footer & Attribution</h3>

            <div class="wl-field">
                <label>Admin Footer Text</label>
                <input type="text" name="wl_admin_footer" value="<?= esc($settings['wl_admin_footer'] ?? 'Powered by Jessie AI-CMS') ?>" placeholder="Powered by Your Brand">
                <div class="hint">Shown at the bottom of admin pages. Leave empty to hide.</div>
            </div>

            <div class="wl-field">
                <label class="wl-switch">
                    <input type="checkbox" name="wl_hide_branding" value="1" <?= ($settings['wl_hide_branding'] ?? '0') === '1' ? 'checked' : '' ?>>
                    <span class="track"></span>
                    <span>Hide "Jessie AI-CMS" branding completely</span>
                </label>
                <div class="hint" style="margin-top:.35rem">Removes all default CMS branding from the admin panel</div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">💾 Save White Label Settings</button>
    </form>
</div>

<script>
const accent = document.getElementById('wl-accent');
const hex = document.getElementById('wl-accent-hex');
const bar = document.getElementById('wl-preview-bar');
accent.addEventListener('input', () => { hex.textContent = accent.value; bar.style.background = accent.value; });

document.querySelector('[name=wl_admin_name]').addEventListener('input', e => {
    document.getElementById('wl-preview-name').textContent = e.target.value || 'Your Brand';
});
document.querySelector('[name=wl_admin_icon]').addEventListener('input', e => {
    document.getElementById('wl-preview-icon').textContent = e.target.value || '🤖';
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
?>
